<?php
declare(strict_types = 1);

// load environment
$config = [
    'app_name'=> 'PHP core OOP project'
];

// route define
$route = ltrim($_SERVER['REQUEST_URI'], '/');

echo($route);

if ($route == "get-error") {
    // trigger a fatal error
    trigger_error("An Intentional Error Occurred.", E_USER_ERROR);
}

if ($route == 'get-exception') {
    // throw an exception
    throw new Exception("An Intentional Exception Occurred.");
}

echo 'Welcome to the ' . $config['app_name'];
