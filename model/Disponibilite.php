<?php
/**
 * Model Disponibilite
 * Creneau hebdomadaire ou un encadrant est disponible pour donner des seances
 */
class Disponibilite
{
    private ?int $id_disponibilite;
    private int $id_encadrant;
    private string $jour_semaine;   // 'lundi' .. 'dimanche'
    private string $heure_debut;
    private string $heure_fin;
    private bool $actif;
    private ?string $date_creation;

    public function __construct(
        ?int $id_disponibilite = null,
        int $id_encadrant = 0,
        string $jour_semaine = '',
        string $heure_debut = '',
        string $heure_fin = '',
        bool $actif = true,
        ?string $date_creation = null
    ) {
        $this->id_disponibilite = $id_disponibilite;
        $this->id_encadrant     = $id_encadrant;
        $this->jour_semaine     = $jour_semaine;
        $this->heure_debut      = $heure_debut;
        $this->heure_fin        = $heure_fin;
        $this->actif            = $actif;
        $this->date_creation    = $date_creation;
    }

    // ====== GETTERS ======
    public function getId(): ?int { return $this->id_disponibilite; }
    public function getIdEncadrant(): int { return $this->id_encadrant; }
    public function getJourSemaine(): string { return $this->jour_semaine; }
    public function getHeureDebut(): string { return $this->heure_debut; }
    public function getHeureFin(): string { return $this->heure_fin; }
    public function isActif(): bool { return $this->actif; }
    public function getDateCreation(): ?string { return $this->date_creation; }

    // ====== SETTERS ======
    public function setId(?int $v): void { $this->id_disponibilite = $v; }
    public function setIdEncadrant(int $v): void { $this->id_encadrant = $v; }
    public function setJourSemaine(string $v): void { $this->jour_semaine = $v; }
    public function setHeureDebut(string $v): void { $this->heure_debut = $v; }
    public function setHeureFin(string $v): void { $this->heure_fin = $v; }
    public function setActif(bool $v): void { $this->actif = $v; }
    public function setDateCreation(?string $v): void { $this->date_creation = $v; }

    /**
     * Mapping nom_jour -> numero ISO (1=lundi, 7=dimanche)
     */
    public static function jourToIso(string $jour): int
    {
        $map = ['lundi'=>1,'mardi'=>2,'mercredi'=>3,'jeudi'=>4,'vendredi'=>5,'samedi'=>6,'dimanche'=>7];
        return $map[strtolower($jour)] ?? 1;
    }

    /**
     * Mapping inverse : 1->lundi, 7->dimanche
     */
    public static function isoToJour(int $iso): string
    {
        $map = [1=>'lundi',2=>'mardi',3=>'mercredi',4=>'jeudi',5=>'vendredi',6=>'samedi',7=>'dimanche'];
        return $map[$iso] ?? 'lundi';
    }
}
