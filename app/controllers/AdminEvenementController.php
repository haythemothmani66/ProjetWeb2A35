<?php

require_once dirname(__DIR__) . '/helpers/ImageHelper.php';

class AdminEvenementController extends Controller {
    public function __construct() {
        $this->requireAdmin();
    }

    private function db() {
        $database = new Database();
        return $database->getConnection();
    }

    private function getAllEvenementsWithCategorie($search = '', $sort = 'date_asc', $statusFilter = '') {
        $allowedSorts = [
            'date_asc' => 'e.date_debut ASC',
            'date_desc' => 'e.date_debut DESC',
            'titre_asc' => 'e.titre ASC',
            'titre_desc' => 'e.titre DESC',
            'statut_asc' => 'e.statut ASC',
            'statut_desc' => 'e.statut DESC',
            'categorie_asc' => 'c.nom_categorie ASC',
            'categorie_desc' => 'c.nom_categorie DESC'
        ];
        $orderBy = $allowedSorts[$sort] ?? $allowedSorts['date_asc'];

        $query = "SELECT e.*, c.nom_categorie
                  FROM evenements e
                  LEFT JOIN categories c ON e.id_categorie = c.id_categorie";
        $conditions = [];
        if ($search !== '') {
            $conditions[] = "(e.titre LIKE :search
                        OR e.description LIKE :search
                        OR e.statut LIKE :search
                        OR e.type_evenement LIKE :search
                        OR c.nom_categorie LIKE :search)";
        }
        if ($statusFilter !== '') {
            $conditions[] = "e.statut = :status_filter";
        }
        if (!empty($conditions)) {
            $query .= " WHERE " . implode(' AND ', $conditions);
        }
        $query .= " ORDER BY " . $orderBy;

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

    private function getAllEvenementsRaw() {
        $stmt = $this->db()->prepare("SELECT e.*, c.nom_categorie
                  FROM evenements e
                  LEFT JOIN categories c ON e.id_categorie = c.id_categorie
                  ORDER BY e.date_debut ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function buildEvenementStats($evenements) {
        $stats = [
            'total' => count($evenements),
            'planifie' => 0,
            'en_cours' => 0,
            'termine' => 0,
            'annule' => 0
        ];

        foreach ($evenements as $evenement) {
            $statut = $evenement['statut'] ?? '';
            if ($statut === 'planifié') {
                $stats['planifie']++;
            } elseif ($statut === 'en cours') {
                $stats['en_cours']++;
            } elseif ($statut === 'terminé') {
                $stats['termine']++;
            } elseif ($statut === 'annulé') {
                $stats['annule']++;
            }
        }

        return $stats;
    }

    private function getAllCategories() {
        $stmt = $this->db()->prepare('SELECT * FROM categories ORDER BY date_creation DESC');
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

    private function createEvenement(Evenement $evenement) {
        $query = "INSERT INTO evenements (id_categorie, titre, description, type_evenement, date_debut, date_fin, heure_debut, heure_fin, partenariat, capacite_max, nb_places_disponibles, lieu, lien_acces, organisateur, statut, image_evenement)
                  VALUES (:id_cat, :titre, :description, :type, :date_debut, :date_fin, :heure_debut, :heure_fin, :partenariat, :capacite, :places, :lieu, :lien, :organisateur, :statut, :image)";
        $stmt = $this->db()->prepare($query);
        $stmt->bindValue(':id_cat', (int) $evenement->id_categorie, PDO::PARAM_INT);
        $stmt->bindValue(':titre', $evenement->titre);
        $stmt->bindValue(':description', $evenement->description);
        $stmt->bindValue(':type', $evenement->type_evenement);
        $stmt->bindValue(':date_debut', $evenement->date_debut);
        $stmt->bindValue(':date_fin', $evenement->date_fin);
        $stmt->bindValue(':heure_debut', $evenement->heure_debut);
        $stmt->bindValue(':heure_fin', $evenement->heure_fin);
        $stmt->bindValue(':partenariat', $evenement->partenariat);
        $stmt->bindValue(':capacite', (int) $evenement->capacite_max, PDO::PARAM_INT);
        $stmt->bindValue(':places', (int) $evenement->nb_places_disponibles, PDO::PARAM_INT);
        $stmt->bindValue(':lieu', $evenement->lieu);
        $stmt->bindValue(':lien', $evenement->lien_acces);
        $stmt->bindValue(':organisateur', $evenement->organisateur);
        $stmt->bindValue(':statut', $evenement->statut);
        $stmt->bindValue(':image', $evenement->image_evenement);
        return $stmt->execute();
    }

    private function updateEvenement(Evenement $evenement) {
        $query = "UPDATE evenements
                  SET id_categorie = :id_cat, titre = :titre, description = :description, type_evenement = :type,
                      date_debut = :date_debut, date_fin = :date_fin, heure_debut = :heure_debut, heure_fin = :heure_fin,
                      partenariat = :partenariat, capacite_max = :capacite, nb_places_disponibles = :places,
                      lieu = :lieu, lien_acces = :lien, organisateur = :organisateur, statut = :statut, image_evenement = :image
                  WHERE id_evenement = :id";
        $stmt = $this->db()->prepare($query);
        $stmt->bindValue(':id_cat', (int) $evenement->id_categorie, PDO::PARAM_INT);
        $stmt->bindValue(':titre', $evenement->titre);
        $stmt->bindValue(':description', $evenement->description);
        $stmt->bindValue(':type', $evenement->type_evenement);
        $stmt->bindValue(':date_debut', $evenement->date_debut);
        $stmt->bindValue(':date_fin', $evenement->date_fin);
        $stmt->bindValue(':heure_debut', $evenement->heure_debut);
        $stmt->bindValue(':heure_fin', $evenement->heure_fin);
        $stmt->bindValue(':partenariat', $evenement->partenariat);
        $stmt->bindValue(':capacite', (int) $evenement->capacite_max, PDO::PARAM_INT);
        $stmt->bindValue(':places', (int) $evenement->nb_places_disponibles, PDO::PARAM_INT);
        $stmt->bindValue(':lieu', $evenement->lieu);
        $stmt->bindValue(':lien', $evenement->lien_acces);
        $stmt->bindValue(':organisateur', $evenement->organisateur);
        $stmt->bindValue(':statut', $evenement->statut);
        $stmt->bindValue(':image', $evenement->image_evenement);
        $stmt->bindValue(':id', (int) $evenement->id_evenement, PDO::PARAM_INT);
        return $stmt->execute();
    }

    private function deleteEvenementById($id) {
        $stmt = $this->db()->prepare('DELETE FROM evenements WHERE id_evenement = :id');
        $stmt->bindValue(':id', (int) $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    private function getParticipationsByEvenementIds($evenementIds) {
        if (empty($evenementIds)) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($evenementIds), '?'));
        $query = "SELECT p.* FROM participations p
                  WHERE p.id_evenement IN (" . $placeholders . ")
                  ORDER BY p.id_evenement ASC, p.date_inscription DESC";
        $stmt = $this->db()->prepare($query);
        foreach (array_values($evenementIds) as $index => $id) {
            $stmt->bindValue($index + 1, (int) $id, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function index() {
        $search = trim($_GET['search'] ?? '');
        $sort = trim($_GET['sort'] ?? 'date_asc');
        $statusFilter = trim($_GET['status_filter'] ?? '');
        $allowedStatusFilters = ['', 'planifié', 'en cours', 'terminé', 'annulé'];
        if (!in_array($statusFilter, $allowedStatusFilters, true)) {
            $statusFilter = '';
        }

        $evenements = $this->getAllEvenementsWithCategorie($search, $sort, $statusFilter);
        $stats = $this->buildEvenementStats($this->getAllEvenementsRaw());

        $evenementIds = array_column($evenements, 'id_evenement');
        $participations = $this->getParticipationsByEvenementIds($evenementIds);
        $participationsParEvenement = [];
        foreach ($participations as $participation) {
            $evenementId = (int) $participation['id_evenement'];
            if (!isset($participationsParEvenement[$evenementId])) {
                $participationsParEvenement[$evenementId] = [];
            }
            $participationsParEvenement[$evenementId][] = $participation;
        }

        $this->view('back/evenements/index', [
            'evenements' => $evenements,
            'stats' => $stats,
            'filters' => [
                'search' => $search,
                'sort' => $sort,
                'status_filter' => $statusFilter
            ],
            'participationsParEvenement' => $participationsParEvenement,
            'flash' => $this->getFlash()
        ]);
    }

    public function stats() {
        $evenements = $this->getAllEvenementsRaw();
        $stats = $this->buildEvenementStats($evenements);

        $evolution = [];
        foreach ($evenements as $evenement) {
            $dateKey = (string) ($evenement['date_debut'] ?? '');
            if ($dateKey === '') {
                continue;
            }
            if (!isset($evolution[$dateKey])) {
                $evolution[$dateKey] = 0;
            }
            $evolution[$dateKey]++;
        }
        ksort($evolution);

        $this->view('back/evenements/stats', [
            'stats' => $stats,
            'evolutionLabels' => array_keys($evolution),
            'evolutionData' => array_values($evolution)
        ]);
    }

    public function create() {
        $categories = $this->getAllCategories();
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
            $evenement->image_evenement = ImageHelper::normalizeEventImageUrl(trim($_POST['image_evenement']));

            if ($this->createEvenement($evenement)) {
                $this->setFlash('success', 'Evenement ajoute avec succes.');
                $this->redirect('/AdminEvenement/index');
            }

            $errors['general'] = "Impossible d'ajouter l'evenement.";
        }
        $this->view('back/evenements/create', ['categories' => $categories, 'errors' => $errors, 'old' => $old]);
    }

    public function edit($id) {
        $evenement = $this->model('Evenement');
        $record = $this->getEvenementById($id);
        if (!$record) {
            $this->setFlash('danger', 'Evenement introuvable.');
            $this->redirect('/AdminEvenement/index');
        }

        $categories = $this->getAllCategories();

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
            $evenement->image_evenement = ImageHelper::normalizeEventImageUrl(trim($_POST['image_evenement']));

            if ($this->updateEvenement($evenement)) {
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
        if ($this->deleteEvenementById($id)) {
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
        } elseif ($dateDebut < date('Y-m-d')) {
            $errors['date_debut'] = "La date de debut doit etre superieure ou egale a aujourd'hui.";
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
