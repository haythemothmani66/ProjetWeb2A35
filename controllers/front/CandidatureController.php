<?php

declare(strict_types=1);

class CandidatureController
{
    private PDO $pdo;
    private array $recaptchaConfig;

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
        $sql = 'SELECT * FROM offreemploi ORDER BY datecreation DESC';
        $statement = $this->pdo->query($sql);

        return $statement ? $statement->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    private function getOffreById(int $id): ?array
    {
        $sql = 'SELECT * FROM offreemploi WHERE id = :id LIMIT 1';
        $statement = $this->pdo->prepare($sql);
        $statement->execute(['id' => $id]);
        $offre = $statement->fetch(PDO::FETCH_ASSOC);

        return $offre ?: null;
    }

    private function createCandidature(array $data): int
    {
        $sql = 'INSERT INTO candidature
                    (nom, prenom, lettremotivation, cv_external_url, cv_file_path, cv_original_name, cv_mime, cv_size, cv_source, email, statut, offreid)
                VALUES
                    (:nom, :prenom, :lettremotivation, :cv_external_url, :cv_file_path, :cv_original_name, :cv_mime, :cv_size, :cv_source, :email, :statut, :offreid)';

        $statement = $this->pdo->prepare($sql);
        $statement->execute([
            'nom' => $data['nom'],
            'prenom' => $data['prenom'],
            'lettremotivation' => $data['lettremotivation'],
            'cv_external_url' => $data['cv_external_url'] ?? null,
            'cv_file_path' => $data['cv_file_path'] ?? null,
            'cv_original_name' => $data['cv_original_name'] ?? null,
            'cv_mime' => $data['cv_mime'] ?? null,
            'cv_size' => $data['cv_size'] ?? null,
            'cv_source' => $data['cv_source'] ?? 'url',
            'email' => $data['email'],
            'statut' => $data['statut'] ?? 'enattente',
            'offreid' => $data['offreid'],
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    private function getCandidatureById(int $id): ?array
    {
        $sql = 'SELECT
                    c.id,
                    c.nom,
                    c.prenom,
                    c.lettremotivation,
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
                    c.email,
                    c.statut,
                    c.datecandidature,
                    c.datereponse,
                    c.offreid,
                    o.titre AS offre_titre,
                    o.description AS offre_description,
                    o.lieu AS offre_lieu,
                    o.typecontrat AS offre_typecontrat,
                    o.datelimite AS offre_datelimite
                FROM candidature c
                LEFT JOIN offreemploi o ON o.id = c.offreid
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
        include __DIR__ . '/../../views/front/candidature/liste.php';
    }

    public function ajouter(): void
    {
        $selectedOffer = null;
        $errors = [];
        $fieldErrors = [];
        $formData = [
            'offreid' => '',
            'nom' => '',
            'prenom' => '',
            'email' => '',
            'cvurl' => '',
            'lettremotivation' => '',
        ];
        $recaptchaSiteKey = (string) ($this->recaptchaConfig['site_key'] ?? '');

        if (isset($_GET['offreid']) && (int) $_GET['offreid'] > 0) {
            $formData['offreid'] = (string) (int) $_GET['offreid'];
            $selectedOffer = $this->getOffreById((int) $formData['offreid']);
            if ($selectedOffer && ($selectedOffer['statut'] ?? '') !== 'ouverte') {
                $selectedOffer = null;
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $formData = [
                'offreid' => trim($_POST['offreid'] ?? ''),
                'nom' => trim($_POST['nom'] ?? ''),
                'prenom' => trim($_POST['prenom'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'cvurl' => trim($_POST['cvurl'] ?? ''),
                'lettremotivation' => trim($_POST['lettremotivation'] ?? ''),
            ];

            if ($formData['offreid'] === '') {
                $fieldErrors['offreid'] = 'Offre invalide. Veuillez revenir a la liste des offres.';
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
            if ($formData['lettremotivation'] === '') {
                $fieldErrors['lettremotivation'] = 'La lettre de motivation est obligatoire.';
            } elseif (mb_strlen($formData['lettremotivation']) < 30) {
                $fieldErrors['lettremotivation'] = 'La lettre de motivation doit contenir au moins 30 caracteres.';
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

            $selectedOffer = $this->getOffreById((int) $formData['offreid']);
            if (!$selectedOffer || ($selectedOffer['statut'] ?? '') !== 'ouverte') {
                $fieldErrors['offreid'] = 'Offre invalide. Veuillez revenir a la liste et cliquer sur Postuler depuis une offre ouverte.';
            }

            $errors = array_values($fieldErrors);

            if (!empty($errors)) {
                foreach (array_keys($fieldErrors) as $fieldName) {
                    if (array_key_exists($fieldName, $formData)) {
                        $formData[$fieldName] = '';
                    }
                }
                include __DIR__ . '/../../views/front/candidature/ajouter.php';
                return;
            }

            $newId = $this->createCandidature([
                'nom' => $formData['nom'],
                'prenom' => $formData['prenom'],
                'lettremotivation' => $formData['lettremotivation'],
                'cv_external_url' => null,
                'cv_file_path' => $cvUploadData['cv_file_path'],
                'cv_original_name' => $cvUploadData['cv_original_name'],
                'cv_mime' => $cvUploadData['cv_mime'],
                'cv_size' => $cvUploadData['cv_size'],
                'cv_source' => 'upload',
                'email' => $formData['email'],
                'offreid' => (int) $formData['offreid'],
                'statut' => 'enattente',
            ]);

            header('Location: index.php?espace=front&module=candidature&action=details&id=' . $newId);
            exit;
        }

        if (!$selectedOffer) {
            $fieldErrors['offreid'] = 'Aucune offre valide selectionnee. Veuillez revenir a la liste des offres pour postuler.';
            $errors = array_values($fieldErrors);
        }

        include __DIR__ . '/../../views/front/candidature/ajouter.php';
    }

    public function details(int $id): void
    {
        $candidature = $this->getCandidatureById($id);

        if (!$candidature) {
            http_response_code(404);
            echo '<h1>Candidature non trouvee</h1>';
            return;
        }

        include __DIR__ . '/../../views/front/candidature/details.php';
    }
}
