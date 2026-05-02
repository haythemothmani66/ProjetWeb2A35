<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/OffreEmploi.php';
require_once __DIR__ . '/models/Candidature.php';
require_once __DIR__ . '/helpers/Mailer.php';
require_once __DIR__ . '/vendor/autoload.php';

$espace = $_GET['espace'] ?? 'front';
$module = $_GET['module'] ?? 'offreemploi';
$action = $_GET['action'] ?? 'liste';
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

switch ($module) {
    case 'offreemploi':
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
                case 'stats':
                    $controller->stats();
                    break;
                case 'exportStatsPdf':
                    $controller->exportStatsPdf();
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
        break;

    case 'candidature':
        if ($espace === 'front') {
            require_once __DIR__ . '/controllers/front/CandidatureController.php';
            $controller = new CandidatureController($pdo);

            switch ($action) {
                case 'ajouter':
                    $controller->ajouter();
                    break;
                case 'details':
                    if ($id > 0) {
                        $controller->details($id);
                        break;
                    }
                    $controller->liste();
                    break;
                case 'liste':
                default:
                    $controller->liste();
                    break;
            }

            exit;
        }

        if ($espace === 'back') {
            require_once __DIR__ . '/controllers/back/CandidatureController.php';
            $controller = new CandidatureController($pdo);

            switch ($action) {
                case 'parOffre':
                    if ($id > 0) {
                        $controller->parOffre($id);
                        break;
                    }
                    $controller->liste();
                    break;
                case 'repondre':
                    if ($id > 0) {
                        $controller->repondre($id);
                        break;
                    }
                    $controller->liste();
                    break;
                case 'details':
                    if ($id > 0) {
                        $controller->details($id);
                        break;
                    }
                    $controller->liste();
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
                case 'liste':
                default:
                    $controller->liste();
                    break;
            }

            exit;
        }
        break;
}

http_response_code(404);
echo 'Module or space not found';
