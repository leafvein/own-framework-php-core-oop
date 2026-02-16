<?php
declare(strict_types=1);

use App\Controllers\HomeController;
use App\Controllers\AuthController;
use App\Controllers\UserController;

$router->get('/', [HomeController::class, 'index']);

# user auth
$router->get('/login', [AuthController::class, 'login']);

$router->get('/register', [UserController::class, 'create']);
