<?php
class Evenement {
    private $conn;
    private $table = "evenements";

    public $id_evenement;
    public $id_categorie;
    public $titre;
    public $description;
    public $type_evenement;
    public $date_debut;
    public $date_fin;
    public $heure_debut;
    public $heure_fin;
    public $partenariat;
    public $capacite_max;
    public $nb_places_disponibles;
    public $lieu;
    public $lien_acces;
    public $organisateur;
    public $statut;
    public $image_evenement;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function read() {
        $query = "SELECT e.*, c.nom_categorie FROM " . $this->table . " e LEFT JOIN categories c ON e.id_categorie = c.id_categorie ORDER BY e.date_debut DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readActive() {
        $query = "SELECT e.*, c.nom_categorie, c.couleur FROM " . $this->table . " e
                  LEFT JOIN categories c ON e.id_categorie = c.id_categorie
                  WHERE c.statut = 'actif' AND e.statut IN ('planifié', 'en cours')
                  ORDER BY e.date_debut ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table . " (id_categorie, titre, description, type_evenement, date_debut, date_fin, heure_debut, heure_fin, partenariat, capacite_max, nb_places_disponibles, lieu, lien_acces, organisateur, statut, image_evenement) VALUES (:id_cat, :titre, :desc, :type, :d_deb, :d_fin, :h_deb, :h_fin, :part, :cap, :nb_p, :lieu, :lien, :org, :stat, :img)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_cat', $this->id_categorie);
        $stmt->bindParam(':titre', $this->titre);
        $stmt->bindParam(':desc', $this->description);
        $stmt->bindParam(':type', $this->type_evenement);
        $stmt->bindParam(':d_deb', $this->date_debut);
        $stmt->bindParam(':d_fin', $this->date_fin);
        $stmt->bindParam(':h_deb', $this->heure_debut);
        $stmt->bindParam(':h_fin', $this->heure_fin);
        $stmt->bindParam(':part', $this->partenariat);
        $stmt->bindParam(':cap', $this->capacite_max);
        $stmt->bindParam(':nb_p', $this->nb_places_disponibles);
        $stmt->bindParam(':lieu', $this->lieu);
        $stmt->bindParam(':lien', $this->lien_acces);
        $stmt->bindParam(':org', $this->organisateur);
        $stmt->bindParam(':stat', $this->statut);
        $stmt->bindParam(':img', $this->image_evenement);
        return $stmt->execute();
    }

    public function update() {
        $query = "UPDATE " . $this->table . " SET id_categorie = :id_cat, titre = :titre, description = :desc, type_evenement = :type, date_debut = :d_deb, date_fin = :d_fin, heure_debut = :h_deb, heure_fin = :h_fin, partenariat = :part, capacite_max = :cap, nb_places_disponibles = :nb_p, lieu = :lieu, lien_acces = :lien, organisateur = :org, statut = :stat, image_evenement = :img WHERE id_evenement = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_cat', $this->id_categorie);
        $stmt->bindParam(':titre', $this->titre);
        $stmt->bindParam(':desc', $this->description);
        $stmt->bindParam(':type', $this->type_evenement);
        $stmt->bindParam(':d_deb', $this->date_debut);
        $stmt->bindParam(':d_fin', $this->date_fin);
        $stmt->bindParam(':h_deb', $this->heure_debut);
        $stmt->bindParam(':h_fin', $this->heure_fin);
        $stmt->bindParam(':part', $this->partenariat);
        $stmt->bindParam(':cap', $this->capacite_max);
        $stmt->bindParam(':nb_p', $this->nb_places_disponibles);
        $stmt->bindParam(':lieu', $this->lieu);
        $stmt->bindParam(':lien', $this->lien_acces);
        $stmt->bindParam(':org', $this->organisateur);
        $stmt->bindParam(':stat', $this->statut);
        $stmt->bindParam(':img', $this->image_evenement);
        $stmt->bindParam(':id', $this->id_evenement);
        return $stmt->execute();
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table . " WHERE id_evenement = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id_evenement);
        return $stmt->execute();
    }

    public function readOne() {
        $query = "SELECT * FROM " . $this->table . " WHERE id_evenement = :id LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id_evenement);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row) {
            foreach($row as $key => $value) {
                $this->$key = $value;
            }
            return true;
        }
        return false;
    }

    public function find($id) {
        $query = "SELECT e.*, c.nom_categorie, c.couleur FROM " . $this->table . " e
                  LEFT JOIN categories c ON e.id_categorie = c.id_categorie
                  WHERE e.id_evenement = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function decrementPlaces($id) {
        $query = "UPDATE " . $this->table . "
                  SET nb_places_disponibles = nb_places_disponibles - 1
                  WHERE id_evenement = :id AND nb_places_disponibles > 0";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
?>
