<?php

declare(strict_types=1);

class CandidatureController
{
    private Candidature $candidatureModel;
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
        $this->candidatureModel = new Candidature($pdo);
        $this->offreModel = new OffreEmploi($pdo);
    }

    private function getOpenOffers(): array
    {
        $offers = $this->offreModel->getAll();

        return array_values(array_filter($offers, static function (array $offre): bool {
            return ($offre['statut'] ?? '') === 'ouverte';
        }));
    }

    public function liste(): void
    {
        $offres = $this->getOpenOffers();
        include __DIR__ . '/../../views/front/candidature/liste.php';
    }

    public function ajouter(): void
    {
        $selectedOffer = null;
        $errors = [];
        $fieldErrors = [];
        $formData = [
            'offreid' => '',
            'nom' => '',
            'prenom' => '',
            'email' => '',
            'cvurl' => '',
            'lettremotivation' => '',
        ];

        if (isset($_GET['offreid']) && (int) $_GET['offreid'] > 0) {
            $formData['offreid'] = (string) (int) $_GET['offreid'];
            $selectedOffer = $this->offreModel->getById((int) $formData['offreid']);
            if ($selectedOffer && ($selectedOffer['statut'] ?? '') !== 'ouverte') {
                $selectedOffer = null;
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $formData = [
                'offreid' => trim($_POST['offreid'] ?? ''),
                'nom' => trim($_POST['nom'] ?? ''),
                'prenom' => trim($_POST['prenom'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'cvurl' => trim($_POST['cvurl'] ?? ''),
                'lettremotivation' => trim($_POST['lettremotivation'] ?? ''),
            ];

            if ($formData['offreid'] === '') {
                $fieldErrors['offreid'] = 'Offre invalide. Veuillez revenir a la liste des offres.';
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

            $selectedOffer = $this->offreModel->getById((int) $formData['offreid']);
            if (!$selectedOffer || ($selectedOffer['statut'] ?? '') !== 'ouverte') {
                $fieldErrors['offreid'] = 'Offre invalide. Veuillez revenir a la liste et cliquer sur Postuler depuis une offre ouverte.';
            }

            $errors = array_values($fieldErrors);

            if (!empty($errors)) {
                foreach (array_keys($fieldErrors) as $fieldName) {
                    if (array_key_exists($fieldName, $formData)) {
                        $formData[$fieldName] = '';
                    }
                }
                include __DIR__ . '/../../views/front/candidature/ajouter.php';
                return;
            }

            $newId = $this->candidatureModel->create([
                'nom' => $formData['nom'],
                'prenom' => $formData['prenom'],
                'lettremotivation' => $formData['lettremotivation'],
                'cvurl' => $formData['cvurl'],
                'email' => $formData['email'],
                'offreid' => (int) $formData['offreid'],
                'statut' => 'enattente',
            ]);

            header('Location: index.php?espace=front&module=candidature&action=details&id=' . $newId);
            exit;
        }

        if (!$selectedOffer) {
            $fieldErrors['offreid'] = 'Aucune offre valide selectionnee. Veuillez revenir a la liste des offres pour postuler.';
            $errors = array_values($fieldErrors);
        }

        include __DIR__ . '/../../views/front/candidature/ajouter.php';
    }

    public function details(int $id): void
    {
        $candidature = $this->candidatureModel->getById($id);

        if (!$candidature) {
            http_response_code(404);
            echo '<h1>Candidature non trouvee</h1>';
            return;
        }

        include __DIR__ . '/../../views/front/candidature/details.php';
    }
}
