<?php
declare(strict_types=1);

class Contract
{
    private PDO $pdo;

    private ?int $id = null;
    private ?int $clientId = null;
    private ?string $contractRef = null;
    private string $companyName = '';
    private string $typeContrat = '';
    private string $statut = 'Actif';
    private ?string $dateDebut = null;
    private ?string $dateFin = null;
    private ?string $dateSignature = null;
    private ?string $renouvellementAuto = null;
    private ?string $details = null;
    private ?string $pdfFileName = null;
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

        $this->setContractRef($data['contract_ref'] ?? $this->contractRef);
        $this->setCompanyName((string)($data['company_name'] ?? $this->companyName));
        $this->setTypeContrat((string)($data['type_contrat'] ?? $this->typeContrat));
        $this->setStatut((string)($data['statut'] ?? $this->statut));
        $this->setDateDebut($data['date_debut'] ?? $this->dateDebut);
        $this->setDateFin($data['date_fin'] ?? $this->dateFin);
        $this->setDateSignature($data['date_signature'] ?? $this->dateSignature);
        $this->setRenouvellementAuto($data['renouvellement_auto'] ?? $this->renouvellementAuto);
        $this->setDetails($data['details'] ?? $this->details);
        $this->setPdfFileName($data['pdf_file_name'] ?? $this->pdfFileName);
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
        $sql = "CREATE TABLE IF NOT EXISTS `contrats` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `client_id` INT NULL,
            `contract_ref` VARCHAR(64) DEFAULT NULL,
            `company_name` VARCHAR(255) NOT NULL,
            `type_contrat` VARCHAR(100) NOT NULL,
            `statut` VARCHAR(50) NOT NULL,
            `date_debut` DATE DEFAULT NULL,
            `date_fin` DATE DEFAULT NULL,
            `date_signature` DATE DEFAULT NULL,
            `renouvellement_auto` VARCHAR(10) DEFAULT NULL,
            `details` TEXT DEFAULT NULL,
            `pdf_file_name` VARCHAR(255) DEFAULT NULL,
            `auth_key_hash` CHAR(64) DEFAULT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY `uniq_contrats_client_id` (`client_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        $this->pdo->exec($sql);
    }

    public function addContract(): bool
    {
        if ($this->clientId === null) {
            $this->clientId = $this->getNextClientId();
        }

        $sql = 'INSERT INTO `contrats` (
            `client_id`, `contract_ref`, `company_name`, `type_contrat`, `statut`,
            `date_debut`, `date_fin`, `date_signature`, `renouvellement_auto`, `details`,
            `pdf_file_name`, `auth_key_hash`, `created_at`, `updated_at`
        ) VALUES (
            :client_id, :contract_ref, :company_name, :type_contrat, :statut,
            :date_debut, :date_fin, :date_signature, :renouvellement_auto, :details,
            :pdf_file_name, :auth_key_hash, NOW(), NOW()
        )';

        $this->pdo->beginTransaction();

        try {
            $stmt = $this->pdo->prepare($sql);
            $success = $stmt->execute([
                ':client_id' => $this->clientId,
                ':contract_ref' => $this->contractRef,
                ':company_name' => $this->companyName,
                ':type_contrat' => $this->typeContrat,
                ':statut' => $this->statut,
                ':date_debut' => $this->dateDebut,
                ':date_fin' => $this->dateFin,
                ':date_signature' => $this->dateSignature,
                ':renouvellement_auto' => $this->renouvellementAuto,
                ':details' => $this->details,
                ':pdf_file_name' => $this->pdfFileName,
                ':auth_key_hash' => $this->authKeyHash,
            ]);

            if (!$success) {
                $this->pdo->rollBack();
                return false;
            }

            $this->id = (int)$this->pdo->lastInsertId();

            if ($this->contractRef === null || trim($this->contractRef) === '') {
                $this->contractRef = sprintf('CTR-%04d', $this->id);
                $updateRef = $this->pdo->prepare('UPDATE `contrats` SET `contract_ref` = :contract_ref WHERE `id` = :id');
                $updateRef->execute([
                    ':contract_ref' => $this->contractRef,
                    ':id' => $this->id,
                ]);
            }

            $this->pdo->commit();
            return true;
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $exception;
        }
    }

    public function listContracts(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM `contrats` ORDER BY `created_at` DESC, `id` DESC');
        return $stmt->fetchAll();
    }

    public function findContractById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM `contrats` WHERE `id` = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public function deleteContract($id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM `contrats` WHERE `id` = :id');
        return $stmt->execute([':id' => (int)$id]);
    }

    public function updateContract($id): bool
    {
        $sql = 'UPDATE `contrats` SET
            `contract_ref` = :contract_ref,
            `company_name` = :company_name,
            `type_contrat` = :type_contrat,
            `statut` = :statut,
            `date_debut` = :date_debut,
            `date_fin` = :date_fin,
            `date_signature` = :date_signature,
            `renouvellement_auto` = :renouvellement_auto,
            `details` = :details,
            `pdf_file_name` = :pdf_file_name,
            `auth_key_hash` = :auth_key_hash,
            `updated_at` = NOW()
        WHERE `id` = :id';

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':contract_ref' => $this->contractRef,
            ':company_name' => $this->companyName,
            ':type_contrat' => $this->typeContrat,
            ':statut' => $this->statut,
            ':date_debut' => $this->dateDebut,
            ':date_fin' => $this->dateFin,
            ':date_signature' => $this->dateSignature,
            ':renouvellement_auto' => $this->renouvellementAuto,
            ':details' => $this->details,
            ':pdf_file_name' => $this->pdfFileName,
            ':auth_key_hash' => $this->authKeyHash,
            ':id' => (int)$id,
        ]);
    }

    public function setStatusById(int $id, string $status): bool
    {
        $stmt = $this->pdo->prepare('UPDATE `contrats` SET `statut` = :status, `updated_at` = NOW() WHERE `id` = :id');
        return $stmt->execute([
            ':status' => $status,
            ':id' => $id,
        ]);
    }

    private function getNextClientId(): int
    {
        $stmt = $this->pdo->query('SELECT COALESCE(MAX(`client_id`), 0) + 1 AS next_id FROM `contrats`');
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

    public function getContractRef(): ?string
    {
        return $this->contractRef;
    }

    public function setContractRef($contractRef): void
    {
        $this->contractRef = $this->normalizeNullableString($contractRef);
    }

    public function getCompanyName(): string
    {
        return $this->companyName;
    }

    public function setCompanyName(string $companyName): void
    {
        $this->companyName = trim($companyName);
    }

    public function getTypeContrat(): string
    {
        return $this->typeContrat;
    }

    public function setTypeContrat(string $typeContrat): void
    {
        $this->typeContrat = trim($typeContrat);
    }

    public function getStatut(): string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): void
    {
        $value = trim($statut);
        $this->statut = $value !== '' ? $value : 'Actif';
    }

    public function getDateDebut(): ?string
    {
        return $this->dateDebut;
    }

    public function setDateDebut($dateDebut): void
    {
        $this->dateDebut = $this->normalizeNullableString($dateDebut);
    }

    public function getDateFin(): ?string
    {
        return $this->dateFin;
    }

    public function setDateFin($dateFin): void
    {
        $this->dateFin = $this->normalizeNullableString($dateFin);
    }

    public function getDateSignature(): ?string
    {
        return $this->dateSignature;
    }

    public function setDateSignature($dateSignature): void
    {
        $this->dateSignature = $this->normalizeNullableString($dateSignature);
    }

    public function getRenouvellementAuto(): ?string
    {
        return $this->renouvellementAuto;
    }

    public function setRenouvellementAuto($renouvellementAuto): void
    {
        $this->renouvellementAuto = $this->normalizeNullableString($renouvellementAuto);
    }

    public function getDetails(): ?string
    {
        return $this->details;
    }

    public function setDetails($details): void
    {
        $this->details = $this->normalizeNullableString($details);
    }

    public function getPdfFileName(): ?string
    {
        return $this->pdfFileName;
    }

    public function setPdfFileName($pdfFileName): void
    {
        $this->pdfFileName = $this->normalizeNullableString($pdfFileName);
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
}
