<?php

declare(strict_types = 1);

namespace App\Core;

use App\Controllers\HomeController;

class App
{
    private Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function run()
    {
        // route define
        $method = $this->request->method();
        $uri    = $this->request->uri();

        if ($uri == "/") {
            $homeController = new HomeController();
            echo $homeController->getGreeting($this->request);
        }
        
        if ($uri == "/get-error") {
            // trigger a fatal error
            trigger_error("An Intentional Error Occurred.", E_USER_ERROR);
        }

        if ($uri == '/get-exception') {
            // throw an exception
            throw new \Exception("An Intentional Exception Occurred.");
        }
    }
}
