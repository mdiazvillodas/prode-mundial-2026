<?php

namespace Database\Factories;

use App\Models\Prediction;
use App\Models\TournamentMatch;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Prediction>
 */
class PredictionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'match_id' => TournamentMatch::factory(),
            'team_a_score' => fake()->numberBetween(0, 8),
            'team_b_score' => fake()->numberBetween(0, 8),
            'status' => Prediction::STATUS_SUBMITTED,
            'points_awarded' => null,
        ];
    }
}
