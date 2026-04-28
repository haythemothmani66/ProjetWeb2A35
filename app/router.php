<?php

class Router {

    private string $controller;
    private string $action;
    private array  $params;

    public function __construct() {
        $this->parseRoute();
    }

    private function parseRoute(): void {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        // Remove base path /gestion users/
        $base = '/gestion_users/';
        if (strpos($uri, $base) === 0) {
            $uri = substr($uri, strlen($base));
        }

        // Remove query string
        if (($pos = strpos($uri, '?')) !== false) {
            $uri = substr($uri, 0, $pos);
        }

        // Remove .html extension
        $uri = preg_replace('/\.html$/', '', $uri);
        $uri = trim($uri, '/');

        $parts = explode('/', $uri);

        $this->controller = !empty($parts[0]) ? strtolower($parts[0]) : 'auth';
        $this->action     = !empty($parts[1]) ? $parts[1] : 'index';
        $this->params     = $_GET;
    }

    public function dispatch(): void {
        // Home page: redirect to FrontOffice index.php
        if ($this->controller === 'auth' && $this->action === 'index') {
            header('Location: /gestion_users/view/template/index.php');
            exit;
        }

        $className      = ucfirst($this->controller) . 'Controller';
        $controllerFile = __DIR__ . '/../controller/' . $className . '.php';

        if (file_exists($controllerFile)) {
            require_once __DIR__ . '/../config/database.php';
            require_once $controllerFile;

            if (class_exists($className)) {
                $obj    = new $className();
                $action = $this->action;

                if (method_exists($obj, $action)) {
                    $obj->$action($this->params);
                    return;
                }
            }
        }

        // Fallback: load view directly
        $this->loadView($this->controller, $this->action);
    }

    private function loadView(string $controller, string $action): void {
        $candidates = [
            __DIR__ . "/../view/template/{$action}.php",
            __DIR__ . "/../view/template/{$action}.html",
            __DIR__ . "/../view/backoffice/src/pages/backoffice/{$action}.php",
            __DIR__ . "/../view/backoffice/src/pages/backoffice/{$action}.html",
        ];

        foreach ($candidates as $file) {
            if (file_exists($file)) {
                require_once $file;
                return;
            }
        }

        http_response_code(404);
        if (file_exists(__DIR__ . '/../view/template/404.html')) {
            require_once __DIR__ . '/../view/template/404.html';
        } else {
            echo '<h1>404 — Page introuvable</h1>';
        }
    }
}
