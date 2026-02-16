<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Core\Request;

final class RequestTest extends TestCase
{
    protected function setUp(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI']    = '/test';
        $_POST['name'] = 'JohnDoe';
    }

    public function test_request_method()
    {
        $request = new Request();
        $this->assertEquals('POST', $request->method());
    }

    public function test_request_uri()
    {
        $request = new Request();
        $this->assertEquals('/test', $request->uri());
    }

    public function test_request_input()
    {
        $request = new Request();
        $this->assertEquals('JohnDoe', $request->input('name'));
    }
}
