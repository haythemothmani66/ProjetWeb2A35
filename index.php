<?php

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/OffreEmploi.php';

$espace = $_GET['espace'] ?? 'front';
$module = $_GET['module'] ?? 'offreemploi';
$action = $_GET['action'] ?? 'liste';
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($module !== 'offreemploi') {
    http_response_code(404);
    echo 'Module introuvable';
    exit;
}

if ($espace === 'front') {
    require_once __DIR__ . '/controllers/front/OffreEmploiController.php';
    $controller = new OffreEmploiController($pdo);

    if ($action === 'details' && $id > 0) {
        $controller->details($id);
        exit;
    }

    $controller->liste();
    exit;
}

if ($espace === 'back') {
    require_once __DIR__ . '/controllers/back/OffreEmploiController.php';
    $controller = new OffreEmploiController($pdo);

    switch ($action) {
        case 'liste':
            $controller->liste();
            break;
        case 'details':
            if ($id > 0) {
                $controller->details($id);
                break;
            }
            $controller->liste();
            break;
        case 'ajouter':
            $controller->ajouter();
            break;
        case 'modifier':
            if ($id > 0) {
                $controller->modifier($id);
                break;
            }
            $controller->liste();
            break;
        case 'supprimer':
            if ($id > 0) {
                $controller->supprimer($id);
                break;
            }
            $controller->liste();
            break;
        default:
            $controller->liste();
            break;
    }

    exit;
}

http_response_code(404);
echo 'Espace introuvable';
