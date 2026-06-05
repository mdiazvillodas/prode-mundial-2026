<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<\App\Models\EmailVerificationCode>
 */
class EmailVerificationCodeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'code_hash' => Hash::make('123456'),
            'expires_at' => now()->addMinutes(15),
            'used_at' => null,
        ];
    }

    public function expired(): static
    {
        return $this->state(fn () => [
            'expires_at' => now()->subMinute(),
        ]);
    }

    public function used(): static
    {
        return $this->state(fn () => [
            'used_at' => now(),
        ]);
    }
}
