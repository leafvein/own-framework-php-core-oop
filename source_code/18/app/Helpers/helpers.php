<?php
declare(strict_types=1);

use App\Core\Config;

if (!function_exists('config')) {
    function config(string $key, mixed $default = null)
    {
        return Config::get($key, $default);
    }
}

if (!function_exists('asset')) {
    function asset(string $path)
    {
        return '/' . ltrim($path, '/');
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token()
    {
        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf'];
    }
}

if (!function_exists('csrf_verify')) {
    function csrf_verify($token)
    {
        if (
            empty($_SESSION['csrf']) ||
            !hash_equals($_SESSION['csrf'], $token ?? '')
        ) {
            http_response_code(419);
            die('CSRF token mismatch');
        }
    }
}
