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

    public static function tableExists(): bool
    {
        $stmt = static::db()->prepare(
            "SELECT COUNT(*) FROM information_schema.tables
             WHERE table_schema = DATABASE()
             AND table_name = ?"
        );

        $stmt->execute([static::$table]);

        return (bool) $stmt->fetchColumn();
    }

    public static function all(): array
    {
        return static::db()
            ->query("SELECT * FROM " . static::$table)
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function truncate(): void
    {
        static::db()->exec("TRUNCATE TABLE " . static::$table);
    }

    public static function create(array $attributes): bool
    {
        $keys = array_keys($attributes);

        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            static::$table,
            implode(',', $keys),
            rtrim(str_repeat('?,', count($keys)), ',')
        );

        return static::db()
            ->prepare($sql)
            ->execute(array_values($attributes));
    }

    public static function find(int $id): ?array
    {
        $stmt = static::db()->prepare(
            "SELECT * FROM " . static::$table . " WHERE id = ? LIMIT 1"
        );
        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public static function findBy(string $column, mixed $value): ?array
    {
        $stmt = static::db()->prepare(
            "SELECT * FROM " . static::$table . " WHERE {$column} = ? LIMIT 1"
        );
        $stmt->execute([$value]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public static function update(int $id, array $attributes): bool
    {
        $fields = [];
        $values = [];

        foreach ($attributes as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            if ($key === 'password') {
                $value = password_hash($value, PASSWORD_BCRYPT);
            }

            $fields[] = "{$key} = ?";
            $values[] = $value;
        }

        if (!$fields) {
            return false;
        }

        $values[] = $id;

        $sql = "UPDATE " . static::$table .
               " SET " . implode(', ', $fields) .
               " WHERE id = ?";

        return static::db()->prepare($sql)->execute($values);
    }

    public static function delete(int $id): bool
    {
        $stmt = static::db()->prepare(
            "DELETE FROM " . static::$table . " WHERE id = ?"
        );
    
        return $stmt->execute([$id]);
    }
    
    public static function deleteBy(string $column, mixed $value): int
    {
        $stmt = static::db()->prepare(
            "DELETE FROM " . static::$table . " WHERE {$column} = ?"
        );
    
        $stmt->execute([$value]);
    
        // return number of affected rows
        return $stmt->rowCount();
    }
}
