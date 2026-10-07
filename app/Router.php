<?php

class Router
{
    private $routes;

    public function __construct(array $routes)
    {
        $this->routes = $routes;
    }

    public function find(string $path): ?array
    {
        return $this->routes[$path] ?? null;
    }

    public function dispatch(array $route): void
    {
        // These values come from config/routes.php, never from request input.
        $file = $route['file'] ?? null;
        $class = $route['controller'] ?? null;
        $action = $route['action'] ?? null;

        if (!is_string($file) || !is_file($file)
            || !is_string($class) || !is_string($action)) {
            throw new LogicException('Invalid MVC route declaration.');
        }

        require_once $file;

        if (!class_exists($class, false)) {
            throw new LogicException('MVC controller class not found.');
        }

        $controller = new $class();
        if (!is_callable([$controller, $action])) {
            throw new LogicException('MVC controller action not callable.');
        }

        $controller->$action();
    }
}
