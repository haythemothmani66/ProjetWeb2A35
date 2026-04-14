<?php
class AdminEvenementController extends Controller {
    public function index() {
        $evenementModel = $this->model('Evenement');
        $evenements = $evenementModel->read()->fetchAll(PDO::FETCH_ASSOC);
        $this->view('back/evenements/index', [
            'evenements' => $evenements,
            'flash' => $this->getFlash()
        ]);
    }

    public function create() {
        $categorieModel = $this->model('Categorie');
        $categories = $categorieModel->read()->fetchAll(PDO::FETCH_ASSOC);
        $errors = [];
        $old = [];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $old = $_POST;
            $errors = $this->validateEvenement($_POST);
            if (!empty($errors)) {
                $this->view('back/evenements/create', ['categories' => $categories, 'errors' => $errors, 'old' => $old]);
                return;
            }

            $evenement = $this->model('Evenement');
            $evenement->id_categorie = (int) $_POST['id_categorie'];
            $evenement->titre = trim($_POST['titre']);
            $evenement->description = trim($_POST['description']);
            $evenement->type_evenement = trim($_POST['type_evenement']);
            $evenement->date_debut = trim($_POST['date_debut']);
            $evenement->date_fin = trim($_POST['date_fin']);
            $evenement->heure_debut = trim($_POST['heure_debut']);
            $evenement->heure_fin = trim($_POST['heure_fin']);
            $evenement->partenariat = trim($_POST['partenariat']);
            $evenement->capacite_max = (int) $_POST['capacite_max'];
            $evenement->nb_places_disponibles = (int) $_POST['capacite_max'];
            $evenement->lieu = trim($_POST['lieu']);
            $evenement->lien_acces = trim($_POST['lien_acces']);
            $evenement->organisateur = trim($_POST['organisateur']);
            $evenement->statut = trim($_POST['statut']);
            $evenement->image_evenement = trim($_POST['image_evenement']);

            if ($evenement->create()) {
                $this->setFlash('success', 'Evenement ajoute avec succes.');
                $this->redirect('/AdminEvenement/index');
            }

            $errors['general'] = "Impossible d'ajouter l'evenement.";
        }
        $this->view('back/evenements/create', ['categories' => $categories, 'errors' => $errors, 'old' => $old]);
    }

    public function edit($id) {
        $evenement = $this->model('Evenement');
        $record = $evenement->find((int) $id);
        if (!$record) {
            $this->setFlash('danger', 'Evenement introuvable.');
            $this->redirect('/AdminEvenement/index');
        }

        $categorieModel = $this->model('Categorie');
        $categories = $categorieModel->read()->fetchAll(PDO::FETCH_ASSOC);

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $errors = $this->validateEvenement($_POST, true);
            if (!empty($errors)) {
                $record = array_merge($record, $_POST);
                $this->view('back/evenements/edit', ['evenement' => $record, 'categories' => $categories, 'errors' => $errors]);
                return;
            }

            $evenement->id_evenement = (int) $id;
            $evenement->id_categorie = (int) $_POST['id_categorie'];
            $evenement->titre = trim($_POST['titre']);
            $evenement->description = trim($_POST['description']);
            $evenement->type_evenement = trim($_POST['type_evenement']);
            $evenement->date_debut = trim($_POST['date_debut']);
            $evenement->date_fin = trim($_POST['date_fin']);
            $evenement->heure_debut = trim($_POST['heure_debut']);
            $evenement->heure_fin = trim($_POST['heure_fin']);
            $evenement->partenariat = trim($_POST['partenariat']);
            $evenement->capacite_max = (int) $_POST['capacite_max'];
            $evenement->nb_places_disponibles = (int) $_POST['nb_places_disponibles'];
            $evenement->lieu = trim($_POST['lieu']);
            $evenement->lien_acces = trim($_POST['lien_acces']);
            $evenement->organisateur = trim($_POST['organisateur']);
            $evenement->statut = trim($_POST['statut']);
            $evenement->image_evenement = trim($_POST['image_evenement']);

            if ($evenement->update()) {
                $this->setFlash('success', 'Evenement modifie avec succes.');
                $this->redirect('/AdminEvenement/index');
            }

            $errors['general'] = "Impossible de modifier l'evenement.";
            $record = array_merge($record, $_POST);
            $this->view('back/evenements/edit', ['evenement' => $record, 'categories' => $categories, 'errors' => $errors]);
            return;
        }
        $this->view('back/evenements/edit', ['evenement' => $record, 'categories' => $categories, 'errors' => []]);
    }

    public function delete($id) {
        $evenement = $this->model('Evenement');
        $evenement->id_evenement = (int) $id;
        if ($evenement->delete()) {
            $this->setFlash('success', 'Evenement supprime.');
        } else {
            $this->setFlash('danger', "Suppression de l'evenement impossible.");
        }
        $this->redirect('/AdminEvenement/index');
    }

    private function validateEvenement($input, $isUpdate = false) {
        $errors = [];

        $titre = trim($input['titre'] ?? '');
        $description = trim($input['description'] ?? '');
        $type = trim($input['type_evenement'] ?? '');
        $dateDebut = trim($input['date_debut'] ?? '');
        $dateFin = trim($input['date_fin'] ?? '');
        $heureDebut = trim($input['heure_debut'] ?? '');
        $heureFin = trim($input['heure_fin'] ?? '');
        $capacite = (int) ($input['capacite_max'] ?? 0);
        $nbPlaces = (int) ($input['nb_places_disponibles'] ?? $capacite);
        $lieu = trim($input['lieu'] ?? '');
        $lien = trim($input['lien_acces'] ?? '');
        $organisateur = trim($input['organisateur'] ?? '');
        $partenariat = trim($input['partenariat'] ?? '');
        $statut = trim($input['statut'] ?? '');

        if (mb_strlen($titre) < 5) {
            $errors['titre'] = 'Le titre doit contenir au moins 5 caracteres.';
        }
        if (mb_strlen($description) < 10) {
            $errors['description'] = 'La description doit contenir au moins 10 caracteres.';
        }
        if (!in_array($type, ['en ligne', 'présentiel', 'hybride'], true)) {
            $errors['type_evenement'] = 'Type evenement invalide.';
        }
        if ($dateDebut === '' || $dateFin === '') {
            $errors['date_debut'] = 'Les dates sont obligatoires.';
        } elseif ($dateFin < $dateDebut) {
            $errors['date_fin'] = 'La date de fin doit etre >= date debut.';
        }
        if ($heureDebut === '' || $heureFin === '') {
            $errors['heure_debut'] = 'Les heures sont obligatoires.';
        } elseif ($heureFin <= $heureDebut) {
            $errors['heure_fin'] = "L'heure de fin doit etre superieure a l'heure de debut.";
        }
        if ($capacite <= 0) {
            $errors['capacite_max'] = 'Capacite invalide.';
        }
        if ($isUpdate && ($nbPlaces < 0 || $nbPlaces > $capacite)) {
            $errors['nb_places_disponibles'] = 'Places disponibles invalides.';
        }
        if ($type === 'en ligne' && $lien === '') {
            $errors['lien_acces'] = "Lien d'acces requis pour evenement en ligne.";
        }
        if ($type === 'présentiel' && $lieu === '') {
            $errors['lieu'] = 'Lieu requis pour evenement presentiel.';
        }
        if (mb_strlen($organisateur) < 3) {
            $errors['organisateur'] = 'Nom organisateur invalide.';
        }
        if (!in_array($partenariat, ['oui', 'non'], true)) {
            $errors['partenariat'] = 'Valeur partenariat invalide.';
        }
        if (!in_array($statut, ['planifié', 'en cours', 'terminé', 'annulé'], true)) {
            $errors['statut'] = 'Statut invalide.';
        }

        return $errors;
    }
}
?>
