<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;

class HomeController
{
    public function index()
    {
        return Response::view('home', ['message' => 'Welcome to My OOP App']);
    }
}
