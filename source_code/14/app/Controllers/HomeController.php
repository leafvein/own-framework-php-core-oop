<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;
use App\Models\User;
use Throwable;

class HomeController
{

    public function index()
    {
        $appName = config('app.name');

        try {
            $greetMessage = User::tableExists()
                ? "Users table exists in {$appName}"
                : "Users table does NOT exist in {$appName}";
        } catch (Throwable $e) {
            $greetMessage = "Database error: " . $e->getMessage();
        }
        
        return Response::view('home', [
            'message' => $greetMessage
        ]);
    }
}
