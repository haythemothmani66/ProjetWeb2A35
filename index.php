<?php

require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/app/routes.php';

spl_autoload_register(function ($class) {
    $directories = [
        __DIR__ . '/models/',
        __DIR__ . '/repositories/',
        __DIR__ . '/controllers/',
        __DIR__ . '/app/',
    ];

    foreach ($directories as $dir) {
        $file = $dir . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

$_GET['route'] = $_GET['route'] ?? 'frontoffice/courses/index';

$router = new Router();
$router->dispatch();
