<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once dirname(__DIR__) . '/config.php';
require_once __DIR__ . '/RecommendationService.php';

function send_json(int $statusCode, array $payload): void
{
    http_response_code($statusCode);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$partnerId = isset($_GET['partner_id']) ? (int)$_GET['partner_id'] : 0;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;

// partner_id is optional. If missing or 0, we provide general recommendations.

try {
    $pdo = getConnexion();
    $recommendations = RecommendationService::getRecommendations($pdo, $partnerId, $limit);

    send_json(200, [
        'success' => true,
        'data' => $recommendations
    ]);
} catch (Throwable $e) {
    send_json(500, [
        'success' => false,
        'message' => 'Error fetching recommendations: ' . $e->getMessage()
    ]);
}
