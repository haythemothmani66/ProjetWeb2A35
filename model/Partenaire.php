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

    public function __construct(PDO $pdo, array $data = [])
    {
        $this->pdo = $pdo;
        $this->ensureTable();

        if (!empty($data)) {
            $this->hydrate($data);
        }
    }

    public function hydrate(array $data): void
    {
        if (isset($data['id'])) {
            $this->setId((int)$data['id']);
        }
        if (isset($data['client_id'])) {
            $this->setClientId((int)$data['client_id']);
        }

        $this->setOrganizationName((string)($data['organization_name'] ?? $this->organizationName));
        $this->setPartnerType((string)($data['partner_type'] ?? $this->partnerType));
        $this->setEmail((string)($data['email'] ?? $this->email));
        $this->setTelephone((string)($data['telephone'] ?? $this->telephone));
        $this->setAddress($data['address'] ?? $this->address);
        $this->setCountry($data['country'] ?? $this->country);
        $this->setDomain($data['domain'] ?? $this->domain);
        $this->setLogo($data['logo'] ?? $this->logo);
        $this->setDescription($data['description'] ?? $this->description);
        $this->setStatus((string)($data['status'] ?? $this->status));
        $this->setAuthKeyHash($data['auth_key_hash'] ?? $this->authKeyHash);

        if (isset($data['created_at'])) {
            $this->setCreatedAt((string)$data['created_at']);
        }
        if (isset($data['updated_at'])) {
            $this->setUpdatedAt((string)$data['updated_at']);
        }
    }

    private function ensureTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS `partenaires` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `client_id` INT NULL,
            `organization_name` VARCHAR(255) NOT NULL,
            `partner_type` VARCHAR(100) NOT NULL,
            `email` VARCHAR(255) NOT NULL,
            `telephone` VARCHAR(50) NOT NULL,
            `address` VARCHAR(255) DEFAULT NULL,
            `country` VARCHAR(100) DEFAULT NULL,
            `domain` VARCHAR(255) DEFAULT NULL,
            `logo` VARCHAR(255) DEFAULT NULL,
            `description` TEXT DEFAULT NULL,
            `status` VARCHAR(50) NOT NULL DEFAULT 'pending',
            `auth_key_hash` CHAR(64) DEFAULT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY `uniq_partenaires_client_id` (`client_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        $this->pdo->exec($sql);
    }

    public function addPartenaire(): bool
    {
        if ($this->clientId === null) {
            $this->clientId = $this->getNextClientId();
        }

        $sql = 'INSERT INTO `partenaires` (
            `client_id`, `organization_name`, `partner_type`, `email`, `telephone`, `address`,
            `country`, `domain`, `logo`, `description`, `status`, `auth_key_hash`, `created_at`, `updated_at`
        ) VALUES (
            :client_id, :organization_name, :partner_type, :email, :telephone, :address,
            :country, :domain, :logo, :description, :status, :auth_key_hash, NOW(), NOW()
        )';

        $stmt = $this->pdo->prepare($sql);
        $success = $stmt->execute([
            ':client_id' => $this->clientId,
            ':organization_name' => $this->organizationName,
            ':partner_type' => $this->partnerType,
            ':email' => $this->email,
            ':telephone' => $this->telephone,
            ':address' => $this->address,
            ':country' => $this->country,
            ':domain' => $this->domain,
            ':logo' => $this->logo,
            ':description' => $this->description,
            ':status' => $this->status,
            ':auth_key_hash' => $this->authKeyHash,
        ]);

        if ($success) {
            $this->id = (int)$this->pdo->lastInsertId();
        }

        return $success;
    }

    public function listPartenaires(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM `partenaires` ORDER BY `created_at` DESC, `id` DESC');
        return $stmt->fetchAll();
    }

    public function findPartenaireById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM `partenaires` WHERE `id` = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public function deletePartenaire($id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM `partenaires` WHERE `id` = :id');
        return $stmt->execute([':id' => (int)$id]);
    }

    public function updatePartenaire($id): bool
    {
        $sql = 'UPDATE `partenaires` SET
            `organization_name` = :organization_name,
            `partner_type` = :partner_type,
            `email` = :email,
            `telephone` = :telephone,
            `address` = :address,
            `country` = :country,
            `domain` = :domain,
            `logo` = :logo,
            `description` = :description,
            `status` = :status,
            `auth_key_hash` = :auth_key_hash,
            `updated_at` = NOW()
        WHERE `id` = :id';

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':organization_name' => $this->organizationName,
            ':partner_type' => $this->partnerType,
            ':email' => $this->email,
            ':telephone' => $this->telephone,
            ':address' => $this->address,
            ':country' => $this->country,
            ':domain' => $this->domain,
            ':logo' => $this->logo,
            ':description' => $this->description,
            ':status' => $this->status,
            ':auth_key_hash' => $this->authKeyHash,
            ':id' => (int)$id,
        ]);
    }

    public function setStatusById(int $id, string $status): bool
    {
        $stmt = $this->pdo->prepare('UPDATE `partenaires` SET `status` = :status, `updated_at` = NOW() WHERE `id` = :id');
        return $stmt->execute([
            ':status' => $status,
            ':id' => $id,
        ]);
    }

    private function getNextClientId(): int
    {
        $stmt = $this->pdo->query('SELECT COALESCE(MAX(`client_id`), 0) + 1 AS next_id FROM `partenaires`');
        $nextId = $stmt->fetchColumn();

        return max(1, (int)$nextId);
    }

    private function normalizeNullableString($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim((string)$value);
        return $trimmed === '' ? null : $trimmed;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getClientId(): ?int
    {
        return $this->clientId;
    }

    public function setClientId(?int $clientId): void
    {
        $this->clientId = $clientId;
    }

    public function getOrganizationName(): string
    {
        return $this->organizationName;
    }

    public function setOrganizationName(string $organizationName): void
    {
        $this->organizationName = trim($organizationName);
    }

    public function getPartnerType(): string
    {
        return $this->partnerType;
    }

    public function setPartnerType(string $partnerType): void
    {
        $this->partnerType = trim($partnerType);
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = trim($email);
    }

    public function getTelephone(): string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): void
    {
        $this->telephone = trim($telephone);
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress($address): void
    {
        $this->address = $this->normalizeNullableString($address);
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry($country): void
    {
        $this->country = $this->normalizeNullableString($country);
    }

    public function getDomain(): ?string
    {
        return $this->domain;
    }

    public function setDomain($domain): void
    {
        $this->domain = $this->normalizeNullableString($domain);
    }

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo($logo): void
    {
        $this->logo = $this->normalizeNullableString($logo);
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription($description): void
    {
        $this->description = $this->normalizeNullableString($description);
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $value = trim($status);
        $this->status = $value !== '' ? $value : 'pending';
    }

    public function getAuthKeyHash(): ?string
    {
        return $this->authKeyHash;
    }

    public function setAuthKeyHash($authKeyHash): void
    {
        $this->authKeyHash = $this->normalizeNullableString($authKeyHash);
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?string $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?string $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * Get all approved/active partners for public display (frontend)
     * Returns only: id, organization_name, logo, description, partner_type
     */
    public function getApprovedPartnersForDisplay(int $limit = 0): array
    {
        $sql = 'SELECT `id`, `organization_name`, `logo`, `description`, `partner_type` 
                FROM `partenaires` 
                WHERE `status` = :status 
                ORDER BY `created_at` DESC, `id` DESC';
        
        if ($limit > 0) {
            $sql .= ' LIMIT ' . (int)$limit;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':status' => 'approved']);
        return $stmt->fetchAll();
    }

    /**
     * Get all approved partners with all public information
     */
    public function getAllApprovedPartners(): array
    {
        $sql = 'SELECT `id`, `organization_name`, `logo`, `description`, `partner_type`, 
                       `email`, `country`, `domain`, `created_at` 
                FROM `partenaires` 
                WHERE `status` = :status 
                ORDER BY `created_at` DESC, `id` DESC';
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':status' => 'approved']);
        return $stmt->fetchAll();
    }
}
