<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::query()->create([
            'name' => 'Admin User',
            'username' => Str::lower('admin'),
            'email' => 'admin@example.com',
            'email_verified_at' => now(),
            'password' => 'password',
        ]);

        User::query()->create([
            'name' => 'Test User',
            'username' => Str::lower('testuser'),
            'email' => 'user@example.com',
            'email_verified_at' => now(),
            'password' => 'password',
        ]);
    }
}
