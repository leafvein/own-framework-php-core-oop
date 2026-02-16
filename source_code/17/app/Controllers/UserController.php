<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;

class UserController
{

    public function create()
    {
        return Response::view('user/register');
    }
}
