<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ApiFootballDiscoverWorldCupCommand extends Command
{
    protected $signature = 'api-football:discover-world-cup
        {--endpoint=all : Endpoint to discover: teams, fixtures, rounds, standings, or all}
        {--league= : Override the configured API-Football league id}
        {--season= : Override the configured API-Football season}
        {--save : Save raw JSON snapshots to private storage}
        {--force : Run without interactive confirmation}
        {--dry-run : Print the planned requests without calling the API}';

    protected $description = 'Discover API-Football World Cup 2026 data without mutating application data.';

    /**
     * @var array<string, array{path: string, label: string}>
     */
    private array $endpoints = [
        'teams' => ['path' => '/teams', 'label' => 'Teams'],
        'fixtures' => ['path' => '/fixtures', 'label' => 'Fixtures'],
        'rounds' => ['path' => '/fixtures/rounds', 'label' => 'Rounds'],
        'standings' => ['path' => '/standings', 'label' => 'Standings'],
    ];

    public function handle(): int
    {
        if (! $this->environmentAllowsDiscovery()) {
            $this->error('Refusing to run API-Football discovery in production or live mode.');
            $this->line('Current APP_ENV: '.app()->environment());
            $this->line('Current APP_MODE: '.config('app.mode'));

            return self::FAILURE;
        }

        $apiKey = (string) config('services.api_football.key');

        if ($apiKey === '') {
            $this->error('Missing API_FOOTBALL_KEY. Configure it in the environment before calling API-Football.');

            return self::FAILURE;
        }

        $selected = $this->selectedEndpoints();

        if ($selected === []) {
            $this->error('Invalid endpoint. Use one of: teams, fixtures, rounds, standings, all.');

            return self::FAILURE;
        }

        $requestCount = count($selected);
        $leagueId = $this->leagueId();
        $season = $this->season();

        $this->line("API-Football World Cup discovery: league={$leagueId}, season={$season}");
        $this->line("Planned API requests: {$requestCount}");

        foreach ($selected as $key => $endpoint) {
            $this->line('- '.$this->urlFor($endpoint['path']).' ['.$key.']');
        }

        if ($this->option('dry-run')) {
            $this->warn('Dry run only. No API requests were made and no snapshots were saved.');

            return self::SUCCESS;
        }

        if (! $this->option('force') && ! $this->confirm('Call API-Football now? This may consume your daily request quota.')) {
            $this->warn('API-Football discovery cancelled.');

            return self::FAILURE;
        }

        $failed = false;
        $timestamp = now()->format('Ymd-His');

        foreach ($selected as $key => $endpoint) {
            $this->newLine();
            $this->info($endpoint['label']);
            $this->line('Request: '.$endpoint['path']);

            try {
                $response = Http::withHeaders([
                    'x-apisports-key' => $apiKey,
                ])
                    ->acceptJson()
                    ->timeout(15)
                    ->get($this->urlFor($endpoint['path']), [
                        'league' => $leagueId,
                        'season' => $season,
                    ]);
            } catch (ConnectionException $exception) {
                $this->error('Network error calling API-Football: '.$exception->getMessage());
                $failed = true;

                continue;
            }

            $this->summarizeResponse($key, $response);

            $payload = $response->json();

            if (is_array($payload) && $this->option('save')) {
                $this->saveSnapshot($key, $payload, $timestamp, $this->hasApiErrors($payload));
            }

            if (! $response->successful()) {
                $this->printErrorHint($response);
                $failed = true;

                continue;
            }

            if (! is_array($payload)) {
                $this->warn('Unexpected response shape: response body was not JSON object/array.');
                $failed = true;

                continue;
            }

            if ($this->hasApiErrors($payload)) {
                $this->printApiErrors($payload);
                $failed = true;

                continue;
            }

            if (array_key_exists('results', $payload)) {
                $this->line('Response count: '.(string) $payload['results']);
            }

            $this->summarizePayload($key, $payload);
        }

        if ($failed) {
            $this->warn('API-Football discovery finished with errors.');

            return self::FAILURE;
        }

        $this->components->info('API-Football discovery complete. No application data was changed.');

        return self::SUCCESS;
    }

    /**
     * @return array<string, array{path: string, label: string}>
     */
    private function selectedEndpoints(): array
    {
        $endpoint = (string) $this->option('endpoint');

        if ($endpoint === 'all') {
            return $this->endpoints;
        }

        if (! array_key_exists($endpoint, $this->endpoints)) {
            return [];
        }

        return [$endpoint => $this->endpoints[$endpoint]];
    }

    private function urlFor(string $path): string
    {
        return rtrim((string) config('services.api_football.base_url'), '/').$path;
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

    private function summarizeResponse(string $key, Response $response): void
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

        $payload = $response->json();

        if (! is_array($payload)) {
            $this->line('Endpoint: '.$key);
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function summarizePayload(string $key, array $payload): void
    {
        $response = Arr::get($payload, 'response', []);

        if (! is_array($response)) {
            $this->warn('Unexpected response shape: missing response array.');

            return;
        }

        match ($key) {
            'teams' => $this->summarizeTeams($response),
            'fixtures' => $this->summarizeFixtures($response),
            'rounds' => $this->summarizeRounds($response),
            'standings' => $this->summarizeStandings($response),
            default => null,
        };
    }

    /**
     * @param  array<int, mixed>  $teams
     */
    private function summarizeTeams(array $teams): void
    {
        $this->line('Teams found: '.count($teams));

        foreach (array_slice($teams, 0, 5) as $item) {
            $team = Arr::get($item, 'team', []);
            $this->line(sprintf(
                '- %s (%s), API id: %s',
                Arr::get($team, 'name', 'unknown'),
                Arr::get($team, 'code', 'n/a') ?: 'n/a',
                Arr::get($team, 'id', 'n/a'),
            ));
        }
    }

    /**
     * @param  array<int, mixed>  $fixtures
     */
    private function summarizeFixtures(array $fixtures): void
    {
        $this->line('Fixtures found: '.count($fixtures));

        foreach (array_slice($fixtures, 0, 5) as $item) {
            $this->line(sprintf(
                '- #%s %s: %s vs %s [%s] at %s',
                Arr::get($item, 'fixture.id', 'n/a'),
                Arr::get($item, 'fixture.date', 'date n/a'),
                Arr::get($item, 'teams.home.name', 'home n/a'),
                Arr::get($item, 'teams.away.name', 'away n/a'),
                Arr::get($item, 'fixture.status.short', 'status n/a'),
                Arr::get($item, 'fixture.venue.name', 'venue n/a') ?: 'venue n/a',
            ));
        }
    }

    /**
     * @param  array<int, mixed>  $rounds
     */
    private function summarizeRounds(array $rounds): void
    {
        $this->line('Rounds found: '.count($rounds));

        foreach (array_slice($rounds, 0, 10) as $round) {
            $this->line('- '.(is_scalar($round) ? (string) $round : json_encode($round)));
        }
    }

    /**
     * @param  array<int, mixed>  $standingsResponse
     */
    private function summarizeStandings(array $standingsResponse): void
    {
        $groups = Arr::get($standingsResponse, '0.league.standings', []);
        $entries = 0;

        if (is_array($groups)) {
            foreach ($groups as $group) {
                $entries += is_array($group) ? count($group) : 0;
            }
        }

        $this->line('Standings groups found: '.(is_array($groups) ? count($groups) : 0));
        $this->line('Standings entries found: '.$entries);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function saveSnapshot(string $key, array $payload, string $timestamp, bool $hasErrors = false): void
    {
        $directory = 'api-football/world-cup-2026';
        $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        if ($json === false) {
            $this->warn("Could not encode {$key} payload for snapshot storage.");

            return;
        }

        $timestampedPath = "{$directory}/{$key}-{$timestamp}.json";
        $latestPath = "{$directory}/{$key}-latest.json";

        Storage::disk('local')->put($timestampedPath, $json);
        Storage::disk('local')->put($latestPath, $json);

        $this->line(($hasErrors ? 'Saved error snapshot: ' : 'Saved snapshot: ').'storage/app/private/'.$timestampedPath);
        $this->line('Updated latest: storage/app/private/'.$latestPath);
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

        $this->warn('Response data was not treated as valid discovery data.');
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
            429 => $this->error('Rate limit reached. Stop running discovery and wait for quota reset.'),
            default => $this->error('API-Football returned an error. Inspect status/body with a manual saved run if needed.'),
        };
    }

    private function environmentAllowsDiscovery(): bool
    {
        if (config('app.mode') === 'live') {
            return false;
        }

        return ! app()->environment('production');
    }
}
