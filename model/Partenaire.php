<?php
declare(strict_types=1);

class Partenaire
{
    private ?int $id = null;
    private ?int $clientId = null;
    private string $organizationName = '';
    private string $partnerType = '';
    private string $email = '';
    private string $telephone = '';
    private ?string $address = null;
    private ?string $country = null;
    private ?string $domain = null;
    private ?string $logo = null;
    private ?string $description = null;
    private string $status = 'pending';
    private ?string $authKeyHash = null;
    private ?string $createdAt = null;
    private ?string $updatedAt = null;
    private ?string $embeddingVector = null;

    public function __construct(
        $id = null,
        $clientId = null,
        $organizationName = '',
        $partnerType = '',
        $email = '',
        $telephone = '',
        $address = null,
        $country = null,
        $domain = null,
        $logo = null,
        $description = null,
        $status = 'pending',
        $authKeyHash = null,
        $createdAt = null,
        $updatedAt = null,
        $embeddingVector = null
    ) {
        $this->id = $id;
        $this->clientId = $clientId;
        $this->organizationName = $organizationName;
        $this->partnerType = $partnerType;
        $this->email = $email;
        $this->telephone = $telephone;
        $this->address = $address;
        $this->country = $country;
        $this->domain = $domain;
        $this->logo = $logo;
        $this->description = $description;
        $this->status = $status;
        $this->authKeyHash = $authKeyHash;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->embeddingVector = $embeddingVector;
    }

    // ===== GETTERS =====
    public function getId() { return $this->id; }
    public function getClientId() { return $this->clientId; }
    public function getOrganizationName() { return $this->organizationName; }
    public function getPartnerType() { return $this->partnerType; }
    public function getEmail() { return $this->email; }
    public function getTelephone() { return $this->telephone; }
    public function getAddress() { return $this->address; }
    public function getCountry() { return $this->country; }
    public function getDomain() { return $this->domain; }
    public function getLogo() { return $this->logo; }
    public function getDescription() { return $this->description; }
    public function getStatus() { return $this->status; }
    public function getAuthKeyHash() { return $this->authKeyHash; }
    public function getCreatedAt() { return $this->createdAt; }
    public function getUpdatedAt() { return $this->updatedAt; }
    public function getEmbeddingVector() { return $this->embeddingVector; }

    // ===== SETTERS =====
    public function setId($id) { $this->id = $id; }
    public function setClientId($clientId) { $this->clientId = $clientId; }
    public function setOrganizationName($organizationName) { $this->organizationName = $organizationName; }
    public function setPartnerType($partnerType) { $this->partnerType = $partnerType; }
    public function setEmail($email) { $this->email = $email; }
    public function setTelephone($telephone) { $this->telephone = $telephone; }
    public function setAddress($address) { $this->address = $address; }
    public function setCountry($country) { $this->country = $country; }
    public function setDomain($domain) { $this->domain = $domain; }
    public function setLogo($logo) { $this->logo = $logo; }
    public function setDescription($description) { $this->description = $description; }
    public function setStatus($status) { $this->status = $status; }
    public function setAuthKeyHash($authKeyHash) { $this->authKeyHash = $authKeyHash; }
    public function setCreatedAt($createdAt) { $this->createdAt = $createdAt; }
    public function setUpdatedAt($updatedAt) { $this->updatedAt = $updatedAt; }
    public function setEmbeddingVector($embeddingVector) { $this->embeddingVector = $embeddingVector; }
}