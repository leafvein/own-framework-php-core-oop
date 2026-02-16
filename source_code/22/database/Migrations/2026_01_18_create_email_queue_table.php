<?php
declare(strict_types=1);

use App\Core\Abstract\Migration;

return new class extends Migration {

    public function up(): void
    {
        if ($this->tableExists('email_queue')) {
            return;
        }

        $this->db->exec("
            CREATE TABLE email_queue (
                id INT AUTO_INCREMENT PRIMARY KEY,
                to_email VARCHAR(150) NOT NULL,
                type VARCHAR(255) NOT NULL,
                attempts TINYINT DEFAULT 0,
                status ENUM('pending','sent','failed') DEFAULT 'pending',
                last_error TEXT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
    }

    public function down(): void
    {
        $this->db->exec("DROP TABLE IF EXISTS email_queue");
    }
};
