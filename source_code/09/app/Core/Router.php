<?php
declare(strict_types = 1);

namespace App\Core;

class Router
{
    protected Request $request;
    protected array $routes = [];

    public function __construct(Request $request)
    {
        $this->request  = $request;
    }

    public function get(string $path, callable|array $callback)
    {
        $this->routes['GET'][$path] = $callback;
    }

    public function post(string $path, callable|array $callback)
    {
        $this->routes['POST'][$path] = $callback;
    }

    public function routes()
    {
        return $this->routes;
    }
}
