<?php

namespace App\Console\Commands;

use App\Models\Team;
use App\Support\TeamFlagMapping;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ApiFootballSyncTeamsCommand extends Command
{
    protected $signature = 'api-football:sync-teams
        {--league= : Override the configured API-Football league id}
        {--season= : Override the configured API-Football season}
        {--force : Run without interactive confirmation}
        {--dry-run : Fetch and parse teams without writing to the database}
        {--from-snapshot= : Load a saved JSON snapshot instead of calling API-Football}';

    protected $description = 'Sync national teams from API-Football into the local teams table.';

    private const PROVIDER = 'api-football';

    public function handle(): int
    {
        if (! $this->environmentAllowsSync()) {
            $this->error('Refusing to sync API-Football teams in production or live mode.');
            $this->line('Current APP_ENV: '.app()->environment());
            $this->line('Current APP_MODE: '.config('app.mode'));

            return self::FAILURE;
        }

        $leagueId = $this->leagueId();
        $season = $this->season();
        $fromSnapshot = (string) ($this->option('from-snapshot') ?? '');
        $usesSnapshot = $fromSnapshot !== '';

        $this->line("API-Football team sync: league={$leagueId}, season={$season}");
        $this->line($usesSnapshot ? 'Planned API requests: 0 (snapshot mode)' : 'Planned API requests: 1');

        if (! $usesSnapshot) {
            $apiKey = (string) config('services.api_football.key');

            if ($apiKey === '') {
                $this->error('Missing API_FOOTBALL_KEY. Configure it in the environment before calling API-Football.');

                return self::FAILURE;
            }
        }

        if (! $this->option('force') && ! $this->confirm($this->option('dry-run')
            ? 'Fetch and parse API-Football teams without writing to the database?'
            : 'Sync API-Football teams into the local database?')) {
            $this->warn('API-Football team sync cancelled.');

            return self::FAILURE;
        }

        $payload = $usesSnapshot
            ? $this->payloadFromSnapshot($fromSnapshot)
            : $this->payloadFromApi($leagueId, $season, (string) config('services.api_football.key'));

        if ($payload === null) {
            return self::FAILURE;
        }

        if ($this->hasApiErrors($payload)) {
            $this->printApiErrors($payload);
            $this->warn('No teams were synced.');

            return self::FAILURE;
        }

        $items = Arr::get($payload, 'response', []);

        if (! is_array($items)) {
            $this->error('Unexpected API-Football response shape: missing response array.');

            return self::FAILURE;
        }

        if ($items === []) {
            $this->warn('API-Football returned an empty teams response. No teams were synced.');

            return self::FAILURE;
        }

        $rows = [];
        $counts = [
            'created' => 0,
            'updated' => 0,
            'linked' => 0,
            'skipped' => 0,
        ];

        foreach ($items as $item) {
            $result = $this->syncTeam($item, (bool) $this->option('dry-run'));
            $counts[$result['action']]++;
            $rows[] = [
                $result['action'],
                $result['api_team_id'] ?? 'n/a',
                $result['name'] ?? 'n/a',
                $result['short_name'] ?? 'n/a',
                $result['reason'],
            ];
        }

        $this->table(['Action', 'API team id', 'Name', 'Code', 'Reason'], $rows);
        $this->line(sprintf(
            'Summary: created=%d, updated=%d, linked=%d, skipped=%d',
            $counts['created'],
            $counts['updated'],
            $counts['linked'],
            $counts['skipped'],
        ));

        if ($this->option('dry-run')) {
            $this->warn('Dry run complete. No database changes were written.');
        } else {
            $this->components->info('API-Football team sync complete.');
        }

        return self::SUCCESS;
    }

    private function payloadFromApi(int $leagueId, int $season, string $apiKey): ?array
    {
        $url = rtrim((string) config('services.api_football.base_url'), '/').'/teams';
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
     * @param  mixed  $item
     * @return array{action: string, api_team_id?: int|string|null, name?: string|null, short_name?: string|null, reason: string}
     */
    private function syncTeam(mixed $item, bool $dryRun): array
    {
        if (! is_array($item)) {
            return [
                'action' => 'skipped',
                'reason' => 'Team item is not an object.',
            ];
        }

        $teamData = Arr::get($item, 'team', []);

        if (! is_array($teamData)) {
            return [
                'action' => 'skipped',
                'reason' => 'Missing team object.',
            ];
        }

        $apiTeamId = Arr::get($teamData, 'id');
        $name = trim((string) Arr::get($teamData, 'name', ''));
        $shortName = $this->nullableString(Arr::get($teamData, 'code'));
        $country = $this->nullableString(Arr::get($teamData, 'country'));
        $logoUrl = $this->nullableString(Arr::get($teamData, 'logo'));

        if (! is_numeric($apiTeamId) || $name === '') {
            return [
                'action' => 'skipped',
                'api_team_id' => $apiTeamId,
                'name' => $name,
                'short_name' => $shortName,
                'reason' => 'Missing required team.id or team.name.',
            ];
        }

        $apiTeamId = (int) $apiTeamId;
        $existing = Team::query()
            ->where('api_provider', self::PROVIDER)
            ->where('api_team_id', $apiTeamId)
            ->first();

        $isLinked = false;

        if (! $existing) {
            $existing = $this->findConservativeFallback($shortName, $name);
            $isLinked = $existing !== null;
        }

        $values = [
            'api_provider' => self::PROVIDER,
            'api_team_id' => $apiTeamId,
            'name' => $name,
            'short_name' => $shortName,
            'country' => $country,
            'logo_url' => $logoUrl,
            'last_synced_at' => now(),
        ];

        $flagMapping = TeamFlagMapping::forCode($shortName);

        if (! $existing) {
            if ($flagMapping !== null && TeamFlagMapping::assetExists($flagMapping['flag_path'])) {
                $values += $flagMapping;
            }

            if (! $dryRun) {
                Team::query()->create($values);
            }

            return [
                'action' => 'created',
                'api_team_id' => $apiTeamId,
                'name' => $name,
                'short_name' => $shortName,
                'reason' => $dryRun ? 'Would create new team.' : 'Created new team.',
            ];
        }

        $values['short_name'] = $shortName ?? $existing->short_name;
        $values['country'] = $country ?? $existing->country;
        $values['logo_url'] = $logoUrl ?? $existing->logo_url;

        if ($flagMapping !== null && TeamFlagMapping::assetExists($flagMapping['flag_path'])) {
            if (blank($existing->country_code)) {
                $values['country_code'] = $flagMapping['country_code'];
            }

            if (blank($existing->flag_path)) {
                $values['flag_path'] = $flagMapping['flag_path'];
            }
        }

        if ($existing->api_team_id !== null && (int) $existing->api_team_id !== $apiTeamId) {
            return [
                'action' => 'skipped',
                'api_team_id' => $apiTeamId,
                'name' => $name,
                'short_name' => $shortName,
                'reason' => 'Fallback candidate already has a different API team id.',
            ];
        }

        if (! $dryRun) {
            $existing->forceFill($values)->save();
        }

        return [
            'action' => $isLinked ? 'linked' : 'updated',
            'api_team_id' => $apiTeamId,
            'name' => $name,
            'short_name' => $shortName,
            'reason' => $dryRun
                ? ($isLinked ? 'Would link existing local team.' : 'Would update existing API-linked team.')
                : ($isLinked ? 'Linked existing local team.' : 'Updated existing API-linked team.'),
        ];
    }

    private function findConservativeFallback(?string $shortName, string $name): ?Team
    {
        if ($shortName !== null) {
            $matches = Team::query()
                ->where('short_name', $shortName)
                ->whereNull('api_team_id')
                ->get();

            if ($matches->count() === 1) {
                return $matches->first();
            }
        }

        $matches = Team::query()
            ->where('name', $name)
            ->whereNull('api_team_id')
            ->get();

        return $matches->count() === 1 ? $matches->first() : null;
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
     * @param  mixed  $errors
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
            429 => $this->error('Rate limit reached. Stop running team sync and wait for quota reset.'),
            default => $this->error('API-Football returned an HTTP error. No teams were synced.'),
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

    private function environmentAllowsSync(): bool
    {
        if (config('app.mode') === 'live') {
            return false;
        }

        return ! app()->environment('production');
    }
}
