<?php
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use App\Core\Database;
use PDO;

abstract class DatabaseTestCase extends TestCase
{
    protected static bool $migrated = false;

    public static function setUpBeforeClass(): void
    {
        if (!self::$migrated) {
            self::runMigrations();
            self::$migrated = true;
        }
    }

    protected function setUp(): void
    {
        $this->truncateTables();
    }

    protected static function runMigrations(): void
    {
        foreach (glob(__DIR__ . '/../database/Migrations/*.php') as $file) {
            $migration = require $file;
            $migration->up();
        }
    }

    protected function truncateTables(): void
    {
        $pdo = Database::connect();
        $pdo->exec('SET FOREIGN_KEY_CHECKS=0');

        $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
        foreach ($tables as $table) {
            $pdo->exec("TRUNCATE TABLE {$table}");
        }

        $pdo->exec('SET FOREIGN_KEY_CHECKS=1');
    }
}
