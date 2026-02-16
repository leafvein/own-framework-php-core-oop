<?php
declare(strict_types=1);

namespace App\Core;

class Config
{
    protected static array $items = [];

    public static function load(string $configPath)
    {
        foreach (glob($configPath . '/*.php') as $file) {
            $key               = basename($file, '.php');
            self::$items[$key] = require $file;
        }
    }

    public static function get(string $key, mixed $default = null)
    {
        $keys  = explode('.', $key);
        $value = self::$items;

        foreach ($keys as $k) {
            if (!is_array($value) || !array_key_exists($k, $value)) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }

    public static function all()
    {
        return self::$items;
    }
}
