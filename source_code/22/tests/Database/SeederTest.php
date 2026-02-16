<?php
declare(strict_types=1);

namespace Tests\Database;

use Tests\DatabaseTestCase;
use Tests\Database\Seeders\UserSeeder;
use App\Models\User;

final class SeederTest extends DatabaseTestCase
{
    public function test_user_seeder_runs()
    {
        $seeder = new UserSeeder();
        $seeder->run();

        $this->assertCount(1, User::all());
    }
}
