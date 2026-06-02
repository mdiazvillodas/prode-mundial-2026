<?php

namespace Tests\Feature;

use App\Models\Prediction;
use App\Models\Team;
use App\Models\TournamentMatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CorePredictionFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_access_inline_predictions_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/predictions')
            ->assertOk()
            ->assertSee('Predicciones');
    }

    public function test_guest_is_redirected_from_inline_predictions_page(): void
    {
        $this->get('/predictions')
            ->assertRedirect('/login');
    }

    public function test_inline_prediction_bulk_save_succeeds_for_predictable_non_knockout_match(): void
    {
        $user = User::factory()->create();
        $match = $this->predictableMatch(['stage' => 'group']);

        $this->actingAs($user)
            ->from('/predictions')
            ->post('/predictions/bulk', [
                'predictions' => [
                    $match->id => [
                        'changed' => '1',
                        'team_a_score' => 2,
                        'team_b_score' => 1,
                    ],
                ],
            ])
            ->assertRedirect('/predictions');

        $this->assertDatabaseHas('predictions', [
            'user_id' => $user->id,
            'match_id' => $match->id,
            'team_a_score' => 2,
            'team_b_score' => 1,
            'predicted_qualified_team_id' => null,
            'status' => Prediction::STATUS_SUBMITTED,
            'points_awarded' => null,
        ]);
    }

    public function test_saved_inline_prediction_is_prefilled_on_predictions_page(): void
    {
        $user = User::factory()->create();
        $match = $this->predictableMatch(['stage' => 'group']);

        Prediction::factory()->create([
            'user_id' => $user->id,
            'match_id' => $match->id,
            'team_a_score' => 3,
            'team_b_score' => 2,
        ]);

        $this->actingAs($user)
            ->get('/predictions')
            ->assertOk()
            ->assertSee('value="3"', false)
            ->assertSee('value="2"', false);
    }

    public function test_single_match_prediction_form_renders_for_predictable_match(): void
    {
        $user = User::factory()->create();
        $match = $this->predictableMatch();

        $this->actingAs($user)
            ->get(route('predictions.show', $match))
            ->assertOk()
            ->assertSee('team_a_score', false)
            ->assertSee('team_b_score', false);
    }

    public function test_single_match_prediction_save_succeeds(): void
    {
        $user = User::factory()->create();
        $match = $this->predictableMatch(['stage' => 'group']);

        $this->actingAs($user)
            ->post(route('predictions.store', $match), [
                'team_a_score' => 1,
                'team_b_score' => 0,
            ])
            ->assertRedirect(route('predictions.show', $match));

        $this->assertDatabaseHas('predictions', [
            'user_id' => $user->id,
            'match_id' => $match->id,
            'team_a_score' => 1,
            'team_b_score' => 0,
            'predicted_qualified_team_id' => null,
        ]);
    }

    public function test_prediction_deadline_is_enforced_server_side(): void
    {
        $user = User::factory()->create();
        $match = $this->predictableMatch([
            'starts_at' => now()->addHour(),
            'prediction_closes_at' => now()->subMinute(),
        ]);

        $this->actingAs($user)
            ->post(route('predictions.store', $match), [
                'team_a_score' => 1,
                'team_b_score' => 1,
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('predictions', [
            'user_id' => $user->id,
            'match_id' => $match->id,
        ]);
    }

    public function test_locked_finished_and_placeholder_matches_cannot_be_predicted(): void
    {
        $user = User::factory()->create();

        $matches = [
            $this->predictableMatch(['status' => TournamentMatch::STATUS_LOCKED]),
            $this->predictableMatch(['status' => TournamentMatch::STATUS_FINISHED]),
            TournamentMatch::factory()->placeholder()->create([
                'starts_at' => now()->addDay(),
                'prediction_closes_at' => now()->addDay()->subMinutes(5),
            ]),
        ];

        foreach ($matches as $match) {
            $this->actingAs($user)
                ->post(route('predictions.store', $match), [
                    'team_a_score' => 1,
                    'team_b_score' => 1,
                ])
                ->assertForbidden();

            $this->assertDatabaseMissing('predictions', [
                'user_id' => $user->id,
                'match_id' => $match->id,
            ]);
        }
    }

    public function test_prediction_history_shows_pending_prediction_before_scoring(): void
    {
        $user = User::factory()->create();
        $match = $this->predictableMatch();

        Prediction::factory()->create([
            'user_id' => $user->id,
            'match_id' => $match->id,
            'team_a_score' => 1,
            'team_b_score' => 1,
            'points_awarded' => null,
        ]);

        $this->actingAs($user)
            ->get('/my-predictions')
            ->assertOk()
            ->assertSee('1 - 1')
            ->assertSee('Pendiente');
    }

    public function test_prediction_history_shows_points_after_admin_saves_result_and_settlement_runs(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $match = $this->predictableMatch();

        Prediction::factory()->create([
            'user_id' => $user->id,
            'match_id' => $match->id,
            'team_a_score' => 2,
            'team_b_score' => 1,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.matches.result.update', $match), [
                'team_a_score' => 2,
                'team_b_score' => 1,
            ])
            ->assertRedirect(route('admin.matches.index'));

        $this->assertDatabaseHas('predictions', [
            'user_id' => $user->id,
            'match_id' => $match->id,
            'status' => Prediction::STATUS_SCORED,
            'points_awarded' => 6,
        ]);

        $this->actingAs($user)
            ->get('/my-predictions')
            ->assertOk()
            ->assertSee('Puntos')
            ->assertSee('6');
    }

    public function test_group_stage_prediction_does_not_require_predicted_qualified_team(): void
    {
        $user = User::factory()->create();
        $match = $this->predictableMatch(['stage' => 'group']);

        $this->actingAs($user)
            ->post(route('predictions.store', $match), [
                'team_a_score' => 0,
                'team_b_score' => 0,
            ])
            ->assertRedirect(route('predictions.show', $match));

        $this->assertDatabaseHas('predictions', [
            'user_id' => $user->id,
            'match_id' => $match->id,
            'predicted_qualified_team_id' => null,
        ]);
    }

    public function test_knockout_prediction_requires_predicted_qualified_team(): void
    {
        $user = User::factory()->create();
        $match = $this->predictableMatch(['stage' => 'quarter_final']);

        $this->actingAs($user)
            ->post(route('predictions.store', $match), [
                'team_a_score' => 1,
                'team_b_score' => 1,
            ])
            ->assertSessionHasErrors(['predicted_qualified_team_id']);
    }

    public function test_knockout_prediction_stores_predicted_qualified_team(): void
    {
        $user = User::factory()->create();
        $match = $this->predictableMatch(['stage' => 'final']);

        $this->actingAs($user)
            ->post(route('predictions.store', $match), [
                'team_a_score' => 2,
                'team_b_score' => 2,
                'predicted_qualified_team_id' => $match->team_b_id,
            ])
            ->assertRedirect(route('predictions.show', $match));

        $this->assertDatabaseHas('predictions', [
            'user_id' => $user->id,
            'match_id' => $match->id,
            'predicted_qualified_team_id' => $match->team_b_id,
        ]);
    }

    private function predictableMatch(array $overrides = []): TournamentMatch
    {
        $teamA = Team::factory()->create();
        $teamB = Team::factory()->create();

        return TournamentMatch::factory()->create(array_merge([
            'team_a_id' => $teamA->id,
            'team_b_id' => $teamB->id,
            'starts_at' => now()->addDay(),
            'prediction_closes_at' => now()->addDay()->subMinutes(5),
            'stage' => 'group',
            'status' => TournamentMatch::STATUS_OPEN,
            'team_a_score' => null,
            'team_b_score' => null,
            'winner_team_id' => null,
        ], $overrides));
    }
}
