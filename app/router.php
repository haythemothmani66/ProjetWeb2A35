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

        // Strip .html extension if present
        if (substr($this->route, -5) === '.html') {
            $this->route = substr($this->route, 0, -5);
        }

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
        $controllerFile = __DIR__ . "/../controller/{$this->resource}.php";

        if (file_exists($controllerFile)) {
            require_once $controllerFile;

            $className = ucfirst($this->resource);
            if (class_exists($className)) {
                $controller = new $className();

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
            $basePath . "frontoffice/" . $this->resource . '.php',
            $basePath . "frontoffice/" . $this->resource . '.html',

            // folder views
            $basePath . $this->resource . '/index.php',
            $basePath . $this->resource . '/index.html',
            $basePath . $this->resource . '/' . $this->action . '.php',
            $basePath . $this->resource . '/' . $this->action . '.html',

            // template subfolders
            $basePath . "frontoffice/" . $this->resource . '/' . $this->action . '.php',
            $basePath . "frontoffice/" . $this->resource . '/' . $this->action . '.html',
        ];

        foreach ($candidates as $file) {
            if (file_exists($file)) {
                $this->renderView($file);
                return;
            }
        }

        http_response_code(404);
        $this->renderView($basePath . "frontoffice/template/404.html");
    }

    private function renderView(string $file): void
    {
        ob_start();
        include $file;
        $content = ob_get_clean();

        if (!is_string($content)) {
            return;
        }

        echo $this->injectBaseHref($content, $file);
    }

    private function injectBaseHref(string $content, string $file): string
    {
        if (stripos($content, '<head') === false || stripos($content, '<base ') !== false) {
            return $content;
        }

        $baseHref = $this->buildBaseHref($file);
        if ($baseHref === '') {
            return $content;
        }

        $safeBaseHref = htmlspecialchars($baseHref, ENT_QUOTES, 'UTF-8');
        $updated = preg_replace('/<head(\s[^>]*)?>/i', "<head$1>\n  <base href=\"{$safeBaseHref}\">", $content, 1);

        return $updated ?? $content;
    }

    private function buildBaseHref(string $file): string
    {
        $realFile = realpath($file);
        $docRoot = $_SERVER['DOCUMENT_ROOT'] ?? '';
        $realDocRoot = realpath($docRoot);

        if (!$realFile || !$realDocRoot) {
            return '';
        }

        $normalizedFileDir = str_replace('\\', '/', dirname($realFile));
        $normalizedDocRoot = rtrim(str_replace('\\', '/', $realDocRoot), '/');

        if ($normalizedDocRoot === '' || strpos($normalizedFileDir, $normalizedDocRoot) !== 0) {
            return '';
        }

        $relativeDir = substr($normalizedFileDir, strlen($normalizedDocRoot));
        if ($relativeDir === false || $relativeDir === '') {
            return '/';
        }

        if ($relativeDir[0] !== '/') {
            $relativeDir = '/' . $relativeDir;
        }

        return rtrim($relativeDir, '/') . '/';
    }
}
