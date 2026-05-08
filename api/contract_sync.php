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
        'message' => 'Invalid contract identifier.'
    ]);
}

try {
    $pdo = Config::getConnexion();

    // S'assurer que la table contrats existe
    $pdo->exec("CREATE TABLE IF NOT EXISTS `contrats` (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    if ($action === 'delete') {
        $stmt = $pdo->prepare('DELETE FROM `contrats` WHERE `client_id` = :client_id');
        $stmt->execute([':client_id' => $clientId]);

        send_json(200, [
            'success' => true,
            'message' => 'Contract deleted from database.'
        ]);
    }

    $contractRef   = nullable_string($record['contract_ref'] ?? null);
    $companyName   = nullable_string($record['company_name'] ?? null);
    $contractType  = nullable_string($record['type_contrat'] ?? null);
    $status        = nullable_string($record['statut'] ?? null);
    $startDate     = nullable_string($record['date_debut'] ?? null);
    $endDate       = nullable_string($record['date_fin'] ?? null);
    $signatureDate = nullable_string($record['date_signature'] ?? null);
    $renewal       = nullable_string($record['renouvellement_auto'] ?? null);
    $details       = nullable_string($record['details'] ?? null);
    $pdfFileName   = nullable_string($record['pdf_file_name'] ?? null);
    $authKeyHash   = strtolower((string)($record['auth_key_hash'] ?? ''));

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
        `client_id`, `contract_ref`, `company_name`, `type_contrat`, `statut`,
        `date_debut`, `date_fin`, `date_signature`, `renouvellement_auto`,
        `details`, `pdf_file_name`, `auth_key_hash`, `created_at`, `updated_at`
    ) VALUES (
        :client_id, :contract_ref, :company_name, :type_contrat, :statut,
        :date_debut, :date_fin, :date_signature, :renouvellement_auto,
        :details, :pdf_file_name, :auth_key_hash, NOW(), NOW()
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

    $stmt = $pdo->prepare($upsertSql);
    $stmt->execute([
        ':client_id'          => $clientId,
        ':contract_ref'       => $contractRef,
        ':company_name'       => $companyName,
        ':type_contrat'       => $contractType,
        ':statut'             => $status,
        ':date_debut'         => $startDate,
        ':date_fin'           => $endDate,
        ':date_signature'     => $signatureDate,
        ':renouvellement_auto'=> $renewal,
        ':details'            => $details,
        ':pdf_file_name'      => $pdfFileName,
        ':auth_key_hash'      => $authKeyHash,
    ]);

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
