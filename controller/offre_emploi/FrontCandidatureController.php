<?php

declare(strict_types=1);

class CandidatureController
{
    private PDO $pdo;
    private array $recaptchaConfig;
    private CvMatchService $cvMatchService;

    private function getCvUploadDir(): string
    {
        return __DIR__ . '/../../uploads/cv';
    }

    private function isPersonName(string $value): bool
    {
        return preg_match('/^(?=.*\p{L})[\p{L}\s\-\']{2,60}$/u', $value) === 1;
    }

    private function isHttpUrl(string $value): bool
    {
        if (!filter_var($value, FILTER_VALIDATE_URL)) {
            return false;
        }

        $scheme = strtolower((string) parse_url($value, PHP_URL_SCHEME));
        return in_array($scheme, ['http', 'https'], true);
    }

    private function sanitizeUploadFileName(string $name): string
    {
        $name = pathinfo($name, PATHINFO_FILENAME);
        $name = preg_replace('/[^A-Za-z0-9._-]+/', '_', $name) ?? 'cv';
        $name = trim($name, '._-');

        return $name !== '' ? $name : 'cv';
    }

    private function handleCvUpload(array &$fieldErrors): ?array
    {
        if (!isset($_FILES['cvfile']) || !is_array($_FILES['cvfile'])) {
            return null;
        }

        $file = $_FILES['cvfile'];
        $uploadError = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);

        if ($uploadError === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if ($uploadError !== UPLOAD_ERR_OK) {
            $fieldErrors['cvurl'] = 'Le telechargement du CV a echoue. Veuillez reessayer.';
            $fieldErrors['cvfile'] = 'Le telechargement du CV a echoue. Veuillez reessayer.';
            return null;
        }

        $tmpName = (string) ($file['tmp_name'] ?? '');
        $originalName = (string) ($file['name'] ?? 'cv');
        $size = (int) ($file['size'] ?? 0);

        if (!is_uploaded_file($tmpName)) {
            $fieldErrors['cvurl'] = 'Le fichier CV est invalide.';
            $fieldErrors['cvfile'] = 'Le fichier CV est invalide.';
            return null;
        }

        if ($size <= 0 || $size > 5242880) {
            $fieldErrors['cvurl'] = 'Le CV doit peser moins de 5 Mo.';
            $fieldErrors['cvfile'] = 'Le CV doit peser moins de 5 Mo.';
            return null;
        }

        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        if (!in_array($extension, ['pdf', 'doc', 'docx'], true)) {
            $fieldErrors['cvurl'] = 'Le CV doit etre au format PDF, DOC ou DOCX.';
            $fieldErrors['cvfile'] = 'Le CV doit etre au format PDF, DOC ou DOCX.';
            return null;
        }

        $uploadDir = $this->getCvUploadDir();
        if (!is_dir($uploadDir) && !@mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
            $fieldErrors['cvurl'] = 'Impossible de creer le dossier de stockage des CV.';
            $fieldErrors['cvfile'] = 'Impossible de creer le dossier de stockage des CV.';
            return null;
        }

        $mimeType = null;
        if (class_exists('finfo')) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $detectedMime = $finfo->file($tmpName);
            $mimeType = is_string($detectedMime) ? $detectedMime : null;
        }

        $safeName = $this->sanitizeUploadFileName($originalName);

        try {
            $uniqueSuffix = bin2hex(random_bytes(6));
        } catch (Throwable) {
            $uniqueSuffix = uniqid('', true);
        }

        $storedFileName = sprintf('%s_%s.%s', date('YmdHis'), $uniqueSuffix, $extension);
        $destination = rtrim($uploadDir, '/\\') . DIRECTORY_SEPARATOR . $storedFileName;

        if (!move_uploaded_file($tmpName, $destination)) {
            $fieldErrors['cvurl'] = 'Impossible d\'enregistrer le CV telecharge.';
            $fieldErrors['cvfile'] = 'Impossible d\'enregistrer le CV telecharge.';
            return null;
        }

        return [
            'cv_external_url' => null,
            'cv_file_path' => 'uploads/cv/' . $storedFileName,
            'cv_original_name' => $safeName . '.' . $extension,
            'cv_mime' => $mimeType,
            'cv_size' => $size,
            'cv_source' => 'upload',
        ];
    }

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->recaptchaConfig = require __DIR__ . '/../../config/recaptcha.php';
        $this->cvMatchService = new CvMatchService();
    }

    private function isRecaptchaConfigured(): bool
    {
        return !empty($this->recaptchaConfig['enabled'])
            && !empty($this->recaptchaConfig['site_key'])
            && !empty($this->recaptchaConfig['secret_key'])
            && $this->recaptchaConfig['site_key'] !== 'your-site-key'
            && $this->recaptchaConfig['secret_key'] !== 'your-secret-key';
            
    }

    private function verifyRecaptchaToken(string $token): bool
    {
        if (!$this->isRecaptchaConfigured() || $token === '') {
            return false;
        }

        $payload = http_build_query([
            'secret' => (string) $this->recaptchaConfig['secret_key'],
            'response' => $token,
        ]);

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'content' => $payload,
                'timeout' => (int) ($this->recaptchaConfig['timeout'] ?? 5),
            ],
        ]);

        $verifyUrl = (string) ($this->recaptchaConfig['verify_url'] ?? 'https://www.google.com/recaptcha/api/siteverify');
        $response = @file_get_contents($verifyUrl, false, $context);
        if ($response === false) {
            return false;
        }

        $decoded = json_decode($response, true);
        return is_array($decoded) && !empty($decoded['success']);
    }

    private function getAllOffres(): array
    {
        $sql = 'SELECT * FROM offre_emploi ORDER BY date_creation DESC';
        $statement = $this->pdo->query($sql);

        return $statement ? $statement->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    private function getOffreById(int $id): ?array
    {
        $sql = 'SELECT * FROM offre_emploi WHERE id = :id LIMIT 1';
        $statement = $this->pdo->prepare($sql);
        $statement->execute(['id' => $id]);
        $offre = $statement->fetch(PDO::FETCH_ASSOC);

        return $offre ?: null;
    }

    private function createCandidature(array $data): int
    {
        $sql = 'INSERT INTO candidature
                    (nom, prenom, lettre_motivation, cv_external_url, cv_file_path, cv_original_name, cv_mime, cv_size, cv_source, email, statut, offre_id)
                VALUES
                    (:nom, :prenom, :lettre_motivation, :cv_external_url, :cv_file_path, :cv_original_name, :cv_mime, :cv_size, :cv_source, :email, :statut, :offre_id)';

        $statement = $this->pdo->prepare($sql);
        $statement->execute([
            'nom' => $data['nom'],
            'prenom' => $data['prenom'],
            'lettre_motivation' => $data['lettre_motivation'],
            'cv_external_url' => $data['cv_external_url'] ?? null,
            'cv_file_path' => $data['cv_file_path'] ?? null,
            'cv_original_name' => $data['cv_original_name'] ?? null,
            'cv_mime' => $data['cv_mime'] ?? null,
            'cv_size' => $data['cv_size'] ?? null,
            'cv_source' => $data['cv_source'] ?? 'url',
            'email' => $data['email'],
            'statut' => $data['statut'] ?? 'en_attente',
            'offre_id' => $data['offre_id'],
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    private function updateMatchAnalysis(int $id, array $analysis): bool
    {
        $sql = 'UPDATE candidature
                SET match_score = :match_score,
                    match_details = :match_details,
                    match_provider = :match_provider,
                    match_model = :match_model,
                    match_generated_at = :match_generated_at
                WHERE id = :id';

        $statement = $this->pdo->prepare($sql);

        return $statement->execute([
            'id' => $id,
            'match_score' => $analysis['match_score'] ?? null,
            'match_details' => json_encode($analysis['match_details'] ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'match_provider' => $analysis['provider'] ?? null,
            'match_model' => $analysis['model'] ?? null,
            'match_generated_at' => $analysis['generated_at'] ?? date('Y-m-d H:i:s'),
        ]);
    }

    private function getCandidatureById(int $id): ?array
    {
        $sql = 'SELECT
                    c.id,
                    c.nom,
                    c.prenom,
                    c.lettre_motivation,
                    CASE
                        WHEN c.cv_source = \'upload\' THEN c.cv_file_path
                        ELSE COALESCE(c.cv_external_url, c.cv_file_path)
                    END AS cvurl,
                    c.cv_external_url,
                    c.cv_file_path,
                    c.cv_original_name,
                    c.cv_mime,
                    c.cv_size,
                    c.cv_source,
                    c.match_score,
                    c.match_details,
                    c.match_provider,
                    c.match_model,
                    c.match_generated_at,
                    c.email,
                    c.statut,
                    c.date_candidature,
                    c.date_reponse,
                    c.offre_id,
                    o.titre AS offre_titre,
                    o.description AS offre_description,
                    o.lieu AS offre_lieu,
                    o.type_contrat AS offre_typecontrat,
                    o.date_limite AS offre_datelimite
                FROM candidature c
                LEFT JOIN offre_emploi o ON o.id = c.offre_id
                WHERE c.id = :id
                LIMIT 1';

        $statement = $this->pdo->prepare($sql);
        $statement->execute(['id' => $id]);
        $candidature = $statement->fetch(PDO::FETCH_ASSOC);

        return $candidature ?: null;
    }

    private function getOpenOffers(): array
    {
        $offers = $this->getAllOffres();

        return array_values(array_filter($offers, static function (array $offre): bool {
            return ($offre['statut'] ?? '') === 'ouverte';
        }));
    }

    public function liste(): void
    {
        $offres = $this->getOpenOffers();
        include __DIR__ . '/../../view/frontoffice/candidature/liste.php';
    }

    public function ajouter(): void
    {
        $selectedOffer = null;
        $errors = [];
        $fieldErrors = [];
        $formData = [
            'offre_id' => '',
            'nom' => '',
            'prenom' => '',
            'email' => '',
            'cvurl' => '',
            'lettre_motivation' => '',
        ];
        $recaptchaSiteKey = (string) ($this->recaptchaConfig['site_key'] ?? '');

        if (isset($_GET['offre_id']) && (int) $_GET['offre_id'] > 0) {
            $formData['offre_id'] = (string) (int) $_GET['offre_id'];
            $selectedOffer = $this->getOffreById((int) $formData['offre_id']);
            if ($selectedOffer && ($selectedOffer['statut'] ?? '') !== 'ouverte') {
                $selectedOffer = null;
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $formData = [
                'offre_id' => trim($_POST['offre_id'] ?? ''),
                'nom' => trim($_POST['nom'] ?? ''),
                'prenom' => trim($_POST['prenom'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'cvurl' => trim($_POST['cvurl'] ?? ''),
                'lettre_motivation' => trim($_POST['lettre_motivation'] ?? ''),
            ];

            if ($formData['offre_id'] === '') {
                $fieldErrors['offre_id'] = 'Offre invalide. Veuillez revenir a la liste des offres.';
            }
            if ($formData['nom'] === '') {
                $fieldErrors['nom'] = 'Le nom est obligatoire.';
            } elseif (!$this->isPersonName($formData['nom'])) {
                $fieldErrors['nom'] = 'Le nom doit contenir uniquement des lettres, espaces, apostrophes ou tirets.';
            }
            if ($formData['prenom'] === '') {
                $fieldErrors['prenom'] = 'Le prenom est obligatoire.';
            } elseif (!$this->isPersonName($formData['prenom'])) {
                $fieldErrors['prenom'] = 'Le prenom doit contenir uniquement des lettres, espaces, apostrophes ou tirets.';
            }
            if ($formData['email'] === '') {
                $fieldErrors['email'] = 'L\'email est obligatoire.';
            } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
                $fieldErrors['email'] = 'Le format de l\'email est invalide.';
            }
            if ($formData['lettre_motivation'] === '') {
                $fieldErrors['lettre_motivation'] = 'La lettre de motivation est obligatoire.';
            } elseif (mb_strlen($formData['lettre_motivation']) < 30) {
                $fieldErrors['lettre_motivation'] = 'La lettre de motivation doit contenir au moins 30 caracteres.';
            }

            $cvUploadData = $this->handleCvUpload($fieldErrors);
            if ($cvUploadData === null && !isset($fieldErrors['cvurl'])) {
                $fieldErrors['cvurl'] = 'Le CV doit etre fourni. Veuillez televerser un fichier PDF, DOC ou DOCX.';
            }

            $recaptchaToken = trim((string) ($_POST['g-recaptcha-response'] ?? ''));
            if (!$this->verifyRecaptchaToken($recaptchaToken)) {
                $fieldErrors['recaptcha'] = $this->isRecaptchaConfigured()
                    ? 'Veuillez confirmer que vous n\'etes pas un robot.'
                    : 'La verification anti-robot n\'est pas configuree sur ce poste.';
            }

            $selectedOffer = $this->getOffreById((int) $formData['offre_id']);
            if (!$selectedOffer || ($selectedOffer['statut'] ?? '') !== 'ouverte') {
                $fieldErrors['offre_id'] = 'Offre invalide. Veuillez revenir a la liste et cliquer sur Postuler depuis une offre ouverte.';
            }

            $errors = array_values($fieldErrors);

            if (!empty($errors)) {
                foreach (array_keys($fieldErrors) as $fieldName) {
                    if (array_key_exists($fieldName, $formData)) {
                        $formData[$fieldName] = '';
                    }
                }
                include __DIR__ . '/../../view/frontoffice/candidature/ajouter.php';
                return;
            }

            $newId = $this->createCandidature([
                'nom' => $formData['nom'],
                'prenom' => $formData['prenom'],
                'lettre_motivation' => $formData['lettre_motivation'],
                'cv_external_url' => null,
                'cv_file_path' => $cvUploadData['cv_file_path'],
                'cv_original_name' => $cvUploadData['cv_original_name'],
                'cv_mime' => $cvUploadData['cv_mime'],
                'cv_size' => $cvUploadData['cv_size'],
                'cv_source' => 'upload',
                'email' => $formData['email'],
                'offre_id' => (int) $formData['offre_id'],
                'statut' => 'en_attente',
            ]);

            // Envoi mail de confirmation au candidat
            try {
                require_once __DIR__ . '/../../api/Mailer.php';
                $mailer = new Mailer();
                $mailer->sendFromTemplate(
                    $formData['email'],
                    'Confirmation de candidature - ' . ($selectedOffer['titre'] ?? 'EduMatch'),
                    __DIR__ . '/../../view/emails/candidature_recue.php',
                    [
                        'candidat_nom'        => $formData['prenom'] . ' ' . $formData['nom'],
                        'offre_titre'         => $selectedOffer['titre'] ?? '',
                        'offre_lieu'          => $selectedOffer['lieu'] ?? '',
                        'offre_type_contrat'  => $selectedOffer['type_contrat'] ?? '',
                    ]
                );
            } catch (Throwable $mailEx) {
                error_log('Confirmation email failed for candidature #' . $newId . ': ' . $mailEx->getMessage());
            }

            try {
                $analysis = $this->cvMatchService->analyze([
                    'cv_file_path' => $cvUploadData['cv_file_path'],
                    'cv_mime' => $cvUploadData['cv_mime'],
                    'cv_original_name' => $cvUploadData['cv_original_name'],
                ], $selectedOffer);

                $this->updateMatchAnalysis($newId, $analysis);
            } catch (Throwable $exception) {
                error_log('AI match analysis failed for candidature #' . $newId . ': ' . $exception->getMessage());
            }

            header('Location: /gestion_users/controller/CandidatureController.php?espace=front&action=merci');
            exit;
        }

        if (!$selectedOffer) {
            $fieldErrors['offre_id'] = 'Aucune offre valide selectionnee. Veuillez revenir a la liste des offres pour postuler.';
            $errors = array_values($fieldErrors);
        }

        include __DIR__ . '/../../view/frontoffice/candidature/ajouter.php';
    }

    public function details(int $id): void
    {
        $candidature = $this->getCandidatureById($id);

        if (!$candidature) {
            http_response_code(404);
            echo '<h1>Candidature non trouvee</h1>';
            return;
        }

        include __DIR__ . '/../../view/frontoffice/candidature/details.php';
    }

    public function merci(): void
    {
        include __DIR__ . '/../../view/frontoffice/candidature/merci.php';
    }
}
