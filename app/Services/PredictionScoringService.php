<?php

namespace App\Services;

use App\Models\Prediction;
use App\Models\TournamentMatch;

class PredictionScoringService
{
    public const POINTS_EXACT_RESULT = 6;

    public const POINTS_CORRECT_OUTCOME = 3;

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
        return $this->calculate(
            $prediction->team_a_score,
            $prediction->team_b_score,
            $tournamentMatch->team_a_score,
            $tournamentMatch->team_b_score,
        );
    }

    private function outcome(int $teamAScore, int $teamBScore): int
    {
        return $teamAScore <=> $teamBScore;
    }
}
