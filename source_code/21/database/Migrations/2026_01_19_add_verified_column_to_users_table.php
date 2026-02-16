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

        if ($this->columnExists('users', 'verified')) {
            echo "Column verified already exists, skipping...\n";
            return;
        }

        $this->db->exec("
            ALTER TABLE users
            ADD COLUMN verified BOOLEAN DEFAULT FALSE AFTER password
        ");

        echo "verified column added to users table.\n";
    }

    public function down(): void
    {
        if (! $this->tableExists('users')) {
            echo "Table users does not exist, skipping...\n";
            return;
        }

        if (! $this->columnExists('users', 'verified')) {
            echo "Column verified does not exist, skipping...\n";
            return;
        }

        $this->db->exec("
            ALTER TABLE users
            DROP COLUMN verified
        ");

        echo "verified column removed from users table.\n";
    }
};
