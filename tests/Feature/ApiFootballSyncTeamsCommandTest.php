<?php

namespace Tests\Feature;

use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ApiFootballSyncTeamsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_missing_api_key_fails(): void
    {
        config(['services.api_football.key' => null]);
        Http::fake();

        $this->artisan('api-football:sync-teams --force')
            ->expectsOutputToContain('Missing API_FOOTBALL_KEY')
            ->assertFailed();

        Http::assertNothingSent();
    }

    public function test_api_errors_are_detected_and_no_database_mutation_occurs(): void
    {
        $this->configureApiFootball();
        Team::factory()->create(['name' => 'Existing Team']);

        Http::fake([
            'https://example.test/teams*' => Http::response([
                'errors' => [
                    'plan' => 'Free plans do not have access to this season.',
                ],
                'response' => [
                    ['team' => ['id' => 99, 'name' => 'External Team', 'code' => 'EXT']],
                ],
            ]),
        ]);

        $this->artisan('api-football:sync-teams --force')
            ->expectsOutputToContain('errors.plan: Free plans do not have access to this season.')
            ->assertFailed();

        $this->assertSame(1, Team::query()->count());
        $this->assertDatabaseMissing('teams', ['name' => 'External Team']);
    }

    public function test_successful_response_creates_teams(): void
    {
        $this->configureApiFootball();
        $this->fakeTeamsResponse();

        $this->artisan('api-football:sync-teams --force')
            ->expectsOutputToContain('created')
            ->assertSuccessful();

        $this->assertDatabaseHas('teams', [
            'api_provider' => 'api-football',
            'api_team_id' => 10,
            'name' => 'Argentina',
            'short_name' => 'ARG',
            'country' => 'Argentina',
            'logo_url' => 'https://media.example/arg.png',
        ]);

        $this->assertDatabaseHas('teams', [
            'api_provider' => 'api-football',
            'api_team_id' => 20,
            'name' => 'Brazil',
            'short_name' => 'BRA',
            'country' => 'Brazil',
            'logo_url' => 'https://media.example/bra.png',
        ]);
    }

    public function test_re_running_updates_existing_teams_without_duplicates(): void
    {
        $this->configureApiFootball();

        Team::factory()->create([
            'api_provider' => 'api-football',
            'api_team_id' => 10,
            'name' => 'Old Argentina',
            'short_name' => 'ARG',
            'country' => 'Old Country',
            'logo_url' => 'https://media.example/old.png',
        ]);

        Http::fake([
            'https://example.test/teams*' => Http::response([
                'results' => 1,
                'response' => [
                    $this->teamPayload(10, 'Argentina', 'ARG', 'Argentina', 'https://media.example/arg.png'),
                ],
            ]),
        ]);

        $this->artisan('api-football:sync-teams --force')
            ->expectsOutputToContain('updated')
            ->assertSuccessful();

        $this->assertSame(1, Team::query()->where('api_team_id', 10)->count());
        $this->assertDatabaseHas('teams', [
            'api_provider' => 'api-football',
            'api_team_id' => 10,
            'name' => 'Argentina',
            'country' => 'Argentina',
            'logo_url' => 'https://media.example/arg.png',
        ]);
    }

    public function test_existing_team_with_matching_code_can_be_linked_safely(): void
    {
        $this->configureApiFootball();

        Team::factory()->create([
            'name' => 'Argentina Local',
            'short_name' => 'ARG',
            'api_provider' => null,
            'api_team_id' => null,
        ]);

        Http::fake([
            'https://example.test/teams*' => Http::response([
                'results' => 1,
                'response' => [
                    $this->teamPayload(10, 'Argentina', 'ARG', 'Argentina', 'https://media.example/arg.png'),
                ],
            ]),
        ]);

        $this->artisan('api-football:sync-teams --force')
            ->expectsOutputToContain('linked')
            ->assertSuccessful();

        $this->assertSame(1, Team::query()->count());
        $this->assertDatabaseHas('teams', [
            'api_provider' => 'api-football',
            'api_team_id' => 10,
            'name' => 'Argentina',
            'short_name' => 'ARG',
        ]);
    }

    public function test_dry_run_does_not_mutate_database(): void
    {
        $this->configureApiFootball();
        $this->fakeTeamsResponse();

        $this->artisan('api-football:sync-teams --dry-run --force')
            ->expectsOutputToContain('Dry run complete')
            ->assertSuccessful();

        $this->assertSame(0, Team::query()->count());
    }

    public function test_country_code_and_flag_path_are_not_overwritten(): void
    {
        $this->configureApiFootball();

        Team::factory()->create([
            'name' => 'Argentina',
            'short_name' => 'ARG',
            'country_code' => 'LOC',
            'flag_path' => 'flags/local-arg.svg',
            'api_provider' => null,
            'api_team_id' => null,
        ]);

        Http::fake([
            'https://example.test/teams*' => Http::response([
                'results' => 1,
                'response' => [
                    $this->teamPayload(10, 'Argentina', 'ARG', 'Argentina', 'https://media.example/arg.png'),
                ],
            ]),
        ]);

        $this->artisan('api-football:sync-teams --force')
            ->assertSuccessful();

        $team = Team::query()->firstOrFail();

        $this->assertSame('LOC', $team->country_code);
        $this->assertSame('flags/local-arg.svg', $team->flag_path);
        $this->assertSame('https://media.example/arg.png', $team->logo_url);
    }

    public function test_flag_mapping_is_applied_when_local_identity_fields_are_null(): void
    {
        $this->configureApiFootball();

        Team::factory()->create([
            'name' => 'Argentina',
            'short_name' => 'ARG',
            'country_code' => null,
            'flag_path' => null,
            'api_provider' => null,
            'api_team_id' => null,
        ]);

        Http::fake([
            'https://example.test/teams*' => Http::response([
                'results' => 1,
                'response' => [
                    $this->teamPayload(10, 'Argentina', 'ARG', 'Argentina', 'https://media.example/arg.png'),
                ],
            ]),
        ]);

        $this->artisan('api-football:sync-teams --force')
            ->assertSuccessful();

        $team = Team::query()->firstOrFail();

        $this->assertSame('ARG', $team->country_code);
        $this->assertSame('flags/arg.svg', $team->flag_path);
        $this->assertSame('https://media.example/arg.png', $team->logo_url);
    }

    public function test_created_api_team_receives_flag_mapping_when_available(): void
    {
        $this->configureApiFootball();

        Http::fake([
            'https://example.test/teams*' => Http::response([
                'results' => 1,
                'response' => [
                    $this->teamPayload(20, 'Brazil', 'BRA', 'Brazil', 'https://media.example/bra.png'),
                ],
            ]),
        ]);

        $this->artisan('api-football:sync-teams --force')
            ->assertSuccessful();

        $this->assertDatabaseHas('teams', [
            'api_provider' => 'api-football',
            'api_team_id' => 20,
            'country_code' => 'BRA',
            'flag_path' => 'flags/bra.svg',
            'logo_url' => 'https://media.example/bra.png',
        ]);
    }

    public function test_venue_data_is_ignored(): void
    {
        $this->configureApiFootball();

        Http::fake([
            'https://example.test/teams*' => Http::response([
                'results' => 1,
                'response' => [
                    $this->teamPayload(10, 'Argentina', 'ARG', 'Argentina', 'https://media.example/arg.png', [
                        'id' => 1,
                        'name' => 'Home Venue',
                        'city' => 'Venue City',
                        'country' => 'Venue Country',
                    ]),
                ],
            ]),
        ]);

        $this->artisan('api-football:sync-teams --force')
            ->assertSuccessful();

        $team = Team::query()->firstOrFail();

        $this->assertSame('Argentina', $team->country);
        $this->assertSame('Argentina', $team->name);
        $this->assertSame('ARG', $team->short_name);
        $this->assertSame('ARG', $team->country_code);
        $this->assertSame('flags/arg.svg', $team->flag_path);
    }

    public function test_command_makes_only_expected_api_request(): void
    {
        $this->configureApiFootball();
        $this->fakeTeamsResponse();

        $this->artisan('api-football:sync-teams --season=2022 --league=1 --force')
            ->assertSuccessful();

        Http::assertSentCount(1);
        Http::assertSent(fn (Request $request): bool => $request->url() === 'https://example.test/teams?league=1&season=2022'
            && $request->hasHeader('x-apisports-key', 'fake-api-key'));
    }

    public function test_snapshot_mode_does_not_require_api_key_or_send_request(): void
    {
        config([
            'services.api_football.key' => null,
        ]);

        Storage::fake('local');
        Http::fake();

        Storage::disk('local')->put('api-football/world-cup-2026/teams-latest.json', json_encode([
            'results' => 1,
            'response' => [
                $this->teamPayload(10, 'Argentina', 'ARG', 'Argentina', 'https://media.example/arg.png'),
            ],
        ]));

        $this->artisan('api-football:sync-teams --from-snapshot=api-football/world-cup-2026/teams-latest.json --force')
            ->expectsOutputToContain('Planned API requests: 0')
            ->assertSuccessful();

        Http::assertNothingSent();
        $this->assertDatabaseHas('teams', [
            'api_provider' => 'api-football',
            'api_team_id' => 10,
            'name' => 'Argentina',
        ]);
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

    private function fakeTeamsResponse(): void
    {
        Http::fake([
            'https://example.test/teams*' => Http::response([
                'results' => 2,
                'response' => [
                    $this->teamPayload(10, 'Argentina', 'ARG', 'Argentina', 'https://media.example/arg.png'),
                    $this->teamPayload(20, 'Brazil', 'BRA', 'Brazil', 'https://media.example/bra.png'),
                ],
            ]),
        ]);
    }

    /**
     * @param  array<string, mixed>  $venue
     * @return array<string, mixed>
     */
    private function teamPayload(int $id, string $name, string $code, string $country, string $logo, array $venue = []): array
    {
        return [
            'team' => [
                'id' => $id,
                'name' => $name,
                'code' => $code,
                'country' => $country,
                'national' => true,
                'logo' => $logo,
            ],
            'venue' => $venue,
        ];
    }
}
