<?php
declare(strict_types=1);

namespace Database\Seeders;

use App\Core\Abstract\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::truncate();

        User::create([
            'name'     => 'John Doe',
            'email'    => 'john@example.com',
        ]);

        User::create([
            'name'     => 'Jane Doe',
            'email'    => 'jane@example.com',
        ]);
    }
}
