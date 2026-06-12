<?php

namespace App\Services;

use App\Models\Prediction;
use App\Models\TournamentMatch;

class PredictionScoringService
{
    public const POINTS_EXACT_RESULT = 6;

    public const POINTS_CORRECT_OUTCOME = 3;

    public const POINTS_CORRECT_QUALIFIED_TEAM = 3;

    public const POINTS_INCORRECT = 0;

    // Knockout scoring matrix (E20-T04).
    public const POINTS_KNOCKOUT_EXACT_AND_QUALIFIED = 8;

    public const POINTS_KNOCKOUT_EXACT_WRONG_QUALIFIED = 5;

    public const POINTS_KNOCKOUT_TREND_AND_QUALIFIED = 5;

    public const POINTS_KNOCKOUT_QUALIFIED_ONLY = 3;

    public const POINTS_KNOCKOUT_TREND_ONLY = 2;

    public function calculate(
        int $predictedTeamAScore,
        int $predictedTeamBScore,
        int $actualTeamAScore,
        int $actualTeamBScore,
    ): int {
        if (
            $predictedTeamAScore === $actualTeamAScore
            && $predictedTeamBScore === $actualTeamBScore
        ) {
            return self::POINTS_EXACT_RESULT;
        }

        if (
            $this->outcome($predictedTeamAScore, $predictedTeamBScore)
            === $this->outcome($actualTeamAScore, $actualTeamBScore)
        ) {
            return self::POINTS_CORRECT_OUTCOME;
        }

        return self::POINTS_INCORRECT;
    }

    public function calculateFor(Prediction $prediction, TournamentMatch $tournamentMatch): int
    {
        if ($tournamentMatch->requiresQualifiedTeamPrediction()) {
            return $this->calculateKnockoutPrediction($prediction, $tournamentMatch);
        }

        return $this->calculate(
            $prediction->team_a_score,
            $prediction->team_b_score,
            $tournamentMatch->team_a_score,
            $tournamentMatch->team_b_score,
        );
    }

    /**
     * Score a knockout prediction against the expanded matrix (E20-T04).
     *
     * | Exact score | Match trend | Qualified team | Points |
     * | ----------- | ----------- | -------------- | -----: |
     * | yes         | (implied)   | yes            | 8      |
     * | yes         | (implied)   | no             | 5      |
     * | no          | yes         | yes            | 5      |
     * | no          | no          | yes            | 3      |
     * | no          | yes         | no             | 2      |
     * | no          | no          | no             | 0      |
     *
     * Exact score compares the final played result before penalties. The
     * qualified team is the prediction's `predicted_qualified_team_id` compared
     * against the match `winner_team_id`. When either is null the qualified team
     * is treated as not matched (no guessing), so a knockout match without a
     * resolved winner can still earn exact-score or trend points but never the
     * qualified-team bonus.
     */
    private function calculateKnockoutPrediction(Prediction $prediction, TournamentMatch $tournamentMatch): int
    {
        $exactScore = $prediction->team_a_score === $tournamentMatch->team_a_score
            && $prediction->team_b_score === $tournamentMatch->team_b_score;

        $correctTrend = $this->outcome($prediction->team_a_score, $prediction->team_b_score)
            === $this->outcome($tournamentMatch->team_a_score, $tournamentMatch->team_b_score);

        $correctQualifiedTeam = $prediction->predicted_qualified_team_id !== null
            && $tournamentMatch->winner_team_id !== null
            && $prediction->predicted_qualified_team_id === $tournamentMatch->winner_team_id;

        if ($exactScore) {
            return $correctQualifiedTeam
                ? self::POINTS_KNOCKOUT_EXACT_AND_QUALIFIED
                : self::POINTS_KNOCKOUT_EXACT_WRONG_QUALIFIED;
        }

        if ($correctTrend && $correctQualifiedTeam) {
            return self::POINTS_KNOCKOUT_TREND_AND_QUALIFIED;
        }

        if ($correctQualifiedTeam) {
            return self::POINTS_KNOCKOUT_QUALIFIED_ONLY;
        }

        if ($correctTrend) {
            return self::POINTS_KNOCKOUT_TREND_ONLY;
        }

        return self::POINTS_INCORRECT;
    }

    private function outcome(int $teamAScore, int $teamBScore): int
    {
        return $teamAScore <=> $teamBScore;
    }
}
