<?php

namespace App\Services;

use App\Models\Prediction;
use App\Models\TournamentMatch;

class MatchPredictionSettlementService
{
    public function __construct(
        private readonly PredictionScoringService $scoring,
    ) {}

    public function score(TournamentMatch $tournamentMatch): int
    {
        if ($tournamentMatch->team_a_score === null || $tournamentMatch->team_b_score === null) {
            return 0;
        }

        $scoredCount = 0;

        $tournamentMatch
            ->predictions()
            ->whereIn('status', [
                Prediction::STATUS_SUBMITTED,
                Prediction::STATUS_SCORED,
            ])
            ->get()
            ->each(function (Prediction $prediction) use ($tournamentMatch, &$scoredCount): void {
                $prediction->update([
                    'points_awarded' => $this->scoring->calculateFor($prediction, $tournamentMatch),
                    'status' => Prediction::STATUS_SCORED,
                ]);

                $scoredCount++;
            });

        return $scoredCount;
    }
}
