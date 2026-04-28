<?php
declare(strict_types=1);

ini_set('display_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/config.php';
startSessionIfNeeded();

$controllerName = strtolower(trim((string)($_GET['controller'] ?? '')));
$actionName = trim((string)($_GET['action'] ?? 'list'));

$controllerMap = [
	'partenaire' => [
		'file' => __DIR__ . '/Controller/PartenaireController.php',
		'class' => 'PartenaireController',
	],
	'contract' => [
		'file' => __DIR__ . '/Controller/ContractController.php',
		'class' => 'ContractController',
	],
	'contrat' => [
		'file' => __DIR__ . '/Controller/ContractController.php',
		'class' => 'ContractController',
	],
];

if (isset($controllerMap[$controllerName])) {
	$target = $controllerMap[$controllerName];
	require_once $target['file'];

	$className = $target['class'];
	$controller = new $className();
	$controller->handleRequest($actionName);
	exit;
}

if ($controllerName === 'frontoffice') {
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

	$viewPath = __DIR__ . '/View/FrontOffice/index.php';
	if ($page !== 'index') {
		$viewPath = __DIR__ . '/View/FrontOffice/' . $page . '/index.php';
	}

	require $viewPath;
	exit;
}

require_once __DIR__ . '/app/router.php';

$router = new Router();
$router->dispatch();