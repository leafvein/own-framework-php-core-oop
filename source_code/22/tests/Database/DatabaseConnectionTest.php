<?php
declare(strict_types=1);

namespace Tests\Database;

use Tests\DatabaseTestCase;
use App\Core\Database;
use PDO;

final class DatabaseConnectionTest extends DatabaseTestCase
{
    public function test_database_connection()
    {
        $pdo = Database::connect();

        $this->assertInstanceOf(PDO::class, $pdo);
    }
}
