<?php

class Devoir
{
    private $id_devoir;
    private $titre;
    private $description;
    private $fichier;
    private $date_soumission;
    private $niveau_difficulte;
    private $type_erreur_predominant;
    private $temps_estime_resolution;
    private $progression_eleve;
    private $mots_cles;
    private $urgence;
    private $id_eleve;

    public function __construct(
        $id_devoir,
        $titre,
        $description,
        $fichier,
        $date_soumission,
        $niveau_difficulte,
        $type_erreur_predominant,
        $temps_estime_resolution,
        $progression_eleve,
        $mots_cles,
        $urgence,
        $id_eleve
    ) {
        $this->id_devoir = $id_devoir;
        $this->titre = $titre;
        $this->description = $description;
        $this->fichier = $fichier;
        $this->date_soumission = $date_soumission;
        $this->niveau_difficulte = $niveau_difficulte;
        $this->type_erreur_predominant = $type_erreur_predominant;
        $this->temps_estime_resolution = $temps_estime_resolution;
        $this->progression_eleve = $progression_eleve;
        $this->mots_cles = $mots_cles;
        $this->urgence = $urgence;
        $this->id_eleve = $id_eleve;
    }

    // ===== GETTERS =====
    public function getIdDevoir() { return $this->id_devoir; }
    public function getTitre() { return $this->titre; }
    public function getDescription() { return $this->description; }
    public function getFichier() { return $this->fichier; }
    public function getDateSoumission() { return $this->date_soumission; }
    public function getNiveauDifficulte() { return $this->niveau_difficulte; }
    public function getTypeErreur() { return $this->type_erreur_predominant; }
    public function getTempsEstime() { return $this->temps_estime_resolution; }
    public function getProgression() { return $this->progression_eleve; }
    public function getMotsCles() { return $this->mots_cles; }
    public function getUrgence() { return $this->urgence; }
    public function getIdEleve() { return $this->id_eleve; }

    // ===== SETTERS =====
    public function setTitre($titre) { $this->titre = $titre; }
    public function setDescription($description) { $this->description = $description; }
    public function setFichier($fichier) { $this->fichier = $fichier; }
    public function setDateSoumission($date) { $this->date_soumission = $date; }
    public function setNiveauDifficulte($niveau) { $this->niveau_difficulte = $niveau; }
    public function setTypeErreur($type) { $this->type_erreur_predominant = $type; }
    public function setTempsEstime($temps) { $this->temps_estime_resolution = $temps; }
    public function setProgression($progression) { $this->progression_eleve = $progression; }
    public function setMotsCles($mots) { $this->mots_cles = $mots; }
    public function setUrgence($urgence) { $this->urgence = $urgence; }
    public function setIdEleve($id) { $this->id_eleve = $id; }
}