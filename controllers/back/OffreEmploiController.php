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
        include __DIR__ . '/../../views/back/offreemploi/liste.php';
    }

    public function details(int $id): void
    {
        $offre = $this->model->getById($id);

        if (!$offre) {
            http_response_code(404);
            echo '<h1>Offre non trouvee</h1>';
            return;
        }

        include __DIR__ . '/../../views/back/offreemploi/details.php';
    }

    public function ajouter(): void
    {
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

            if ($data['titre'] === '' || $data['description'] === '' || $data['lieu'] === '' || $data['typecontrat'] === '' || $data['datelimite'] === '') {
                $error = 'Les champs obligatoires doivent etre remplis.';
                include __DIR__ . '/../../views/back/offreemploi/ajouter.php';
                return;
            }

            $newId = $this->model->create($data);
            header('Location: index.php?espace=back&module=offreemploi&action=details&id=' . $newId);
            exit;
        }

        include __DIR__ . '/../../views/back/offreemploi/ajouter.php';
    }

    public function modifier(int $id): void
    {
        $offre = $this->model->getById($id);

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

            if ($data['titre'] === '' || $data['description'] === '' || $data['lieu'] === '' || $data['typecontrat'] === '' || $data['datelimite'] === '') {
                $error = 'Les champs obligatoires doivent etre remplis.';
                include __DIR__ . '/../../views/back/offreemploi/modifier.php';
                return;
            }

            $this->model->update($id, $data);
            header('Location: index.php?espace=back&module=offreemploi&action=details&id=' . $id);
            exit;
        }

        include __DIR__ . '/../../views/back/offreemploi/modifier.php';
    }

    public function supprimer(int $id): void
    {
        $this->model->delete($id);
        header('Location: index.php?espace=back&module=offreemploi&action=liste');
        exit;
    }
}
