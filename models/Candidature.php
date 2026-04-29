<?php

declare(strict_types=1);

class Candidature
{
    private ?int $id;
    private string $nom;
    private string $prenom;
    private string $lettreMotivation;
    private string $cvUrl;
    private string $email;
    private string $statut;
    private ?string $dateCandidature;
    private ?string $dateReponse;
    private int $offreId;

    public function __construct(
        ?int $id = null,
        string $nom = '',
        string $prenom = '',
        string $lettreMotivation = '',
        string $cvUrl = '',
        string $email = '',
        string $statut = 'enattente',
        ?string $dateCandidature = null,
        ?string $dateReponse = null,
        int $offreId = 0
    ) {
        $this->id = $id;
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->lettreMotivation = $lettreMotivation;
        $this->cvUrl = $cvUrl;
        $this->email = $email;
        $this->statut = $statut;
        $this->dateCandidature = $dateCandidature;
        $this->dateReponse = $dateReponse;
        $this->offreId = $offreId;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function setNom(string $nom): void
    {
        $this->nom = $nom;
    }

    public function getPrenom(): string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): void
    {
        $this->prenom = $prenom;
    }

    public function getLettreMotivation(): string
    {
        return $this->lettreMotivation;
    }

    public function setLettreMotivation(string $lettreMotivation): void
    {
        $this->lettreMotivation = $lettreMotivation;
    }

    public function getCvUrl(): string
    {
        return $this->cvUrl;
    }

    public function setCvUrl(string $cvUrl): void
    {
        $this->cvUrl = $cvUrl;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getStatut(): string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): void
    {
        $this->statut = $statut;
    }

    public function getDateCandidature(): ?string
    {
        return $this->dateCandidature;
    }

    public function setDateCandidature(?string $dateCandidature): void
    {
        $this->dateCandidature = $dateCandidature;
    }

    public function getDateReponse(): ?string
    {
        return $this->dateReponse;
    }

    public function setDateReponse(?string $dateReponse): void
    {
        $this->dateReponse = $dateReponse;
    }

    public function getOffreId(): int
    {
        return $this->offreId;
    }

    public function setOffreId(int $offreId): void
    {
        $this->offreId = $offreId;
    }
}
