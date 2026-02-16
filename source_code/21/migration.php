<?php
declare(strict_types=1);

require __DIR__ . '/app/Core/Bootstrap.php';

$command = $argv[1] ?? null;

if (!in_array($command, ['up', 'down'], true)) {
    echo "Usage:\n";
    echo "  php migration.php up\n";
    echo "  php migration.php down\n";
    exit(1);
}

$files = glob(__DIR__ . '/database/Migrations/*.php');

sort($files); // important for ordered migrations

foreach ($files as $file) {
    echo "Running: " . basename($file) . "\n";

    $migration = require $file;

    if ($command === 'up') {
        $migration->up();
    } else {
        $migration->down();
    }
}

echo "Migration {$command} completed.\n";
