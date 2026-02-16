<?php
declare(strict_types=1);

namespace App\Core\Abstract;

use PDO;
use App\Core\Database;

abstract class Model
{
    protected static string $table;
    protected static ?PDO $db = null;

    protected static function db(): PDO
    {
        if (!static::$db) {
            static::$db = Database::connect();
        }
        return static::$db;
    }
    
    /**
     * Check whether the model's table exists
     */
    public static function tableExists(): bool
    {
        $stmt = static::db()->prepare("
            SELECT COUNT(*)
            FROM information_schema.tables
            WHERE table_schema = DATABASE()
              AND table_name = :table
        ");

        $stmt->execute([
            'table' => static::$table
        ]);

        return (bool) $stmt->fetchColumn();
    }

    public static function create(array $attributes): bool
    {
        $columns      = array_keys($attributes);
        $placeholders = array_fill(0, count($columns), '?');

        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            static::$table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        $stmt = static::db()->prepare($sql);
        return $stmt->execute(array_values($attributes));
    }

    public static function truncate(): void
    {
        static::db()->exec("TRUNCATE TABLE " . static::$table);
    }

    public static function all(): array
    {
        $stmt = static::db()->query(
            "SELECT * FROM " . static::$table
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
