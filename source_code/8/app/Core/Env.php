<?php
declare(strict_types=1);

namespace App\Core;

class Env
{
    public static function load(string $path)
    {
        if (!file_exists($path)) {
            throw new \RuntimeException(".env file not found at {$path}");
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            [$name, $value] = array_map('trim', explode('=', $line, 2));
            $value          = trim($value, "\"'");

            putenv("$name=$value");
            $_ENV[$name]    = $value;
            $_SERVER[$name] = $value;
        }
    }
}
