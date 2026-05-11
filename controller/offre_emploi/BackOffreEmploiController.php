<?php

declare(strict_types=1);

class OffreEmploiController
{
    private PDO $pdo;

    private function isTextLikeField(string $value): bool
    {
        // Require at least one letter and allow common separators.
        return preg_match('/^(?=.*\p{L})[\p{L}\d\s\-\'\.,\/]{2,100}$/u', $value) === 1;
    }
    private function expireOffers(): void
    {
    $sql = "UPDATE offre_emploi SET statut = 'fermee' WHERE statut = 'ouverte' AND date_limite < NOW()";
    $this->pdo->exec($sql);
}
    private function validateOffreData(array $data): array
    {
        $fieldErrors = [];

        if ($data['titre'] === '') {
            $fieldErrors['titre'][] = 'Le titre est obligatoire.';
        }
        if ($data['description'] === '') {
            $fieldErrors['description'][] = 'La description est obligatoire.';
        }
        if ($data['lieu'] === '') {
            $fieldErrors['lieu'][] = 'Le lieu est obligatoire.';
        } elseif (!$this->isTextLikeField($data['lieu'])) {
            $fieldErrors['lieu'][] = 'Le lieu doit contenir du texte valide (pas uniquement des chiffres).';
        }
        if ($data['type_contrat'] === '') {
            $fieldErrors['type_contrat'][] = 'Le type de contrat est obligatoire.';
        } elseif (!$this->isTextLikeField($data['type_contrat'])) {
            $fieldErrors['type_contrat'][] = 'Le type de contrat doit contenir du texte valide (pas uniquement des chiffres).';
        }
        if ($data['date_limite'] === '') {
            $fieldErrors['date_limite'][] = 'La date limite est obligatoire.';
        } else {
            error_log("DEBUG RAW INPUT: " . htmlspecialchars($data['date_limite']));
            // Parse datetime string with explicit timezone
            $timestamp = strtotime($data['date_limite'] . ' UTC');
            if ($timestamp === false) {
                $fieldErrors['date_limite'][] = 'La date limite est invalide. (Format reçu: ' . htmlspecialchars($data['date_limite']) . ')';
            } else {
                $parsedDate = new DateTime('@' . $timestamp);
                $now = new DateTime('now');
                error_log("DEBUG: Raw input: " . htmlspecialchars($data['date_limite']) . " | Parsed date: " . $parsedDate->format('Y-m-d H:i:s') . " | Now: " . $now->format('Y-m-d H:i:s') . " | Comparison: " . ($parsedDate <= $now ? 'PAST/NOW' : 'FUTURE'));
                if ($parsedDate <= $now) {
                    $fieldErrors['date_limite'][] = 'La date limite doit etre strictement posterieure a la date du jour. (Input: ' . htmlspecialchars($data['date_limite']) . ' | Parsed: ' . $parsedDate->format('Y-m-d H:i:s') . ' | Now: ' . $now->format('Y-m-d H:i:s') . ')';
                }
            }
        }

        if ($data['salaire_min'] !== null && $data['salaire_min'] < 0) {
            $fieldErrors['salaire_min'][] = 'Le salaire minimum doit etre positif.';
        }
        if ($data['salaire_max'] !== null && $data['salaire_max'] < 0) {
            $fieldErrors['salaire_max'][] = 'Le salaire maximum doit etre positif.';
        }
        if ($data['salaire_min'] !== null && $data['salaire_max'] !== null && $data['salaire_max'] < $data['salaire_min']) {
            $fieldErrors['salaire_max'][] = 'Le salaire maximum doit etre superieur ou egal au salaire minimum.';
        }

        if (!in_array($data['statut'], ['ouverte', 'fermee'], true)) {
            $fieldErrors['statut'][] = 'Le statut selectionne est invalide.';
        }

        return $fieldErrors;
    }

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    private function getSortFieldMap(): array
    {
        return [
            'titre' => 'titre',
            'lieu' => 'lieu',
            'type_contrat' => 'type_contrat',
            'salaire_min' => 'salaire_min',
            'salaire_max' => 'salaire_max',
            'date_creation' => 'date_creation',
            'date_limite' => 'date_limite',
            'statut' => 'statut',
        ];
    }

    private function getAllOffres(string $searchTerm = '', string $sortBy = 'date_creation', string $sortDir = 'desc'): array
    {
        $sortFieldMap = $this->getSortFieldMap();
        $sortColumn = $sortFieldMap[$sortBy] ?? 'date_creation';
        $direction = strtolower($sortDir) === 'asc' ? 'ASC' : 'DESC';

        $sql = 'SELECT * FROM offre_emploi';
        $params = [];

        if ($searchTerm !== '') {
            $sql .= ' WHERE (
                titre LIKE :search
                OR description LIKE :search
                OR competences_requises LIKE :search
                OR lieu LIKE :search
                OR type_contrat LIKE :search
                OR statut LIKE :search
                OR CAST(salaire_min AS CHAR) LIKE :search
                OR CAST(salaire_max AS CHAR) LIKE :search
                OR CAST(date_creation AS CHAR) LIKE :search
                OR CAST(date_limite AS CHAR) LIKE :search
            )';
            $params['search'] = '%' . $searchTerm . '%';
        }

        $sql .= ' ORDER BY ' . $sortColumn . ' ' . $direction;
        $statement = $this->pdo->prepare($sql);
        $statement->execute($params);

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

    private function createOffre(array $data): int
    {
        $sql = 'INSERT INTO offre_emploi
            (titre, description, competences_requises, lieu, type_contrat, salaire_min, salaire_max, date_limite, statut)
                VALUES
            (:titre, :description, :competences_requises, :lieu, :type_contrat, :salaire_min, :salaire_max, :date_limite, :statut)';

        $statement = $this->pdo->prepare($sql);
        $statement->execute([
            'titre' => $data['titre'],
            'description' => $data['description'],
            'competences_requises' => $data['competences_requises'] ?? null,
            'lieu' => $data['lieu'],
            'type_contrat' => $data['type_contrat'],
            'salaire_min' => $data['salaire_min'] ?? null,
            'salaire_max' => $data['salaire_max'] ?? null,
            'date_limite' => $data['date_limite'],
            'statut' => $data['statut'] ?? 'ouverte',
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    private function updateOffre(int $id, array $data): bool
    {
        $sql = 'UPDATE offre_emploi
                SET titre = :titre,
                    description = :description,
                    competences_requises = :competences_requises,
                    lieu = :lieu,
                    type_contrat = :type_contrat,
                    salaire_min = :salaire_min,
                    salaire_max = :salaire_max,
                    date_limite = :date_limite,
                    statut = :statut
                WHERE id = :id';

        $statement = $this->pdo->prepare($sql);

        return $statement->execute([
            'id' => $id,
            'titre' => $data['titre'],
            'description' => $data['description'],
            'competences_requises' => $data['competences_requises'] ?? null,
            'lieu' => $data['lieu'],
            'type_contrat' => $data['type_contrat'],
            'salaire_min' => $data['salaire_min'] ?? null,
            'salaire_max' => $data['salaire_max'] ?? null,
            'date_limite' => $data['date_limite'],
            'statut' => $data['statut'] ?? 'ouverte',
        ]);
    }

    private function deleteOffre(int $id): bool
    {
        $sql = 'DELETE FROM offre_emploi WHERE id = :id';
        $statement = $this->pdo->prepare($sql);

        return $statement->execute(['id' => $id]);
    }

    public function liste(): void
    {
        $this->expireOffers();
        $sortFieldMap = $this->getSortFieldMap();

        $searchTerm = trim((string) ($_GET['q'] ?? ''));
        $sortBy = trim((string) ($_GET['sort_by'] ?? 'date_creation'));
        if (!array_key_exists($sortBy, $sortFieldMap)) {
            $sortBy = 'date_creation';
        }

        $sortDir = strtolower(trim((string) ($_GET['sort_dir'] ?? 'desc')));
        if (!in_array($sortDir, ['asc', 'desc'], true)) {
            $sortDir = 'desc';
        }

        $offres = $this->getAllOffres($searchTerm, $sortBy, $sortDir);

        $filterState = [
            'q' => $searchTerm,
            'sort_by' => $sortBy,
            'sort_dir' => $sortDir,
            'sort_fields' => array_keys($sortFieldMap),
        ];

        include __DIR__ . '/../../view/backoffice/src/pages/backoffice/offre_emploi/liste.php';
    }

    public function stats(): void
    {
        $this->expireOffers();
        $offerSummarySql = 'SELECT
                COUNT(*) AS total_offres,
                COALESCE(SUM(CASE WHEN statut = :ouverte THEN 1 ELSE 0 END), 0) AS offres_ouvertes,
                COALESCE(SUM(CASE WHEN statut = :fermee THEN 1 ELSE 0 END), 0) AS offres_fermees
            FROM offre_emploi';
        $offerSummaryStatement = $this->pdo->prepare($offerSummarySql);
        $offerSummaryStatement->execute([
            'ouverte' => 'ouverte',
            'fermee' => 'fermee',
        ]);
        $offerSummary = $offerSummaryStatement->fetch(PDO::FETCH_ASSOC) ?: [
            'total_offres' => 0,
            'offres_ouvertes' => 0,
            'offres_fermees' => 0,
        ];

        $candidatureSummarySql = 'SELECT
                COUNT(*) AS total_candidatures,
                COALESCE(SUM(CASE WHEN statut = :en_attente THEN 1 ELSE 0 END), 0) AS candidatures_en_attente,
                COALESCE(SUM(CASE WHEN statut = :acceptee THEN 1 ELSE 0 END), 0) AS candidatures_acceptees,
                COALESCE(SUM(CASE WHEN statut = :refusee THEN 1 ELSE 0 END), 0) AS candidatures_refusees
            FROM candidature';
        $candidatureSummaryStatement = $this->pdo->prepare($candidatureSummarySql);
        $candidatureSummaryStatement->execute([
            'en_attente' => 'en_attente',
            'acceptee' => 'acceptee',
            'refusee' => 'refusee',
        ]);
        $candidatureSummary = $candidatureSummaryStatement->fetch(PDO::FETCH_ASSOC) ?: [
            'total_candidatures' => 0,
            'candidatures_en_attente' => 0,
            'candidatures_acceptees' => 0,
            'candidatures_refusees' => 0,
        ];

        $topOffersSql = 'SELECT
                o.id,
                o.titre,
                o.lieu,
                o.statut,
                COUNT(c.id) AS candidatures_count
            FROM offre_emploi o
            LEFT JOIN candidature c ON c.offre_id = o.id
            GROUP BY o.id, o.titre, o.lieu, o.statut, o.date_creation
            ORDER BY candidatures_count DESC, o.date_creation DESC
            LIMIT 8';
        $topOffersStatement = $this->pdo->query($topOffersSql);
        $topOffers = $topOffersStatement ? $topOffersStatement->fetchAll(PDO::FETCH_ASSOC) : [];

        $expiringOffersSql = 'SELECT
                id,
                titre,
                lieu,
                date_limite,
                DATEDIFF(date_limite, CURDATE()) AS days_left
            FROM offre_emploi
            WHERE statut = :statut
              AND date_limite BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 14 DAY)
            ORDER BY date_limite ASC
            LIMIT 6';
        $expiringOffersStatement = $this->pdo->prepare($expiringOffersSql);
        $expiringOffersStatement->execute(['statut' => 'ouverte']);
        $expiringOffers = $expiringOffersStatement ? $expiringOffersStatement->fetchAll(PDO::FETCH_ASSOC) : [];

        $pendingRepliesSql = 'SELECT
                c.id,
                c.nom,
                c.prenom,
                c.date_candidature,
                o.titre AS offre_titre
            FROM candidature c
            LEFT JOIN offre_emploi o ON o.id = c.offre_id
            WHERE c.statut = :statut
            ORDER BY c.date_candidature DESC
            LIMIT 6';
        $pendingRepliesStatement = $this->pdo->prepare($pendingRepliesSql);
        $pendingRepliesStatement->execute(['statut' => 'en_attente']);
        $pendingReplies = $pendingRepliesStatement ? $pendingRepliesStatement->fetchAll(PDO::FETCH_ASSOC) : [];

        $stats = [
            'offers' => [
                'total' => (int) ($offerSummary['total_offres'] ?? 0),
                'open' => (int) ($offerSummary['offres_ouvertes'] ?? 0),
                'closed' => (int) ($offerSummary['offres_fermees'] ?? 0),
            ],
            'candidatures' => [
                'total' => (int) ($candidatureSummary['total_candidatures'] ?? 0),
                'pending' => (int) ($candidatureSummary['candidatures_en_attente'] ?? 0),
                'accepted' => (int) ($candidatureSummary['candidatures_acceptees'] ?? 0),
                'refused' => (int) ($candidatureSummary['candidatures_refusees'] ?? 0),
            ],
            'alerts' => [
                'expiring_count' => count($expiringOffers),
                'pending_count' => count($pendingReplies),
            ],
        ];

        $chartData = [
            'offer_labels' => array_map(static fn (array $offer): string => (string) $offer['titre'], $topOffers),
            'offer_counts' => array_map(static fn (array $offer): int => (int) $offer['candidatures_count'], $topOffers),
            'status_labels' => ['En attente', 'Acceptées', 'Refusées'],
            'status_counts' => [
                (int) ($candidatureSummary['candidatures_en_attente'] ?? 0),
                (int) ($candidatureSummary['candidatures_acceptees'] ?? 0),
                (int) ($candidatureSummary['candidatures_refusees'] ?? 0),
            ],
        ];

        include __DIR__ . '/../../view/backoffice/src/pages/backoffice/offre_emploi/stats.php';
    }
    public function exportStatsPdf(): void
    {
        $offerSummarySql = 'SELECT
                COUNT(*) AS total_offres,
                COALESCE(SUM(CASE WHEN statut = :ouverte THEN 1 ELSE 0 END), 0) AS offres_ouvertes,
                COALESCE(SUM(CASE WHEN statut = :fermee THEN 1 ELSE 0 END), 0) AS offres_fermees
            FROM offre_emploi';
        $offerSummaryStatement = $this->pdo->prepare($offerSummarySql);
        $offerSummaryStatement->execute([
            'ouverte' => 'ouverte',
            'fermee' => 'fermee',
        ]);
        $offerSummary = $offerSummaryStatement->fetch(PDO::FETCH_ASSOC) ?: [
            'total_offres' => 0,
            'offres_ouvertes' => 0,
            'offres_fermees' => 0,
        ];

        $candidatureSummarySql = 'SELECT
                COUNT(*) AS total_candidatures,
                COALESCE(SUM(CASE WHEN statut = :en_attente THEN 1 ELSE 0 END), 0) AS candidatures_en_attente,
                COALESCE(SUM(CASE WHEN statut = :acceptee THEN 1 ELSE 0 END), 0) AS candidatures_acceptees,
                COALESCE(SUM(CASE WHEN statut = :refusee THEN 1 ELSE 0 END), 0) AS candidatures_refusees
            FROM candidature';
        $candidatureSummaryStatement = $this->pdo->prepare($candidatureSummarySql);
        $candidatureSummaryStatement->execute([
            'en_attente' => 'en_attente',
            'acceptee' => 'acceptee',
            'refusee' => 'refusee',
        ]);
        $candidatureSummary = $candidatureSummaryStatement->fetch(PDO::FETCH_ASSOC) ?: [
            'total_candidatures' => 0,
            'candidatures_en_attente' => 0,
            'candidatures_acceptees' => 0,
            'candidatures_refusees' => 0,
        ];

        $expiringOffersSql = 'SELECT
                id,
                titre,
                lieu,
                date_limite,
                DATEDIFF(date_limite, CURDATE()) AS days_left
            FROM offre_emploi
            WHERE statut = :statut
              AND date_limite BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 14 DAY)
            ORDER BY date_limite ASC
            LIMIT 6';
        $expiringOffersStatement = $this->pdo->prepare($expiringOffersSql);
        $expiringOffersStatement->execute(['statut' => 'ouverte']);
        $expiringOffers = $expiringOffersStatement ? $expiringOffersStatement->fetchAll(PDO::FETCH_ASSOC) : [];

        $pendingRepliesSql = 'SELECT
                c.id,
                c.nom,
                c.prenom,
                c.date_candidature,
                o.titre AS offre_titre
            FROM candidature c
            LEFT JOIN offre_emploi o ON o.id = c.offre_id
            WHERE c.statut = :statut
            ORDER BY c.date_candidature DESC
            LIMIT 6';
        $pendingRepliesStatement = $this->pdo->prepare($pendingRepliesSql);
        $pendingRepliesStatement->execute(['statut' => 'en_attente']);
        $pendingReplies = $pendingRepliesStatement ? $pendingRepliesStatement->fetchAll(PDO::FETCH_ASSOC) : [];

        $stats = [
            'offers' => [
                'total' => (int) ($offerSummary['total_offres'] ?? 0),
                'open' => (int) ($offerSummary['offres_ouvertes'] ?? 0),
                'closed' => (int) ($offerSummary['offres_fermees'] ?? 0),
            ],
            'candidatures' => [
                'total' => (int) ($candidatureSummary['total_candidatures'] ?? 0),
                'pending' => (int) ($candidatureSummary['candidatures_en_attente'] ?? 0),
                'accepted' => (int) ($candidatureSummary['candidatures_acceptees'] ?? 0),
                'refused' => (int) ($candidatureSummary['candidatures_refusees'] ?? 0),
            ],
            'alerts' => [
                'expiring_count' => count($expiringOffers),
                'pending_count' => count($pendingReplies),
            ],
        ];

        $exportDate = date('d/m/Y H:i');

        include __DIR__ . '/../../view/backoffice/src/pages/backoffice/offre_emploi/stats-pdf.php';
    }
    public function details(int $id): void
    {
        $offre = $this->getOffreById($id);

        if (!$offre) {
            http_response_code(404);
            echo '<h1>Offre non trouvee</h1>';
            return;
        }

        include __DIR__ . '/../../view/backoffice/src/pages/backoffice/offre_emploi/details.php';
    }

    public function ajouter(): void
    {
        $fieldErrors = [];
        $formData = [
            'titre' => '',
            'description' => '',
            'competences_requises' => '',
            'lieu' => '',
            'type_contrat' => '',
            'salaire_min' => '',
            'salaire_max' => '',
            'date_limite' => '',
            'statut' => 'ouverte',
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $formData = [
                'titre' => trim($_POST['titre'] ?? ''),
                'description' => trim($_POST['description'] ?? ''),
                'competences_requises' => trim($_POST['competences_requises'] ?? ''),
                'lieu' => trim($_POST['lieu'] ?? ''),
                'type_contrat' => trim($_POST['type_contrat'] ?? ''),
                'salaire_min' => trim((string) ($_POST['salaire_min'] ?? '')),
                'salaire_max' => trim((string) ($_POST['salaire_max'] ?? '')),
                'date_limite' => trim($_POST['date_limite'] ?? ''),
                'statut' => trim($_POST['statut'] ?? 'ouverte'),
            ];

            $data = [
                'titre' => $formData['titre'],
                'description' => $formData['description'],
                'competences_requises' => $formData['competences_requises'],
                'lieu' => $formData['lieu'],
                'type_contrat' => $formData['type_contrat'],
                'salaire_min' => $formData['salaire_min'] !== '' ? (float) $formData['salaire_min'] : null,
                'salaire_max' => $formData['salaire_max'] !== '' ? (float) $formData['salaire_max'] : null,
                'date_limite' => $formData['date_limite'],
                'statut' => $formData['statut'],
            ];

            $fieldErrors = $this->validateOffreData($data);
            if (!empty($fieldErrors)) {
                $error = 'Merci de corriger les erreurs du formulaire.';
                include __DIR__ . '/../../view/backoffice/src/pages/backoffice/offre_emploi/ajouter.php';
                return;
            }

            $this->createOffre($data);
            header('Location: /gestion_users/controller/OffreEmploiController.php?espace=back&action=liste&success=ajoutee');
            exit;
        }

        include __DIR__ . '/../../view/backoffice/src/pages/backoffice/offre_emploi/ajouter.php';
    }

    public function modifier(int $id): void
    {
        $offre = $this->getOffreById($id);
        $fieldErrors = [];

        if (!$offre) {
            http_response_code(404);
            echo '<h1>Offre non trouvee</h1>';
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'titre' => trim($_POST['titre'] ?? ''),
                'description' => trim($_POST['description'] ?? ''),
                'competences_requises' => trim($_POST['competences_requises'] ?? ''),
                'lieu' => trim($_POST['lieu'] ?? ''),
                'type_contrat' => trim($_POST['type_contrat'] ?? ''),
                'salaire_min' => ($_POST['salaire_min'] ?? '') !== '' ? (float) $_POST['salaire_min'] : null,
                'salaire_max' => ($_POST['salaire_max'] ?? '') !== '' ? (float) $_POST['salaire_max'] : null,
                'date_limite' => trim($_POST['date_limite'] ?? ''),
                'statut' => trim($_POST['statut'] ?? 'ouverte'),
            ];

            $fieldErrors = $this->validateOffreData($data);
            if (!empty($fieldErrors)) {
                $error = 'Merci de corriger les erreurs du formulaire.';
                $offre = array_merge($offre, [
                    'titre' => $data['titre'],
                    'description' => $data['description'],
                    'competences_requises' => $data['competences_requises'],
                    'lieu' => $data['lieu'],
                    'type_contrat' => $data['type_contrat'],
                    'salaire_min' => $data['salaire_min'],
                    'salaire_max' => $data['salaire_max'],
                    'date_limite' => $data['date_limite'],
                    'statut' => $data['statut'],
                ]);
                include __DIR__ . '/../../view/backoffice/src/pages/backoffice/offre_emploi/modifier.php';
                return;
            }

            $this->updateOffre($id, $data);
            header('Location: /gestion_users/controller/OffreEmploiController.php?espace=back&action=liste&success=modifiee');
            exit;
        }

        include __DIR__ . '/../../view/backoffice/src/pages/backoffice/offre_emploi/modifier.php';
    }


    public function supprimer(int $id): void
    {
        $this->deleteOffre($id);
        header('Location: /gestion_users/controller/OffreEmploiController.php?espace=back&action=liste');
        exit;
    }
}
