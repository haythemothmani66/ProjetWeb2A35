<?php

declare(strict_types=1);

class OffreEmploiController
{
    private OffreEmploi $model;

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

            $newId = $this->model->create($data);
            header('Location: index.php?espace=back&module=offreemploi&action=details&id=' . $newId);
            exit;
        }

        include __DIR__ . '/../../views/back/offreemploi/ajouter.php';
    }

    public function modifier(int $id): void
    {
        $offre = $this->model->getById($id);
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
