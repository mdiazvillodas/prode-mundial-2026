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

    private function calculateKnockoutPrediction(Prediction $prediction, TournamentMatch $tournamentMatch): int
    {
        if (
            $prediction->predicted_qualified_team_id === null
            || $tournamentMatch->winner_team_id === null
        ) {
            return self::POINTS_INCORRECT;
        }

        if ($prediction->predicted_qualified_team_id !== $tournamentMatch->winner_team_id) {
            return self::POINTS_INCORRECT;
        }

        if (
            $prediction->team_a_score === $tournamentMatch->team_a_score
            && $prediction->team_b_score === $tournamentMatch->team_b_score
        ) {
            return self::POINTS_EXACT_RESULT;
        }

        return self::POINTS_CORRECT_QUALIFIED_TEAM;
    }

    private function outcome(int $teamAScore, int $teamBScore): int
    {
        return $teamAScore <=> $teamBScore;
    }
}
