<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

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

function connect_database(): mysqli
{
    $host = 'localhost';
    $username = 'root';
    $password = '';
    $dbCandidates = ['databaseedumatch', 'database_edumatch'];

    foreach ($dbCandidates as $databaseName) {
        $conn = @new mysqli($host, $username, $password, $databaseName);
        if (!$conn->connect_errno) {
            $conn->set_charset('utf8mb4');
            return $conn;
        }
    }

    throw new RuntimeException('Unable to connect to MySQL database.');
}

function ensure_column(mysqli $conn, string $table, string $column, string $definition): void
{
    $columnEscaped = $conn->real_escape_string($column);
    $result = $conn->query("SHOW COLUMNS FROM `{$table}` LIKE '{$columnEscaped}'");
    if ($result && $result->num_rows > 0) {
        return;
    }

    if (!$conn->query("ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition}")) {
        throw new RuntimeException('Failed to add column ' . $column . ': ' . $conn->error);
    }
}

function ensure_unique_index(mysqli $conn, string $table, string $indexName, string $column): void
{
    $indexEscaped = $conn->real_escape_string($indexName);
    $result = $conn->query("SHOW INDEX FROM `{$table}` WHERE Key_name = '{$indexEscaped}'");
    if ($result && $result->num_rows > 0) {
        return;
    }

    if (!$conn->query("ALTER TABLE `{$table}` ADD UNIQUE KEY `{$indexName}` (`{$column}`)")) {
        throw new RuntimeException('Failed to add index ' . $indexName . ': ' . $conn->error);
    }
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
    $conn = connect_database();

    $createTableSql = "CREATE TABLE IF NOT EXISTS `partenaires` (
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
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    if (!$conn->query($createTableSql)) {
        throw new RuntimeException('Failed to initialize partenaires table: ' . $conn->error);
    }

    ensure_column($conn, 'partenaires', 'client_id', 'INT NULL');
    ensure_column($conn, 'partenaires', 'auth_key_hash', 'CHAR(64) DEFAULT NULL');
    ensure_column($conn, 'partenaires', 'updated_at', 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');
    ensure_unique_index($conn, 'partenaires', 'uniq_partenaires_client_id', 'client_id');

    if ($action === 'delete') {
        $deleteStmt = $conn->prepare('DELETE FROM `partenaires` WHERE `client_id` = ?');
        if (!$deleteStmt) {
            throw new RuntimeException('Failed to prepare delete statement: ' . $conn->error);
        }

        $deleteStmt->bind_param('i', $clientId);
        if (!$deleteStmt->execute()) {
            throw new RuntimeException('Failed to delete partner record: ' . $deleteStmt->error);
        }

        $deleteStmt->close();
        $conn->close();

        send_json(200, [
            'success' => true,
            'message' => 'Partner deleted from database.'
        ]);
    }

    $organizationName = nullable_string($record['organization_name'] ?? null);
    $partnerType = nullable_string($record['partner_type'] ?? null);
    $email = nullable_string($record['email'] ?? null);
    $telephone = nullable_string($record['telephone'] ?? null);
    $address = nullable_string($record['address'] ?? null);
    $country = nullable_string($record['country'] ?? null);
    $domain = nullable_string($record['domain'] ?? null);
    $logo = nullable_string($record['logo_file_name'] ?? null);
    $description = nullable_string($record['description'] ?? null);
    $status = nullable_string($record['status'] ?? 'pending') ?? 'pending';
    $authKeyHash = strtolower((string)($record['auth_key_hash'] ?? ''));

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

    $upsertSql = 'INSERT INTO `partenaires` (
        `client_id`, `organization_name`, `partner_type`, `email`, `telephone`, `address`, `country`, `domain`, `logo`, `description`, `status`, `auth_key_hash`, `created_at`, `updated_at`
    ) VALUES (
        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW()
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
        `updated_at` = NOW()';

    $upsertStmt = $conn->prepare($upsertSql);
    if (!$upsertStmt) {
        throw new RuntimeException('Failed to prepare upsert statement: ' . $conn->error);
    }

    $upsertStmt->bind_param(
        'isssssssssss',
        $clientId,
        $organizationName,
        $partnerType,
        $email,
        $telephone,
        $address,
        $country,
        $domain,
        $logo,
        $description,
        $status,
        $authKeyHash
    );

    if (!$upsertStmt->execute()) {
        throw new RuntimeException('Failed to sync partner record: ' . $upsertStmt->error);
    }

    $upsertStmt->close();
    $conn->close();

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
