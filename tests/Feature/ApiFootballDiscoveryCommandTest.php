<?php

namespace Tests\Feature;

use App\Models\Team;
use App\Models\TournamentMatch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ApiFootballDiscoveryCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_missing_api_key_fails_safely(): void
    {
        config(['services.api_football.key' => null]);
        Http::fake();

        $this->artisan('api-football:discover-world-cup --endpoint=teams --force')
            ->expectsOutputToContain('Missing API_FOOTBALL_KEY')
            ->assertFailed();

        Http::assertNothingSent();
    }

    public function test_teams_endpoint_calls_correct_url_and_header(): void
    {
        $this->configureApiFootball();

        Http::fake([
            'https://example.test/teams*' => Http::response([
                'results' => 1,
                'response' => [
                    ['team' => ['id' => 10, 'name' => 'Argentina', 'code' => 'ARG']],
                ],
            ], 200, ['X-RateLimit-Requests-Remaining' => '99']),
        ]);

        $this->artisan('api-football:discover-world-cup --endpoint=teams --force')
            ->expectsOutputToContain('Planned API requests: 1')
            ->expectsOutputToContain('Teams found: 1')
            ->assertSuccessful();

        Http::assertSent(function (Request $request): bool {
            return $request->url() === 'https://example.test/teams?league=1&season=2026'
                && $request->hasHeader('x-apisports-key', 'fake-api-key');
        });
    }

    public function test_all_endpoint_makes_expected_four_calls(): void
    {
        $this->configureApiFootball();

        Http::fake([
            'https://example.test/teams*' => Http::response(['results' => 0, 'response' => []]),
            'https://example.test/fixtures*' => Http::response(['results' => 0, 'response' => []]),
            'https://example.test/fixtures/rounds*' => Http::response(['results' => 0, 'response' => []]),
            'https://example.test/standings*' => Http::response(['results' => 0, 'response' => []]),
        ]);

        $this->artisan('api-football:discover-world-cup --endpoint=all --force')
            ->expectsOutputToContain('Planned API requests: 4')
            ->assertSuccessful();

        Http::assertSentCount(4);
    }

    public function test_save_writes_snapshots_to_fake_storage(): void
    {
        $this->configureApiFootball();
        Storage::fake('local');

        Http::fake([
            'https://example.test/teams*' => Http::response([
                'results' => 1,
                'response' => [
                    ['team' => ['id' => 10, 'name' => 'Argentina', 'code' => 'ARG']],
                ],
            ]),
        ]);

        $this->artisan('api-football:discover-world-cup --endpoint=teams --save --force')
            ->expectsOutputToContain('Saved snapshot')
            ->assertSuccessful();

        Storage::disk('local')->assertExists('api-football/world-cup-2026/teams-latest.json');

        $files = Storage::disk('local')->files('api-football/world-cup-2026');
        $timestampedSnapshots = array_filter(
            $files,
            fn (string $file): bool => preg_match('/teams-\d{8}-\d{6}\.json$/', $file) === 1,
        );

        $this->assertCount(1, $timestampedSnapshots);
    }

    public function test_api_error_is_handled_gracefully(): void
    {
        $this->configureApiFootball();

        Http::fake([
            'https://example.test/teams*' => Http::response([
                'errors' => ['token' => 'Invalid API key'],
                'response' => [],
            ], 401),
        ]);

        $this->artisan('api-football:discover-world-cup --endpoint=teams --force')
            ->expectsOutputToContain('Unauthorized')
            ->assertFailed();
    }

    public function test_http_200_with_non_empty_api_errors_returns_failure(): void
    {
        $this->configureApiFootball();

        Http::fake([
            'https://example.test/teams*' => Http::response([
                'errors' => [
                    'plan' => 'Free plans do not have access to this season, try from 2022 to 2024.',
                ],
                'results' => 0,
                'response' => [],
            ]),
        ]);

        $this->artisan('api-football:discover-world-cup --endpoint=teams --force')
            ->expectsOutputToContain('API-Football returned logical errors')
            ->expectsOutputToContain('errors.plan: Free plans do not have access to this season, try from 2022 to 2024.')
            ->doesntExpectOutputToContain('Response count: 0')
            ->assertFailed();
    }

    public function test_season_override_changes_request_query(): void
    {
        $this->configureApiFootball();

        Http::fake([
            'https://example.test/teams*' => Http::response(['results' => 0, 'response' => []]),
        ]);

        $this->artisan('api-football:discover-world-cup --endpoint=teams --season=2022 --force')
            ->expectsOutputToContain('league=1, season=2022')
            ->assertSuccessful();

        Http::assertSent(fn (Request $request): bool => $request->url() === 'https://example.test/teams?league=1&season=2022');
    }

    public function test_league_override_changes_request_query(): void
    {
        $this->configureApiFootball();

        Http::fake([
            'https://example.test/fixtures*' => Http::response(['results' => 0, 'response' => []]),
        ]);

        $this->artisan('api-football:discover-world-cup --endpoint=fixtures --league=99 --force')
            ->expectsOutputToContain('league=99, season=2026')
            ->assertSuccessful();

        Http::assertSent(fn (Request $request): bool => $request->url() === 'https://example.test/fixtures?league=99&season=2026');
    }

    public function test_save_writes_snapshot_for_api_error_response(): void
    {
        $this->configureApiFootball();
        Storage::fake('local');

        Http::fake([
            'https://example.test/teams*' => Http::response([
                'errors' => [
                    'plan' => 'Free plans do not have access to this season, try from 2022 to 2024.',
                ],
                'results' => 0,
                'response' => [],
            ]),
        ]);

        $this->artisan('api-football:discover-world-cup --endpoint=teams --save --force')
            ->expectsOutputToContain('Saved error snapshot')
            ->assertFailed();

        Storage::disk('local')->assertExists('api-football/world-cup-2026/teams-latest.json');
    }

    public function test_command_does_not_modify_teams_or_matches_database(): void
    {
        $this->configureApiFootball();

        Team::factory()->count(2)->create();
        TournamentMatch::factory()->count(2)->create();
        $teamCount = Team::query()->count();
        $matchCount = TournamentMatch::query()->count();

        Http::fake([
            'https://example.test/teams*' => Http::response([
                'results' => 1,
                'response' => [
                    ['team' => ['id' => 99, 'name' => 'External Team', 'code' => 'EXT']],
                ],
            ]),
        ]);

        $this->artisan('api-football:discover-world-cup --endpoint=teams --force')
            ->assertSuccessful();

        $this->assertSame($teamCount, Team::query()->count());
        $this->assertSame($matchCount, TournamentMatch::query()->count());
        $this->assertDatabaseMissing('teams', ['name' => 'External Team']);
    }

    private function configureApiFootball(): void
    {
        config([
            'services.api_football.base_url' => 'https://example.test',
            'services.api_football.key' => 'fake-api-key',
            'services.api_football.world_cup_league_id' => 1,
            'services.api_football.world_cup_season' => 2026,
        ]);
    }
}
