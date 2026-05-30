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
