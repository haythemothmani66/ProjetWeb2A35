<?php

class Profil {

    private ?int    $id_profil   = null;
    private int     $user_id     = 0;
    private ?string $bio_text    = null;
    private ?string $niveau      = null;
    private ?string $specialite  = null;
    private ?string $classe      = null;
    private ?string $email_universitaire = null;
    private ?string $card_image  = null;
    private ?string $adresse     = null;
    private ?string $etablissement_ecole = null;
    private ?string $identifiant_card = null;
    private ?string $annee_universitaire = null;
    private ?string $created_at  = null;

    // ---------- Getters ----------

    public function getIdProfil(): ?int {
        return $this->id_profil;
    }

    public function getUserId(): int {
        return $this->user_id;
    }

    public function getBioText(): ?string {
        return $this->bio_text;
    }

    public function getNiveau(): ?string {
        return $this->niveau;
    }

    public function getSpecialite(): ?string {
        return $this->specialite;
    }

    public function getClasse(): ?string {
        return $this->classe;
    }

    public function getEmailUniversitaire(): ?string {
        return $this->email_universitaire;
    }

    public function getCardImage(): ?string {
        return $this->card_image;
    }

    public function getAdresse(): ?string {
        return $this->adresse;
    }

    public function getEtablissementEcole(): ?string {
        return $this->etablissement_ecole;
    }

    public function getIdentifiantCard(): ?string {
        return $this->identifiant_card;
    }

    public function getAnneeUniversitaire(): ?string {
        return $this->annee_universitaire;
    }

    public function getCreatedAt(): ?string {
        return $this->created_at;
    }

    // ---------- Setters ----------

    public function setIdProfil(?int $id_profil): void {
        $this->id_profil = $id_profil;
    }

    public function setUserId(int $user_id): void {
        $this->user_id = $user_id;
    }

    public function setBioText(?string $bio_text): void {
        $this->bio_text = $bio_text ? trim($bio_text) : null;
    }

    public function setNiveau(?string $niveau): void {
        $this->niveau = $niveau ? trim($niveau) : null;
    }

    public function setSpecialite(?string $specialite): void {
        $this->specialite = $specialite ? trim($specialite) : null;
    }

    public function setClasse(?string $classe): void {
        $this->classe = $classe ? trim($classe) : null;
    }

    public function setEmailUniversitaire(?string $email): void {
        $this->email_universitaire = $email ? trim($email) : null;
    }

    public function setCardImage(?string $card_image): void {
        $this->card_image = $card_image;
    }

    public function setAdresse(?string $adresse): void {
        $this->adresse = $adresse ? trim($adresse) : null;
    }

    public function setEtablissementEcole(?string $etablissement): void {
        $this->etablissement_ecole = $etablissement ? trim($etablissement) : null;
    }

    public function setIdentifiantCard(?string $identifiant): void {
        $this->identifiant_card = $identifiant ? trim($identifiant) : null;
    }

    public function setAnneeUniversitaire(?string $annee): void {
        $this->annee_universitaire = $annee ? trim($annee) : null;
    }

    public function setCreatedAt(?string $created_at): void {
        $this->created_at = $created_at;
    }
}
