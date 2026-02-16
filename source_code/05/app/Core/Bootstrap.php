<?php
declare(strict_types = 1);

require __DIR__ . '/../../vendor/autoload.php';

use App\Controllers\HomeController;

// load environment
$config = [
    'app_name'=> 'PHP core OOP project'
];

// route define
$route = ltrim($_SERVER['REQUEST_URI'], '/');

if ($route == "get-error") {
    // trigger a fatal error
    trigger_error("An Intentional Error Occurred.", E_USER_ERROR);
}

if ($route == 'get-exception') {
    // throw an exception
    throw new Exception("An Intentional Exception Occurred.");
}

$homeController = new HomeController($config['app_name']);
echo $homeController->getGreeting();
