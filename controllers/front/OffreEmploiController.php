<?php

declare(strict_types=1);

class OffreEmploiController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
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

    public function liste(): void
    {
        $offres = $this->getAllOffres();
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
