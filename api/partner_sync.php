<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

// Utiliser Config::getConnexion() — DB unifiee edumatch (PDO)
require_once __DIR__ . '/../config/database.php';

function send_json(int $statusCode, array $payload): void
{
    http_response_code($statusCode);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function nullable_string($value): ?string
{
    if ($value === null) {
        return null;
    }

    $trimmed = trim((string)$value);
    return $trimmed === '' ? null : $trimmed;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json(405, [
        'success' => false,
        'message' => 'Method not allowed.'
    ]);
}

$rawBody = file_get_contents('php://input');
$decoded = json_decode($rawBody ?: '{}', true);
if (!is_array($decoded)) {
    send_json(400, [
        'success' => false,
        'message' => 'Invalid JSON payload.'
    ]);
}

$action = strtolower((string)($decoded['action'] ?? 'upsert'));
$record = $decoded['record'] ?? null;
if (!is_array($record)) {
    send_json(400, [
        'success' => false,
        'message' => 'Missing record payload.'
    ]);
}

$clientId = (int)($record['id'] ?? 0);
if ($clientId <= 0) {
    send_json(422, [
        'success' => false,
        'message' => 'Invalid partner identifier.'
    ]);
}

try {
    $pdo = Config::getConnexion();

    // S'assurer que la table partenaires existe
    $pdo->exec("CREATE TABLE IF NOT EXISTS `partenaires` (
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
        `embedding_vector` TEXT DEFAULT NULL,
        `view_count` INT DEFAULT 0,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY `uniq_partenaires_client_id` (`client_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    if ($action === 'delete') {
        $stmt = $pdo->prepare('DELETE FROM `partenaires` WHERE `client_id` = :client_id');
        $stmt->execute([':client_id' => $clientId]);

        send_json(200, [
            'success' => true,
            'message' => 'Partner deleted from database.'
        ]);
    }

    $organizationName = nullable_string($record['organization_name'] ?? null);
    $partnerType      = nullable_string($record['partner_type'] ?? null);
    $email            = nullable_string($record['email'] ?? null);
    $telephone        = nullable_string($record['telephone'] ?? null);
    $address          = nullable_string($record['address'] ?? null);
    $country          = nullable_string($record['country'] ?? null);
    $domain           = nullable_string($record['domain'] ?? null);
    $logo             = nullable_string($record['logo_file_name'] ?? null);
    $description      = nullable_string($record['description'] ?? null);
    $status           = nullable_string($record['status'] ?? 'pending') ?? 'pending';
    $authKeyHash      = strtolower((string)($record['auth_key_hash'] ?? ''));

    if ($organizationName === null || $partnerType === null || $email === null || $telephone === null) {
        send_json(422, [
            'success' => false,
            'message' => 'Missing required partner fields.'
        ]);
    }

    if (!preg_match('/^[a-f0-9]{64}$/', $authKeyHash)) {
        send_json(422, [
            'success' => false,
            'message' => 'Invalid auth key hash format.'
        ]);
    }

    // Calculate embedding vector
    require_once __DIR__ . '/RecommendationService.php';
    $embeddingVector = RecommendationService::generateEmbedding([
        'organization_name' => $organizationName,
        'partner_type' => $partnerType,
        'description' => $description,
        'country' => $country
    ]);

    $upsertSql = 'INSERT INTO `partenaires` (
        `client_id`, `organization_name`, `partner_type`, `email`, `telephone`,
        `address`, `country`, `domain`, `logo`, `description`,
        `status`, `auth_key_hash`, `embedding_vector`, `created_at`, `updated_at`
    ) VALUES (
        :client_id, :organization_name, :partner_type, :email, :telephone,
        :address, :country, :domain, :logo, :description,
        :status, :auth_key_hash, :embedding_vector, NOW(), NOW()
    ) ON DUPLICATE KEY UPDATE
        `organization_name` = VALUES(`organization_name`),
        `partner_type` = VALUES(`partner_type`),
        `email` = VALUES(`email`),
        `telephone` = VALUES(`telephone`),
        `address` = VALUES(`address`),
        `country` = VALUES(`country`),
        `domain` = VALUES(`domain`),
        `logo` = VALUES(`logo`),
        `description` = VALUES(`description`),
        `status` = VALUES(`status`),
        `auth_key_hash` = VALUES(`auth_key_hash`),
        `embedding_vector` = VALUES(`embedding_vector`),
        `updated_at` = NOW()';

    $stmt = $pdo->prepare($upsertSql);
    $stmt->execute([
        ':client_id'         => $clientId,
        ':organization_name' => $organizationName,
        ':partner_type'      => $partnerType,
        ':email'             => $email,
        ':telephone'         => $telephone,
        ':address'           => $address,
        ':country'           => $country,
        ':domain'            => $domain,
        ':logo'              => $logo,
        ':description'       => $description,
        ':status'            => $status,
        ':auth_key_hash'     => $authKeyHash,
        ':embedding_vector'  => $embeddingVector,
    ]);

    $affectedRows = $stmt->rowCount();

    if ($affectedRows === 1 && $status === 'pending') {
        require_once __DIR__ . '/MailHelper.php';
        MailHelper::sendPendingEmail($email, $organizationName);
    }

    send_json(200, [
        'success' => true,
        'message' => 'Partner synced successfully.'
    ]);
} catch (Throwable $exception) {
    send_json(500, [
        'success' => false,
        'message' => $exception->getMessage()
    ]);
}
