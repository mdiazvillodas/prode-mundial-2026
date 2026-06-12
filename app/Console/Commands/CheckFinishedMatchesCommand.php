<?php

namespace App\Console\Commands;

use App\Services\Operations\FinishedMatchConsistencyChecker;
use Illuminate\Console\Command;

class CheckFinishedMatchesCommand extends Command
{
    protected $signature = 'prode:check-finished-matches';

    protected $description = 'Read-only consistency check for finished matches and prediction settlement. Does not modify data.';

    public function handle(FinishedMatchConsistencyChecker $checker): int
    {
        $this->line('Read-only finished-match consistency check. No data is modified.');

        $result = $checker->check();
        $summary = $result['summary'];

        $this->line(sprintf(
            'Summary: finished_checked=%d, api_finished_mismatch=%d, missing_scores=%d, missing_knockout_winners=%d, unscored_finished_predictions=%d, finished_with_predictions_zero_scored=%d',
            $result['finished_checked'],
            $summary['api_finished_mismatch'],
            $summary['missing_scores'],
            $summary['missing_knockout_winners'],
            $summary['unscored_finished_predictions'],
            $summary['finished_with_predictions_zero_scored'],
        ));

        if ($result['issues'] === []) {
            $this->components->info('No finished-match inconsistencies found.');

            return self::SUCCESS;
        }

        $rows = array_map(static fn (array $issue): array => [
            $issue['match_id'],
            $issue['api_fixture_id'] ?? 'n/a',
            $issue['teams'],
            $issue['api_status'] ?? 'n/a',
            $issue['local_status'] ?? 'n/a',
            $issue['score'],
            $issue['winner_team_id'] ?? 'null',
            ($issue['critical'] ? '' : '(warn) ').$issue['code'],
        ], $result['issues']);

        $this->table(
            ['Match', 'API fixture', 'Teams', 'API status', 'Local status', 'Score', 'Winner', 'Issue'],
            $rows,
        );

        foreach ($result['issues'] as $issue) {
            $this->line(sprintf('- match #%d [%s] %s', $issue['match_id'], $issue['code'], $issue['message']));
        }

        if ($result['has_critical']) {
            $this->error('Critical finished-match inconsistencies detected. Investigate before trusting settlement.');

            return self::FAILURE;
        }

        $this->warn('Only non-critical warnings detected.');

        return self::SUCCESS;
    }
}
