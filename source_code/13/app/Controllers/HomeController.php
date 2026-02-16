<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\Response;
use Throwable;

class HomeController
{

    public function index()
    {
        $appName = config('app.name');

        try {
            Database::connect();

            $greetMessage = "Database connection to {$appName} implemented successfully";
        } catch (Throwable) {
            $greetMessage = "Database connection to {$appName} failed";
        }
        
        return Response::view('home', [
            'message' => $greetMessage
        ]);
    }
}
