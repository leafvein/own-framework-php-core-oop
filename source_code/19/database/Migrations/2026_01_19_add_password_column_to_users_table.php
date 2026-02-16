<?php
declare(strict_types=1);

use App\Core\Abstract\Migration;

return new class extends Migration {

    public function up(): void
    {
        if (! $this->tableExists('users')) {
            echo "Table users does not exist, skipping...\n";
            return;
        }

        if ($this->columnExists('users', 'password')) {
            echo "Column password already exists, skipping...\n";
            return;
        }

        $this->db->exec("
            ALTER TABLE users
            ADD COLUMN password VARCHAR(255) NOT NULL AFTER email
        ");

        echo "Password column added to users table.\n";
    }

    public function down(): void
    {
        if (! $this->tableExists('users')) {
            echo "Table users does not exist, skipping...\n";
            return;
        }

        if (! $this->columnExists('users', 'password')) {
            echo "Column password does not exist, skipping...\n";
            return;
        }

        $this->db->exec("
            ALTER TABLE users
            DROP COLUMN password
        ");

        echo "Password column removed from users table.\n";
    }
};
