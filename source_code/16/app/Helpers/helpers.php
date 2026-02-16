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
