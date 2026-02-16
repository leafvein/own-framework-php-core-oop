<?php
declare(strict_types=1);

namespace App\Core\Abstract;

use App\Core\Database;
use PDO;

abstract class Migration
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    /**
     * Shared helper: check if table exists
     */
    protected function tableExists(string $table): bool
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM information_schema.tables
            WHERE table_schema = DATABASE()
            AND table_name = ?
        ");

        $stmt->execute([$table]);

        return (bool) $stmt->fetchColumn();
    }

    /**
     * Check if column exists in a table
     */
    protected function columnExists(string $table, string $column): bool
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM information_schema.columns
            WHERE table_schema = DATABASE()
              AND table_name = ?
              AND column_name = ?
        ");

        $stmt->execute([$table, $column]);

        return (bool) $stmt->fetchColumn();
    }

    abstract public function up(): void;
    abstract public function down(): void;
}
