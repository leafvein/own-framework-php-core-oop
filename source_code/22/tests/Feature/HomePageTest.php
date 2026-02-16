<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use App\Core\App;
use App\Controllers\HomeController;

final class HomePageTest extends TestCase
{
    protected function setUp(): void
    {
        Response::$testing = true;
    }

    public function test_home_page_returns_view()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI']    = '/';

        $request = new Request();
        $router  = new Router($request);

        $router->get('/', [HomeController::class, 'index']);

        $app = new App($router, $request);

        ob_start();
        $app->run();
        $output = ob_get_clean();

        $this->assertStringContainsString(
            'Welcome to PHP core OOP project',
            $output
        );
    }
}
