<?php

namespace Tests\Feature;

use App\Models\TournamentMatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KnockoutPredictionFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_knockout_draw_prediction_requires_predicted_qualified_team(): void
    {
        // A drawn knockout score cannot infer who advances, so an explicit
        // qualified team is required. Non-draw scores are inferred instead.
        $user = User::factory()->create();
        $match = TournamentMatch::factory()->state([
            'stage' => 'quarter_final',
            'status' => TournamentMatch::STATUS_OPEN,
            'starts_at' => now()->addDay(),
            'prediction_closes_at' => now()->addDay()->subMinutes(5),
        ])->create();

        $response = $this->actingAs($user)
            ->post("/matches/{$match->id}/prediction", [
                'team_a_score' => 1,
                'team_b_score' => 1,
            ]);

        $response->assertSessionHasErrors(['predicted_qualified_team_id']);
    }

    public function test_knockout_non_draw_prediction_infers_qualified_team_from_score(): void
    {
        $user = User::factory()->create();
        $match = TournamentMatch::factory()->state([
            'stage' => 'quarter_final',
            'status' => TournamentMatch::STATUS_OPEN,
            'starts_at' => now()->addDay(),
            'prediction_closes_at' => now()->addDay()->subMinutes(5),
        ])->create();

        $response = $this->actingAs($user)
            ->post("/matches/{$match->id}/prediction", [
                'team_a_score' => 1,
                'team_b_score' => 2,
            ]);

        $response->assertSessionDoesntHaveErrors(['predicted_qualified_team_id']);

        $this->assertDatabaseHas('predictions', [
            'user_id' => $user->id,
            'match_id' => $match->id,
            'predicted_qualified_team_id' => $match->team_b_id,
        ]);
    }

    public function test_knockout_match_prediction_can_be_saved_with_valid_qualified_team(): void
    {
        $user = User::factory()->create();
        $match = TournamentMatch::factory()->state([
            'stage' => 'round_of_16',
            'status' => TournamentMatch::STATUS_OPEN,
            'starts_at' => now()->addDay(),
            'prediction_closes_at' => now()->addDay()->subMinutes(5),
        ])->create();

        $response = $this->actingAs($user)
            ->post("/matches/{$match->id}/prediction", [
                'team_a_score' => 2,
                'team_b_score' => 1,
                'predicted_qualified_team_id' => $match->team_a_id,
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('predictions', [
            'user_id' => $user->id,
            'match_id' => $match->id,
            'team_a_score' => 2,
            'team_b_score' => 1,
            'predicted_qualified_team_id' => $match->team_a_id,
        ]);
    }

    public function test_bulk_store_requires_valid_qualified_team_for_knockout_draw(): void
    {
        // A drawn knockout score with an invalid qualified team id is rejected.
        $user = User::factory()->create();
        $match = TournamentMatch::factory()->state([
            'stage' => 'semi_final',
            'status' => TournamentMatch::STATUS_OPEN,
            'starts_at' => now()->addDay(),
            'prediction_closes_at' => now()->addDay()->subMinutes(5),
        ])->create();

        $response = $this->actingAs($user)
            ->from('/predictions')
            ->post('/predictions/bulk', [
                'predictions' => [
                    $match->id => [
                        'changed' => '1',
                        'team_a_score' => 1,
                        'team_b_score' => 1,
                        'predicted_qualified_team_id' => 999999,
                    ],
                ],
            ]);

        $response->assertSessionHasErrors(["predictions.{$match->id}.predicted_qualified_team_id"]);
    }
}
