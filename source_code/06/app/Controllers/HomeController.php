<?php

namespace App\Controllers;

class HomeController
{
    private $appName;

    public function __construct($appName) {
        $this->appName = $appName;
    }

    public function getGreeting()
    {
        return 'Welcome to the ' . $this->appName;
    }
}
