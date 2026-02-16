<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;

class UserController
{

    public function create()
    {
        return Response::view('user/register');
    }

    public function store(Request $request)
    {
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

        header('Location: /login');
        exit;
    }
}
