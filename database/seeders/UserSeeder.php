<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::query()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'email_verified_at' => now(),
            'password' => 'password',
        ]);

        User::query()->create([
            'name' => 'Test User',
            'email' => 'user@example.com',
            'email_verified_at' => now(),
            'password' => 'password',
        ]);
    }
}
