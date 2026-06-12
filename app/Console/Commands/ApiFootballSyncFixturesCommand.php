<?php

namespace App\Console\Commands;

use App\Models\Team;
use App\Models\Tournament;
use App\Models\TournamentMatch;
use App\Services\MatchPredictionSettlementService;
use App\Support\ApiFootballProductionSyncGuard;
use App\Support\ApiSyncLogWriter;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ApiFootballSyncFixturesCommand extends Command
{
    protected $signature = 'api-football:sync-fixtures
        {--league= : Override the configured API-Football league id}
        {--season= : Override the configured API-Football season}
        {--force : Run without interactive confirmation}
        {--dry-run : Fetch and parse fixtures without writing to the database}
        {--from-snapshot= : Load a saved JSON snapshot instead of calling API-Football}';

    protected $description = 'Sync World Cup fixtures from API-Football into the local matches table.';

    private const PROVIDER = 'api-football';

    /**
     * @var array<int, string>
     */
    private const LIVE_API_STATUSES = [
        '1H',
        '2H',
        'HT',
        'ET',
        'BT',
        'P',
        'SUSP',
        'INT',
        'LIVE',
    ];

    /**
     * @var array<int, string>
     */
    private array $apiStatusSamples = [];

    /**
     * Non-empty API round labels that could not be mapped to a known stage.
     *
     * @var array<int, string>
     */
    private array $unknownRounds = [];

    /**
     * @var array<string, int|null>
     */
    private array $lastResponseMetrics = [];

    public function handle(): int
    {
        $startedAt = now();
        $startedNs = hrtime(true);

        if (! ApiFootballProductionSyncGuard::allowsSync()) {
            $this->error('Refusing to sync API-Football fixtures in production or live mode.');
            $this->line('Current APP_ENV: '.app()->environment());
            $this->line('Current APP_MODE: '.config('app.mode'));

            return $this->finishSyncLog(self::FAILURE, 'failed', $startedAt, $startedNs, [
                'error_message' => 'Refusing to sync API-Football fixtures in production or live mode.',
            ]);
        }

        ApiFootballProductionSyncGuard::warnIfAllowed($this);

        $leagueId = $this->leagueId();
        $season = $this->season();
        $fromSnapshot = (string) ($this->option('from-snapshot') ?? '');
        $usesSnapshot = $fromSnapshot !== '';

        $this->line("API-Football fixture sync: league={$leagueId}, season={$season}");
        $this->line($usesSnapshot ? 'Planned API requests: 0 (snapshot mode)' : 'Planned API requests: 1');

        if (! $usesSnapshot) {
            $apiKey = (string) config('services.api_football.key');

            if ($apiKey === '') {
                $this->error('Missing API_FOOTBALL_KEY. Configure it in the environment before calling API-Football.');

                return $this->finishSyncLog(self::FAILURE, 'failed', $startedAt, $startedNs, [
                    'error_message' => 'Missing API_FOOTBALL_KEY.',
                    'metadata' => $this->metadata($leagueId, $season, $usesSnapshot),
                ]);
            }
        }

        if (! $this->option('force') && ! $this->confirm($this->option('dry-run')
            ? 'Fetch and parse API-Football fixtures without writing to the database?'
            : 'Sync API-Football fixtures into the local database?')) {
            $this->warn('API-Football fixture sync cancelled.');

            return $this->finishSyncLog(self::FAILURE, 'skipped', $startedAt, $startedNs, [
                'error_message' => 'API-Football fixture sync cancelled.',
                'metadata' => $this->metadata($leagueId, $season, $usesSnapshot),
            ]);
        }

        $tournament = Tournament::query()
            ->where('slug', 'fifa-world-cup-2026')
            ->first();

        if (! $tournament) {
            $this->error('Tournament fifa-world-cup-2026 was not found. Run the base seeders before syncing fixtures.');

            return $this->finishSyncLog(self::FAILURE, 'failed', $startedAt, $startedNs, [
                'error_message' => 'Tournament fifa-world-cup-2026 was not found.',
                'metadata' => $this->metadata($leagueId, $season, $usesSnapshot),
            ]);
        }

        $payload = $usesSnapshot
            ? $this->payloadFromSnapshot($fromSnapshot)
            : $this->payloadFromApi($leagueId, $season, (string) config('services.api_football.key'));

        if ($payload === null) {
            return $this->finishSyncLog(self::FAILURE, 'failed', $startedAt, $startedNs, [
                'error_message' => 'Could not load API-Football fixtures payload.',
                'metadata' => $this->metadata($leagueId, $season, $usesSnapshot),
            ]);
        }

        if ($this->hasApiErrors($payload)) {
            $this->printApiErrors($payload);
            $this->warn('No fixtures were synced.');

            return $this->finishSyncLog(self::FAILURE, 'failed', $startedAt, $startedNs, [
                'items_received' => $this->payloadResultCount($payload),
                'error_message' => $this->apiErrorMessage($payload),
                'metadata' => $this->metadata($leagueId, $season, $usesSnapshot),
            ]);
        }

        $items = Arr::get($payload, 'response', []);

        if (! is_array($items)) {
            $this->error('Unexpected API-Football response shape: missing response array.');

            return $this->finishSyncLog(self::FAILURE, 'failed', $startedAt, $startedNs, [
                'error_message' => 'Unexpected API-Football response shape: missing response array.',
                'metadata' => $this->metadata($leagueId, $season, $usesSnapshot),
            ]);
        }

        if ($items === []) {
            $this->warn('API-Football returned an empty fixtures response. No fixtures were synced.');

            return $this->finishSyncLog(self::FAILURE, 'failed', $startedAt, $startedNs, [
                'items_received' => 0,
                'error_message' => 'API-Football returned an empty fixtures response.',
                'metadata' => $this->metadata($leagueId, $season, $usesSnapshot),
            ]);
        }

        $rows = [];
        $counts = [
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'missing_teams' => 0,
        ];

        foreach ($items as $item) {
            $result = $this->syncFixture($item, $tournament, (bool) $this->option('dry-run'));
            $counts[$result['action']]++;

            if (($result['missing_team'] ?? false) === true) {
                $counts['missing_teams']++;
            }

            $rows[] = [
                $result['action'],
                $result['api_fixture_id'] ?? 'n/a',
                $result['api_status'] ?? 'n/a',
                $result['home'] ?? 'n/a',
                $result['away'] ?? 'n/a',
                $result['reason'],
            ];
        }

        $this->table(['Action', 'API fixture id', 'API status', 'Home', 'Away', 'Reason'], array_slice($rows, 0, 10));

        if (count($rows) > 10) {
            $this->line('Showing first 10 fixture actions of '.count($rows).'.');
        }

        if ($this->apiStatusSamples !== []) {
            $this->line('API status samples: '.implode(', ', array_unique($this->apiStatusSamples)));
        }

        if ($this->unknownRounds !== []) {
            $this->warn('Unmapped API round labels: '.implode(', ', $this->unknownRounds));
            $this->line('Stage left null and raw round preserved. Review these labels for knockout mapping.');
            Log::warning('API-Football fixture sync found unmapped round labels.', [
                'provider' => self::PROVIDER,
                'unknown_rounds' => $this->unknownRounds,
            ]);
        }

        $this->line(sprintf(
            'Summary: created=%d, updated=%d, skipped=%d, missing_teams=%d',
            $counts['created'],
            $counts['updated'],
            $counts['skipped'],
            $counts['missing_teams'],
        ));

        if ($counts['missing_teams'] > 0) {
            $this->warn('Team not found. Run api-football:sync-teams first.');
        }

        if ($this->option('dry-run')) {
            $this->warn('Dry run complete. No database changes were written.');
        } else {
            $this->components->info('API-Football fixture sync complete.');
        }

        return $this->finishSyncLog(self::SUCCESS, $this->option('dry-run') ? 'skipped' : 'success', $startedAt, $startedNs, [
            'items_received' => count($items),
            'items_created' => $counts['created'],
            'items_updated' => $counts['updated'],
            'items_skipped' => $counts['skipped'],
            'metadata' => $this->metadata($leagueId, $season, $usesSnapshot) + [
                'dry_run' => (bool) $this->option('dry-run'),
                'missing_teams' => $counts['missing_teams'],
                'api_status_samples' => array_values(array_unique($this->apiStatusSamples)),
                'unknown_rounds' => $this->unknownRounds,
            ],
        ]);
    }

    private function payloadFromApi(int $leagueId, int $season, string $apiKey): ?array
    {
        $url = rtrim((string) config('services.api_football.base_url'), '/').'/fixtures';
        $this->line('Request: '.$url);

        try {
            $response = Http::withHeaders([
                'x-apisports-key' => $apiKey,
            ])
                ->acceptJson()
                ->timeout(15)
                ->get($url, [
                    'league' => $leagueId,
                    'season' => $season,
                ]);
        } catch (ConnectionException $exception) {
            $this->error('Network error calling API-Football: '.$exception->getMessage());

            return null;
        }

        $this->lastResponseMetrics = ApiSyncLogWriter::responseMetrics($response);
        $this->summarizeResponse($response);

        if (! $response->successful()) {
            $this->printErrorHint($response);

            return null;
        }

        $payload = $response->json();

        if (! is_array($payload)) {
            $this->error('Unexpected API-Football response shape: response body was not JSON object/array.');

            return null;
        }

        return $payload;
    }

    private function payloadFromSnapshot(string $path): ?array
    {
        $contents = null;

        if (is_file($path)) {
            $contents = file_get_contents($path);
        } elseif (Storage::disk('local')->exists($path)) {
            $contents = Storage::disk('local')->get($path);
        }

        if ($contents === false || $contents === null) {
            $this->error("Snapshot not found: {$path}");

            return null;
        }

        $payload = json_decode($contents, true);

        if (! is_array($payload)) {
            $this->error('Snapshot is not valid JSON object/array.');

            return null;
        }

        $this->line('Loaded snapshot: '.$path);

        return $payload;
    }

    /**
     * @return array{action: string, api_fixture_id?: int|string|null, api_status?: string|null, home?: string|null, away?: string|null, reason: string, missing_team?: bool}
     */
    private function syncFixture(mixed $item, Tournament $tournament, bool $dryRun): array
    {
        if (! is_array($item)) {
            return [
                'action' => 'skipped',
                'reason' => 'Fixture item is not an object.',
            ];
        }

        $fixtureData = Arr::get($item, 'fixture', []);
        $leagueData = Arr::get($item, 'league', []);

        if (! is_array($fixtureData) || ! is_array($leagueData)) {
            return [
                'action' => 'skipped',
                'reason' => 'Missing fixture or league object.',
            ];
        }

        $apiFixtureId = Arr::get($fixtureData, 'id');
        $homeApiTeamId = Arr::get($item, 'teams.home.id');
        $awayApiTeamId = Arr::get($item, 'teams.away.id');
        $apiStatus = $this->nullableString(Arr::get($fixtureData, 'status.short'));
        $round = $this->nullableString(Arr::get($leagueData, 'round'));
        $homeName = $this->nullableString(Arr::get($item, 'teams.home.name'));
        $awayName = $this->nullableString(Arr::get($item, 'teams.away.name'));

        if ($apiStatus !== null) {
            $this->apiStatusSamples[] = $apiStatus;
        }

        if (! is_numeric($apiFixtureId) || ! is_numeric($homeApiTeamId) || ! is_numeric($awayApiTeamId)) {
            return [
                'action' => 'skipped',
                'api_fixture_id' => $apiFixtureId,
                'api_status' => $apiStatus,
                'home' => $homeName,
                'away' => $awayName,
                'reason' => 'Missing required fixture.id or team ids.',
            ];
        }

        $apiFixtureId = (int) $apiFixtureId;
        $homeApiTeamId = (int) $homeApiTeamId;
        $awayApiTeamId = (int) $awayApiTeamId;

        $homeTeam = $this->findApiTeam($homeApiTeamId);
        $awayTeam = $this->findApiTeam($awayApiTeamId);

        if (! $homeTeam || ! $awayTeam) {
            return [
                'action' => 'skipped',
                'api_fixture_id' => $apiFixtureId,
                'api_status' => $apiStatus,
                'home' => $homeName,
                'away' => $awayName,
                'reason' => 'Team not found. Run api-football:sync-teams first.',
                'missing_team' => true,
            ];
        }

        $existing = TournamentMatch::query()
            ->where('api_provider', self::PROVIDER)
            ->where('api_fixture_id', $apiFixtureId)
            ->first();

        $startsAt = $this->parseDateTime(Arr::get($fixtureData, 'date'));
        $isFinished = $this->isFinishedApiStatus($apiStatus);
        $isLive = $this->isLiveApiStatus($apiStatus);

        $values = [
            'tournament_id' => $tournament->id,
            'team_a_id' => $homeTeam->id,
            'team_b_id' => $awayTeam->id,
            'starts_at' => $startsAt ?? $existing?->starts_at,
            'prediction_closes_at' => $startsAt?->copy()->subMinutes(5) ?? $existing?->prediction_closes_at,
            'api_provider' => self::PROVIDER,
            'api_fixture_id' => $apiFixtureId,
            'api_status' => $apiStatus,
            'round' => $round,
            'venue_name' => $this->nullableString(Arr::get($fixtureData, 'venue.name')),
            'venue_city' => $this->nullableString(Arr::get($fixtureData, 'venue.city')),
            'last_synced_at' => now(),
        ];

        $stage = $this->stageFromRound($round);

        if (! $existing) {
            $values['stage'] = $stage;
            $values['status'] = $isFinished
                ? TournamentMatch::STATUS_FINISHED
                : TournamentMatch::STATUS_SCHEDULED;
        } else {
            $values['stage'] = $existing->stage ?? $stage;
            $values['status'] = $isFinished
                ? TournamentMatch::STATUS_FINISHED
                : $existing->status;
        }

        if ($isFinished || $isLive) {
            $values['team_a_score'] = $this->nullableInteger(Arr::get($item, 'goals.home'));
            $values['team_b_score'] = $this->nullableInteger(Arr::get($item, 'goals.away'));

            if ($isFinished && $values['team_a_score'] !== null && $values['team_b_score'] !== null) {
                $values['winner_team_id'] = $this->resolveWinnerTeamId(
                    $values['team_a_score'],
                    $values['team_b_score'],
                    in_array($values['stage'], TournamentMatch::KNOCKOUT_STAGES, true),
                    Arr::get($item, 'teams.home.winner'),
                    Arr::get($item, 'teams.away.winner'),
                    $homeTeam->id,
                    $awayTeam->id,
                );
            }
        } else {
            $values['team_a_score'] = null;
            $values['team_b_score'] = null;
        }

        if (! $dryRun) {
            if ($existing) {
                $existing->forceFill($values)->save();
                $match = $existing->fresh();
            } else {
                $match = TournamentMatch::query()->create($values);
            }

            if ($isFinished && $match->team_a_score !== null && $match->team_b_score !== null) {
                app(MatchPredictionSettlementService::class)->score($match);
            }
        }

        $action = $existing ? 'updated' : 'created';

        return [
            'action' => $action,
            'api_fixture_id' => $apiFixtureId,
            'api_status' => $apiStatus,
            'home' => $homeTeam->short_name ?? $homeTeam->name,
            'away' => $awayTeam->short_name ?? $awayTeam->name,
            'reason' => $dryRun
                ? ($existing ? 'Would update existing fixture.' : 'Would create new fixture.')
                : ($existing ? 'Updated existing fixture.' : 'Created new fixture.'),
        ];
    }

    private function findApiTeam(int $apiTeamId): ?Team
    {
        return Team::query()
            ->where('api_provider', self::PROVIDER)
            ->where('api_team_id', $apiTeamId)
            ->first();
    }

    private function parseDateTime(mixed $value): ?Carbon
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        try {
            return Carbon::parse((string) $value);
        } catch (\Throwable) {
            return null;
        }
    }

    private function isFinishedApiStatus(?string $status): bool
    {
        return in_array($status, ['FT', 'AET', 'PEN'], true);
    }

    private function isLiveApiStatus(?string $status): bool
    {
        return in_array($status, self::LIVE_API_STATUSES, true);
    }

    /**
     * Resolve the winning team for a finished fixture.
     *
     * The local `team_a_score` / `team_b_score` hold the final played result
     * before penalties (API home maps to team A, API away maps to team B):
     *
     * - A non-draw played score (FT or AET) yields the higher-scoring team.
     * - A tied played score has no winner in the group stage, so it stays null.
     * - A tied played score in a knockout match (decided on penalties, status
     *   PEN) is resolved from the API-Football `teams.home.winner` /
     *   `teams.away.winner` flags. When those flags are absent the winner stays
     *   null rather than guessing.
     *
     * @param  mixed  $homeWinnerFlag  API `teams.home.winner` (true/false/null)
     * @param  mixed  $awayWinnerFlag  API `teams.away.winner` (true/false/null)
     */
    private function resolveWinnerTeamId(
        int $teamAScore,
        int $teamBScore,
        bool $isKnockout,
        mixed $homeWinnerFlag,
        mixed $awayWinnerFlag,
        int $teamAId,
        int $teamBId,
    ): ?int {
        if ($teamAScore > $teamBScore) {
            return $teamAId;
        }

        if ($teamBScore > $teamAScore) {
            return $teamBId;
        }

        if (! $isKnockout) {
            return null;
        }

        if ($homeWinnerFlag === true) {
            return $teamAId;
        }

        if ($awayWinnerFlag === true) {
            return $teamBId;
        }

        return null;
    }

    /**
     * Map the API round label to a local stage, recording any non-empty label
     * that could not be mapped so it can be surfaced for admin review.
     */
    private function stageFromRound(?string $round): ?string
    {
        $stage = TournamentMatch::stageFromApiRound($round);

        if ($stage === null && $round !== null && ! in_array($round, $this->unknownRounds, true)) {
            $this->unknownRounds[] = $round;
        }

        return $stage;
    }

    private function summarizeResponse(Response $response): void
    {
        $this->line('Status: '.$response->status());

        foreach ([
            'X-RateLimit-Requests-Remaining',
            'X-RateLimit-Remaining',
            'X-RateLimit-Limit',
        ] as $header) {
            $value = $response->header($header);

            if ($value !== null) {
                $this->line($header.': '.$value);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function hasApiErrors(array $payload): bool
    {
        $errors = Arr::get($payload, 'errors', []);

        if (is_array($errors)) {
            return $errors !== [];
        }

        return filled($errors);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function printApiErrors(array $payload): void
    {
        $this->error('API-Football returned logical errors:');

        foreach ($this->flattenApiErrors(Arr::get($payload, 'errors', [])) as $key => $message) {
            $this->line("- {$key}: {$message}");
        }
    }

    /**
     * @return array<string, string>
     */
    private function flattenApiErrors(mixed $errors, string $prefix = 'errors'): array
    {
        if (! is_array($errors)) {
            return [$prefix => (string) $errors];
        }

        $flattened = [];

        foreach ($errors as $key => $value) {
            $path = $prefix.'.'.$key;

            if (is_array($value)) {
                $flattened += $this->flattenApiErrors($value, $path);

                continue;
            }

            $flattened[$path] = (string) $value;
        }

        return $flattened;
    }

    private function printErrorHint(Response $response): void
    {
        match ($response->status()) {
            401, 403 => $this->error('Unauthorized. Check API_FOOTBALL_KEY and API-Sports plan access.'),
            429 => $this->error('Rate limit reached. Stop running fixture sync and wait for quota reset.'),
            default => $this->error('API-Football returned an HTTP error. No fixtures were synced.'),
        };
    }

    private function leagueId(): int
    {
        $league = $this->option('league');

        return $league !== null && $league !== ''
            ? (int) $league
            : (int) config('services.api_football.world_cup_league_id');
    }

    private function season(): int
    {
        $season = $this->option('season');

        return $season !== null && $season !== ''
            ? (int) $season
            : (int) config('services.api_football.world_cup_season');
    }

    private function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function nullableInteger(mixed $value): ?int
    {
        if (! is_numeric($value)) {
            return null;
        }

        return (int) $value;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function finishSyncLog(int $exitCode, string $status, mixed $startedAt, int $startedNs, array $attributes = []): int
    {
        $finishedAt = now();

        ApiSyncLogWriter::write(array_merge([
            'provider' => self::PROVIDER,
            'sync_type' => 'fixtures',
            'status' => $status,
            'started_at' => $startedAt,
            'finished_at' => $finishedAt,
            'duration_ms' => (int) round((hrtime(true) - $startedNs) / 1_000_000),
        ], $this->lastResponseMetrics, $attributes));

        return $exitCode;
    }

    /**
     * @return array<string, mixed>
     */
    private function metadata(int $leagueId, int $season, bool $usesSnapshot): array
    {
        return [
            'league' => $leagueId,
            'season' => $season,
            'source' => $usesSnapshot ? 'snapshot' : 'api',
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function payloadResultCount(array $payload): ?int
    {
        $response = Arr::get($payload, 'response');

        return is_array($response) ? count($response) : null;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function apiErrorMessage(array $payload): string
    {
        return collect($this->flattenApiErrors(Arr::get($payload, 'errors', [])))
            ->map(fn (string $message, string $key): string => "{$key}: {$message}")
            ->implode('; ');
    }
}
