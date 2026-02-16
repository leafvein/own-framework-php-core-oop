<?php

declare(strict_types = 1);

namespace App\Core;

use App\Controllers\HomeController;

class App
{
    public function run()
    {
        // route define
        $route = $_SERVER['REQUEST_URI'];

        if ($route == "/") {
            $homeController = new HomeController();
            echo $homeController->getGreeting();
        }
        
        if ($route == "/get-error") {
            // trigger a fatal error
            trigger_error("An Intentional Error Occurred.", E_USER_ERROR);
        }

        if ($route == '/get-exception') {
            // throw an exception
            throw new \Exception("An Intentional Exception Occurred.");
        }
    }
}
