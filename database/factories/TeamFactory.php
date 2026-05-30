<?php

namespace Database\Factories;

use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Team>
 */
class TeamFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->country(),
            'short_name' => fake()->optional()->lexify('???'),
            'country_code' => fake()->optional()->countryISOAlpha3(),
            'flag_path' => fake()->optional()->filePath(),
        ];
    }
}
