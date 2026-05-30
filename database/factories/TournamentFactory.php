<?php

namespace Database\Factories;

use App\Models\Tournament;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tournament>
 */
class TournamentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $year = fake()->numberBetween(2026, 2034);
        $name = "FIFA World Cup {$year}";

        return [
            'name' => $name,
            'slug' => fake()->unique()->slug(3).'-'.$year,
            'year' => $year,
            'starts_at' => fake()->dateTimeBetween("{$year}-06-01", "{$year}-06-30"),
            'ends_at' => fake()->dateTimeBetween("{$year}-07-01", "{$year}-07-31"),
            'status' => fake()->randomElement(['draft', 'scheduled', 'active', 'finished']),
        ];
    }
}
