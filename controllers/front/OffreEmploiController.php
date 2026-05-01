<?php

declare(strict_types=1);

class OffreEmploiController
{
    private PDO $pdo;

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

        $sql = 'SELECT * FROM offreemploi WHERE statut = "ouverte"';
        $params = [];

        if ($searchTerm !== '') {
            $sql .= ' WHERE (
                titre LIKE :search
                OR description LIKE :search
                OR competencesrequises LIKE :search
                OR lieu LIKE :search
                OR typecontrat LIKE :search
                OR statut LIKE :search
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

        include __DIR__ . '/../../views/front/offreemploi/liste.php';
    }

    public function details(int $id): void
    {
        $offre = $this->getOffreById($id);

        if (!$offre) {
            http_response_code(404);
            echo '<h1>Offre non trouvee</h1>';
            return;
        }

        include __DIR__ . '/../../views/front/offreemploi/details.php';
    }

    public function stats(): void
    {
        $this->liste();
    }
}
