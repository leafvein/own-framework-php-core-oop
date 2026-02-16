<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;
use App\Models\User;

class AuthController
{
    public function login()
    {
        if (isset($_SESSION['user_id'])) {
            header('Location: /dashboard');
            exit;
        }
        return Response::view('auth/login');
    }

    public function process(Request $request)
    {
        if (isset($_SESSION['user_id'])) {
            header('Location: /dashboard');
            exit;
        }
        csrf_verify($request->input('csrf'));

        $validator = new Validator();

        $validator->required('email', $request->input('email'));
        $validator->email('email', $request->input('email'));
        $validator->required('password', $request->input('password'));
        $validator->min('password', $request->input('password'), 6);

        if ($validator->fails()) {
            return Response::view('auth/login', [
                'errors' => $validator->errors()
            ]);
        }

        $user = User::findBy('email', $request->input('email'));

        if (!$user || !password_verify($request->input('password'), $user['password'])) {
            return Response::view('auth/login', [
                'error' => 'Invalid credentials'
            ]);
        }

        $_SESSION['user_id'] = $user['id'];

        header('Location: /dashboard');
        exit;
    }

    public function logout()
    {
        session_destroy();
        header('Location: /login');
        exit;
    }
}
