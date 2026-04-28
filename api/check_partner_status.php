<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

function send_json(int $statusCode, array $payload): void
{
    http_response_code($statusCode);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function getConnexion(): PDO
{
    $host = '127.0.0.1';
    $port = 3306;
    $dbCandidates = ['databaseedumatch', 'database_edumatch'];
    $user = 'root';
    $password = '';

    $lastException = null;

    foreach ($dbCandidates as $databaseName) {
        try {
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
                $host,
                $port,
                $databaseName
            );

            $pdo = new PDO($dsn, $user, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);

            return $pdo;
        } catch (PDOException $exception) {
            $lastException = $exception;
        }
    }

    throw new RuntimeException('Unable to connect to the database.', 0, $lastException);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json(405, [
        'success' => false,
        'message' => 'Method not allowed. Use POST.'
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

$organizationName = trim((string)($decoded['organization_name'] ?? ''));
if ($organizationName === '') {
    send_json(422, [
        'success' => false,
        'message' => 'Organization name is required.'
    ]);
}

try {
    $pdo = getConnexion();
    
    // Vérifier si la table existe
    $tableCheck = $pdo->query("SHOW TABLES LIKE 'partenaires'");
    if ($tableCheck->rowCount() === 0) {
        send_json(200, [
            'success' => true,
            'found' => false,
            'message' => 'No partnerships found. Please submit an application first.'
        ]);
    }
    
    // Rechercher le partenaire
    $stmt = $pdo->prepare("SELECT id, organization_name, email, telephone, status, created_at, updated_at FROM partenaires WHERE LOWER(organization_name) = LOWER(:org_name)");
    $stmt->execute([':org_name' => $organizationName]);
    
    if ($stmt->rowCount() === 0) {
        send_json(200, [
            'success' => true,
            'found' => false,
            'message' => 'No partnership found for "' . $organizationName . '".'
        ]);
    }
    
    $partner = $stmt->fetch();
    
    send_json(200, [
        'success' => true,
        'found' => true,
        'partner' => [
            'id' => (int)$partner['id'],
            'organization_name' => $partner['organization_name'],
            'email' => $partner['email'],
            'telephone' => $partner['telephone'],
            'status' => $partner['status'],
            'created_at' => $partner['created_at'],
            'updated_at' => $partner['updated_at']
        ]
    ]);
    
} catch (Throwable $exception) {
    send_json(500, [
        'success' => false,
        'message' => 'Database error: ' . $exception->getMessage()
    ]);
}
?>