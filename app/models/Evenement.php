<?php
class Evenement {
    private $conn;

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

    public function __destruct() {
        $this->conn = null;
    }

    public function getConn() { return $this->conn; }

    public function getIdEvenement() { return $this->id_evenement; }
    public function setIdEvenement($id) { $this->id_evenement = (int) $id; }

    public function getIdCategorie() { return $this->id_categorie; }
    public function setIdCategorie($idCategorie) { $this->id_categorie = (int) $idCategorie; }

    public function getTitre() { return $this->titre; }
    public function setTitre($titre) { $this->titre = trim((string) $titre); }

    public function getDescription() { return $this->description; }
    public function setDescription($description) { $this->description = trim((string) $description); }

    public function getTypeEvenement() { return $this->type_evenement; }
    public function setTypeEvenement($type) { $this->type_evenement = trim((string) $type); }

    public function getDateDebut() { return $this->date_debut; }
    public function setDateDebut($dateDebut) { $this->date_debut = trim((string) $dateDebut); }

    public function getDateFin() { return $this->date_fin; }
    public function setDateFin($dateFin) { $this->date_fin = trim((string) $dateFin); }

    public function getHeureDebut() { return $this->heure_debut; }
    public function setHeureDebut($heureDebut) { $this->heure_debut = trim((string) $heureDebut); }

    public function getHeureFin() { return $this->heure_fin; }
    public function setHeureFin($heureFin) { $this->heure_fin = trim((string) $heureFin); }

    public function getPartenariat() { return $this->partenariat; }
    public function setPartenariat($partenariat) { $this->partenariat = trim((string) $partenariat); }

    public function getCapaciteMax() { return $this->capacite_max; }
    public function setCapaciteMax($capacite) { $this->capacite_max = (int) $capacite; }

    public function getNbPlacesDisponibles() { return $this->nb_places_disponibles; }
    public function setNbPlacesDisponibles($nbPlaces) { $this->nb_places_disponibles = (int) $nbPlaces; }

    public function getLieu() { return $this->lieu; }
    public function setLieu($lieu) { $this->lieu = trim((string) $lieu); }

    public function getLienAcces() { return $this->lien_acces; }
    public function setLienAcces($lien) { $this->lien_acces = trim((string) $lien); }

    public function getOrganisateur() { return $this->organisateur; }
    public function setOrganisateur($organisateur) { $this->organisateur = trim((string) $organisateur); }

    public function getStatut() { return $this->statut; }
    public function setStatut($statut) { $this->statut = trim((string) $statut); }

    public function getImageEvenement() { return $this->image_evenement; }
    public function setImageEvenement($image) { $this->image_evenement = trim((string) $image); }
}
?>
