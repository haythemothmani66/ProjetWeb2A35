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
        'message' => 'Invalid contract identifier.'
    ]);
}

try {
    $conn = connect_database();

    $createTableSql = "CREATE TABLE IF NOT EXISTS `contrats` (
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
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    if (!$conn->query($createTableSql)) {
        throw new RuntimeException('Failed to initialize contrats table: ' . $conn->error);
    }

    ensure_column($conn, 'contrats', 'client_id', 'INT NULL');
    ensure_column($conn, 'contrats', 'auth_key_hash', 'CHAR(64) DEFAULT NULL');
    ensure_column($conn, 'contrats', 'updated_at', 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');
    ensure_column($conn, 'contrats', 'contract_ref', 'VARCHAR(64) DEFAULT NULL');
    ensure_unique_index($conn, 'contrats', 'uniq_contrats_client_id', 'client_id');

    if ($action === 'delete') {
        $deleteStmt = $conn->prepare('DELETE FROM `contrats` WHERE `client_id` = ?');
        if (!$deleteStmt) {
            throw new RuntimeException('Failed to prepare delete statement: ' . $conn->error);
        }

        $deleteStmt->bind_param('i', $clientId);
        if (!$deleteStmt->execute()) {
            throw new RuntimeException('Failed to delete contract record: ' . $deleteStmt->error);
        }

        $deleteStmt->close();
        $conn->close();

        send_json(200, [
            'success' => true,
            'message' => 'Contract deleted from database.'
        ]);
    }

    $contractRef = nullable_string($record['contract_ref'] ?? null);
    $companyName = nullable_string($record['company_name'] ?? null);
    $contractType = nullable_string($record['type_contrat'] ?? null);
    $status = nullable_string($record['statut'] ?? null);
    $startDate = nullable_string($record['date_debut'] ?? null);
    $endDate = nullable_string($record['date_fin'] ?? null);
    $signatureDate = nullable_string($record['date_signature'] ?? null);
    $renewal = nullable_string($record['renouvellement_auto'] ?? null);
    $details = nullable_string($record['details'] ?? null);
    $pdfFileName = nullable_string($record['pdf_file_name'] ?? null);
    $authKeyHash = strtolower((string)($record['auth_key_hash'] ?? ''));

    if ($companyName === null || $contractType === null || $status === null) {
        send_json(422, [
            'success' => false,
            'message' => 'Missing required contract fields.'
        ]);
    }

    if (!preg_match('/^[a-f0-9]{64}$/', $authKeyHash)) {
        send_json(422, [
            'success' => false,
            'message' => 'Invalid auth key hash format.'
        ]);
    }

    $upsertSql = 'INSERT INTO `contrats` (
        `client_id`, `contract_ref`, `company_name`, `type_contrat`, `statut`, `date_debut`, `date_fin`, `date_signature`, `renouvellement_auto`, `details`, `pdf_file_name`, `auth_key_hash`, `created_at`, `updated_at`
    ) VALUES (
        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW()
    ) ON DUPLICATE KEY UPDATE
        `contract_ref` = VALUES(`contract_ref`),
        `company_name` = VALUES(`company_name`),
        `type_contrat` = VALUES(`type_contrat`),
        `statut` = VALUES(`statut`),
        `date_debut` = VALUES(`date_debut`),
        `date_fin` = VALUES(`date_fin`),
        `date_signature` = VALUES(`date_signature`),
        `renouvellement_auto` = VALUES(`renouvellement_auto`),
        `details` = VALUES(`details`),
        `pdf_file_name` = VALUES(`pdf_file_name`),
        `auth_key_hash` = VALUES(`auth_key_hash`),
        `updated_at` = NOW()';

    $upsertStmt = $conn->prepare($upsertSql);
    if (!$upsertStmt) {
        throw new RuntimeException('Failed to prepare upsert statement: ' . $conn->error);
    }

    $upsertStmt->bind_param(
        'isssssssssss',
        $clientId,
        $contractRef,
        $companyName,
        $contractType,
        $status,
        $startDate,
        $endDate,
        $signatureDate,
        $renewal,
        $details,
        $pdfFileName,
        $authKeyHash
    );

    if (!$upsertStmt->execute()) {
        throw new RuntimeException('Failed to sync contract record: ' . $upsertStmt->error);
    }

    $upsertStmt->close();
    $conn->close();

    send_json(200, [
        'success' => true,
        'message' => 'Contract synced successfully.'
    ]);
} catch (Throwable $exception) {
    send_json(500, [
        'success' => false,
        'message' => $exception->getMessage()
    ]);
}
