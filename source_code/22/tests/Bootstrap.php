<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Core\Env;
use App\Core\Config;
use App\Core\Response;

// Load env safely
Env::load(__DIR__ . '/../.env.testing');

// Load configs
Config::load(__DIR__ . '/../config');

// Load helpers
require __DIR__ . '/../app/Helpers/helpers.php';

// Disable exit in tests
Response::$testing = true;

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
