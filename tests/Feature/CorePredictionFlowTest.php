<?php

namespace Tests\Feature;

use App\Models\Prediction;
use App\Models\Team;
use App\Models\TournamentMatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
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

    public function test_predictions_shows_only_dates_that_have_matches(): void
    {
        $user = User::factory()->create();

        $this->datedMatch('Argentina', 'Brazil', '2026-06-11 18:00:00');
        $this->datedMatch('France', 'Spain', '2026-06-13 18:00:00');

        $this->actingAs($user)
            ->get('/predictions?date=2026-06-11')
            ->assertOk()
            ->assertSee('date=2026-06-11', false)
            ->assertSee('date=2026-06-13', false)
            ->assertDontSee('date=2026-06-12', false);
    }

    public function test_predictions_date_chip_markup_marks_active_date(): void
    {
        $user = User::factory()->create();

        $this->datedMatch('Argentina', 'Brazil', '2026-06-11 18:00:00');
        $this->datedMatch('France', 'Spain', '2026-06-13 18:00:00');

        $this->actingAs($user)
            ->get('/predictions?date=2026-06-13')
            ->assertOk()
            ->assertSee('data-date-nav', false)
            ->assertSee('data-date-chip', false)
            ->assertSee('data-active-date-chip', false)
            ->assertSee('aria-current="date"', false);
    }

    public function test_predictions_selected_date_filters_matches(): void
    {
        $user = User::factory()->create();

        $this->datedMatch('Argentina', 'Brazil', '2026-06-11 18:00:00');
        $this->datedMatch('France', 'Spain', '2026-06-13 18:00:00');

        $this->actingAs($user)
            ->get('/predictions?date=2026-06-13')
            ->assertOk()
            ->assertSee('France')
            ->assertSee('Spain')
            ->assertDontSee('Argentina')
            ->assertDontSee('Brazil');
    }

    public function test_predictions_without_selected_date_defaults_to_next_available_match_date(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-10 12:00:00'));
        $user = User::factory()->create();

        $this->datedMatch('Past Team A', 'Past Team B', '2026-06-09 18:00:00');
        $this->datedMatch('Next Team A', 'Next Team B', '2026-06-12 18:00:00');
        $this->datedMatch('Later Team A', 'Later Team B', '2026-06-14 18:00:00');

        $this->actingAs($user)
            ->get('/predictions')
            ->assertOk()
            ->assertSee('Next Team A')
            ->assertSee('Next Team B')
            ->assertDontSee('Past Team A')
            ->assertDontSee('Later Team A');

        Carbon::setTestNow();
    }

    public function test_predictions_orders_matches_by_start_time_within_selected_date(): void
    {
        $user = User::factory()->create();

        $this->datedMatch('Late Team A', 'Late Team B', '2026-06-11 22:00:00');
        $this->datedMatch('Early Team A', 'Early Team B', '2026-06-11 18:00:00');
        $this->datedMatch('Middle Team A', 'Middle Team B', '2026-06-11 20:00:00');

        $this->actingAs($user)
            ->get('/predictions?date=2026-06-11')
            ->assertOk()
            ->assertSeeInOrder([
                'Early Team A',
                'Middle Team A',
                'Late Team A',
            ]);
    }

    public function test_predictions_empty_state_when_no_matches_exist(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/predictions')
            ->assertOk()
            ->assertSee('Todavía no hay partidos cargados')
            ->assertDontSee('date=', false);
    }

    public function test_match_more_than_one_hour_from_lock_does_not_show_closing_soon(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-11 12:00:00'));
        $user = User::factory()->create();

        $this->datedMatch('Future Team A', 'Future Team B', '2026-06-18 18:00:00');

        $this->actingAs($user)
            ->get('/predictions?date=2026-06-18')
            ->assertOk()
            ->assertSee('Abierto')
            ->assertDontSee('Cierra pronto');

        Carbon::setTestNow();
    }

    public function test_match_closing_within_one_hour_shows_closing_soon(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-11 12:00:00'));
        $user = User::factory()->create();

        $this->datedMatch('Soon Team A', 'Soon Team B', '2026-06-11 12:35:00');

        $this->actingAs($user)
            ->get('/predictions?date=2026-06-11')
            ->assertOk()
            ->assertSee('Cierra pronto');

        Carbon::setTestNow();
    }

    public function test_locked_match_does_not_show_closing_soon(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-11 12:00:00'));
        $user = User::factory()->create();

        $this->datedMatch('Locked Team A', 'Locked Team B', '2026-06-11 12:35:00', [
            'status' => TournamentMatch::STATUS_LOCKED,
        ]);

        $this->actingAs($user)
            ->get('/predictions?date=2026-06-11')
            ->assertOk()
            ->assertSee('Cerrado')
            ->assertDontSee('Cierra pronto');

        Carbon::setTestNow();
    }

    public function test_predictions_displays_match_time_in_viewer_timezone(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-10 12:00:00', 'UTC'));
        $user = User::factory()->create();

        $this->datedMatch('Argentina', 'Algeria', Carbon::parse('2026-06-17 01:00:00', 'UTC'));

        $this->actingAs($user)
            ->get('/predictions?date=2026-06-17&tz=Europe/Madrid')
            ->assertOk()
            ->assertSee('Argentina')
            ->assertSee('Algeria')
            ->assertSee('03:00')
            ->assertSee('02:55')
            ->assertSee('Horarios en tu hora local');

        Carbon::setTestNow();
    }

    public function test_dashboard_and_predictions_render_same_madrid_summer_kickoff_and_deadline_times(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-10 12:00:00', 'UTC'));
        $user = User::factory()->create();

        $this->datedMatch('France', 'Uruguay', Carbon::parse('2026-06-11 14:00:00', 'UTC'));
        $this->datedMatch('Mexico', 'South Africa', Carbon::parse('2026-06-11 17:00:00', 'UTC'));

        $this->actingAs($user)
            ->get('/dashboard?tz=Europe/Madrid')
            ->assertOk()
            ->assertSee('France')
            ->assertSee('Uruguay')
            ->assertSee('Mexico')
            ->assertSee('South Africa')
            ->assertSee('Juega 16:00')
            ->assertSee('Editás hasta 15:55')
            ->assertSee('Juega 19:00')
            ->assertSee('Editás hasta 18:55');

        $this->actingAs($user)
            ->get('/predictions?date=2026-06-11&tz=Europe/Madrid')
            ->assertOk()
            ->assertSee('France')
            ->assertSee('Uruguay')
            ->assertSee('Mexico')
            ->assertSee('South Africa')
            ->assertSee('16:00')
            ->assertSee('Editar hasta')
            ->assertSee('15:55')
            ->assertSee('19:00')
            ->assertSee('18:55')
            ->assertDontSee('18:00')
            ->assertDontSee('21:00')
            ->assertDontSee('data-local-time', false);

        Carbon::setTestNow();
    }

    public function test_predictions_date_chips_use_viewer_local_dates(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-10 12:00:00', 'UTC'));
        $user = User::factory()->create();

        $this->datedMatch('Boundary Team A', 'Boundary Team B', Carbon::parse('2026-06-16 23:30:00', 'UTC'));

        $this->actingAs($user)
            ->get('/predictions?date=2026-06-17&tz=Europe/Madrid')
            ->assertOk()
            ->assertSee('date=2026-06-17', false)
            ->assertDontSee('date=2026-06-16', false)
            ->assertSee('Boundary Team A')
            ->assertSee('Boundary Team B')
            ->assertSee('01:30');

        Carbon::setTestNow();
    }

    public function test_predictions_filters_by_viewer_local_date_across_utc_midnight(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-10 12:00:00', 'UTC'));
        $user = User::factory()->create();

        $this->datedMatch('Local Next Day A', 'Local Next Day B', Carbon::parse('2026-06-16 23:30:00', 'UTC'));
        $this->datedMatch('Local Same Day A', 'Local Same Day B', Carbon::parse('2026-06-17 22:30:00', 'UTC'));

        $this->actingAs($user)
            ->get('/predictions?date=2026-06-17&tz=Europe/Madrid')
            ->assertOk()
            ->assertSee('Local Next Day A')
            ->assertDontSee('Local Same Day A');

        $this->actingAs($user)
            ->get('/predictions?date=2026-06-17&tz=UTC')
            ->assertOk()
            ->assertDontSee('Local Next Day A')
            ->assertSee('Local Same Day A');

        Carbon::setTestNow();
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

    public function test_inline_prediction_bulk_save_preserves_date_and_timezone_context(): void
    {
        $user = User::factory()->create();
        $match = $this->predictableMatch([
            'stage' => 'group',
            'starts_at' => Carbon::parse('2026-06-17 01:00:00', 'UTC'),
            'prediction_closes_at' => Carbon::parse('2026-06-17 00:55:00', 'UTC'),
        ]);

        $this->actingAs($user)
            ->from('/predictions?date=2026-06-17&tz=Europe/Madrid')
            ->post('/predictions/bulk?date=2026-06-17&tz=Europe/Madrid', [
                'predictions' => [
                    $match->id => [
                        'changed' => '1',
                        'team_a_score' => 2,
                        'team_b_score' => 1,
                    ],
                ],
            ])
            ->assertRedirect('/predictions?date=2026-06-17&tz=Europe%2FMadrid')
            ->assertSessionHas('status', 'Predicciones guardadas.');

        $this->assertDatabaseHas('predictions', [
            'user_id' => $user->id,
            'match_id' => $match->id,
            'team_a_score' => 2,
            'team_b_score' => 1,
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

    private function datedMatch(string $teamAName, string $teamBName, Carbon|string $startsAt, array $overrides = []): TournamentMatch
    {
        $teamA = Team::factory()->create(['name' => $teamAName]);
        $teamB = Team::factory()->create(['name' => $teamBName]);
        $startsAt = $startsAt instanceof Carbon ? $startsAt : Carbon::parse($startsAt);

        return TournamentMatch::factory()->create(array_merge([
            'team_a_id' => $teamA->id,
            'team_b_id' => $teamB->id,
            'starts_at' => $startsAt,
            'prediction_closes_at' => $startsAt->copy()->subMinutes(5),
            'stage' => 'group',
            'status' => TournamentMatch::STATUS_OPEN,
            'team_a_score' => null,
            'team_b_score' => null,
            'winner_team_id' => null,
        ], $overrides));
    }
}
