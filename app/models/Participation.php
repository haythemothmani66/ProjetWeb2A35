<?php
class Participation {
    private $conn;
    private $table = "participations";

    public $id_participation;
    public $id_evenement;
    public $id_user;
    public $nom_participant;
    public $email;
    public $telephone;
    public $statut_participation;
    public $date_inscription;
    public $mode_participation;
    public $feedback;
    public $note;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function read() {
        $query = "SELECT p.*, e.titre as titre_evenement FROM " . $this->table . " p LEFT JOIN evenements e ON p.id_evenement = e.id_evenement ORDER BY p.date_inscription DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table . " (id_evenement, id_user, nom_participant, email, telephone, statut_participation, mode_participation, feedback, note) VALUES (:id_ev, :id_u, :nom, :email, :tel, :stat, :mode, :feed, :note)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_ev', $this->id_evenement);
        $stmt->bindParam(':id_u', $this->id_user);
        $stmt->bindParam(':nom', $this->nom_participant);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':tel', $this->telephone);
        $stmt->bindParam(':stat', $this->statut_participation);
        $stmt->bindParam(':mode', $this->mode_participation);
        $stmt->bindParam(':feed', $this->feedback);
        $stmt->bindParam(':note', $this->note);
        return $stmt->execute();
    }

    public function update() {
        $query = "UPDATE " . $this->table . " SET id_evenement = :id_ev, id_user = :id_u, nom_participant = :nom, email = :email, telephone = :tel, statut_participation = :stat, mode_participation = :mode, feedback = :feed, note = :note WHERE id_participation = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_ev', $this->id_evenement);
        $stmt->bindParam(':id_u', $this->id_user);
        $stmt->bindParam(':nom', $this->nom_participant);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':tel', $this->telephone);
        $stmt->bindParam(':stat', $this->statut_participation);
        $stmt->bindParam(':mode', $this->mode_participation);
        $stmt->bindParam(':feed', $this->feedback);
        $stmt->bindParam(':note', $this->note);
        $stmt->bindParam(':id', $this->id_participation);
        return $stmt->execute();
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table . " WHERE id_participation = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id_participation);
        return $stmt->execute();
    }

    public function readOne() {
        $query = "SELECT * FROM " . $this->table . " WHERE id_participation = :id LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id_participation);
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
        $query = "SELECT * FROM " . $this->table . " WHERE id_participation = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
