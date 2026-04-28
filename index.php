<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

define('BASE_URL', '/gestion_users/');
define('ASSETS_URL', BASE_URL . 'assets/');

require_once __DIR__ . '/app/router.php';

$router = new Router();
$router->dispatch();
