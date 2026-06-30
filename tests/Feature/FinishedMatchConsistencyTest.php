<?php

namespace Tests\Feature;

use App\Models\Prediction;
use App\Models\Team;
use App\Models\TournamentMatch;
use App\Models\User;
use App\Services\MatchPredictionSettlementService;
use App\Services\Operations\FinishedMatchConsistencyChecker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Tests\TestCase;

class FinishedMatchConsistencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_clean_finished_group_match_with_scored_predictions_passes(): void
    {
        $match = $this->finishedMatch(['team_a_score' => 2, 'team_b_score' => 1], winnerIsTeamA: true);
        $this->scoredPrediction($match, 2, 1, 6);

        $this->assertSame([], $this->codesFor());

        $this->artisan('prode:check-finished-matches')
            ->expectsOutputToContain('No finished-match inconsistencies found.')
            ->assertSuccessful();
    }

    public function test_no_finished_matches_passes(): void
    {
        $this->artisan('prode:check-finished-matches')->assertSuccessful();
    }

    public function test_api_finished_but_local_not_finished_is_reported(): void
    {
        $this->finishedMatch([
            'status' => TournamentMatch::STATUS_SCHEDULED,
            'api_status' => 'FT',
            'team_a_score' => null,
            'team_b_score' => null,
        ]);

        $this->assertContains('api_finished_local_not_finished', $this->codesFor());

        $this->artisan('prode:check-finished-matches')
            ->expectsOutputToContain('api_finished_local_not_finished')
            ->assertFailed();
    }

    public function test_finished_match_with_missing_score_is_reported(): void
    {
        $this->finishedMatch(['team_a_score' => null, 'team_b_score' => null]);

        $this->assertContains('finished_missing_score', $this->codesFor());

        $this->artisan('prode:check-finished-matches')
            ->expectsOutputToContain('finished_missing_score')
            ->assertFailed();
    }

    public function test_finished_knockout_without_winner_is_reported(): void
    {
        $this->finishedMatch([
            'stage' => 'final',
            'team_a_score' => 1,
            'team_b_score' => 1,
            'winner_team_id' => null,
        ]);

        $this->assertContains('knockout_finished_missing_winner', $this->codesFor());

        $this->artisan('prode:check-finished-matches')
            ->expectsOutputToContain('knockout_finished_missing_winner')
            ->assertFailed();
    }

    public function test_finished_knockout_is_clean_after_winner_is_set_and_settlement_reruns(): void
    {
        $match = $this->finishedMatch([
            'stage' => 'final',
            'team_a_score' => 1,
            'team_b_score' => 1,
            'winner_team_id' => null,
        ]);
        $prediction = $this->unscoredPrediction($match, 1, 1);
        $prediction->forceFill(['predicted_qualified_team_id' => $match->team_b_id])->save();

        $this->assertContains('knockout_finished_missing_winner', $this->codesFor());

        $match->forceFill(['winner_team_id' => $match->team_b_id])->save();
        $this->assertSame(1, app(MatchPredictionSettlementService::class)->score($match->refresh()));

        $this->assertSame([], $this->codesFor());
        $this->assertSame(Prediction::STATUS_SCORED, $prediction->refresh()->status);
        $this->assertSame(8, $prediction->points_awarded);
    }

    public function test_finished_match_with_unscored_prediction_is_reported(): void
    {
        $match = $this->finishedMatch(['team_a_score' => 2, 'team_b_score' => 1], winnerIsTeamA: true);
        $this->scoredPrediction($match, 2, 1, 6);
        $this->unscoredPrediction($match, 1, 0);

        $codes = $this->codesFor();
        $this->assertContains('finished_unscored_predictions', $codes);
        // One scored prediction remains, so the "none scored" issue must NOT fire.
        $this->assertNotContains('finished_predictions_none_scored', $codes);

        $this->artisan('prode:check-finished-matches')
            ->expectsOutputToContain('finished_unscored_predictions')
            ->assertFailed();
    }

    public function test_finished_match_with_predictions_but_zero_scored_is_reported(): void
    {
        $match = $this->finishedMatch(['team_a_score' => 2, 'team_b_score' => 1], winnerIsTeamA: true);
        $this->unscoredPrediction($match, 2, 1);
        $this->unscoredPrediction($match, 0, 0);

        $codes = $this->codesFor();
        $this->assertContains('finished_predictions_none_scored', $codes);
        $this->assertContains('finished_unscored_predictions', $codes);

        $this->artisan('prode:check-finished-matches')
            ->expectsOutputToContain('finished_predictions_none_scored')
            ->assertFailed();
    }

    public function test_command_does_not_mutate_data(): void
    {
        $match = $this->finishedMatch([
            'stage' => 'final',
            'team_a_score' => 1,
            'team_b_score' => 1,
            'winner_team_id' => null,
        ]);
        $prediction = $this->unscoredPrediction($match, 0, 0);

        $matchBefore = $match->fresh()->getAttributes();
        $predictionBefore = $prediction->fresh()->getAttributes();

        $this->artisan('prode:check-finished-matches')->assertFailed();

        $this->assertSame($matchBefore, $match->fresh()->getAttributes());
        $this->assertSame($predictionBefore, $prediction->fresh()->getAttributes());
    }

    public function test_group_draw_with_winner_set_is_reported(): void
    {
        $match = $this->finishedMatch([
            'stage' => 'group',
            'team_a_score' => 1,
            'team_b_score' => 1,
        ], winnerIsTeamA: true);

        $this->assertContains('group_draw_has_winner', $this->codesFor());

        $this->artisan('prode:check-finished-matches')
            ->expectsOutputToContain('group_draw_has_winner')
            ->assertFailed();
    }

    public function test_finished_non_draw_with_wrong_winner_is_reported(): void
    {
        $match = $this->finishedMatch([
            'stage' => 'group',
            'team_a_score' => 2,
            'team_b_score' => 1,
        ]);
        // 2-1 should resolve to team A, but team B is recorded as the winner.
        $match->forceFill(['winner_team_id' => $match->team_b_id])->save();

        $this->assertContains('non_draw_winner_mismatch', $this->codesFor());

        $this->artisan('prode:check-finished-matches')
            ->expectsOutputToContain('non_draw_winner_mismatch')
            ->assertFailed();
    }

    public function test_knockout_winner_not_belonging_to_either_team_is_reported(): void
    {
        $other = Team::factory()->create();
        $match = $this->finishedMatch([
            'stage' => 'final',
            'team_a_score' => 2,
            'team_b_score' => 1,
        ]);
        $match->forceFill(['winner_team_id' => $other->id])->save();

        $this->assertContains('knockout_winner_not_in_teams', $this->codesFor());

        $this->artisan('prode:check-finished-matches')
            ->expectsOutputToContain('knockout_winner_not_in_teams')
            ->assertFailed();
    }

    /**
     * @return array<int, string>
     */
    private function codesFor(): array
    {
        return Arr::pluck(app(FinishedMatchConsistencyChecker::class)->check()['issues'], 'code');
    }

    private function finishedMatch(array $overrides = [], bool $winnerIsTeamA = false): TournamentMatch
    {
        $teamA = Team::factory()->create();
        $teamB = Team::factory()->create();

        $attributes = array_merge([
            'team_a_id' => $teamA->id,
            'team_b_id' => $teamB->id,
            'starts_at' => now()->subDay(),
            'prediction_closes_at' => now()->subDay()->subMinutes(5),
            'stage' => 'group',
            'status' => TournamentMatch::STATUS_FINISHED,
            'team_a_score' => null,
            'team_b_score' => null,
            'winner_team_id' => null,
        ], $overrides);

        if ($winnerIsTeamA) {
            $attributes['winner_team_id'] = $teamA->id;
        }

        return TournamentMatch::factory()->create($attributes);
    }

    private function scoredPrediction(TournamentMatch $match, int $teamAScore, int $teamBScore, int $points): Prediction
    {
        return Prediction::factory()->create([
            'user_id' => User::factory()->create()->id,
            'match_id' => $match->id,
            'team_a_score' => $teamAScore,
            'team_b_score' => $teamBScore,
            'status' => Prediction::STATUS_SCORED,
            'points_awarded' => $points,
        ]);
    }

    private function unscoredPrediction(TournamentMatch $match, int $teamAScore, int $teamBScore): Prediction
    {
        return Prediction::factory()->create([
            'user_id' => User::factory()->create()->id,
            'match_id' => $match->id,
            'team_a_score' => $teamAScore,
            'team_b_score' => $teamBScore,
            'status' => Prediction::STATUS_SUBMITTED,
            'points_awarded' => null,
        ]);
    }
}
