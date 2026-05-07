<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

define('BASE_URL', '/gestion_users/');
define('ASSETS_URL', BASE_URL . 'assets/');

// ===== Module Partenariat : routing via ?controller=partenaire&action=... =====
$controllerName = strtolower(trim((string)($_GET['controller'] ?? '')));
$actionName     = trim((string)($_GET['action'] ?? 'list'));

$controllerMap = [
    'partenaire' => [
        'file'  => __DIR__ . '/controller/PartenaireController.php',
        'class' => 'PartenaireController',
    ],
    'contract' => [
        'file'  => __DIR__ . '/controller/ContractController.php',
        'class' => 'ContractController',
    ],
    'contrat' => [
        'file'  => __DIR__ . '/controller/ContractController.php',
        'class' => 'ContractController',
    ],
];

if (isset($controllerMap[$controllerName])) {
    require_once __DIR__ . '/config/database.php';
    require_once __DIR__ . '/config.php';
    $target = $controllerMap[$controllerName];
    require_once $target['file'];

    $className  = $target['class'];
    $controller = new $className();
    $controller->handleRequest($actionName);
    exit;
}

// ===== Module Partenariat : frontoffice via ?controller=frontoffice&page=... =====
if ($controllerName === 'frontoffice') {
    require_once __DIR__ . '/config/database.php';
    require_once __DIR__ . '/config.php';
    $page = strtolower(trim((string)($_GET['page'] ?? 'index')));
    if ($page === 'partenariat') {
        $page = 'partenaire';
    }
    $allowed = ['index', 'partenaire', 'contract'];

    if (!in_array($page, $allowed, true)) {
        http_response_code(404);
        echo 'FrontOffice page not found.';
        exit;
    }

    $viewPath = __DIR__ . '/view/frontoffice/index.php';
    if ($page !== 'index') {
        $viewPath = __DIR__ . '/view/frontoffice/' . $page . '/index.php';
    }

    require $viewPath;
    exit;
}

// ===== Routing principal (User, Auth, Profil) =====
require_once __DIR__ . '/app/router.php';

$router = new Router();
$router->dispatch();
