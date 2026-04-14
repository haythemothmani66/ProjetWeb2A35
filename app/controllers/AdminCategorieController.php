<?php
class AdminCategorieController extends Controller {
    public function index() {
        $categorieModel = $this->model('Categorie');
        $categories = $categorieModel->read()->fetchAll(PDO::FETCH_ASSOC);
        $this->view('back/categories/index', [
            'categories' => $categories,
            'flash' => $this->getFlash()
        ]);
    }

    public function create() {
        $errors = [];
        $old = [];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $old = $_POST;
            $errors = $this->validateCategorie($_POST);

            if (!empty($errors)) {
                $this->view('back/categories/create', ['errors' => $errors, 'old' => $old]);
                return;
            }

            $categorie = $this->model('Categorie');
            $categorie->nom_categorie = trim($_POST['nom_categorie']);
            $categorie->description = trim($_POST['description']);
            $categorie->couleur = trim($_POST['couleur']);
            $categorie->statut = trim($_POST['statut']);

            if ($categorie->create()) {
                $this->setFlash('success', 'Categorie ajoutee avec succes.');
                $this->redirect('/AdminCategorie/index');
            }

            $errors['general'] = "Impossible d'ajouter la categorie.";
        }

        $this->view('back/categories/create', ['errors' => $errors, 'old' => $old]);
    }

    public function edit($id) {
        $categorie = $this->model('Categorie');
        $record = $categorie->find((int) $id);
        if (!$record) {
            $this->setFlash('danger', 'Categorie introuvable.');
            $this->redirect('/AdminCategorie/index');
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $errors = $this->validateCategorie($_POST);
            if (!empty($errors)) {
                $record = array_merge($record, $_POST);
                $this->view('back/categories/edit', ['categorie' => $record, 'errors' => $errors]);
                return;
            }

            $categorie->id_categorie = (int) $id;
            $categorie->nom_categorie = trim($_POST['nom_categorie']);
            $categorie->description = trim($_POST['description']);
            $categorie->couleur = trim($_POST['couleur']);
            $categorie->statut = trim($_POST['statut']);

            if ($categorie->update()) {
                $this->setFlash('success', 'Categorie modifiee avec succes.');
                $this->redirect('/AdminCategorie/index');
            }

            $errors['general'] = 'Impossible de modifier la categorie.';
            $record = array_merge($record, $_POST);
            $this->view('back/categories/edit', ['categorie' => $record, 'errors' => $errors]);
            return;
        }

        $this->view('back/categories/edit', ['categorie' => $record, 'errors' => []]);
    }

    public function delete($id) {
        $categorie = $this->model('Categorie');
        $categorie->id_categorie = (int) $id;
        if ($categorie->delete()) {
            $this->setFlash('success', 'Categorie supprimee.');
        } else {
            $this->setFlash('danger', 'Suppression impossible (categorie peut etre liee a un evenement).');
        }
        $this->redirect('/AdminCategorie/index');
    }

    private function validateCategorie($input) {
        $errors = [];
        $nom = trim($input['nom_categorie'] ?? '');
        $couleur = trim($input['couleur'] ?? '');
        $statut = trim($input['statut'] ?? '');
        $allowedColors = ['#1F5DB8', '#2D79DF', '#32B7C5', '#F2B705', '#F26A4B', '#6A5ACD'];

        if (mb_strlen($nom) < 3) {
            $errors['nom_categorie'] = 'Le nom doit contenir au moins 3 caracteres.';
        }

        if (!in_array($couleur, $allowedColors, true)) {
            $errors['couleur'] = 'Couleur invalide.';
        }

        if (!in_array($statut, ['actif', 'inactif'], true)) {
            $errors['statut'] = 'Statut invalide.';
        }

        return $errors;
    }
}
?>
