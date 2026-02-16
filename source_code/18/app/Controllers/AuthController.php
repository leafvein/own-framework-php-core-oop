<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;

class AuthController
{
    public function login()
    {
        return Response::view('auth/login');
    }

    public function process(Request $request)
    {
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

        header('Location: /');
        exit;
    }
}
