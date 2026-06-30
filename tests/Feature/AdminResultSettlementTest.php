<?php

namespace Tests\Feature;

use App\Models\LeagueMembership;
use App\Models\Prediction;
use App\Models\PrivateLeague;
use App\Models\Team;
use App\Models\TournamentMatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminResultSettlementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_manual_result_form_for_valid_match(): void
    {
        $admin = $this->admin();
        $match = $this->matchReadyForResult();

        $this->actingAs($admin)
            ->get(route('admin.matches.result.edit', $match))
            ->assertOk()
            ->assertSee('Resultado')
            ->assertSee($match->teamA->name)
            ->assertSee($match->teamB->name)
            ->assertSee('team_a_score', false)
            ->assertSee('team_b_score', false);
    }

    public function test_admin_can_save_valid_result_and_set_winner_team_id(): void
    {
        $admin = $this->admin();
        $match = $this->matchReadyForResult();

        $this->actingAs($admin)
            ->post(route('admin.matches.result.update', $match), [
                'team_a_score' => 2,
                'team_b_score' => 1,
            ])
            ->assertRedirect(route('admin.matches.index'));

        $match->refresh();

        $this->assertSame(2, $match->team_a_score);
        $this->assertSame(1, $match->team_b_score);
        $this->assertSame($match->team_a_id, $match->winner_team_id);
        $this->assertSame(TournamentMatch::STATUS_FINISHED, $match->status);
    }

    public function test_draw_result_sets_winner_team_id_to_null(): void
    {
        $admin = $this->admin();
        $match = $this->matchReadyForResult();

        $this->actingAs($admin)
            ->post(route('admin.matches.result.update', $match), [
                'team_a_score' => 1,
                'team_b_score' => 1,
            ])
            ->assertRedirect(route('admin.matches.index'));

        $match->refresh();

        $this->assertSame(1, $match->team_a_score);
        $this->assertSame(1, $match->team_b_score);
        $this->assertNull($match->winner_team_id);
        $this->assertSame(TournamentMatch::STATUS_FINISHED, $match->status);
    }

    public function test_knockout_tied_result_without_winner_is_rejected_and_does_not_settle(): void
    {
        $admin = $this->admin();
        $match = $this->matchReadyForResult(['stage' => 'final']);
        $prediction = $this->knockoutPrediction($match, 1, 1, $match->team_a_id);

        $this->actingAs($admin)
            ->from(route('admin.matches.result.edit', $match))
            ->post(route('admin.matches.result.update', $match), [
                'team_a_score' => 1,
                'team_b_score' => 1,
            ])
            ->assertRedirect(route('admin.matches.result.edit', $match))
            ->assertSessionHasErrors(['winner_team_id']);

        $match->refresh();
        $prediction->refresh();

        $this->assertNull($match->team_a_score);
        $this->assertNull($match->team_b_score);
        $this->assertNull($match->winner_team_id);
        $this->assertSame(TournamentMatch::STATUS_OPEN, $match->status);
        $this->assertSame(Prediction::STATUS_SUBMITTED, $prediction->status);
        $this->assertNull($prediction->points_awarded);
    }

    public function test_knockout_tied_result_with_team_a_winner_saves_and_settles(): void
    {
        $admin = $this->admin();
        $match = $this->matchReadyForResult(['stage' => 'final']);
        $teamA = $match->team_a_id;
        $teamB = $match->team_b_id;
        $exactAndQualified = $this->knockoutPrediction($match, 1, 1, $teamA);
        $exactWrongQualified = $this->knockoutPrediction($match, 1, 1, $teamB);

        $this->actingAs($admin)
            ->post(route('admin.matches.result.update', $match), [
                'team_a_score' => 1,
                'team_b_score' => 1,
                'winner_team_id' => $teamA,
            ])
            ->assertRedirect(route('admin.matches.index'));

        $match->refresh();

        $this->assertSame($teamA, $match->winner_team_id);
        $this->assertSame(TournamentMatch::STATUS_FINISHED, $match->status);
        $this->assertSame(8, $exactAndQualified->refresh()->points_awarded);
        $this->assertSame(5, $exactWrongQualified->refresh()->points_awarded);
    }

    public function test_knockout_tied_result_with_team_b_winner_saves_and_settles(): void
    {
        $admin = $this->admin();
        $match = $this->matchReadyForResult(['stage' => 'semi_final']);
        $teamA = $match->team_a_id;
        $teamB = $match->team_b_id;
        $exactWrongQualified = $this->knockoutPrediction($match, 1, 1, $teamA);
        $exactAndQualified = $this->knockoutPrediction($match, 1, 1, $teamB);

        $this->actingAs($admin)
            ->post(route('admin.matches.result.update', $match), [
                'team_a_score' => 1,
                'team_b_score' => 1,
                'winner_team_id' => $teamB,
            ])
            ->assertRedirect(route('admin.matches.index'));

        $match->refresh();

        $this->assertSame($teamB, $match->winner_team_id);
        $this->assertSame(TournamentMatch::STATUS_FINISHED, $match->status);
        $this->assertSame(5, $exactWrongQualified->refresh()->points_awarded);
        $this->assertSame(8, $exactAndQualified->refresh()->points_awarded);
    }

    public function test_knockout_tied_result_rejects_winner_not_in_match_teams(): void
    {
        $admin = $this->admin();
        $match = $this->matchReadyForResult(['stage' => 'quarter_final']);
        $otherTeam = Team::factory()->create();
        $prediction = $this->knockoutPrediction($match, 1, 1, $match->team_a_id);

        $this->actingAs($admin)
            ->from(route('admin.matches.result.edit', $match))
            ->post(route('admin.matches.result.update', $match), [
                'team_a_score' => 1,
                'team_b_score' => 1,
                'winner_team_id' => $otherTeam->id,
            ])
            ->assertRedirect(route('admin.matches.result.edit', $match))
            ->assertSessionHasErrors(['winner_team_id']);

        $match->refresh();
        $prediction->refresh();

        $this->assertNull($match->team_a_score);
        $this->assertNull($match->team_b_score);
        $this->assertNull($match->winner_team_id);
        $this->assertSame(TournamentMatch::STATUS_OPEN, $match->status);
        $this->assertSame(Prediction::STATUS_SUBMITTED, $prediction->status);
        $this->assertNull($prediction->points_awarded);
    }

    public function test_result_correction_recalculates_prediction_points_idempotently(): void
    {
        $admin = $this->admin();
        $user = User::factory()->create();
        $match = $this->matchReadyForResult();

        $prediction = Prediction::factory()->create([
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

        $this->assertSame(6, $prediction->refresh()->points_awarded);

        $this->actingAs($admin)
            ->post(route('admin.matches.result.update', $match), [
                'team_a_score' => 1,
                'team_b_score' => 0,
            ])
            ->assertRedirect(route('admin.matches.index'));

        $prediction->refresh();

        $this->assertSame(3, $prediction->points_awarded);
        $this->assertSame(Prediction::STATUS_SCORED, $prediction->status);

        $this->actingAs($admin)
            ->post(route('admin.matches.result.update', $match), [
                'team_a_score' => 1,
                'team_b_score' => 0,
            ])
            ->assertRedirect(route('admin.matches.index'));

        $this->assertSame(3, $prediction->refresh()->points_awarded);
        $this->assertSame(1, Prediction::where('user_id', $user->id)->where('match_id', $match->id)->count());
    }

    public function test_settlement_scores_exact_correct_outcome_and_incorrect_predictions(): void
    {
        $admin = $this->admin();
        $match = $this->matchReadyForResult();
        $exactUser = User::factory()->create();
        $trendUser = User::factory()->create();
        $wrongUser = User::factory()->create();

        Prediction::factory()->create([
            'user_id' => $exactUser->id,
            'match_id' => $match->id,
            'team_a_score' => 2,
            'team_b_score' => 1,
        ]);
        Prediction::factory()->create([
            'user_id' => $trendUser->id,
            'match_id' => $match->id,
            'team_a_score' => 1,
            'team_b_score' => 0,
        ]);
        Prediction::factory()->create([
            'user_id' => $wrongUser->id,
            'match_id' => $match->id,
            'team_a_score' => 0,
            'team_b_score' => 2,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.matches.result.update', $match), [
                'team_a_score' => 2,
                'team_b_score' => 1,
            ])
            ->assertRedirect(route('admin.matches.index'));

        $this->assertDatabaseHas('predictions', [
            'user_id' => $exactUser->id,
            'match_id' => $match->id,
            'status' => Prediction::STATUS_SCORED,
            'points_awarded' => 6,
        ]);
        $this->assertDatabaseHas('predictions', [
            'user_id' => $trendUser->id,
            'match_id' => $match->id,
            'status' => Prediction::STATUS_SCORED,
            'points_awarded' => 3,
        ]);
        $this->assertDatabaseHas('predictions', [
            'user_id' => $wrongUser->id,
            'match_id' => $match->id,
            'status' => Prediction::STATUS_SCORED,
            'points_awarded' => 0,
        ]);
    }

    public function test_knockout_settlement_stores_matrix_points_and_is_idempotent(): void
    {
        $admin = $this->admin();
        $match = $this->matchReadyForResult(['stage' => 'quarter_final']);
        $teamA = $match->team_a_id;
        $teamB = $match->team_b_id;

        // Actual result: team A wins 2-1, so winner_team_id resolves to team A.
        $perfect = $this->knockoutPrediction($match, 2, 1, $teamA);       // exact + qualified -> 8
        $exactWrongQualified = $this->knockoutPrediction($match, 2, 1, $teamB); // exact + wrong qualified -> 5
        $trendAndQualified = $this->knockoutPrediction($match, 1, 0, $teamA);   // trend + qualified, no exact -> 5
        $qualifiedOnly = $this->knockoutPrediction($match, 0, 1, $teamA);       // wrong trend, qualified -> 3
        $trendOnly = $this->knockoutPrediction($match, 3, 1, $teamB);           // trend only -> 2
        $incorrect = $this->knockoutPrediction($match, 0, 2, $teamB);           // wrong trend + wrong qualified -> 0

        $this->actingAs($admin)
            ->post(route('admin.matches.result.update', $match), [
                'team_a_score' => 2,
                'team_b_score' => 1,
            ])
            ->assertRedirect(route('admin.matches.index'));

        $match->refresh();
        $this->assertSame($teamA, $match->winner_team_id);

        $expected = [
            $perfect->id => 8,
            $exactWrongQualified->id => 5,
            $trendAndQualified->id => 5,
            $qualifiedOnly->id => 3,
            $trendOnly->id => 2,
            $incorrect->id => 0,
        ];

        foreach ($expected as $predictionId => $points) {
            $this->assertDatabaseHas('predictions', [
                'id' => $predictionId,
                'status' => Prediction::STATUS_SCORED,
                'points_awarded' => $points,
            ]);
        }

        // Re-running settlement with the same result must be idempotent.
        $this->actingAs($admin)
            ->post(route('admin.matches.result.update', $match), [
                'team_a_score' => 2,
                'team_b_score' => 1,
            ])
            ->assertRedirect(route('admin.matches.index'));

        foreach ($expected as $predictionId => $points) {
            $this->assertSame($points, Prediction::query()->findOrFail($predictionId)->points_awarded);
        }

        $this->assertSame(count($expected), Prediction::query()->where('match_id', $match->id)->count());
    }

    public function test_saving_result_with_no_predictions_still_works(): void
    {
        $admin = $this->admin();
        $match = $this->matchReadyForResult();

        $this->actingAs($admin)
            ->post(route('admin.matches.result.update', $match), [
                'team_a_score' => 3,
                'team_b_score' => 2,
            ])
            ->assertRedirect(route('admin.matches.index'));

        $this->assertDatabaseHas('matches', [
            'id' => $match->id,
            'team_a_score' => 3,
            'team_b_score' => 2,
            'status' => TournamentMatch::STATUS_FINISHED,
        ]);
    }

    public function test_admin_cannot_load_result_for_placeholder_or_missing_team_match(): void
    {
        $admin = $this->admin();
        $match = TournamentMatch::factory()->placeholder()->create([
            'starts_at' => now()->addDay(),
            'prediction_closes_at' => now()->addDay()->subMinutes(5),
        ]);

        $this->actingAs($admin)
            ->post(route('admin.matches.result.update', $match), [
                'team_a_score' => 1,
                'team_b_score' => 0,
            ])
            ->assertSessionHasErrors(['result']);

        $match->refresh();

        $this->assertSame(TournamentMatch::STATUS_PLACEHOLDER, $match->status);
        $this->assertNull($match->team_a_score);
        $this->assertNull($match->team_b_score);
    }

    public function test_admin_can_assign_teams_to_placeholder_match_and_status_becomes_scheduled(): void
    {
        $admin = $this->admin();
        $match = TournamentMatch::factory()->placeholder()->create();
        $teamA = Team::factory()->create(['name' => 'Mexico']);
        $teamB = Team::factory()->create(['name' => 'Uruguay']);

        $this->actingAs($admin)
            ->get(route('admin.matches.teams.edit', $match))
            ->assertOk()
            ->assertSee('Asignar equipos');

        $this->actingAs($admin)
            ->post(route('admin.matches.teams.update', $match), [
                'team_a_id' => $teamA->id,
                'team_b_id' => $teamB->id,
            ])
            ->assertRedirect(route('admin.matches.index'));

        $match->refresh();

        $this->assertSame($teamA->id, $match->team_a_id);
        $this->assertSame($teamB->id, $match->team_b_id);
        $this->assertSame(TournamentMatch::STATUS_SCHEDULED, $match->status);
    }

    public function test_admin_cannot_assign_the_same_team_twice_to_placeholder_match(): void
    {
        $admin = $this->admin();
        $match = TournamentMatch::factory()->placeholder()->create();
        $team = Team::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.matches.teams.update', $match), [
                'team_a_id' => $team->id,
                'team_b_id' => $team->id,
            ])
            ->assertSessionHasErrors(['team_b_id']);

        $match->refresh();

        $this->assertNull($match->team_a_id);
        $this->assertNull($match->team_b_id);
        $this->assertSame(TournamentMatch::STATUS_PLACEHOLDER, $match->status);
    }

    public function test_global_leaderboard_reflects_scored_points_after_settlement(): void
    {
        $admin = $this->admin();
        $exactUser = User::factory()->create(['username' => 'exact_user']);
        $trendUser = User::factory()->create(['username' => 'trend_user']);
        $match = $this->matchReadyForResult();

        Prediction::factory()->create([
            'user_id' => $exactUser->id,
            'match_id' => $match->id,
            'team_a_score' => 2,
            'team_b_score' => 1,
        ]);
        Prediction::factory()->create([
            'user_id' => $trendUser->id,
            'match_id' => $match->id,
            'team_a_score' => 1,
            'team_b_score' => 0,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.matches.result.update', $match), [
                'team_a_score' => 2,
                'team_b_score' => 1,
            ]);

        $this->actingAs($admin)
            ->get(route('leaderboard.index'))
            ->assertOk()
            ->assertSeeInOrder(['@exact_user', '6', '@trend_user', '3']);
    }

    public function test_private_league_leaderboard_reflects_settlement_for_active_members_only(): void
    {
        $admin = $this->admin();
        $owner = User::factory()->create(['username' => 'owner_user']);
        $exactMember = User::factory()->create(['username' => 'exact_member']);
        $trendMember = User::factory()->create(['username' => 'trend_member']);
        $removedMember = User::factory()->create(['username' => 'removed_member']);
        $outsideUser = User::factory()->create(['username' => 'outside_user']);
        $league = $owner->ownedPrivateLeague()->create(['name' => 'Liga Settlement']);
        $match = $this->matchReadyForResult();

        foreach ([$exactMember, $trendMember] as $member) {
            $league->memberships()->create([
                'user_id' => $member->id,
                'status' => LeagueMembership::STATUS_ACTIVE,
                'joined_at' => now(),
            ]);
        }

        $league->memberships()->create([
            'user_id' => $removedMember->id,
            'status' => LeagueMembership::STATUS_REMOVED,
            'joined_at' => now()->subDay(),
        ]);

        Prediction::factory()->create([
            'user_id' => $exactMember->id,
            'match_id' => $match->id,
            'team_a_score' => 2,
            'team_b_score' => 1,
        ]);
        Prediction::factory()->create([
            'user_id' => $trendMember->id,
            'match_id' => $match->id,
            'team_a_score' => 1,
            'team_b_score' => 0,
        ]);
        Prediction::factory()->create([
            'user_id' => $removedMember->id,
            'match_id' => $match->id,
            'team_a_score' => 2,
            'team_b_score' => 1,
        ]);
        Prediction::factory()->create([
            'user_id' => $outsideUser->id,
            'match_id' => $match->id,
            'team_a_score' => 2,
            'team_b_score' => 1,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.matches.result.update', $match), [
                'team_a_score' => 2,
                'team_b_score' => 1,
            ]);

        $this->actingAs($owner)
            ->get(route('private-leagues.show', $league))
            ->assertOk()
            ->assertSeeInOrder(['@exact_member', '6', '@trend_member', '3', '@owner_user', '0'])
            ->assertDontSee('@removed_member')
            ->assertDontSee('@outside_user');
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_ADMIN]);
    }

    private function knockoutPrediction(TournamentMatch $match, int $teamAScore, int $teamBScore, int $qualifiedTeamId): Prediction
    {
        return Prediction::factory()->create([
            'user_id' => User::factory()->create()->id,
            'match_id' => $match->id,
            'team_a_score' => $teamAScore,
            'team_b_score' => $teamBScore,
            'predicted_qualified_team_id' => $qualifiedTeamId,
        ]);
    }

    private function matchReadyForResult(array $overrides = []): TournamentMatch
    {
        $teamA = Team::factory()->create(['name' => 'Team A']);
        $teamB = Team::factory()->create(['name' => 'Team B']);

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
