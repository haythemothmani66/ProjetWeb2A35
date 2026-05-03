<?php

declare(strict_types=1);

class CandidatureController
{
    private PDO $pdo;

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
            'datecandidature' => 'c.datecandidature',
            'datereponse' => 'c.datereponse',
        ];
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

    private function getAllCandidatures(string $searchTerm = '', string $sortBy = 'datecandidature', string $sortDir = 'desc'): array
    {
        $sortFieldMap = $this->getSortFieldMap();
        $sortColumn = $sortFieldMap[$sortBy] ?? 'c.datecandidature';
        $direction = strtolower($sortDir) === 'asc' ? 'ASC' : 'DESC';

        $sql = 'SELECT
                    c.id,
                    c.nom,
                    c.prenom,
                    c.lettremotivation,
                    CASE
                        WHEN c.cv_source = \'upload\' THEN c.cv_file_path
                        ELSE COALESCE(c.cv_external_url, c.cv_file_path)
                    END AS cvurl,
                    c.cv_source,
                    c.cv_original_name,
                    c.email,
                    c.statut,
                    c.datecandidature,
                    c.datereponse,
                    c.offreid,
                    o.titre AS offre_titre,
                    o.lieu AS offre_lieu,
                    o.typecontrat AS offre_typecontrat
                FROM candidature c
                LEFT JOIN offreemploi o ON o.id = c.offreid';

        $params = [];

        if ($searchTerm !== '') {
            $sql .= ' WHERE (
                    c.nom LIKE :search
                    OR c.prenom LIKE :search
                    OR c.email LIKE :search
                    OR c.statut LIKE :search
                    OR c.datecandidature LIKE :search
                    OR c.datereponse LIKE :search
                    OR o.titre LIKE :search
                    OR o.lieu LIKE :search
                    OR o.typecontrat LIKE :search
                )';
            $params['search'] = '%' . $searchTerm . '%';
        }

        $sql .= ' ORDER BY ' . $sortColumn . ' ' . $direction;

        $statement = $this->pdo->prepare($sql);
        $statement->execute($params);
        return $statement ? $statement->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    private function getCandidaturesByOffreId(int $offreId): array
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
                    c.cv_source,
                    c.cv_original_name,
                    c.email,
                    c.statut,
                    c.datecandidature,
                    c.datereponse,
                    c.offreid,
                    o.titre AS offre_titre,
                    o.lieu AS offre_lieu,
                    o.typecontrat AS offre_typecontrat
                FROM candidature c
                LEFT JOIN offreemploi o ON o.id = c.offreid
                WHERE c.offreid = :offreid
                ORDER BY c.datecandidature DESC';

        $statement = $this->pdo->prepare($sql);
        $statement->execute(['offreid' => $offreId]);

        return $statement ? $statement->fetchAll(PDO::FETCH_ASSOC) : [];
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
                    c.cv_source,
                    c.cv_original_name,
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

    private function updateCandidature(int $id, array $data): bool
    {
        $sql = 'UPDATE candidature
                SET nom = :nom,
                    prenom = :prenom,
                    lettremotivation = :lettremotivation,
                    email = :email,
                    statut = :statut,
                    datereponse = :datereponse,
                    offreid = :offreid
                WHERE id = :id';

        $statement = $this->pdo->prepare($sql);
        return $statement->execute([
            'id' => $id,
            'nom' => $data['nom'],
            'prenom' => $data['prenom'],
            'lettremotivation' => $data['lettremotivation'],
            'email' => $data['email'],
            'statut' => $data['statut'] ?? 'enattente',
            'datereponse' => $data['datereponse'] !== '' ? ($data['datereponse'] ?? null) : null,
            'offreid' => $data['offreid'],
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
        $sortBy = trim((string) ($_GET['sort_by'] ?? 'datecandidature'));
        if (!array_key_exists($sortBy, $sortFieldMap)) {
            $sortBy = 'datecandidature';
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

        include __DIR__ . '/../../views/back/candidature/liste.php';
    }

    public function parOffre(int $offreId): void
    {
        $offre = $this->getOffreById($offreId);

        if (!$offre) {
            http_response_code(404);
            echo '<h1>Offre non trouvee</h1>';
            return;
        }

        $candidatures = $this->getCandidaturesByOffreId($offreId);
        $filterState = [
            'q' => '',
            'sort_by' => 'datecandidature',
            'sort_dir' => 'desc',
            'sort_fields' => ['offre_titre', 'nom', 'prenom', 'email', 'statut', 'datecandidature', 'datereponse'],
        ];
        $contextOffre = $offre;

        include __DIR__ . '/../../views/back/candidature/liste.php';
    }

    public function details(int $id): void
    {
        $candidature = $this->getCandidatureById($id);

        if (!$candidature) {
            http_response_code(404);
            echo '<h1>Candidature non trouvee</h1>';
            return;
        }

        include __DIR__ . '/../../views/back/candidature/details.php';
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
                'lettremotivation' => trim($_POST['lettremotivation'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'statut' => trim($_POST['statut'] ?? 'enattente'),
                'datereponse' => trim($_POST['datereponse'] ?? ''),
                'offreid' => trim($_POST['offreid'] ?? ''),
            ];

            if ($formData['offreid'] === '') {
                $fieldErrors['offreid'] = 'L\'offre est obligatoire.';
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
            if (!in_array($formData['statut'], ['enattente', 'acceptee', 'acceptée', 'refusee', 'refusée'], true)) {
                $fieldErrors['statut'] = 'Le statut selectionne est invalide.';
            }
            if ($formData['datereponse'] !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $formData['datereponse'])) {
                $fieldErrors['datereponse'] = 'La date de reponse est invalide.';
            } elseif ($formData['datereponse'] !== '') {
                $dateReponse = DateTime::createFromFormat('Y-m-d', $formData['datereponse']);
                if ($dateReponse === false) {
                    $fieldErrors['datereponse'] = 'La date de reponse est invalide.';
                }
            }

            $offerIds = array_map(static fn (array $offre): string => (string) $offre['id'], $offres);
            if ($formData['offreid'] !== '' && !in_array($formData['offreid'], $offerIds, true)) {
                $fieldErrors['offreid'] = 'L\'offre selectionnee n\'existe pas.';
            }

            $errors = array_values($fieldErrors);

            if (!empty($errors)) {
                $candidature = array_merge($candidature, $formData);
                foreach (array_keys($fieldErrors) as $fieldName) {
                    if (array_key_exists($fieldName, $candidature)) {
                        $candidature[$fieldName] = '';
                    }
                }
                include __DIR__ . '/../../views/back/candidature/modifier.php';
                return;
            }

            $this->updateCandidature($id, [
                'nom' => $formData['nom'],
                'prenom' => $formData['prenom'],
                'lettremotivation' => $formData['lettremotivation'],
                'email' => $formData['email'],
                'statut' => $formData['statut'],
                'datereponse' => $formData['datereponse'],
                'offreid' => (int) $formData['offreid'],
            ]);

            header('Location: index.php?espace=back&module=candidature&action=details&id=' . $id);
            exit;
        }

        include __DIR__ . '/../../views/back/candidature/modifier.php';
    }

    public function supprimer(int $id): void
    {
        $this->deleteCandidature($id);
        header('Location: index.php?espace=back&module=candidature&action=liste');
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
        $validStatuts = ['enattente', 'acceptee', 'refusee'];

        if (!in_array($statut, $validStatuts, true)) {
            http_response_code(400);
            echo '<h1>Statut invalide</h1>';
            return;
        }

        $dateReponse = date('Y-m-d');

        $sql = 'UPDATE candidature
                SET statut = :statut,
                    datereponse = :datereponse
                WHERE id = :id';

        $statement = $this->pdo->prepare($sql);
        $success = $statement->execute([
            'statut' => $statut,
            'datereponse' => $dateReponse,
            'id' => $id,
        ]);

        if ($success) {
            // Send email notification based on status
            $candidat_nom = $candidature['prenom'] . ' ' . $candidature['nom'];
            $candidat_email = $candidature['email'];
            $offre_id = (int) ($candidature['offreid'] ?? $candidature['offre_id'] ?? 0);
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
                        __DIR__ . '/../../views/emails/candidature_accept.php',
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
                        __DIR__ . '/../../views/emails/candidature_refuse.php',
                        $templateData
                    );
                }
            } catch (Exception $e) {
                error_log('Error sending email in repondre(): ' . $e->getMessage());
            }

            $statusMap = [
                'acceptee' => 'acceptee',
                'refusee' => 'refusee',
                'enattente' => 'enattente'
            ];
            header('Location: index.php?espace=back&module=candidature&action=details&id=' . $id . '&reply=' . $statusMap[$statut]);
        } else {
            header('Location: index.php?espace=back&module=candidature&action=details&id=' . $id . '&error=update');
        }
        exit;
    }
}
