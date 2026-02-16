<?php
declare(strict_types=1);

/**
 * Seeder Runner
 *
 * Usage command: `php seeder.php seed`
 */

require __DIR__ . '/app/Core/Bootstrap.php';

$command = $argv[1] ?? null;

if ($command !== 'seed') {
    echo "Usage:\n";
    echo "  php seeder.php seed\n";
    exit(1);
}

// load all seeder files
$seederPath = __DIR__ . '/database/Seeders/*.php';

foreach (glob($seederPath) as $file) {
    require_once $file;
}


// process each seeder files
foreach (get_declared_classes() as $class) {
    if (is_subclass_of($class, App\Core\Abstract\Seeder::class)) {
        echo "Seeding: {$class}\n";
        (new $class())->run();
    }
}

echo "Seeding completed.\n";
