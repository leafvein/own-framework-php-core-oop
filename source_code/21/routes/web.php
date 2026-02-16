<?php
declare(strict_types=1);

use App\Controllers\HomeController;
use App\Controllers\AuthController;
use App\Controllers\UserController;
use App\Controllers\EmailVerificationController;

$router->get('/', [HomeController::class, 'index']);

# user auth
$router->get('/login', [AuthController::class, 'login']);
$router->post('/login', [AuthController::class, 'process']);

$router->get('/register', [UserController::class, 'create']);
$router->post('/register', [UserController::class, 'store']);

$router->post('/logout', [AuthController::class, 'logout']);

$router->get('/dashboard', [UserController::class, 'show']);

$router->get('/profile', [UserController::class, 'edit']);
$router->post('/profile', [UserController::class, 'update']);

$router->post('/verification-email/send', [EmailVerificationController::class, 'send']);
$router->get('/verification-email/verify', [EmailVerificationController::class, 'verify']);
