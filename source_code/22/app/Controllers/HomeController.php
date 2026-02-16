<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;

class HomeController
{

    public function index()
    {
        $appName = config('app.name');

        $greetMessage = 'Welcome to '. $appName . ': ';
        $intro        = 'A Simple user registration Application';
        
        return Response::view('home', [
            'message' => $greetMessage . $intro
        ]);
    }
}
