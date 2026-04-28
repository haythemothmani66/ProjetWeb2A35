<?php
class HomeController extends Controller {
    private function db() {
        $database = new Database();
        return $database->getConnection();
    }

    private function getActiveEvenements() {
        $query = "SELECT e.*, c.nom_categorie, c.couleur
                  FROM evenements e
                  LEFT JOIN categories c ON e.id_categorie = c.id_categorie
                  WHERE c.statut = 'actif' AND e.statut IN ('planifié', 'en cours')
                  ORDER BY e.date_debut ASC";
        $stmt = $this->db()->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getEvenementById($id) {
        $query = "SELECT e.*, c.nom_categorie, c.couleur
                  FROM evenements e
                  LEFT JOIN categories c ON e.id_categorie = c.id_categorie
                  WHERE e.id_evenement = :id LIMIT 1";
        $stmt = $this->db()->prepare($query);
        $stmt->bindValue(':id', (int) $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function createParticipation(Participation $participation) {
        $query = "INSERT INTO participations (id_evenement, id_user, nom_participant, email, telephone, statut_participation, mode_participation, feedback, note)
                  VALUES (:id_ev, :id_user, :nom, :email, :telephone, :statut, :mode, :feedback, :note)";
        $stmt = $this->db()->prepare($query);
        $stmt->bindValue(':id_ev', (int) $participation->id_evenement, PDO::PARAM_INT);
        $stmt->bindValue(':id_user', (int) $participation->id_user, PDO::PARAM_INT);
        $stmt->bindValue(':nom', $participation->nom_participant);
        $stmt->bindValue(':email', $participation->email);
        $stmt->bindValue(':telephone', $participation->telephone);
        $stmt->bindValue(':statut', $participation->statut_participation);
        $stmt->bindValue(':mode', $participation->mode_participation);
        $stmt->bindValue(':feedback', $participation->feedback);
        $stmt->bindValue(':note', $participation->note);
        return $stmt->execute();
    }

    private function decrementEvenementPlaces($id) {
        $query = "UPDATE evenements
                  SET nb_places_disponibles = nb_places_disponibles - 1
                  WHERE id_evenement = :id AND nb_places_disponibles > 0";
        $stmt = $this->db()->prepare($query);
        $stmt->bindValue(':id', (int) $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function index() {
        $evenements = $this->getActiveEvenements();
        $this->view('front/index', [
            'evenements' => $evenements,
            'flash' => $this->getFlash()
        ]);
    }

    public function detail($id) {
        $record = $this->getEvenementById($id);

        if (!$record) {
            $this->setFlash('danger', "L'événement demandé est introuvable.");
            $this->redirect('/Home/index');
        }

        $this->view('front/detail', ['evenement' => $record]);
    }

    public function register($id) {
        $record = $this->getEvenementById($id);

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
            $participation->feedback = '';
            $participation->note = null;

            if ($this->createParticipation($participation) && $this->decrementEvenementPlaces($id)) {
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
