<?php

class Profil {

    private ?int    $id_profil   = null;
    private int     $user_id     = 0;
    private ?string $bio_text    = null;
    private ?string $niveau      = null;
    private ?string $specialite  = null;
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

    public function setCreatedAt(?string $created_at): void {
        $this->created_at = $created_at;
    }
}
