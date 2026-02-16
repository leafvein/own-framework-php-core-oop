<?php
declare(strict_types=1);

namespace Tests\Database;

use Tests\DatabaseTestCase;
use App\Core\Database;

final class MigrationTest extends DatabaseTestCase
{
    public function test_users_table_exists()
    {
        $pdo = Database::connect();

        $stmt = $pdo->query("
            SELECT COUNT(*) 
            FROM information_schema.tables 
            WHERE table_schema = DATABASE()
              AND table_name = 'users'
        ");

        $this->assertEquals(1, $stmt->fetchColumn());
    }
}
