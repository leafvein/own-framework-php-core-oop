<?php
declare(strict_types = 1);

namespace App\Core;

class App
{
    private Router $router;
    private Request $request;

    public function __construct(Router $router, Request $request)
    {
        $this->router  = $router;
        $this->request = $request;
    }

    public function run()
    {
        $method = $this->request->method();
        $uri    = $this->request->uri();

        $routes = $this->router->routes();

        $action = $routes[$method][$uri] ?? null;

        if (!$action) {
            http_response_code(404);
            if (strpos($uri, '/api/') === 0) {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Route not found']);
            } else {
                echo '404 Not Found';
            }
            return;
        }

        if (is_array($action)) {
            [$class, $method] = $action;
            $controller       = new $class();

            return $controller->$method($this->request);
        }

        return $action($this->request);
    }
}
