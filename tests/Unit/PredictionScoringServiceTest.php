<?php

namespace Tests\Unit;

use App\Models\Prediction;
use App\Models\TournamentMatch;
use App\Services\PredictionScoringService;
use PHPUnit\Framework\TestCase;

class PredictionScoringServiceTest extends TestCase
{
    private PredictionScoringService $scoring;

    protected function setUp(): void
    {
        parent::setUp();

        $this->scoring = new PredictionScoringService;
    }

    public function test_exact_result_returns_six_points(): void
    {
        $this->assertSame(6, $this->scoring->calculate(2, 1, 2, 1));
    }

    public function test_correct_team_a_win_without_exact_result_returns_three_points(): void
    {
        $this->assertSame(3, $this->scoring->calculate(2, 1, 1, 0));
    }

    public function test_correct_team_b_win_without_exact_result_returns_three_points(): void
    {
        $this->assertSame(3, $this->scoring->calculate(0, 1, 1, 2));
    }

    public function test_correct_draw_without_exact_result_returns_three_points(): void
    {
        $this->assertSame(3, $this->scoring->calculate(1, 1, 0, 0));
    }

    public function test_incorrect_prediction_returns_zero_points(): void
    {
        $this->assertSame(0, $this->scoring->calculate(2, 1, 1, 1));
        $this->assertSame(0, $this->scoring->calculate(0, 1, 2, 1));
    }

    public function test_high_but_valid_scores_are_scored(): void
    {
        $this->assertSame(6, $this->scoring->calculate(99, 98, 99, 98));
        $this->assertSame(3, $this->scoring->calculate(98, 99, 1, 99));
    }

    public function test_knockout_exact_score_and_correct_qualified_team_returns_eight_points(): void
    {
        // Prediction 1-1 + team A qualifies, actual 1-1 + team A qualifies.
        $points = $this->scoreKnockout(predA: 1, predB: 1, qualified: 1, actualA: 1, actualB: 1, winner: 1);

        $this->assertSame(8, $points);
    }

    public function test_knockout_exact_score_with_wrong_qualified_team_returns_five_points(): void
    {
        // Prediction 1-1 + team A qualifies, actual 1-1 + team B qualifies.
        $points = $this->scoreKnockout(predA: 1, predB: 1, qualified: 1, actualA: 1, actualB: 1, winner: 2);

        $this->assertSame(5, $points);
    }

    public function test_knockout_correct_trend_and_qualified_team_without_exact_returns_five_points(): void
    {
        // Prediction 0-0 + team A qualifies, actual 1-1 + team A qualifies.
        $points = $this->scoreKnockout(predA: 0, predB: 0, qualified: 1, actualA: 1, actualB: 1, winner: 1);

        $this->assertSame(5, $points);
    }

    public function test_knockout_correct_qualified_team_only_returns_three_points(): void
    {
        // Prediction 0-0 (draw) + team A qualifies, actual team A wins 2-1.
        $points = $this->scoreKnockout(predA: 0, predB: 0, qualified: 1, actualA: 2, actualB: 1, winner: 1);

        $this->assertSame(3, $points);
    }

    public function test_knockout_correct_trend_only_returns_two_points(): void
    {
        // Prediction 0-0 + team A qualifies, actual 1-1 + team B qualifies.
        $points = $this->scoreKnockout(predA: 0, predB: 0, qualified: 1, actualA: 1, actualB: 1, winner: 2);

        $this->assertSame(2, $points);
    }

    public function test_knockout_correct_qualified_team_with_wrong_trend_returns_three_points(): void
    {
        // Prediction team A wins 2-1 + team A qualifies, actual 1-1 + team A qualifies (drawn, decided on penalties).
        $points = $this->scoreKnockout(predA: 2, predB: 1, qualified: 1, actualA: 1, actualB: 1, winner: 1);

        $this->assertSame(3, $points);
    }

    public function test_knockout_correct_trend_and_qualified_team_with_wrong_score_returns_five_points(): void
    {
        // Prediction team A wins 1-0, actual team A wins 2-1.
        $points = $this->scoreKnockout(predA: 1, predB: 0, qualified: 1, actualA: 2, actualB: 1, winner: 1);

        $this->assertSame(5, $points);
    }

    public function test_knockout_fully_incorrect_returns_zero_points(): void
    {
        // Prediction team B wins 0-1 + team B qualifies, actual team A wins 2-0 + team A qualifies.
        $points = $this->scoreKnockout(predA: 0, predB: 1, qualified: 2, actualA: 2, actualB: 0, winner: 1);

        $this->assertSame(0, $points);
    }

    public function test_knockout_without_predicted_qualified_team_does_not_award_qualified_points(): void
    {
        // Exact score but no qualified team predicted: scores at the exact-without-qualified tier (5), not 8.
        $exact = $this->scoreKnockout(predA: 1, predB: 1, qualified: null, actualA: 1, actualB: 1, winner: 1);
        $this->assertSame(5, $exact);

        // Correct trend but no qualified team: trend-only tier (2).
        $trend = $this->scoreKnockout(predA: 0, predB: 0, qualified: null, actualA: 1, actualB: 1, winner: 1);
        $this->assertSame(2, $trend);
    }

    public function test_knockout_without_resolved_winner_does_not_guess_qualified_team(): void
    {
        // Match finished with no winner_team_id (e.g. tied knockout, API flags absent).
        // Exact score still earns the exact-without-qualified tier (5), never the 8 bonus.
        $exact = $this->scoreKnockout(predA: 1, predB: 1, qualified: 1, actualA: 1, actualB: 1, winner: null);
        $this->assertSame(5, $exact);

        // Correct trend without a resolvable winner earns trend-only (2).
        $trend = $this->scoreKnockout(predA: 0, predB: 0, qualified: 1, actualA: 1, actualB: 1, winner: null);
        $this->assertSame(2, $trend);
    }

    public function test_group_stage_scoring_is_unchanged_via_calculate_for(): void
    {
        $exact = $this->scoreGroup(predA: 2, predB: 1, actualA: 2, actualB: 1);
        $this->assertSame(6, $exact);

        $trend = $this->scoreGroup(predA: 1, predB: 0, actualA: 3, actualB: 1);
        $this->assertSame(3, $trend);

        $incorrect = $this->scoreGroup(predA: 0, predB: 2, actualA: 2, actualB: 1);
        $this->assertSame(0, $incorrect);
    }

    private function scoreKnockout(
        int $predA,
        int $predB,
        ?int $qualified,
        int $actualA,
        int $actualB,
        ?int $winner,
    ): int {
        $prediction = new Prediction([
            'team_a_score' => $predA,
            'team_b_score' => $predB,
            'predicted_qualified_team_id' => $qualified,
        ]);

        $tournamentMatch = new TournamentMatch([
            'team_a_score' => $actualA,
            'team_b_score' => $actualB,
            'winner_team_id' => $winner,
            'stage' => 'quarter_final',
        ]);

        return $this->scoring->calculateFor($prediction, $tournamentMatch);
    }

    private function scoreGroup(int $predA, int $predB, int $actualA, int $actualB): int
    {
        $prediction = new Prediction([
            'team_a_score' => $predA,
            'team_b_score' => $predB,
        ]);

        $tournamentMatch = new TournamentMatch([
            'team_a_score' => $actualA,
            'team_b_score' => $actualB,
            'stage' => 'group',
        ]);

        return $this->scoring->calculateFor($prediction, $tournamentMatch);
    }

    public function test_prediction_and_tournament_match_can_be_scored_together(): void
    {
        $prediction = new Prediction([
            'team_a_score' => 2,
            'team_b_score' => 1,
        ]);

        $tournamentMatch = new TournamentMatch([
            'team_a_score' => 3,
            'team_b_score' => 2,
        ]);

        $this->assertSame(3, $this->scoring->calculateFor($prediction, $tournamentMatch));
    }
}
