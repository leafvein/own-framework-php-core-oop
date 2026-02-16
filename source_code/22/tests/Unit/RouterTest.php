<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Core\Router;
use App\Core\Request;

final class RouterTest extends TestCase
{
    public function test_route_registration()
    {
        $router = new Router(new Request());

        $router->get('/home', fn () => 'ok');

        $routes = $router->routes();

        $this->assertArrayHasKey('/home', $routes['GET']);
    }
}
