<?php

declare(strict_types=1);

class OffreEmploi
{
    private ?int $id;
    private string $titre;
    private string $description;
    private ?string $competencesRequises;
    private string $lieu;
    private string $typeContrat;
    private ?float $salaireMin;
    private ?float $salaireMax;
    private string $dateLimite;
    private string $statut;
    private ?string $dateCreation;

    public function __construct(
        ?int $id = null,
        string $titre = '',
        string $description = '',
        ?string $competencesRequises = null,
        string $lieu = '',
        string $typeContrat = '',
        ?float $salaireMin = null,
        ?float $salaireMax = null,
        string $dateLimite = '',
        string $statut = 'ouverte',
        ?string $dateCreation = null
    ) {
        $this->id = $id;
        $this->titre = $titre;
        $this->description = $description;
        $this->competencesRequises = $competencesRequises;
        $this->lieu = $lieu;
        $this->typeContrat = $typeContrat;
        $this->salaireMin = $salaireMin;
        $this->salaireMax = $salaireMax;
        $this->dateLimite = $dateLimite;
        $this->statut = $statut;
        $this->dateCreation = $dateCreation;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getTitre(): string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): void
    {
        $this->titre = $titre;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getCompetencesRequises(): ?string
    {
        return $this->competencesRequises;
    }

    public function setCompetencesRequises(?string $competencesRequises): void
    {
        $this->competencesRequises = $competencesRequises;
    }

    public function getLieu(): string
    {
        return $this->lieu;
    }

    public function setLieu(string $lieu): void
    {
        $this->lieu = $lieu;
    }

    public function getTypeContrat(): string
    {
        return $this->typeContrat;
    }

    public function setTypeContrat(string $typeContrat): void
    {
        $this->typeContrat = $typeContrat;
    }

    public function getSalaireMin(): ?float
    {
        return $this->salaireMin;
    }

    public function setSalaireMin(?float $salaireMin): void
    {
        $this->salaireMin = $salaireMin;
    }

    public function getSalaireMax(): ?float
    {
        return $this->salaireMax;
    }

    public function setSalaireMax(?float $salaireMax): void
    {
        $this->salaireMax = $salaireMax;
    }

    public function getDateLimite(): string
    {
        return $this->dateLimite;
    }

    public function setDateLimite(string $dateLimite): void
    {
        $this->dateLimite = $dateLimite;
    }

    public function getStatut(): string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): void
    {
        $this->statut = $statut;
    }

    public function getDateCreation(): ?string
    {
        return $this->dateCreation;
    }

    public function setDateCreation(?string $dateCreation): void
    {
        $this->dateCreation = $dateCreation;
    }
}
