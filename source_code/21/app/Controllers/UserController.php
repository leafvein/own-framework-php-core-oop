<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;
use App\Models\User;
use App\Queue\MailQueue;

class UserController
{

    public function create()
    {
        if (isset($_SESSION['user_id'])) {
            header('Location: /dashboard');
            exit;
        }

        return Response::view('user/register');
    }

    public function store(Request $request)
    {
        if (isset($_SESSION['user_id'])) {
            header('Location: /dashboard');
            exit;
        }
        
        csrf_verify($request->input('csrf'));

        $validator = new Validator();

        $validator->required('name', $request->input('name'));
        $validator->required('email', $request->input('email'));
        $validator->email('email', $request->input('email'));
        $validator->min('password', $request->input('password'), 6);
        $validator->confirmed(
            'password',
            $request->input('password'),
            $request->input('password_confirmation')
        );

        if ($validator->fails()) {
            return Response::view('user/register', [
                'errors' => $validator->errors()
            ]);
        }

        User::create([
            'name'     => $request->input('name'),
            'email'    => $request->input('email'),
            'password' => password_hash($request->input('password'), PASSWORD_BCRYPT)
        ]);

        // queue trigger to send verification email
        MailQueue::push($request->input('email'), 'verify');
        $_SESSION['flash_message'] = 'User Created. Verification Email has been Sent';

        header('Location: /login');
        exit;
    }

    // dashboard
    public function show()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $user = User::find($_SESSION['user_id']);
        
        return Response::view('user/show', [
            'user' => $user
        ]);
    }
    
    // profile
    public function edit()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $user = User::find($_SESSION['user_id']);
        return Response::view('user/edit', [
            'user' => $user
        ]);
    }

    public function update(Request $request)
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        csrf_verify($request->input('csrf'));
        
        $validator = new Validator();

        $validator->required('name', $request->input('name'));
        $validator->min('password', $request->input('password'), 6);
        $validator->confirmed(
            'password',
            $request->input('password'),
            $request->input('password_confirmation')
        );

        if ($validator->fails()) {
            return Response::view('user/register', [
                'errors' => $validator->errors()
            ]);
        }

        $user = User::find($_SESSION['user_id']);
        User::update($user['id'], [
            'name'     => $request->input('name'),
            'password' => $request->input('password')
        ]);

        header('Location: /dashboard');
        exit;
    }
}
