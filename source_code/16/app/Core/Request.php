<?php
declare(strict_types = 1);

namespace App\Core;

class Request
{
    protected array $data = [];

    public function __construct()
    {
        $body       = file_get_contents('php://input');
        $json       = json_decode($body, true);
        $this->data = array_merge($_GET, $_POST, is_array($json) ? $json : []);
    }

    public function method()
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public function uri()
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $uri = strtok($uri, '?');
        $uri = rtrim($uri, '/') ?: '/';
        
        return $uri;
    }

    public function input(string $key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    public function all(): array
    {
        return $this->data;
    }
}
