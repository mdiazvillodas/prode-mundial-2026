<?php

namespace App\Console\Commands;

use App\Models\Team;
use App\Models\Tournament;
use App\Models\TournamentMatch;
use App\Services\MatchPredictionSettlementService;
use Illuminate\Console\Command;

class DemoSimulateResultsCommand extends Command
{
    protected $signature = 'demo:simulate-results
        {--scenario=group-day-1 : Demo result scenario to apply}
        {--force : Run without interactive confirmation}';

    protected $description = 'Simulate deterministic demo match results for local/testing/staging QA.';

    public function handle(MatchPredictionSettlementService $settlement): int
    {
        if (! $this->environmentAllowsSimulation()) {
            $this->error('Refusing to simulate demo results outside local/testing/staging or when APP_MODE=live.');
            $this->line('Current APP_ENV: '.app()->environment());
            $this->line('Current APP_MODE: '.config('app.mode'));

            return self::FAILURE;
        }

        $scenario = (string) $this->option('scenario');

        if (! in_array($scenario, ['group-day-1', 'knockout-qa'], true)) {
            $this->error("Unknown demo result scenario [{$scenario}].");
            $this->line('Available scenarios: group-day-1, knockout-qa');

            return self::FAILURE;
        }

        if (! $this->option('force') && ! $this->confirm("Apply demo result scenario [{$scenario}]?")) {
            $this->warn('Demo result simulation cancelled.');

            return self::FAILURE;
        }

        $this->info("Applying demo result scenario [{$scenario}]...");
        $this->warn('These are deterministic QA results only, not official match results.');

        $tournament = Tournament::query()
            ->where('slug', 'fifa-world-cup-2026')
            ->first();

        if (! $tournament) {
            $this->error('Demo tournament was not found. Run `php artisan demo:reset-staging --force` first.');

            return self::FAILURE;
        }

        $results = $scenario === 'knockout-qa'
            ? $this->knockoutQaScenario()
            : $this->groupDayOneScenario();
        $missing = [];
        $updated = [];
        $settledPredictions = 0;

        foreach ($results as $result) {
            $match = $this->findDemoMatch($tournament, $result);

            if (! $match) {
                $missing[] = $result['label'];

                continue;
            }

            $winnerTeamId = null;

            if ($result['winner'] !== null) {
                $winner = Team::query()->where('short_name', $result['winner'])->first();

                if (! $winner) {
                    $missing[] = $result['label'].' winner '.$result['winner'];

                    continue;
                }

                $winnerTeamId = $winner->id;
            }

            $match->update([
                'team_a_score' => $result['team_a_score'],
                'team_b_score' => $result['team_b_score'],
                'winner_team_id' => $winnerTeamId,
                'status' => TournamentMatch::STATUS_FINISHED,
                'api_status' => $result['api_status'] ?? $match->api_status,
            ]);

            $settled = $settlement->score($match->refresh());
            $settledPredictions += $settled;

            $updated[] = [
                $result['label'],
                "{$result['team_a_score']}-{$result['team_b_score']}",
                $result['winner'] ?? 'draw',
                $settled,
            ];
        }

        if ($updated !== []) {
            $this->table(['Match', 'Result', 'Winner/qualified', 'Predictions settled'], $updated);
        }

        if ($missing !== []) {
            $this->warn('Missing demo data:');

            foreach ($missing as $item) {
                $this->line('- '.$item);
            }

            $this->line('Run `php artisan demo:reset-staging --force` before simulating results.');
        }

        $this->line('Scenario: '.$scenario);
        $this->line('Matches updated: '.count($updated));
        $this->line('Predictions settled: '.$settledPredictions);
        $this->line('Missing/skipped records: '.count($missing));

        if ($scenario === 'knockout-qa') {
            $this->line('QA users: mariano@prode.test, ana@prode.test, juan@prode.test, lucia@prode.test, diego@prode.test, sofia@prode.test');
            $this->line('QA private league: Liga Demo Palermo');
            $this->line('Run `php artisan prode:check-finished-matches` after simulation; it should report clean.');
        }

        if ($missing !== []) {
            return self::FAILURE;
        }

        $this->components->info('Demo result simulation complete.');

        return self::SUCCESS;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function groupDayOneScenario(): array
    {
        return [
            [
                'label' => 'Argentina vs United States',
                'stage' => 'group',
                'group' => 'A',
                'team_a' => 'ARG',
                'team_b' => 'USA',
                'team_a_score' => 3,
                'team_b_score' => 1,
                'winner' => 'ARG',
            ],
            [
                'label' => 'Brazil vs Spain',
                'stage' => 'group',
                'group' => 'B',
                'team_a' => 'BRA',
                'team_b' => 'ESP',
                'team_a_score' => 0,
                'team_b_score' => 2,
                'winner' => 'ESP',
            ],
            [
                'label' => 'Spain vs United States knockout',
                'stage' => 'round_of_16',
                'group' => null,
                'team_a' => 'ESP',
                'team_b' => 'USA',
                'team_a_score' => 1,
                'team_b_score' => 1,
                'winner' => 'ESP',
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function knockoutQaScenario(): array
    {
        return [
            [
                'label' => 'E20 FT: Argentina vs Brazil',
                'stage' => 'quarter_final',
                'group' => null,
                'team_a' => 'ARG',
                'team_b' => 'BRA',
                'team_a_score' => 2,
                'team_b_score' => 1,
                'winner' => 'ARG',
                'api_status' => 'FT',
            ],
            [
                'label' => 'E20 AET: France vs Uruguay',
                'stage' => 'quarter_final',
                'group' => null,
                'team_a' => 'FRA',
                'team_b' => 'URU',
                'team_a_score' => 2,
                'team_b_score' => 1,
                'winner' => 'FRA',
                'api_status' => 'AET',
            ],
            [
                'label' => 'E20 PEN team A: Germany vs Mexico',
                'stage' => 'semi_final',
                'group' => null,
                'team_a' => 'GER',
                'team_b' => 'MEX',
                'team_a_score' => 1,
                'team_b_score' => 1,
                'winner' => 'GER',
                'api_status' => 'PEN',
            ],
            [
                'label' => 'E20 PEN team B: England vs Japan',
                'stage' => 'semi_final',
                'group' => null,
                'team_a' => 'ENG',
                'team_b' => 'JPN',
                'team_a_score' => 1,
                'team_b_score' => 1,
                'winner' => 'JPN',
                'api_status' => 'PEN',
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $result
     */
    private function findDemoMatch(Tournament $tournament, array $result): ?TournamentMatch
    {
        $teamA = Team::query()->where('short_name', $result['team_a'])->first();
        $teamB = Team::query()->where('short_name', $result['team_b'])->first();

        if (! $teamA || ! $teamB) {
            return null;
        }

        return TournamentMatch::query()
            ->where('tournament_id', $tournament->id)
            ->where('stage', $result['stage'])
            ->where('group', $result['group'])
            ->where('team_a_id', $teamA->id)
            ->where('team_b_id', $teamB->id)
            ->first();
    }

    private function environmentAllowsSimulation(): bool
    {
        if (config('app.mode') === 'live') {
            return false;
        }

        return app()->environment(['local', 'testing', 'staging']);
    }
}
