<?php

class User {

    private ?int    $id          = null;
    private string  $nom         = '';
    private string  $prenom      = '';
    private string  $email       = '';
    private string  $password    = '';
    private string  $telephone   = '';
    private string  $role        = 'etudiant';
    private int     $statut      = 1;
    private string  $photo       = 'default.png';
    private ?string $token_verif = null;
    private ?string $created_at  = null;

    // ---------- Getters ----------

    public function getId(): ?int {
        return $this->id;
    }

    public function getNom(): string {
        return $this->nom;
    }

    public function getPrenom(): string {
        return $this->prenom;
    }

    public function getEmail(): string {
        return $this->email;
    }

    public function getPassword(): string {
        return $this->password;
    }

    public function getTelephone(): string {
        return $this->telephone;
    }

    public function getRole(): string {
        return $this->role;
    }

    public function getStatut(): int {
        return $this->statut;
    }

    public function getPhoto(): string {
        return $this->photo;
    }

    public function getTokenVerif(): ?string {
        return $this->token_verif;
    }

    public function getCreatedAt(): ?string {
        return $this->created_at;
    }

    // ---------- Setters ----------

    public function setId(?int $id): void {
        $this->id = $id;
    }

    public function setNom(string $nom): void {
        $this->nom = trim($nom);
    }

    public function setPrenom(string $prenom): void {
        $this->prenom = trim($prenom);
    }

    public function setEmail(string $email): void {
        $this->email = trim(strtolower($email));
    }

    public function setPassword(string $password): void {
        $this->password = $password;
    }

    public function setTelephone(string $telephone): void {
        $this->telephone = trim($telephone);
    }

    public function setRole(string $role): void {
        $allowed = ['admin', 'encadrant', 'etudiant'];
        $this->role = in_array($role, $allowed) ? $role : 'etudiant';
    }

    public function setStatut(int $statut): void {
        $this->statut = ($statut === 1) ? 1 : 0;
    }

    public function setPhoto(string $photo): void {
        $this->photo = !empty($photo) ? $photo : 'default.png';
    }

    public function setTokenVerif(?string $token): void {
        $this->token_verif = $token;
    }

    public function setCreatedAt(?string $created_at): void {
        $this->created_at = $created_at;
    }
}
