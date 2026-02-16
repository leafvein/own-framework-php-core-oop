<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;

class AuthController
{
    public function login()
    {
        return Response::view('auth/login');
    }
}
