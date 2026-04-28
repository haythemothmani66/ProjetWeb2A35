<?php
class AdminCategorieController extends Controller {
    private function db() {
        $database = new Database();
        return $database->getConnection();
    }

    private function getAllCategories($search = '', $sort = 'date_desc', $statusFilter = '') {
        $allowedSorts = [
            'date_desc' => 'date_creation DESC',
            'date_asc' => 'date_creation ASC',
            'nom_asc' => 'nom_categorie ASC',
            'nom_desc' => 'nom_categorie DESC',
            'statut_asc' => 'statut ASC',
            'statut_desc' => 'statut DESC'
        ];
        $orderBy = $allowedSorts[$sort] ?? $allowedSorts['date_desc'];

        $query = 'SELECT * FROM categories';
        $conditions = [];
        if ($search !== '') {
            $conditions[] = '(nom_categorie LIKE :search OR description LIKE :search OR statut LIKE :search)';
        }
        if ($statusFilter !== '') {
            $conditions[] = 'statut = :status_filter';
        }
        if (!empty($conditions)) {
            $query .= ' WHERE ' . implode(' AND ', $conditions);
        }
        $query .= ' ORDER BY ' . $orderBy;

        $stmt = $this->db()->prepare($query);
        if ($search !== '') {
            $stmt->bindValue(':search', '%' . $search . '%');
        }
        if ($statusFilter !== '') {
            $stmt->bindValue(':status_filter', $statusFilter);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getAllCategoriesRaw() {
        $stmt = $this->db()->prepare('SELECT * FROM categories ORDER BY date_creation DESC');
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function buildCategorieStats($categories) {
        $stats = [
            'total' => count($categories),
            'actif' => 0,
            'inactif' => 0
        ];

        foreach ($categories as $categorie) {
            if (($categorie['statut'] ?? '') === 'actif') {
                $stats['actif']++;
            }
            if (($categorie['statut'] ?? '') === 'inactif') {
                $stats['inactif']++;
            }
        }

        return $stats;
    }

    private function getCategorieById($id) {
        $stmt = $this->db()->prepare('SELECT * FROM categories WHERE id_categorie = :id LIMIT 1');
        $stmt->bindValue(':id', (int) $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function createCategorie(Categorie $categorie) {
        $stmt = $this->db()->prepare('INSERT INTO categories (nom_categorie, description, couleur, statut) VALUES (:nom, :description, :couleur, :statut)');
        $stmt->bindValue(':nom', $categorie->nom_categorie);
        $stmt->bindValue(':description', $categorie->description);
        $stmt->bindValue(':couleur', $categorie->couleur);
        $stmt->bindValue(':statut', $categorie->statut);
        return $stmt->execute();
    }

    private function updateCategorie(Categorie $categorie) {
        $stmt = $this->db()->prepare('UPDATE categories SET nom_categorie = :nom, description = :description, couleur = :couleur, statut = :statut WHERE id_categorie = :id');
        $stmt->bindValue(':nom', $categorie->nom_categorie);
        $stmt->bindValue(':description', $categorie->description);
        $stmt->bindValue(':couleur', $categorie->couleur);
        $stmt->bindValue(':statut', $categorie->statut);
        $stmt->bindValue(':id', (int) $categorie->id_categorie, PDO::PARAM_INT);
        return $stmt->execute();
    }

    private function deleteCategorieById($id) {
        $stmt = $this->db()->prepare('DELETE FROM categories WHERE id_categorie = :id');
        $stmt->bindValue(':id', (int) $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function index() {
        $search = trim($_GET['search'] ?? '');
        $sort = trim($_GET['sort'] ?? 'date_desc');
        $statusFilter = trim($_GET['status_filter'] ?? '');
        $allowedStatusFilters = ['', 'actif', 'inactif'];
        if (!in_array($statusFilter, $allowedStatusFilters, true)) {
            $statusFilter = '';
        }

        $categories = $this->getAllCategories($search, $sort, $statusFilter);
        $stats = $this->buildCategorieStats($this->getAllCategoriesRaw());

        $this->view('back/categories/index', [
            'categories' => $categories,
            'stats' => $stats,
            'filters' => [
                'search' => $search,
                'sort' => $sort,
                'status_filter' => $statusFilter
            ],
            'flash' => $this->getFlash()
        ]);
    }

    public function stats() {
        $categories = $this->getAllCategoriesRaw();
        $stats = $this->buildCategorieStats($categories);

        $evolution = [];
        foreach ($categories as $categorie) {
            $dateKey = substr((string) ($categorie['date_creation'] ?? ''), 0, 10);
            if ($dateKey === '') {
                continue;
            }
            if (!isset($evolution[$dateKey])) {
                $evolution[$dateKey] = 0;
            }
            $evolution[$dateKey]++;
        }
        ksort($evolution);

        $this->view('back/categories/stats', [
            'stats' => $stats,
            'evolutionLabels' => array_keys($evolution),
            'evolutionData' => array_values($evolution)
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

            if ($this->createCategorie($categorie)) {
                $this->setFlash('success', 'Categorie ajoutee avec succes.');
                $this->redirect('/AdminCategorie/index');
            }

            $errors['general'] = "Impossible d'ajouter la categorie.";
        }

        $this->view('back/categories/create', ['errors' => $errors, 'old' => $old]);
    }

    public function edit($id) {
        $categorie = $this->model('Categorie');
        $record = $this->getCategorieById($id);
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

            if ($this->updateCategorie($categorie)) {
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
        if ($this->deleteCategorieById($id)) {
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
