<?php
declare(strict_types = 1);

use App\Core\Env;
use App\Core\Config;
use App\Core\Request;
use App\Core\App;

require __DIR__ . '/../../vendor/autoload.php';

// Create an instance of the ExceptionHandler and register it
$exceptionHandler = new \App\Core\ExceptionHandler();
$exceptionHandler->register();

// 1. Load .env
Env::load(__DIR__ . '/../../.env');

// 2. Load configs
Config::load(__DIR__ . '/../../config');

// 3. Load helpers
require __DIR__ . '/../Helpers/helpers.php';

$request  = new Request();
$app = new App($request);

return $app;
