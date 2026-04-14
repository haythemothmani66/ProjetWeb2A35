<?php
class Categorie {
    private $conn;
    private $table = "categories";

    public $id_categorie;
    public $nom_categorie;
    public $description;
    public $couleur;
    public $statut;
    public $date_creation;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function read() {
        $query = "SELECT * FROM " . $this->table . " ORDER BY date_creation DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table . " (nom_categorie, description, couleur, statut) VALUES (:nom, :desc, :coul, :stat)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nom', $this->nom_categorie);
        $stmt->bindParam(':desc', $this->description);
        $stmt->bindParam(':coul', $this->couleur);
        $stmt->bindParam(':stat', $this->statut);
        return $stmt->execute();
    }

    public function update() {
        $query = "UPDATE " . $this->table . " SET nom_categorie = :nom, description = :desc, couleur = :coul, statut = :stat WHERE id_categorie = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nom', $this->nom_categorie);
        $stmt->bindParam(':desc', $this->description);
        $stmt->bindParam(':coul', $this->couleur);
        $stmt->bindParam(':stat', $this->statut);
        $stmt->bindParam(':id', $this->id_categorie);
        return $stmt->execute();
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table . " WHERE id_categorie = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id_categorie);
        return $stmt->execute();
    }

    public function readOne() {
        $query = "SELECT * FROM " . $this->table . " WHERE id_categorie = :id LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id_categorie);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row) {
            $this->nom_categorie = $row['nom_categorie'];
            $this->description = $row['description'];
            $this->couleur = $row['couleur'];
            $this->statut = $row['statut'];
            return true;
        }
        return false;
    }

    public function find($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id_categorie = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
