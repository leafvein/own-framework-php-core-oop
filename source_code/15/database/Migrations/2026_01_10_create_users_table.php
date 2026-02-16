<?php
declare(strict_types=1);

use App\Core\Abstract\Migration;

return new class extends Migration {

    public function up(): void
    {
        if ($this->tableExists('users')) {
            echo "Table users already exists, skipping...\n";
            return;
        }

        $this->db->exec("
            CREATE TABLE users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100),
                email VARCHAR(150),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
    }

    public function down(): void
    {
        if (! $this->tableExists('users')) {
            echo "Table users does not exist, skipping...\n";
            return;
        }

        $this->db->exec("DROP TABLE users");
    }
};
