<?php
use App\Controllers\Api\UserController;

$router->get('/api/users', [UserController::class, 'index']);

$router->post('/api/users', [UserController::class, 'store']);
