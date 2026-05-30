<?php

namespace Database\Factories;

use App\Models\Team;
use App\Models\Tournament;
use App\Models\TournamentMatch;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TournamentMatch>
 */
class TournamentMatchFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startsAt = fake()->dateTimeBetween('2026-06-11', '2026-07-19');

        return [
            'tournament_id' => Tournament::factory(),
            'team_a_id' => Team::factory(),
            'team_b_id' => Team::factory(),
            'starts_at' => $startsAt,
            'prediction_closes_at' => (clone $startsAt)->modify('-5 minutes'),
            'stage' => fake()->randomElement(['group', 'round_of_32', 'round_of_16', 'quarter_final', 'semi_final', 'final']),
            'group' => fake()->optional()->randomElement(['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H']),
            'status' => fake()->randomElement(['scheduled', 'open', 'locked', 'finished']),
            'team_a_score' => null,
            'team_b_score' => null,
            'winner_team_id' => null,
        ];
    }

    public function placeholder(): static
    {
        return $this->state(fn (array $attributes) => [
            'team_a_id' => null,
            'team_b_id' => null,
            'winner_team_id' => null,
            'status' => 'placeholder',
        ]);
    }
}
