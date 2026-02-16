<?php
declare(strict_types=1);

/**
 * Queue Runner
 *
 * Usage command: `php queue.php work`
 */

require __DIR__ . '/app/Core/Bootstrap.php';

use App\Queue\MailQueue;

$command = $argv[1] ?? null;

if ($command !== 'work') {
    echo "Usage:\n";
    echo "  php queue.php work\n";
    exit(1);
}

$worker = new MailQueue();

echo "Processing email queue...\n";
$worker->work();
echo "Queue processing finished.\n";
