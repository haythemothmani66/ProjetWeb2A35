<?php
/**
 * Model Reservation
 * Reservation de seance entre un etudiant et un encadrant
 */
class Reservation
{
    private ?int $id_reservation;
    private int $id_etudiant;
    private int $id_encadrant;
    private string $date_reservation;
    private string $heure_debut;
    private string $heure_fin;
    private string $matiere;
    private ?string $sujet;
    private string $mode;          // 'en_ligne' | 'presentiel'
    private string $statut;        // 'en_attente' | 'acceptee' | 'refusee' | 'annulee' | 'terminee'
    private ?string $notes_etudiant;
    private ?string $notes_encadrant;
    private string $token_action;
    private ?string $date_creation;
    private ?string $date_reponse;

    public function __construct(
        ?int $id_reservation = null,
        int $id_etudiant = 0,
        int $id_encadrant = 0,
        string $date_reservation = '',
        string $heure_debut = '',
        string $heure_fin = '',
        string $matiere = '',
        ?string $sujet = null,
        string $mode = 'en_ligne',
        string $statut = 'en_attente',
        ?string $notes_etudiant = null,
        ?string $notes_encadrant = null,
        string $token_action = '',
        ?string $date_creation = null,
        ?string $date_reponse = null
    ) {
        $this->id_reservation   = $id_reservation;
        $this->id_etudiant      = $id_etudiant;
        $this->id_encadrant     = $id_encadrant;
        $this->date_reservation = $date_reservation;
        $this->heure_debut      = $heure_debut;
        $this->heure_fin        = $heure_fin;
        $this->matiere          = $matiere;
        $this->sujet            = $sujet;
        $this->mode             = $mode;
        $this->statut           = $statut;
        $this->notes_etudiant   = $notes_etudiant;
        $this->notes_encadrant  = $notes_encadrant;
        $this->token_action     = $token_action;
        $this->date_creation    = $date_creation;
        $this->date_reponse     = $date_reponse;
    }

    // ====== GETTERS ======
    public function getId(): ?int { return $this->id_reservation; }
    public function getIdEtudiant(): int { return $this->id_etudiant; }
    public function getIdEncadrant(): int { return $this->id_encadrant; }
    public function getDateReservation(): string { return $this->date_reservation; }
    public function getHeureDebut(): string { return $this->heure_debut; }
    public function getHeureFin(): string { return $this->heure_fin; }
    public function getMatiere(): string { return $this->matiere; }
    public function getSujet(): ?string { return $this->sujet; }
    public function getMode(): string { return $this->mode; }
    public function getStatut(): string { return $this->statut; }
    public function getNotesEtudiant(): ?string { return $this->notes_etudiant; }
    public function getNotesEncadrant(): ?string { return $this->notes_encadrant; }
    public function getTokenAction(): string { return $this->token_action; }
    public function getDateCreation(): ?string { return $this->date_creation; }
    public function getDateReponse(): ?string { return $this->date_reponse; }

    // ====== SETTERS ======
    public function setId(?int $v): void { $this->id_reservation = $v; }
    public function setIdEtudiant(int $v): void { $this->id_etudiant = $v; }
    public function setIdEncadrant(int $v): void { $this->id_encadrant = $v; }
    public function setDateReservation(string $v): void { $this->date_reservation = $v; }
    public function setHeureDebut(string $v): void { $this->heure_debut = $v; }
    public function setHeureFin(string $v): void { $this->heure_fin = $v; }
    public function setMatiere(string $v): void { $this->matiere = $v; }
    public function setSujet(?string $v): void { $this->sujet = $v; }
    public function setMode(string $v): void { $this->mode = $v; }
    public function setStatut(string $v): void { $this->statut = $v; }
    public function setNotesEtudiant(?string $v): void { $this->notes_etudiant = $v; }
    public function setNotesEncadrant(?string $v): void { $this->notes_encadrant = $v; }
    public function setTokenAction(string $v): void { $this->token_action = $v; }
    public function setDateCreation(?string $v): void { $this->date_creation = $v; }
    public function setDateReponse(?string $v): void { $this->date_reponse = $v; }

    /**
     * Genere un token unique 64 chars pour les actions par email
     */
    public static function generateToken(): string
    {
        return bin2hex(random_bytes(32));
    }
}
