<?php
declare(strict_types=1);

namespace Tests\Database;

use Tests\DatabaseTestCase;
use App\Models\User;

final class UserModelTest extends DatabaseTestCase
{
    public function test_create_user()
    {
        $created = User::create([
            'name'  => 'John',
            'email' => 'john@example.com',
            'password' => 'secret',
        ]);

        $this->assertTrue($created);
        $this->assertCount(1, User::all());
    }

    public function test_find_user_by_id()
    {
        User::create([
            'name' => 'Jane',
            'email' => 'jane@example.com',
            'password' => 'secret',
        ]);

        $user = User::find(1);

        $this->assertEquals('Jane', $user['name']);
    }

    public function test_find_by_column()
    {
        User::create([
            'name' => 'Alice',
            'email' => 'alice@example.com',
            'password' => 'secret',
        ]);

        $user = User::findBy('email', 'alice@example.com');

        $this->assertNotNull($user);
    }

    public function test_update_user()
    {
        User::create([
            'name' => 'Bob',
            'email' => 'bob@example.com',
            'password' => 'secret',
        ]);

        User::update(1, ['name' => 'Bobby']);

        $user = User::find(1);

        $this->assertEquals('Bobby', $user['name']);
    }

    public function test_delete_user()
    {
        User::create([
            'name' => 'Tom',
            'email' => 'tom@example.com',
            'password' => 'secret',
        ]);

        $this->assertTrue(User::delete(1));
        $this->assertCount(0, User::all());
    }
}
