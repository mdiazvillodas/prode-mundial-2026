<?php

namespace App\Services\Rankings;

use App\Models\Prediction;
use App\Models\TournamentMatch;
use App\Services\PredictionScoringService;
use Illuminate\Support\Collection;

class RecentFormService
{
    public const STATE_EXACT = 'exact';

    public const STATE_TREND = 'trend';

    public const STATE_INCORRECT = 'incorrect';

    public const STATE_NONE = 'none';

    public function __construct(
        private readonly int $limit = 5,
    ) {}

    /**
     * @template T of object
     *
     * @param  Collection<int, T>  $entries
     * @return Collection<int, T>
     */
    public function attachToEntries(Collection $entries): Collection
    {
        $matches = $this->latestComparableMatches();

        if ($entries->isEmpty() || $matches->isEmpty()) {
            return $entries;
        }

        $userIds = $entries->pluck('id')->map(fn ($id): int => (int) $id)->all();
        $matchIds = $matches->pluck('id')->all();

        $predictions = Prediction::query()
            ->whereIn('user_id', $userIds)
            ->whereIn('match_id', $matchIds)
            ->whereNotNull('points_awarded')
            ->get()
            ->keyBy(fn (Prediction $prediction): string => $prediction->user_id.'-'.$prediction->match_id);

        return $entries->map(function (object $entry) use ($matches, $predictions): object {
            $entry->recent_form = $matches
                ->map(function (TournamentMatch $match) use ($entry, $predictions): array {
                    $prediction = $predictions->get(((int) $entry->id).'-'.$match->id);

                    return [
                        'match_id' => $match->id,
                        'state' => $this->stateForPrediction($prediction),
                    ];
                })
                ->values()
                ->all();

            return $entry;
        });
    }

    /**
     * @return Collection<int, TournamentMatch>
     */
    public function latestComparableMatches(): Collection
    {
        return TournamentMatch::query()
            ->where('status', TournamentMatch::STATUS_FINISHED)
            ->whereNotNull('team_a_id')
            ->whereNotNull('team_b_id')
            ->whereNotNull('team_a_score')
            ->whereNotNull('team_b_score')
            ->whereHas('predictions', fn ($query) => $query->whereNotNull('points_awarded'))
            ->orderByDesc('starts_at')
            ->orderByDesc('id')
            ->limit($this->limit)
            ->get()
            ->sortBy('starts_at')
            ->values();
    }

    private function stateForPrediction(?Prediction $prediction): string
    {
        if (! $prediction) {
            return self::STATE_NONE;
        }

        return match ((int) $prediction->points_awarded) {
            PredictionScoringService::POINTS_EXACT_RESULT => self::STATE_EXACT,
            PredictionScoringService::POINTS_CORRECT_OUTCOME => self::STATE_TREND,
            default => self::STATE_INCORRECT,
        };
    }
}
