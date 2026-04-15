<?php

declare(strict_types=1);

class OffreEmploiController
{
    private OffreEmploi $model;

    public function __construct(PDO $pdo)
    {
        $this->model = new OffreEmploi($pdo);
    }

    public function liste(): void
    {
        $offres = $this->model->getAll();
        include __DIR__ . '/../../views/front/offreemploi/liste.php';
    }

    public function details(int $id): void
    {
        $offre = $this->model->getById($id);

        if (!$offre) {
            http_response_code(404);
            echo '<h1>Offre non trouvee</h1>';
            return;
        }

        include __DIR__ . '/../../views/front/offreemploi/details.php';
    }
}
