<?php
class Participation {
    private $conn;

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

    public function __destruct() {
        $this->conn = null;
    }

    public function getConn() { return $this->conn; }

    public function getIdParticipation() { return $this->id_participation; }
    public function setIdParticipation($id) { $this->id_participation = (int) $id; }

    public function getIdEvenement() { return $this->id_evenement; }
    public function setIdEvenement($idEvenement) { $this->id_evenement = (int) $idEvenement; }

    public function getIdUser() { return $this->id_user; }
    public function setIdUser($idUser) { $this->id_user = (int) $idUser; }

    public function getNomParticipant() { return $this->nom_participant; }
    public function setNomParticipant($nomParticipant) { $this->nom_participant = trim((string) $nomParticipant); }

    public function getEmail() { return $this->email; }
    public function setEmail($email) { $this->email = trim((string) $email); }

    public function getTelephone() { return $this->telephone; }
    public function setTelephone($telephone) { $this->telephone = trim((string) $telephone); }

    public function getStatutParticipation() { return $this->statut_participation; }
    public function setStatutParticipation($statut) { $this->statut_participation = trim((string) $statut); }

    public function getDateInscription() { return $this->date_inscription; }
    public function setDateInscription($dateInscription) { $this->date_inscription = $dateInscription; }

    public function getModeParticipation() { return $this->mode_participation; }
    public function setModeParticipation($mode) { $this->mode_participation = trim((string) $mode); }

    public function getFeedback() { return $this->feedback; }
    public function setFeedback($feedback) { $this->feedback = trim((string) $feedback); }

    public function getNote() { return $this->note; }
    public function setNote($note) { $this->note = $note === null ? null : (int) $note; }
}
?>
