<?php
declare(strict_types=1);

namespace App\Controllers;

class HomeController
{
    public function index()
    {
        echo 'Welcome to the ' . config('app.name');
    }
}
