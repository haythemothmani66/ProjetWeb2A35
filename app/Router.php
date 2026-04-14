<?php

class Router
{
    private string $route = '';
    private array $parts = [];
    private string $area = 'frontoffice';
    private string $resource = 'courses';
    private string $action = 'index';
    private array $params = [];

    public function __construct()
    {
        $this->parseRoute();
    }

    private function parseRoute(): void
    {
        $this->route = $_GET['route'] ?? 'frontoffice/courses/index';
        $this->parts = array_filter(explode('/', $this->route));

        $this->area = $this->parts[0] ?? 'frontoffice';
        $this->resource = $this->parts[1] ?? 'courses';
        $this->action = $this->parts[2] ?? 'index';

        $this->params = $_GET;
    }

    public function dispatch(): void
    {
        if (!in_array($this->area, ['frontoffice', 'backoffice'], true)) {
            $this->handleNotFound("Invalid area: {$this->area}");
            return;
        }

        $controllerName = ucfirst($this->area) . ucfirst($this->resource) . 'Controller';

        if (!class_exists($controllerName)) {
            $this->handleNotFound("Controller not found: {$controllerName}");
            return;
        }

        $controller = new $controllerName();

        if (!method_exists($controller, $this->action)) {
            $this->handleNotFound("Action not found: {$controllerName}::{$this->action}()");
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->{$this->action}($_POST);
        } else {
            $controller->{$this->action}($this->params);
        }
    }

    private function handleNotFound(string $message): void
    {
        http_response_code(404);
        echo "<h1>404 - Not Found</h1>";
        echo "<p>{$message}</p>";
    }

    public function getArea(): string
    {
        return $this->area;
    }

    public function getResource(): string
    {
        return $this->resource;
    }

    public function getAction(): string
    {
        return $this->action;
    }
}
