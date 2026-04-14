<?php
class HomeController extends Controller {
    public function index() {
        $evenementModel = $this->model('Evenement');
        $evenements = $evenementModel->readActive()->fetchAll(PDO::FETCH_ASSOC);
        $this->view('front/index', [
            'evenements' => $evenements,
            'flash' => $this->getFlash()
        ]);
    }

    public function detail($id) {
        $evenement = $this->model('Evenement');
        $record = $evenement->find((int) $id);

        if (!$record) {
            $this->setFlash('danger', "L'événement demandé est introuvable.");
            $this->redirect('/Home/index');
        }

        $this->view('front/detail', ['evenement' => $record]);
    }

    public function register($id) {
        $evenement = $this->model('Evenement');
        $record = $evenement->find((int) $id);

        if (!$record) {
            $this->setFlash('danger', "L'événement demandé est introuvable.");
            $this->redirect('/Home/index');
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if ((int) $record['nb_places_disponibles'] <= 0) {
                $this->setFlash('danger', "Il n'y a plus de places disponibles.");
                $this->redirect('/Home/detail/' . (int) $id);
            }

            $errors = $this->validateRegistration($_POST);
            if (!empty($errors)) {
                $this->view('front/register', [
                    'evenement' => $record,
                    'errors' => $errors,
                    'old' => $_POST
                ]);
                return;
            }

            $participation = $this->model('Participation');
            $participation->id_evenement = (int) $id;
            $participation->id_user = 1;
            $participation->nom_participant = trim($_POST['nom_participant']);
            $participation->email = trim($_POST['email']);
            $participation->telephone = trim($_POST['telephone']);
            $participation->mode_participation = trim($_POST['mode_participation']);
            $participation->statut_participation = 'inscrit';

            if ($participation->create() && $evenement->decrementPlaces((int) $id)) {
                $this->setFlash('success', 'Votre inscription a ete enregistree avec succes.');
                $this->redirect('/Home/index');
            }

            $errors['general'] = "Une erreur est survenue pendant l'inscription.";
            $this->view('front/register', ['evenement' => $record, 'errors' => $errors, 'old' => $_POST]);
            return;
        }

        $this->view('front/register', ['evenement' => $record, 'errors' => [], 'old' => []]);
    }

    private function validateRegistration($input) {
        $errors = [];

        $nom = trim($input['nom_participant'] ?? '');
        $email = trim($input['email'] ?? '');
        $telephone = trim($input['telephone'] ?? '');
        $mode = trim($input['mode_participation'] ?? '');

        if (mb_strlen($nom) < 3) {
            $errors['nom_participant'] = 'Le nom doit contenir au moins 3 caracteres.';
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Adresse email invalide.';
        }

        if (!preg_match('/^[0-9+\s]{8,20}$/', $telephone)) {
            $errors['telephone'] = 'Telephone invalide (8 chiffres minimum).';
        }

        if (!in_array($mode, ['en ligne', 'présentiel'], true)) {
            $errors['mode_participation'] = 'Mode de participation invalide.';
        }

        return $errors;
    }
}
?>
