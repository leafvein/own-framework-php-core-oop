<?php
declare(strict_types=1);

use App\Core\Abstract\Migration;

return new class extends Migration {

    public function up(): void
    {
        if ($this->tableExists('email_verifications')) {
            echo "Table email_verifications already exists\n";
            return;
        }

        $this->db->exec("
            CREATE TABLE email_verifications (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                token VARCHAR(64) NOT NULL UNIQUE,
                expires_at DATETIME NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )
        ");
    }

    public function down(): void
    {
        if (! $this->tableExists('email_verifications')) {
            return;
        }

        $this->db->exec("DROP TABLE email_verifications");
    }
};
