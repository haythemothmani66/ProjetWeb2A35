<?php
declare(strict_types=1);

class Partenaire
{
    private PDO $pdo;

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

    public function __construct(PDO $pdo, array $data = [])
    {
        $this->pdo = $pdo;
        if (!empty($data)) {
            $this->hydrate($data);
        }
    }

    public function hydrate(array $data): void
    {
        $this->setId(isset($data['id']) ? (int)$data['id'] : $this->id);
        $this->setClientId(isset($data['client_id']) ? (int)$data['client_id'] : $this->clientId);
        $this->setOrganizationName($data['organization_name'] ?? $this->organizationName);
        $this->setPartnerType($data['partner_type'] ?? $this->partnerType);
        $this->setEmail($data['email'] ?? $this->email);
        $this->setTelephone($data['telephone'] ?? $this->telephone);
        $this->setAddress($data['address'] ?? $this->address);
        $this->setCountry($data['country'] ?? $this->country);
        $this->setDomain($data['domain'] ?? $this->domain);
        $this->setLogo($data['logo'] ?? $this->logo);
        $this->setDescription($data['description'] ?? $this->description);
        $this->setStatus($data['status'] ?? $this->status);
        $this->setAuthKeyHash($data['auth_key_hash'] ?? $this->authKeyHash);
        $this->setCreatedAt($data['created_at'] ?? $this->createdAt);
        $this->setUpdatedAt($data['updated_at'] ?? $this->updatedAt);
        $this->setEmbeddingVector($data['embedding_vector'] ?? $this->embeddingVector);
    }

    public function listPartenaires(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM `partenaires` ORDER BY `created_at` DESC, `id` DESC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findPartenaireById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM `partenaires` WHERE `id` = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function findPartenaireByName(string $name): ?array
    {
        // Try searching by name first, then by email if it looks like one
        $sql = 'SELECT * FROM `partenaires` WHERE LOWER(TRIM(organization_name)) = LOWER(TRIM(?))';
        if (filter_var($name, FILTER_VALIDATE_EMAIL)) {
            $sql .= ' OR LOWER(TRIM(email)) = LOWER(TRIM(?))';
            $stmt = $this->pdo->prepare($sql . ' LIMIT 1');
            $stmt->execute([$name, $name]);
        } else {
            $stmt = $this->pdo->prepare($sql . ' LIMIT 1');
            $stmt->execute([$name]);
        }
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    // ===== GETTERS =====
    public function getId(): ?int { return $this->id; }
    public function getClientId(): ?int { return $this->clientId; }
    public function getOrganizationName(): string { return $this->organizationName; }
    public function getPartnerType(): string { return $this->partnerType; }
    public function getEmail(): string { return $this->email; }
    public function getTelephone(): string { return $this->telephone; }
    public function getAddress(): ?string { return $this->address; }
    public function getCountry(): ?string { return $this->country; }
    public function getDomain(): ?string { return $this->domain; }
    public function getLogo(): ?string { return $this->logo; }
    public function getDescription(): ?string { return $this->description; }
    public function getStatus(): string { return $this->status; }
    public function getAuthKeyHash(): ?string { return $this->authKeyHash; }
    public function getCreatedAt(): ?string { return $this->createdAt; }
    public function getUpdatedAt(): ?string { return $this->updatedAt; }
    public function getEmbeddingVector(): ?string { return $this->embeddingVector; }

    // ===== SETTERS =====
    public function setId(?int $id): void { $this->id = $id; }
    public function setClientId(?int $clientId): void { $this->clientId = $clientId; }
    public function setOrganizationName(string $name): void { $this->organizationName = $name; }
    public function setPartnerType(string $type): void { $this->partnerType = $type; }
    public function setEmail(string $email): void { $this->email = $email; }
    public function setTelephone(string $tel): void { $this->telephone = $tel; }
    public function setAddress(?string $addr): void { $this->address = $addr; }
    public function setCountry(?string $country): void { $this->country = $country; }
    public function setDomain(?string $domain): void { $this->domain = $domain; }
    public function setLogo(?string $logo): void { $this->logo = $logo; }
    public function setDescription(?string $desc): void { $this->description = $desc; }
    public function setStatus(string $status): void { $this->status = $status; }
    public function setAuthKeyHash(?string $hash): void { $this->authKeyHash = $hash; }
    public function setCreatedAt(?string $date): void { $this->createdAt = $date; }
    public function setUpdatedAt(?string $date): void { $this->updatedAt = $date; }
    public function setEmbeddingVector(?string $vector): void { $this->embeddingVector = $vector; }
}