<?php

class Correction
{
    private $id_correction;
    private $commentaire;
    private $fichier_corrige;
    private $date_correction;
    private $type_feedback;
    private $note_estimee;
    private $competences_evaluees;
    private $nombre_iterations;
    private $suggestions_personnalisees;
    private $ressources_recommandees;
    private $rapidite_correction;
    private $ton_feedback;
    private $id_devoir;
    private $id_encadrant;

    public function __construct(
        $id_correction,
        $commentaire,
        $fichier_corrige,
        $date_correction,
        $type_feedback,
        $note_estimee,
        $competences_evaluees,
        $nombre_iterations,
        $suggestions_personnalisees,
        $ressources_recommandees,
        $rapidite_correction,
        $ton_feedback,
        $id_devoir,
        $id_encadrant
    ) {
        $this->id_correction = $id_correction;
        $this->commentaire = $commentaire;
        $this->fichier_corrige = $fichier_corrige;
        $this->date_correction = $date_correction;
        $this->type_feedback = $type_feedback;
        $this->note_estimee = $note_estimee;
        $this->competences_evaluees = $competences_evaluees;
        $this->nombre_iterations = $nombre_iterations;
        $this->suggestions_personnalisees = $suggestions_personnalisees;
        $this->ressources_recommandees = $ressources_recommandees;
        $this->rapidite_correction = $rapidite_correction;
        $this->ton_feedback = $ton_feedback;
        $this->id_devoir = $id_devoir;
        $this->id_encadrant = $id_encadrant;
    }

    // ===== GETTERS =====
    public function getCommentaire() { return $this->commentaire; }
    public function getDateCorrection() { return $this->date_correction; }
    public function getTypeFeedback() { return $this->type_feedback; }
    public function getNote() { return $this->note_estimee; }
    public function getCompetences() { return $this->competences_evaluees; }
    public function getIterations() { return $this->nombre_iterations; }
    public function getSuggestions() { return $this->suggestions_personnalisees; }
    public function getRessources() { return $this->ressources_recommandees; }
    public function getRapidite() { return $this->rapidite_correction; }
    public function getTon() { return $this->ton_feedback; }
    public function getIdDevoir() { return $this->id_devoir; }

    // (tu peux ajouter setters si besoin)
}