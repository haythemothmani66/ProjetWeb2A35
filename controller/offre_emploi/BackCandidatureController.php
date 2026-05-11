<?php

declare(strict_types=1);

class CandidatureController
{
    private PDO $pdo;

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

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    private function getSortFieldMap(): array
    {
        return [
            'offre_titre' => 'o.titre',
            'nom' => 'c.nom',
            'prenom' => 'c.prenom',
            'email' => 'c.email',
            'statut' => 'c.statut',
            'match_score' => 'c.match_score',
            'date_candidature' => 'c.date_candidature',
            'date_reponse' => 'c.date_reponse',
        ];
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

    private function getAllCandidatures(string $searchTerm = '', string $sortBy = 'date_candidature', string $sortDir = 'desc'): array
    {
        $sortFieldMap = $this->getSortFieldMap();
        $sortColumn = $sortFieldMap[$sortBy] ?? 'c.date_candidature';
        $direction = strtolower($sortDir) === 'asc' ? 'ASC' : 'DESC';

        $sql = 'SELECT
                    c.id,
                    c.nom,
                    c.prenom,
                    c.lettre_motivation,
                    CASE
                        WHEN c.cv_source = \'upload\' THEN c.cv_file_path
                        ELSE COALESCE(c.cv_external_url, c.cv_file_path)
                    END AS cvurl,
                    c.cv_source,
                    c.cv_original_name,
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
                    o.lieu AS offre_lieu,
                    o.type_contrat AS offre_typecontrat
                FROM candidature c
                LEFT JOIN offre_emploi o ON o.id = c.offre_id';

        $params = [];

        if ($searchTerm !== '') {
            $sql .= ' WHERE (
                    c.nom LIKE :search
                    OR c.prenom LIKE :search
                    OR c.email LIKE :search
                    OR c.statut LIKE :search
                    OR c.date_candidature LIKE :search
                    OR c.date_reponse LIKE :search
                    OR o.titre LIKE :search
                    OR o.lieu LIKE :search
                    OR o.type_contrat LIKE :search
                )';
            $params['search'] = '%' . $searchTerm . '%';
        }

        $sql .= ' ORDER BY ' . $sortColumn . ' ' . $direction;

        $statement = $this->pdo->prepare($sql);
        $statement->execute($params);
        return $statement ? $statement->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    private function getCandidaturesByOffreId(int $offre_id): array
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
                    c.cv_source,
                    c.cv_original_name,
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
                    o.lieu AS offre_lieu,
                    o.type_contrat AS offre_typecontrat
                FROM candidature c
                LEFT JOIN offre_emploi o ON o.id = c.offre_id
                WHERE c.offre_id = :offre_id
                ORDER BY c.date_candidature DESC';

        $statement = $this->pdo->prepare($sql);
        $statement->execute(['offre_id' => $offre_id]);

        return $statement ? $statement->fetchAll(PDO::FETCH_ASSOC) : [];
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
                    c.cv_file_path,
                    c.cv_mime,
                    c.cv_source,
                    c.cv_original_name,
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

    private function updateCandidature(int $id, array $data): bool
    {
        $sql = 'UPDATE candidature
                SET nom = :nom,
                    prenom = :prenom,
                    lettre_motivation = :lettre_motivation,
                    email = :email,
                    statut = :statut,
                    date_reponse = :date_reponse,
                    offre_id = :offre_id
                WHERE id = :id';

        $statement = $this->pdo->prepare($sql);
        return $statement->execute([
            'id' => $id,
            'nom' => $data['nom'],
            'prenom' => $data['prenom'],
            'lettre_motivation' => $data['lettre_motivation'],
            'email' => $data['email'],
            'statut' => $data['statut'] ?? 'en_attente',
            'date_reponse' => $data['date_reponse'] !== '' ? ($data['date_reponse'] ?? null) : null,
            'offre_id' => $data['offre_id'],
        ]);
    }

    private function deleteCandidature(int $id): bool
    {
        $sql = 'DELETE FROM candidature WHERE id = :id';
        $statement = $this->pdo->prepare($sql);

        return $statement->execute(['id' => $id]);
    }

    private function getOffers(): array
    {
        return $this->getAllOffres();
    }

    public function liste(): void
    {
        $sortFieldMap = $this->getSortFieldMap();

        $searchTerm = trim((string) ($_GET['q'] ?? ''));
        $sortBy = trim((string) ($_GET['sort_by'] ?? 'date_candidature'));
        if (!array_key_exists($sortBy, $sortFieldMap)) {
            $sortBy = 'date_candidature';
        }

        $sortDir = strtolower(trim((string) ($_GET['sort_dir'] ?? 'desc')));
        if (!in_array($sortDir, ['asc', 'desc'], true)) {
            $sortDir = 'desc';
        }

        $candidatures = $this->getAllCandidatures($searchTerm, $sortBy, $sortDir);

        $filterState = [
            'q' => $searchTerm,
            'sort_by' => $sortBy,
            'sort_dir' => $sortDir,
            'sort_fields' => array_keys($sortFieldMap),
        ];

        include __DIR__ . '/../../view/backoffice/src/pages/backoffice/candidature/liste.php';
    }

    public function parOffre(int $offre_id): void
    {
        $offre = $this->getOffreById($offre_id);

        if (!$offre) {
            http_response_code(404);
            echo '<h1>Offre non trouvee</h1>';
            return;
        }

        $candidatures = $this->getCandidaturesByOffreId($offre_id);
        $filterState = [
            'q' => '',
            'sort_by' => 'date_candidature',
            'sort_dir' => 'desc',
            'sort_fields' => ['offre_titre', 'nom', 'prenom', 'email', 'statut', 'date_candidature', 'date_reponse'],
        ];
        $contextOffre = $offre;

        include __DIR__ . '/../../view/backoffice/src/pages/backoffice/candidature/liste.php';
    }

    public function details(int $id): void
    {
        $candidature = $this->getCandidatureById($id);

        if (!$candidature) {
            http_response_code(404);
            echo '<h1>Candidature non trouvee</h1>';
            return;
        }

        include __DIR__ . '/../../view/backoffice/src/pages/backoffice/candidature/details.php';
    }

    public function modifier(int $id): void
    {
        $candidature = $this->getCandidatureById($id);

        if (!$candidature) {
            http_response_code(404);
            echo '<h1>Candidature non trouvee</h1>';
            return;
        }

        $offres = $this->getOffers();
        $errors = [];
        $fieldErrors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $formData = [
                'nom' => trim($_POST['nom'] ?? ''),
                'prenom' => trim($_POST['prenom'] ?? ''),
                'lettre_motivation' => trim($_POST['lettre_motivation'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'statut' => trim($_POST['statut'] ?? 'en_attente'),
                'date_reponse' => trim($_POST['date_reponse'] ?? ''),
                'offre_id' => trim($_POST['offre_id'] ?? ''),
            ];

            if ($formData['offre_id'] === '') {
                $fieldErrors['offre_id'] = 'L\'offre est obligatoire.';
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
            if (!in_array($formData['statut'], ['en_attente', 'acceptee', 'acceptée', 'refusee', 'refusée'], true)) {
                $fieldErrors['statut'] = 'Le statut selectionne est invalide.';
            }
            if ($formData['date_reponse'] !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $formData['date_reponse'])) {
                $fieldErrors['date_reponse'] = 'La date de reponse est invalide.';
            } elseif ($formData['date_reponse'] !== '') {
                $date_reponse = DateTime::createFromFormat('Y-m-d', $formData['date_reponse']);
                if ($date_reponse === false) {
                    $fieldErrors['date_reponse'] = 'La date de reponse est invalide.';
                }
            }

            $offerIds = array_map(static fn (array $offre): string => (string) $offre['id'], $offres);
            if ($formData['offre_id'] !== '' && !in_array($formData['offre_id'], $offerIds, true)) {
                $fieldErrors['offre_id'] = 'L\'offre selectionnee n\'existe pas.';
            }

            $errors = array_values($fieldErrors);

            if (!empty($errors)) {
                $candidature = array_merge($candidature, $formData);
                foreach (array_keys($fieldErrors) as $fieldName) {
                    if (array_key_exists($fieldName, $candidature)) {
                        $candidature[$fieldName] = '';
                    }
                }
                include __DIR__ . '/../../view/backoffice/src/pages/backoffice/candidature/modifier.php';
                return;
            }

            $this->updateCandidature($id, [
                'nom' => $formData['nom'],
                'prenom' => $formData['prenom'],
                'lettre_motivation' => $formData['lettre_motivation'],
                'email' => $formData['email'],
                'statut' => $formData['statut'],
                'date_reponse' => $formData['date_reponse'],
                'offre_id' => (int) $formData['offre_id'],
            ]);

            header('Location: /gestion_users/controller/CandidatureController.php?espace=back&action=details&id=' . $id);
            exit;
        }

        include __DIR__ . '/../../view/backoffice/src/pages/backoffice/candidature/modifier.php';
    }

    public function supprimer(int $id): void
    {
        $this->deleteCandidature($id);
        header('Location: /gestion_users/controller/CandidatureController.php?espace=back&action=liste');
        exit;
    }

    public function repondre(int $id): void
    {
        $candidature = $this->getCandidatureById($id);

        if (!$candidature) {
            http_response_code(404);
            echo '<h1>Candidature non trouvee</h1>';
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo '<h1>Method Not Allowed</h1>';
            return;
        }

        $statut = trim($_POST['statut'] ?? '');
        $validStatuts = ['en_attente', 'acceptee', 'refusee'];

        if (!in_array($statut, $validStatuts, true)) {
            http_response_code(400);
            echo '<h1>Statut invalide</h1>';
            return;
        }

        $date_reponse = date('Y-m-d');

        $sql = 'UPDATE candidature
                SET statut = :statut,
                    date_reponse = :date_reponse
                WHERE id = :id';

        $statement = $this->pdo->prepare($sql);
        $success = $statement->execute([
            'statut' => $statut,
            'date_reponse' => $date_reponse,
            'id' => $id,
        ]);

        if ($success) {
            // Send email notification based on status
            $candidat_nom = $candidature['prenom'] . ' ' . $candidature['nom'];
            $candidat_email = $candidature['email'];
            $offre_id = (int) ($candidature['offre_id'] ?? $candidature['offre_id'] ?? 0);
            $offre = $offre_id > 0 ? $this->getOffreById($offre_id) : null;
            $offre_titre = $offre ? $offre['titre'] : 'Offre d\'emploi';

            try {
                $mailer = new Mailer();

                if ($statut === 'acceptee') {
                    // Send acceptance email
                    $date_entretien = $_POST['date_entretien'] ?? null;
                    $heure_entretien = $_POST['heure_entretien'] ?? null;
                    $mode_entretien = $_POST['mode_entretien'] ?? null;
                    $lieu_entretien = $_POST['lieu_entretien'] ?? null;

                    $templateData = [
                        'candidat_nom' => $candidat_nom,
                        'offre_titre' => $offre_titre,
                        'date_entretien' => $date_entretien,
                        'heure_entretien' => $heure_entretien,
                        'mode_entretien' => $mode_entretien,
                        'lieu_entretien' => $lieu_entretien,
                    ];

                    $mailer->sendFromTemplate(
                        $candidat_email,
                        'Félicitations ! Votre candidature a été acceptée',
                        __DIR__ . '/../../view/emails/candidature_accept.php',
                        $templateData
                    );
                } elseif ($statut === 'refusee') {
                    // Send refusal email
                    $motif_refus = $_POST['motif_refus'] ?? '';

                    $templateData = [
                        'candidat_nom' => $candidat_nom,
                        'offre_titre' => $offre_titre,
                        'motif_refus' => $motif_refus,
                    ];

                    $mailer->sendFromTemplate(
                        $candidat_email,
                        'Résultat de votre candidature',
                        __DIR__ . '/../../view/emails/candidature_refuse.php',
                        $templateData
                    );
                }
            } catch (Exception $e) {
                error_log('Error sending email in repondre(): ' . $e->getMessage());
            }

            $statusMap = [
                'acceptee' => 'acceptee',
                'refusee' => 'refusee',
                'en_attente' => 'en_attente'
            ];
            header('Location: /gestion_users/controller/CandidatureController.php?espace=back&action=details&id=' . $id . '&reply=' . $statusMap[$statut]);
        } else {
            header('Location: /gestion_users/controller/CandidatureController.php?espace=back&action=details&id=' . $id . '&error=update');
        }
        exit;
    }

    public function reanalyze(int $id): void
    {
        $candidature = $this->getCandidatureById($id);

        if (!$candidature) {
            http_response_code(404);
            echo '<h1>Candidature non trouvee</h1>';
            return;
        }

        try {
            $service = new CvMatchService();
            $analysis = $service->analyze([
                'cv_file_path' => (string) ($candidature['cv_file_path'] ?? ''),
                'cv_mime' => (string) ($candidature['cv_mime'] ?? ''),
                'cv_original_name' => (string) ($candidature['cv_original_name'] ?? ''),
            ], [
                'titre' => (string) ($candidature['offre_titre'] ?? ''),
                'description' => (string) ($candidature['offre_description'] ?? ''),
                'competences_requises' => '',
                'lieu' => (string) ($candidature['offre_lieu'] ?? ''),
                'type_contrat' => (string) ($candidature['offre_typecontrat'] ?? ''),
                'date_limite' => (string) ($candidature['offre_datelimite'] ?? ''),
            ]);

            $this->updateMatchAnalysis($id, $analysis);
            header('Location: /gestion_users/controller/CandidatureController.php?espace=back&action=details&id=' . $id . '&ai=reanalyzed');
        } catch (Throwable $exception) {
            error_log('AI reanalyze failed for candidature #' . $id . ': ' . $exception->getMessage());
            header('Location: /gestion_users/controller/CandidatureController.php?espace=back&action=details&id=' . $id . '&ai=reanalyze_failed');
        }

        exit;
    }
}
