<?php

declare(strict_types=1);

class CandidatureController
{
    private Candidature $model;
    private OffreEmploi $offreModel;

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
        $this->model = new Candidature($pdo);
        $this->offreModel = new OffreEmploi($pdo);
    }

    private function getOffers(): array
    {
        return $this->offreModel->getAll();
    }

    public function liste(): void
    {
        $candidatures = $this->model->getAll();
        include __DIR__ . '/../../views/back/candidature/liste.php';
    }

    public function details(int $id): void
    {
        $candidature = $this->model->getById($id);

        if (!$candidature) {
            http_response_code(404);
            echo '<h1>Candidature non trouvee</h1>';
            return;
        }

        include __DIR__ . '/../../views/back/candidature/details.php';
    }

    public function modifier(int $id): void
    {
        $candidature = $this->model->getById($id);

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
                'cvurl' => trim($_POST['cvurl'] ?? ''),
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
            if ($formData['cvurl'] === '') {
                $fieldErrors['cvurl'] = 'Le lien du CV est obligatoire.';
            } elseif (!$this->isHttpUrl($formData['cvurl'])) {
                $fieldErrors['cvurl'] = 'Le lien du CV doit etre une URL valide en http:// ou https://.';
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

            $this->model->update($id, [
                'nom' => $formData['nom'],
                'prenom' => $formData['prenom'],
                'lettremotivation' => $formData['lettremotivation'],
                'cvurl' => $formData['cvurl'],
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
        $this->model->delete($id);
        header('Location: index.php?espace=back&module=candidature&action=liste');
        exit;
    }
}
