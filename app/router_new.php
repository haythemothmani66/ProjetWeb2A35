<?php

class Router
{
    private string $route;
    private array $parts = [];

    private string $resource = 'index';
    private string $action = 'index';
    private array $params = [];

    public function __construct()
    {
        $this->parseRoute();
    }

    private function parseRoute(): void
    {
        $route = $_GET['route'] ?? null;

        if (!$route) {
            $route = $this->extractRouteFromRequestUri();
        }

        $this->route = $route ?: 'index';

        $this->parts = array_values(
            array_filter(explode('/', $this->route))
        );

        $this->resource = $this->parts[0] ?? 'index';
        $this->action   = $this->parts[1] ?? 'index';

        $this->params = $_GET;
        unset($this->params['route']);
    }

    private function extractRouteFromRequestUri(): string
    {
        if (!empty($_SERVER['PATH_INFO'])) {
            $path = $_SERVER['PATH_INFO'];
        } else {
            $requestUri = $_SERVER['REQUEST_URI'] ?? '';
            $path = parse_url($requestUri, PHP_URL_PATH) ?: '';
        }

        if (!$path) {
            return '';
        }

        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $scriptDir = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
        $path = str_replace('\\', '/', $path);

        if ($scriptDir !== '' && $scriptDir !== '/' && strpos($path, $scriptDir) === 0) {
            $path = substr($path, strlen($scriptDir));
        }

        $path = trim($path, '/');

        if ($path === '' || $path === 'index.php') {
            return '';
        }

        return $path;
    }

    public function dispatch(): void
    {
        // 1️⃣ Try controller (optional future feature)
        $controllerFile = __DIR__ . "/controller/{$this->resource}.php";

        if (file_exists($controllerFile)) {
            require_once $controllerFile;

            if (class_exists($this->resource)) {
                $controller = new $this->resource();

                if (method_exists($controller, $this->action)) {
                    $controller->{$this->action}($this->params);
                    return;
                }
            }
        }

        // 2️⃣ fallback → load view
        $this->loadView();
    }

  private function loadView(): void
{
    $basePath = __DIR__ . "/../view/";

    $candidates = [
        // root views
        $basePath . $this->resource . '.php',
        $basePath . $this->resource . '.html',

        // template folder (YOUR CASE)
        $basePath . "template/" . $this->resource . '.php',
        $basePath . "template/" . $this->resource . '.html',

        // folder views
        $basePath . $this->resource . '/index.php',
        $basePath . $this->resource . '/index.html',
        $basePath . $this->resource . '/' . $this->action . '.php',
        $basePath . $this->resource . '/' . $this->action . '.html',

        // template subfolders
        $basePath . "template/" . $this->resource . '/' . $this->action . '.php',
        $basePath . "template/" . $this->resource . '/' . $this->action . '.html',
    ];

    foreach ($candidates as $file) {
        if (file_exists($file)) {
            $this->renderView($file);
            return;
        }
    }

    http_response_code(404);
    $this->renderView($basePath . "404.html");
}

    private function renderView(string $file): void
    {
        ob_start();
        include $file;
        $content = ob_get_clean();

        echo $this->rewriteAssetPaths($content);
    }

    private function rewriteAssetPaths(string $content): string
    {
        $baseUrl = $this->getBaseUrl();

        $replacements = [
            '/(href|src)=([