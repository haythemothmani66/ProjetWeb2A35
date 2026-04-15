<?php

// Enable errors (recommended for development)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Load Router
require_once __DIR__ . '/app/Router.php';

// Load database config (if you need it globally)
require_once __DIR__ . '/config/database.php';

// Run app
$router = new Router();
$router->dispatch();