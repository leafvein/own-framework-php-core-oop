<?php
declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Request;
use App\Core\Response;

class UserController
{
    private Request $request;
    private array $users;
    
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->users = [
            [
                'id'        => 1,
                'username'  => 'jhondoe',
                'email'     => 'jhondoe@example.com',
                'firstName' => 'Jhon',
                'lastName'  => 'Doe',
            ],
            [
                'id'        => 2,
                'username'  => 'adamsmith',
                'email'     => 'adamsmith@example.com',
                'firstName' => 'Adam',
                'lastName'  => 'Smith',
            ],
        ];
    }

    public function index()
    {
        return Response::json($this->users);
    }

    public function store()
    {
        $userData = $this->request->all();

        $this->users[] = [
            'id' => count($this->users) + 1,
            ...$userData
        ];

        return Response::json($this->users, 201);
    }
}
