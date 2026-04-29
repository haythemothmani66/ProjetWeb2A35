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
        if ($data['typecontrat'] === '') {
            $fieldErrors['typecontrat'][] = 'Le type de contrat est obligatoire.';
        } elseif (!$this->isTextLikeField($data['typecontrat'])) {
            $fieldErrors['typecontrat'][] = 'Le type de contrat doit contenir du texte valide (pas uniquement des chiffres).';
        }
        if ($data['datelimite'] === '') {
            $fieldErrors['datelimite'][] = 'La date limite est obligatoire.';
        } elseif (DateTime::createFromFormat('Y-m-d', $data['datelimite']) === false) {
            $fieldErrors['datelimite'][] = 'La date limite est invalide.';
        } else {
            $today = new DateTimeImmutable('today');
            $dateLimite = new DateTimeImmutable($data['datelimite']);
            if ($dateLimite <= $today) {
                $fieldErrors['datelimite'][] = 'La date limite doit etre strictement posterieure a la date du jour.';
            }
        }

        if ($data['salairemin'] !== null && $data['salairemin'] < 0) {
            $fieldErrors['salairemin'][] = 'Le salaire minimum doit etre positif.';
        }
        if ($data['salairemax'] !== null && $data['salairemax'] < 0) {
            $fieldErrors['salairemax'][] = 'Le salaire maximum doit etre positif.';
        }
        if ($data['salairemin'] !== null && $data['salairemax'] !== null && $data['salairemax'] < $data['salairemin']) {
            $fieldErrors['salairemax'][] = 'Le salaire maximum doit etre superieur ou egal au salaire minimum.';
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
            'typecontrat' => 'typecontrat',
            'salairemin' => 'salairemin',
            'salairemax' => 'salairemax',
            'datecreation' => 'datecreation',
            'datelimite' => 'datelimite',
            'statut' => 'statut',
        ];
    }

    private function getAllOffres(string $searchTerm = '', string $sortBy = 'datecreation', string $sortDir = 'desc'): array
    {
        $sortFieldMap = $this->getSortFieldMap();
        $sortColumn = $sortFieldMap[$sortBy] ?? 'datecreation';
        $direction = strtolower($sortDir) === 'asc' ? 'ASC' : 'DESC';

        $sql = 'SELECT * FROM offreemploi';
        $params = [];

        if ($searchTerm !== '') {
            $sql .= ' WHERE (
                titre LIKE :search
                OR description LIKE :search
                OR competencesrequises LIKE :search
                OR lieu LIKE :search
                OR typecontrat LIKE :search
                OR statut LIKE :search
                OR CAST(salairemin AS CHAR) LIKE :search
                OR CAST(salairemax AS CHAR) LIKE :search
                OR CAST(datecreation AS CHAR) LIKE :search
                OR CAST(datelimite AS CHAR) LIKE :search
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
        $sql = 'SELECT * FROM offreemploi WHERE id = :id LIMIT 1';
        $statement = $this->pdo->prepare($sql);
        $statement->execute(['id' => $id]);
        $offre = $statement->fetch(PDO::FETCH_ASSOC);

        return $offre ?: null;
    }

    private function createOffre(array $data): int
    {
        $sql = 'INSERT INTO offreemploi
            (titre, description, competencesrequises, lieu, typecontrat, salairemin, salairemax, datelimite, statut)
                VALUES
            (:titre, :description, :competencesrequises, :lieu, :typecontrat, :salairemin, :salairemax, :datelimite, :statut)';

        $statement = $this->pdo->prepare($sql);
        $statement->execute([
            'titre' => $data['titre'],
            'description' => $data['description'],
            'competencesrequises' => $data['competencesrequises'] ?? null,
            'lieu' => $data['lieu'],
            'typecontrat' => $data['typecontrat'],
            'salairemin' => $data['salairemin'] ?? null,
            'salairemax' => $data['salairemax'] ?? null,
            'datelimite' => $data['datelimite'],
            'statut' => $data['statut'] ?? 'ouverte',
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    private function updateOffre(int $id, array $data): bool
    {
        $sql = 'UPDATE offreemploi
                SET titre = :titre,
                    description = :description,
                    competencesrequises = :competencesrequises,
                    lieu = :lieu,
                    typecontrat = :typecontrat,
                    salairemin = :salairemin,
                    salairemax = :salairemax,
                    datelimite = :datelimite,
                    statut = :statut
                WHERE id = :id';

        $statement = $this->pdo->prepare($sql);

        return $statement->execute([
            'id' => $id,
            'titre' => $data['titre'],
            'description' => $data['description'],
            'competencesrequises' => $data['competencesrequises'] ?? null,
            'lieu' => $data['lieu'],
            'typecontrat' => $data['typecontrat'],
            'salairemin' => $data['salairemin'] ?? null,
            'salairemax' => $data['salairemax'] ?? null,
            'datelimite' => $data['datelimite'],
            'statut' => $data['statut'] ?? 'ouverte',
        ]);
    }

    private function deleteOffre(int $id): bool
    {
        $sql = 'DELETE FROM offreemploi WHERE id = :id';
        $statement = $this->pdo->prepare($sql);

        return $statement->execute(['id' => $id]);
    }

    public function liste(): void
    {
        $sortFieldMap = $this->getSortFieldMap();

        $searchTerm = trim((string) ($_GET['q'] ?? ''));
        $sortBy = trim((string) ($_GET['sort_by'] ?? 'datecreation'));
        if (!array_key_exists($sortBy, $sortFieldMap)) {
            $sortBy = 'datecreation';
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

        include __DIR__ . '/../../views/back/offreemploi/liste.php';
    }

    public function stats(): void
    {
        $offerSummarySql = 'SELECT
                COUNT(*) AS total_offres,
                COALESCE(SUM(CASE WHEN statut = :ouverte THEN 1 ELSE 0 END), 0) AS offres_ouvertes,
                COALESCE(SUM(CASE WHEN statut = :fermee THEN 1 ELSE 0 END), 0) AS offres_fermees
            FROM offreemploi';
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
                COALESCE(SUM(CASE WHEN statut = :enattente THEN 1 ELSE 0 END), 0) AS candidatures_en_attente,
                COALESCE(SUM(CASE WHEN statut = :acceptee THEN 1 ELSE 0 END), 0) AS candidatures_acceptees,
                COALESCE(SUM(CASE WHEN statut = :refusee THEN 1 ELSE 0 END), 0) AS candidatures_refusees
            FROM candidature';
        $candidatureSummaryStatement = $this->pdo->prepare($candidatureSummarySql);
        $candidatureSummaryStatement->execute([
            'enattente' => 'enattente',
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
            FROM offreemploi o
            LEFT JOIN candidature c ON c.offreid = o.id
            GROUP BY o.id, o.titre, o.lieu, o.statut, o.datecreation
            ORDER BY candidatures_count DESC, o.datecreation DESC
            LIMIT 8';
        $topOffersStatement = $this->pdo->query($topOffersSql);
        $topOffers = $topOffersStatement ? $topOffersStatement->fetchAll(PDO::FETCH_ASSOC) : [];

        $expiringOffersSql = 'SELECT
                id,
                titre,
                lieu,
                datelimite,
                DATEDIFF(datelimite, CURDATE()) AS days_left
            FROM offreemploi
            WHERE statut = :statut
              AND datelimite BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 14 DAY)
            ORDER BY datelimite ASC
            LIMIT 6';
        $expiringOffersStatement = $this->pdo->prepare($expiringOffersSql);
        $expiringOffersStatement->execute(['statut' => 'ouverte']);
        $expiringOffers = $expiringOffersStatement ? $expiringOffersStatement->fetchAll(PDO::FETCH_ASSOC) : [];

        $pendingRepliesSql = 'SELECT
                c.id,
                c.nom,
                c.prenom,
                c.datecandidature,
                o.titre AS offre_titre
            FROM candidature c
            LEFT JOIN offreemploi o ON o.id = c.offreid
            WHERE c.statut = :statut
            ORDER BY c.datecandidature DESC
            LIMIT 6';
        $pendingRepliesStatement = $this->pdo->prepare($pendingRepliesSql);
        $pendingRepliesStatement->execute(['statut' => 'enattente']);
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

        include __DIR__ . '/../../views/back/offreemploi/stats.php';
    }

    public function details(int $id): void
    {
        $offre = $this->getOffreById($id);

        if (!$offre) {
            http_response_code(404);
            echo '<h1>Offre non trouvee</h1>';
            return;
        }

        include __DIR__ . '/../../views/back/offreemploi/details.php';
    }

    public function ajouter(): void
    {
        $fieldErrors = [];
        $formData = [
            'titre' => '',
            'description' => '',
            'competencesrequises' => '',
            'lieu' => '',
            'typecontrat' => '',
            'salairemin' => '',
            'salairemax' => '',
            'datelimite' => '',
            'statut' => 'ouverte',
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $formData = [
                'titre' => trim($_POST['titre'] ?? ''),
                'description' => trim($_POST['description'] ?? ''),
                'competencesrequises' => trim($_POST['competencesrequises'] ?? ''),
                'lieu' => trim($_POST['lieu'] ?? ''),
                'typecontrat' => trim($_POST['typecontrat'] ?? ''),
                'salairemin' => trim((string) ($_POST['salairemin'] ?? '')),
                'salairemax' => trim((string) ($_POST['salairemax'] ?? '')),
                'datelimite' => trim($_POST['datelimite'] ?? ''),
                'statut' => trim($_POST['statut'] ?? 'ouverte'),
            ];

            $data = [
                'titre' => $formData['titre'],
                'description' => $formData['description'],
                'competencesrequises' => $formData['competencesrequises'],
                'lieu' => $formData['lieu'],
                'typecontrat' => $formData['typecontrat'],
                'salairemin' => $formData['salairemin'] !== '' ? (float) $formData['salairemin'] : null,
                'salairemax' => $formData['salairemax'] !== '' ? (float) $formData['salairemax'] : null,
                'datelimite' => $formData['datelimite'],
                'statut' => $formData['statut'],
            ];

            $fieldErrors = $this->validateOffreData($data);
            if (!empty($fieldErrors)) {
                $error = 'Merci de corriger les erreurs du formulaire.';
                include __DIR__ . '/../../views/back/offreemploi/ajouter.php';
                return;
            }

            $this->createOffre($data);
            header('Location: index.php?espace=back&module=offreemploi&action=liste&success=ajoutee');
            exit;
        }

        include __DIR__ . '/../../views/back/offreemploi/ajouter.php';
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
                'competencesrequises' => trim($_POST['competencesrequises'] ?? ''),
                'lieu' => trim($_POST['lieu'] ?? ''),
                'typecontrat' => trim($_POST['typecontrat'] ?? ''),
                'salairemin' => ($_POST['salairemin'] ?? '') !== '' ? (float) $_POST['salairemin'] : null,
                'salairemax' => ($_POST['salairemax'] ?? '') !== '' ? (float) $_POST['salairemax'] : null,
                'datelimite' => trim($_POST['datelimite'] ?? ''),
                'statut' => trim($_POST['statut'] ?? 'ouverte'),
            ];

            $fieldErrors = $this->validateOffreData($data);
            if (!empty($fieldErrors)) {
                $error = 'Merci de corriger les erreurs du formulaire.';
                $offre = array_merge($offre, [
                    'titre' => $data['titre'],
                    'description' => $data['description'],
                    'competencesrequises' => $data['competencesrequises'],
                    'lieu' => $data['lieu'],
                    'typecontrat' => $data['typecontrat'],
                    'salairemin' => $data['salairemin'],
                    'salairemax' => $data['salairemax'],
                    'datelimite' => $data['datelimite'],
                    'statut' => $data['statut'],
                ]);
                include __DIR__ . '/../../views/back/offreemploi/modifier.php';
                return;
            }

            $this->updateOffre($id, $data);
            header('Location: index.php?espace=back&module=offreemploi&action=liste&success=modifiee');
            exit;
        }

        include __DIR__ . '/../../views/back/offreemploi/modifier.php';
    }

    public function supprimer(int $id): void
    {
        $this->deleteOffre($id);
        header('Location: index.php?espace=back&module=offreemploi&action=liste');
        exit;
    }
}
