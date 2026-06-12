<?php

namespace Tests\Feature;

use App\Models\Prediction;
use App\Models\Team;
use App\Models\TournamentMatch;
use App\Models\User;
use App\Services\MatchPredictionSettlementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MatchPredictionSettlementServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_group_stage_settlement_scores_exact_trend_draw_wrong_and_missing_predictions(): void
    {
        $match = $this->finishedMatch(stage: 'group', teamAScore: 2, teamBScore: 2);
        $exact = User::factory()->create();
        $winnerTrend = User::factory()->create();
        $drawTrend = User::factory()->create();
        $wrong = User::factory()->create();
        $noPrediction = User::factory()->create();

        $this->prediction($exact, $match, 2, 2);
        $this->prediction($winnerTrend, $match, 1, 0);
        $this->prediction($drawTrend, $match, 1, 1);
        $this->prediction($wrong, $match, 0, 1);

        $scoredCount = $this->settlement()->score($match);

        $this->assertSame(4, $scoredCount);
        $this->assertSame(6, $this->pointsFor($exact, $match));
        $this->assertSame(0, $this->pointsFor($winnerTrend, $match));
        $this->assertSame(3, $this->pointsFor($drawTrend, $match));
        $this->assertSame(0, $this->pointsFor($wrong, $match));
        $this->assertDatabaseMissing('predictions', [
            'user_id' => $noPrediction->id,
            'match_id' => $match->id,
        ]);
    }

    public function test_group_stage_winner_trend_scores_three_points(): void
    {
        $match = $this->finishedMatch(stage: 'group', teamAScore: 2, teamBScore: 1);
        $trend = User::factory()->create();

        $this->prediction($trend, $match, 1, 0);

        $this->settlement()->score($match);

        $this->assertSame(3, $this->pointsFor($trend, $match));
    }

    public function test_settlement_is_idempotent_when_run_more_than_once(): void
    {
        $match = $this->finishedMatch(stage: 'group', teamAScore: 3, teamBScore: 1);
        $exact = User::factory()->create();
        $trend = User::factory()->create();

        $this->prediction($exact, $match, 3, 1);
        $this->prediction($trend, $match, 2, 0);

        $settlement = $this->settlement();

        $this->assertSame(2, $settlement->score($match));
        $firstTotals = Prediction::query()
            ->where('match_id', $match->id)
            ->pluck('points_awarded', 'user_id')
            ->all();

        $this->assertSame(2, $settlement->score($match->refresh()));
        $secondTotals = Prediction::query()
            ->where('match_id', $match->id)
            ->pluck('points_awarded', 'user_id')
            ->all();

        $this->assertSame($firstTotals, $secondTotals);
        $this->assertSame(2, Prediction::query()->where('match_id', $match->id)->count());
        $this->assertSame(9, (int) Prediction::query()->where('match_id', $match->id)->sum('points_awarded'));
    }

    public function test_knockout_settlement_applies_expanded_matrix_using_winner_team(): void
    {
        $teamA = Team::factory()->create(['name' => 'Team A']);
        $teamB = Team::factory()->create(['name' => 'Team B']);
        // Final played score 1-1 (tied), resolved on penalties to team A.
        $match = $this->finishedMatch(
            stage: 'final',
            teamAScore: 1,
            teamBScore: 1,
            teamA: $teamA,
            teamB: $teamB,
            winnerTeam: $teamA,
        );

        $exactAndQualified = User::factory()->create();   // 1-1 + team A -> 8
        $exactWrongQualified = User::factory()->create(); // 1-1 + team B -> 5
        $trendAndQualified = User::factory()->create();   // 2-2 (draw) + team A -> 5
        $qualifiedOnly = User::factory()->create();       // 2-1 (wrong trend) + team A -> 3
        $trendOnly = User::factory()->create();           // 0-0 (draw) + team B -> 2
        $incorrect = User::factory()->create();           // 2-1 (wrong trend) + team B -> 0

        $this->prediction($exactAndQualified, $match, 1, 1, $teamA);
        $this->prediction($exactWrongQualified, $match, 1, 1, $teamB);
        $this->prediction($trendAndQualified, $match, 2, 2, $teamA);
        $this->prediction($qualifiedOnly, $match, 2, 1, $teamA);
        $this->prediction($trendOnly, $match, 0, 0, $teamB);
        $this->prediction($incorrect, $match, 2, 1, $teamB);

        $this->settlement()->score($match);

        $this->assertSame(8, $this->pointsFor($exactAndQualified, $match));
        $this->assertSame(5, $this->pointsFor($exactWrongQualified, $match));
        $this->assertSame(5, $this->pointsFor($trendAndQualified, $match));
        $this->assertSame(3, $this->pointsFor($qualifiedOnly, $match));
        $this->assertSame(2, $this->pointsFor($trendOnly, $match));
        $this->assertSame(0, $this->pointsFor($incorrect, $match));
    }

    public function test_knockout_settlement_is_idempotent_across_all_buckets(): void
    {
        $teamA = Team::factory()->create(['name' => 'Team A']);
        $teamB = Team::factory()->create(['name' => 'Team B']);
        // Final played score 1-1 (tied), resolved on penalties to team A.
        $match = $this->finishedMatch(
            stage: 'final',
            teamAScore: 1,
            teamBScore: 1,
            teamA: $teamA,
            teamB: $teamB,
            winnerTeam: $teamA,
        );

        $exactAndQualified = User::factory()->create();   // 1-1 + team A -> 8
        $exactWrongQualified = User::factory()->create(); // 1-1 + team B -> 5
        $trendAndQualified = User::factory()->create();   // 2-2 (draw) + team A -> 5
        $qualifiedOnly = User::factory()->create();       // 2-1 (wrong trend) + team A -> 3
        $trendOnly = User::factory()->create();           // 0-0 (draw) + team B -> 2
        $incorrect = User::factory()->create();           // 2-1 (wrong trend) + team B -> 0

        $this->prediction($exactAndQualified, $match, 1, 1, $teamA);
        $this->prediction($exactWrongQualified, $match, 1, 1, $teamB);
        $this->prediction($trendAndQualified, $match, 2, 2, $teamA);
        $this->prediction($qualifiedOnly, $match, 2, 1, $teamA);
        $this->prediction($trendOnly, $match, 0, 0, $teamB);
        $this->prediction($incorrect, $match, 2, 1, $teamB);

        $settlement = $this->settlement();

        $this->assertSame(6, $settlement->score($match));
        $firstTotals = Prediction::query()
            ->where('match_id', $match->id)
            ->orderBy('user_id')
            ->pluck('points_awarded', 'user_id')
            ->all();

        // Re-running settlement must not change any awarded points or duplicate rows.
        $this->assertSame(6, $settlement->score($match->refresh()));
        $secondTotals = Prediction::query()
            ->where('match_id', $match->id)
            ->orderBy('user_id')
            ->pluck('points_awarded', 'user_id')
            ->all();

        $this->assertSame($firstTotals, $secondTotals);
        $this->assertSame(6, Prediction::query()->where('match_id', $match->id)->count());
        $this->assertSame(23, (int) Prediction::query()->where('match_id', $match->id)->sum('points_awarded'));
    }

    public function test_knockout_settlement_without_resolved_winner_does_not_award_qualified_points(): void
    {
        $teamA = Team::factory()->create(['name' => 'Team A']);
        $teamB = Team::factory()->create(['name' => 'Team B']);
        // Tied knockout with no resolvable winner (API winner flags absent).
        $match = $this->finishedMatch(
            stage: 'semi_final',
            teamAScore: 1,
            teamBScore: 1,
            teamA: $teamA,
            teamB: $teamB,
            winnerTeam: null,
        );
        $this->assertNull($match->winner_team_id);

        $exact = User::factory()->create();
        $trend = User::factory()->create();

        $this->prediction($exact, $match, 1, 1, $teamA); // exact, qualified not credited -> 5
        $this->prediction($trend, $match, 0, 0, $teamA); // draw trend, qualified not credited -> 2

        $this->settlement()->score($match);

        $this->assertSame(5, $this->pointsFor($exact, $match));
        $this->assertSame(2, $this->pointsFor($trend, $match));
    }

    private function settlement(): MatchPredictionSettlementService
    {
        return app(MatchPredictionSettlementService::class);
    }

    private function finishedMatch(
        string $stage,
        int $teamAScore,
        int $teamBScore,
        ?Team $teamA = null,
        ?Team $teamB = null,
        ?Team $winnerTeam = null,
    ): TournamentMatch {
        $teamA ??= Team::factory()->create();
        $teamB ??= Team::factory()->create();

        return TournamentMatch::factory()->create([
            'team_a_id' => $teamA->id,
            'team_b_id' => $teamB->id,
            'stage' => $stage,
            'status' => TournamentMatch::STATUS_FINISHED,
            'team_a_score' => $teamAScore,
            'team_b_score' => $teamBScore,
            'winner_team_id' => $winnerTeam?->id ?? ($teamAScore > $teamBScore ? $teamA->id : ($teamBScore > $teamAScore ? $teamB->id : null)),
        ]);
    }

    private function prediction(
        User $user,
        TournamentMatch $match,
        int $teamAScore,
        int $teamBScore,
        ?Team $qualifiedTeam = null,
    ): Prediction {
        return Prediction::factory()->create([
            'user_id' => $user->id,
            'match_id' => $match->id,
            'team_a_score' => $teamAScore,
            'team_b_score' => $teamBScore,
            'predicted_qualified_team_id' => $qualifiedTeam?->id,
            'status' => Prediction::STATUS_SUBMITTED,
            'points_awarded' => null,
        ]);
    }

    private function pointsFor(User $user, TournamentMatch $match): int
    {
        return (int) Prediction::query()
            ->where('user_id', $user->id)
            ->where('match_id', $match->id)
            ->value('points_awarded');
    }
}
