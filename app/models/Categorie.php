<?php
class Categorie {
    private $conn;
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

    public function __destruct() {
        $this->conn = null;
    }

    public function getConn() { return $this->conn; }

    public function getIdCategorie() { return $this->id_categorie; }
    public function setIdCategorie($id) { $this->id_categorie = (int) $id; }

    public function getNomCategorie() { return $this->nom_categorie; }
    public function setNomCategorie($nom) { $this->nom_categorie = trim((string) $nom); }

    public function getDescription() { return $this->description; }
    public function setDescription($description) { $this->description = trim((string) $description); }

    public function getCouleur() { return $this->couleur; }
    public function setCouleur($couleur) { $this->couleur = trim((string) $couleur); }

    public function getStatut() { return $this->statut; }
    public function setStatut($statut) { $this->statut = trim((string) $statut); }

    public function getDateCreation() { return $this->date_creation; }
    public function setDateCreation($dateCreation) { $this->date_creation = $dateCreation; }
}
?>
