<?php
declare(strict_types=1);

/**
 * PHP built-in server router configuration
 */

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = rtrim($path, '/');

$requestedFile = __DIR__ . '/public' . $path;

// Serve static file directly if it exists
if (is_file($requestedFile)) {
    return false;
}

// Otherwise, forward to front controller
require __DIR__ . '/public/index.php';
