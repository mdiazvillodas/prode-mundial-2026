<?php

namespace App\Services\Operations;

use App\Models\Prediction;
use App\Models\TournamentMatch;

/**
 * Read-only consistency checker for finished matches and prediction settlement.
 *
 * This service never mutates data. It inspects existing TournamentMatch and
 * Prediction records and reports actionable inconsistencies so operators can
 * detect settlement problems (such as a finished fixture that never resolved a
 * winner or never scored its predictions) without touching the database.
 */
class FinishedMatchConsistencyChecker
{
    /**
     * @var array<int, string>
     */
    public const FINISHED_API_STATUSES = ['FT', 'AET', 'PEN'];

    /**
     * @var array<int, string>
     */
    public const LIVE_API_STATUSES = ['1H', '2H', 'HT', 'ET', 'BT', 'P', 'SUSP', 'INT', 'LIVE'];

    /**
     * @return array{
     *     finished_checked: int,
     *     summary: array<string, int>,
     *     issues: array<int, array<string, mixed>>,
     *     has_critical: bool
     * }
     */
    public function check(): array
    {
        $matches = TournamentMatch::query()
            ->with(['teamA:id,name,short_name', 'teamB:id,name,short_name'])
            ->withCount([
                'predictions',
                'predictions as scored_predictions_count' => fn ($query) => $query
                    ->where('status', Prediction::STATUS_SCORED)
                    ->whereNotNull('points_awarded'),
                'predictions as unscored_predictions_count' => fn ($query) => $query
                    ->where(fn ($inner) => $inner
                        ->where('status', '!=', Prediction::STATUS_SCORED)
                        ->orWhereNull('points_awarded')),
            ])
            ->where(function ($query): void {
                $query->where('status', TournamentMatch::STATUS_FINISHED)
                    ->orWhereIn('api_status', self::FINISHED_API_STATUSES)
                    ->orWhereIn('api_status', self::LIVE_API_STATUSES);
            })
            ->orderBy('id')
            ->get();

        $issues = [];
        $summary = [
            'api_finished_mismatch' => 0,
            'missing_scores' => 0,
            'missing_knockout_winners' => 0,
            'unscored_finished_predictions' => 0,
            'finished_with_predictions_zero_scored' => 0,
        ];

        $finishedChecked = 0;

        foreach ($matches as $match) {
            $isLocalFinished = $match->status === TournamentMatch::STATUS_FINISHED;
            $apiFinished = in_array($match->api_status, self::FINISHED_API_STATUSES, true);
            $apiLive = in_array($match->api_status, self::LIVE_API_STATUSES, true);
            $hasScores = $match->team_a_score !== null && $match->team_b_score !== null;

            if ($isLocalFinished) {
                $finishedChecked++;
            }

            // 1. API reports finished but the local match is not finished.
            if ($apiFinished && ! $isLocalFinished) {
                $summary['api_finished_mismatch']++;
                $issues[] = $this->issue($match, 'api_finished_local_not_finished',
                    'API reports finished but local match is not finished.');
            }

            // Optional: API reports live/in-progress but the local match is finished.
            if ($apiLive && $isLocalFinished) {
                $issues[] = $this->issue($match, 'api_live_local_finished',
                    'API reports live/in-progress but local match is finished.', critical: false);
            }

            if (! $isLocalFinished) {
                continue;
            }

            // 2. Finished match is missing a score.
            if (! $hasScores) {
                $summary['missing_scores']++;
                $issues[] = $this->issue($match, 'finished_missing_score',
                    'Finished match is missing a score.');
            }

            // 3. Knockout finished match has no winner.
            if ($match->isKnockout() && $match->winner_team_id === null) {
                $summary['missing_knockout_winners']++;
                $issues[] = $this->issue($match, 'knockout_finished_missing_winner',
                    'Knockout finished match has no winner_team_id.');
            }

            // The remaining checks require a final score to be meaningful.
            if (! $hasScores) {
                continue;
            }

            $isDraw = $match->team_a_score === $match->team_b_score;
            $expectedWinner = $isDraw
                ? null
                : ($match->team_a_score > $match->team_b_score ? $match->team_a_id : $match->team_b_id);

            // Optional: group-stage draw should not carry a winner.
            if (! $match->isKnockout() && $isDraw && $match->winner_team_id !== null) {
                $issues[] = $this->issue($match, 'group_draw_has_winner',
                    'Group-stage draw has a winner_team_id set.');
            }

            // Optional: a non-draw winner must match the score winner.
            if (! $isDraw && $match->winner_team_id !== null && $match->winner_team_id !== $expectedWinner) {
                $issues[] = $this->issue($match, 'non_draw_winner_mismatch',
                    'winner_team_id does not match the score winner.');
            }

            // Optional: a knockout winner must be one of the two teams.
            if ($match->isKnockout()
                && $match->winner_team_id !== null
                && ! in_array($match->winner_team_id, [$match->team_a_id, $match->team_b_id], true)) {
                $issues[] = $this->issue($match, 'knockout_winner_not_in_teams',
                    'Knockout winner_team_id does not belong to either team.');
            }

            // 4 & 5. A finished, scored match should have all its predictions settled.
            if ($match->predictions_count > 0) {
                if ($match->unscored_predictions_count > 0) {
                    $summary['unscored_finished_predictions'] += $match->unscored_predictions_count;
                    $issues[] = $this->issue($match, 'finished_unscored_predictions',
                        "Finished match has {$match->unscored_predictions_count} unscored prediction(s).");
                }

                if ($match->scored_predictions_count === 0) {
                    $summary['finished_with_predictions_zero_scored']++;
                    $issues[] = $this->issue($match, 'finished_predictions_none_scored',
                        'Finished match has predictions but none are scored.');
                }
            }
        }

        $hasCritical = false;

        foreach ($issues as $issue) {
            if ($issue['critical']) {
                $hasCritical = true;

                break;
            }
        }

        return [
            'finished_checked' => $finishedChecked,
            'summary' => $summary,
            'issues' => $issues,
            'has_critical' => $hasCritical,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function issue(TournamentMatch $match, string $code, string $message, bool $critical = true): array
    {
        return [
            'match_id' => $match->id,
            'api_fixture_id' => $match->api_fixture_id,
            'teams' => $this->teamsLabel($match),
            'api_status' => $match->api_status,
            'local_status' => $match->status,
            'score' => $this->scoreLabel($match),
            'winner_team_id' => $match->winner_team_id,
            'code' => $code,
            'message' => $message,
            'critical' => $critical,
        ];
    }

    private function teamsLabel(TournamentMatch $match): string
    {
        $a = ($match->teamA?->short_name ?: $match->teamA?->name) ?: ($match->team_a_id ? "#{$match->team_a_id}" : 'TBD');
        $b = ($match->teamB?->short_name ?: $match->teamB?->name) ?: ($match->team_b_id ? "#{$match->team_b_id}" : 'TBD');

        return "{$a} vs {$b}";
    }

    private function scoreLabel(TournamentMatch $match): string
    {
        if ($match->team_a_score === null || $match->team_b_score === null) {
            return '—';
        }

        return "{$match->team_a_score} - {$match->team_b_score}";
    }
}
