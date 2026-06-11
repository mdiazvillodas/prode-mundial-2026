<?php

namespace Tests\Feature;

use App\Models\ApiSyncLog;
use App\Models\Prediction;
use App\Models\Team;
use App\Models\Tournament;
use App\Models\TournamentMatch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ApiFootballSyncFixturesCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_missing_api_key_fails_unless_snapshot_mode_is_used(): void
    {
        $this->createTournament();
        config(['services.api_football.key' => null]);
        Http::fake();

        $this->artisan('api-football:sync-fixtures --force')
            ->expectsOutputToContain('Missing API_FOOTBALL_KEY')
            ->assertFailed();

        Http::assertNothingSent();
    }

    public function test_sync_fixtures_refuses_in_production_live_when_production_sync_flag_is_false(): void
    {
        $this->createTournament();
        $this->configureApiFootball();
        $this->configureProtectedEnvironment(false);
        Http::fake();

        $this->artisan('api-football:sync-fixtures --force')
            ->expectsOutputToContain('Refusing to sync API-Football fixtures in production or live mode.')
            ->expectsOutputToContain('Current APP_ENV: production')
            ->expectsOutputToContain('Current APP_MODE: live')
            ->assertFailed();

        Http::assertNothingSent();
        $this->assertSame(0, TournamentMatch::query()->count());
    }

    public function test_sync_fixtures_allows_production_live_when_production_sync_flag_is_true(): void
    {
        $this->createTournament();
        $this->configureApiFootball();
        $this->configureProtectedEnvironment(true);
        $home = $this->createApiTeam(10, 'Argentina', 'ARG');
        $away = $this->createApiTeam(20, 'Brazil', 'BRA');
        $this->fakeFixturesResponse();

        $this->artisan('api-football:sync-fixtures --force')
            ->expectsOutputToContain('API-Football production/live sync is enabled by API_FOOTBALL_ALLOW_PRODUCTION_SYNC=true.')
            ->expectsOutputToContain('Proceeding in APP_ENV=production and APP_MODE=live.')
            ->expectsOutputToContain('created')
            ->assertSuccessful();

        Http::assertSentCount(1);
        $this->assertDatabaseHas('matches', [
            'api_provider' => 'api-football',
            'api_fixture_id' => 1001,
            'team_a_id' => $home->id,
            'team_b_id' => $away->id,
        ]);
    }

    public function test_snapshot_mode_does_not_require_api_key_or_send_request(): void
    {
        $this->createTournament();
        $this->createApiTeam(10, 'Argentina', 'ARG');
        $this->createApiTeam(20, 'Brazil', 'BRA');

        config(['services.api_football.key' => null]);
        Storage::fake('local');
        Http::fake();

        Storage::disk('local')->put('api-football/world-cup-2026/fixtures-latest.json', json_encode([
            'results' => 1,
            'response' => [
                $this->fixturePayload(1001, 10, 20),
            ],
        ]));

        $this->artisan('api-football:sync-fixtures --from-snapshot=api-football/world-cup-2026/fixtures-latest.json --force')
            ->expectsOutputToContain('Planned API requests: 0')
            ->assertSuccessful();

        Http::assertNothingSent();
        $this->assertDatabaseHas('matches', [
            'api_provider' => 'api-football',
            'api_fixture_id' => 1001,
        ]);
    }

    public function test_api_errors_are_detected_and_no_database_mutation_occurs(): void
    {
        $this->createTournament();
        $this->configureApiFootball();
        $this->createApiTeam(10, 'Argentina', 'ARG');
        $this->createApiTeam(20, 'Brazil', 'BRA');

        Http::fake([
            'https://example.test/fixtures*' => Http::response([
                'errors' => [
                    'plan' => 'Free plans do not have access to this season.',
                ],
                'response' => [
                    $this->fixturePayload(1001, 10, 20),
                ],
            ]),
        ]);

        $this->artisan('api-football:sync-fixtures --force')
            ->expectsOutputToContain('errors.plan: Free plans do not have access to this season.')
            ->assertFailed();

        $this->assertSame(0, TournamentMatch::query()->count());
        $this->assertDatabaseHas('api_sync_logs', [
            'provider' => 'api-football',
            'sync_type' => 'fixtures',
            'status' => 'failed',
            'http_status' => 200,
            'items_received' => 1,
        ]);
    }

    public function test_successful_response_creates_fixture_when_teams_exist(): void
    {
        $tournament = $this->createTournament();
        $this->configureApiFootball();
        $home = $this->createApiTeam(10, 'Argentina', 'ARG');
        $away = $this->createApiTeam(20, 'Brazil', 'BRA');
        $this->fakeFixturesResponse();

        $this->artisan('api-football:sync-fixtures --force')
            ->expectsOutputToContain('created')
            ->assertSuccessful();

        $this->assertDatabaseHas('matches', [
            'tournament_id' => $tournament->id,
            'api_provider' => 'api-football',
            'api_fixture_id' => 1001,
            'api_status' => 'NS',
            'round' => 'Group Stage - 1',
            'venue_name' => 'MetLife Stadium',
            'venue_city' => 'East Rutherford',
            'team_a_id' => $home->id,
            'team_b_id' => $away->id,
            'stage' => 'group',
            'status' => TournamentMatch::STATUS_SCHEDULED,
        ]);

        $log = ApiSyncLog::query()->latest()->firstOrFail();

        $this->assertSame('api-football', $log->provider);
        $this->assertSame('fixtures', $log->sync_type);
        $this->assertSame('success', $log->status);
        $this->assertSame(200, $log->http_status);
        $this->assertSame(1, $log->items_received);
        $this->assertSame(1, $log->items_created);
    }

    public function test_missing_teams_are_skipped_with_clear_output(): void
    {
        $this->createTournament();
        $this->configureApiFootball();
        $this->fakeFixturesResponse();

        $this->artisan('api-football:sync-fixtures --force')
            ->expectsOutputToContain('Team not found. Run api-football:sync-teams first.')
            ->expectsOutputToContain('missing_teams=1')
            ->assertSuccessful();

        $this->assertSame(0, TournamentMatch::query()->count());
    }

    public function test_re_running_updates_existing_fixture_without_duplicates(): void
    {
        $tournament = $this->createTournament();
        $this->configureApiFootball();
        $home = $this->createApiTeam(10, 'Argentina', 'ARG');
        $away = $this->createApiTeam(20, 'Brazil', 'BRA');

        TournamentMatch::factory()->create([
            'tournament_id' => $tournament->id,
            'team_a_id' => $home->id,
            'team_b_id' => $away->id,
            'api_provider' => 'api-football',
            'api_fixture_id' => 1001,
            'api_status' => 'TBD',
            'venue_name' => 'Old Venue',
            'round' => 'Old Round',
            'status' => TournamentMatch::STATUS_OPEN,
        ]);

        $this->fakeFixturesResponse();

        $this->artisan('api-football:sync-fixtures --force')
            ->expectsOutputToContain('updated')
            ->assertSuccessful();

        $this->assertSame(1, TournamentMatch::query()->where('api_fixture_id', 1001)->count());
        $this->assertDatabaseHas('matches', [
            'api_provider' => 'api-football',
            'api_fixture_id' => 1001,
            'api_status' => 'NS',
            'venue_name' => 'MetLife Stadium',
            'round' => 'Group Stage - 1',
            'status' => TournamentMatch::STATUS_OPEN,
        ]);
    }

    public function test_dry_run_does_not_mutate_database(): void
    {
        $this->createTournament();
        $this->configureApiFootball();
        $this->createApiTeam(10, 'Argentina', 'ARG');
        $this->createApiTeam(20, 'Brazil', 'BRA');
        $this->fakeFixturesResponse();

        $this->artisan('api-football:sync-fixtures --dry-run --force')
            ->expectsOutputToContain('Dry run complete')
            ->assertSuccessful();

        $this->assertSame(0, TournamentMatch::query()->count());
    }

    public function test_finished_fixture_stores_scores_winner_and_settles_predictions(): void
    {
        $tournament = $this->createTournament();
        $this->configureApiFootball();
        $home = $this->createApiTeam(10, 'Argentina', 'ARG');
        $away = $this->createApiTeam(20, 'Brazil', 'BRA');

        $match = TournamentMatch::factory()->create([
            'tournament_id' => $tournament->id,
            'team_a_id' => $home->id,
            'team_b_id' => $away->id,
            'api_provider' => 'api-football',
            'api_fixture_id' => 1001,
            'stage' => 'group',
            'status' => TournamentMatch::STATUS_OPEN,
            'team_a_score' => null,
            'team_b_score' => null,
            'winner_team_id' => null,
        ]);

        $prediction = Prediction::factory()->create([
            'match_id' => $match->id,
            'team_a_score' => 2,
            'team_b_score' => 1,
            'predicted_qualified_team_id' => null,
            'status' => Prediction::STATUS_SUBMITTED,
            'points_awarded' => null,
        ]);

        Http::fake([
            'https://example.test/fixtures*' => Http::response([
                'results' => 1,
                'response' => [
                    $this->fixturePayload(1001, 10, 20, status: 'FT', homeGoals: 2, awayGoals: 1),
                ],
            ]),
        ]);

        $this->artisan('api-football:sync-fixtures --force')
            ->assertSuccessful();

        $match->refresh();
        $prediction->refresh();

        $this->assertSame(TournamentMatch::STATUS_FINISHED, $match->status);
        $this->assertSame('group', $match->stage);
        $this->assertSame(2, $match->team_a_score);
        $this->assertSame(1, $match->team_b_score);
        $this->assertSame($home->id, $match->winner_team_id);
        $this->assertSame(Prediction::STATUS_SCORED, $prediction->status);
        $this->assertSame(6, $prediction->points_awarded);
    }

    public function test_live_fixture_stores_partial_score_without_finished_status_or_settlement(): void
    {
        $this->createTournament();
        $this->configureApiFootball();
        $home = $this->createApiTeam(10, 'Argentina', 'ARG');
        $away = $this->createApiTeam(20, 'Brazil', 'BRA');

        Http::fake([
            'https://example.test/fixtures*' => Http::response([
                'results' => 1,
                'response' => [
                    $this->fixturePayload(1002, 10, 20, status: '1H', homeGoals: 1, awayGoals: 0),
                ],
            ]),
        ]);

        $this->artisan('api-football:sync-fixtures --force')
            ->assertSuccessful();

        $this->assertDatabaseHas('matches', [
            'api_provider' => 'api-football',
            'api_fixture_id' => 1002,
            'api_status' => '1H',
            'team_a_id' => $home->id,
            'team_b_id' => $away->id,
            'status' => TournamentMatch::STATUS_SCHEDULED,
            'team_a_score' => 1,
            'team_b_score' => 0,
            'winner_team_id' => null,
        ]);
    }

    public function test_penalty_finished_fixture_maps_existing_goal_fields_and_keeps_penalties_out_of_local_score_columns(): void
    {
        $this->createTournament();
        $this->configureApiFootball();
        $home = $this->createApiTeam(10, 'Argentina', 'ARG');
        $away = $this->createApiTeam(20, 'Brazil', 'BRA');

        Http::fake([
            'https://example.test/fixtures*' => Http::response([
                'results' => 1,
                'response' => [
                    $this->fixturePayload(
                        1003,
                        10,
                        20,
                        status: 'PEN',
                        homeGoals: 1,
                        awayGoals: 1,
                        round: 'Final',
                        score: [
                            'fulltime' => ['home' => 1, 'away' => 1],
                            'extratime' => ['home' => 1, 'away' => 1],
                            'penalty' => ['home' => 4, 'away' => 3],
                        ],
                    ),
                ],
            ]),
        ]);

        $this->artisan('api-football:sync-fixtures --force')
            ->assertSuccessful();

        $this->assertDatabaseHas('matches', [
            'api_provider' => 'api-football',
            'api_fixture_id' => 1003,
            'api_status' => 'PEN',
            'round' => 'Final',
            'team_a_id' => $home->id,
            'team_b_id' => $away->id,
            'stage' => 'final',
            'status' => TournamentMatch::STATUS_FINISHED,
            'team_a_score' => 1,
            'team_b_score' => 1,
            'winner_team_id' => null,
        ]);
    }

    public function test_finished_fixture_with_null_score_fields_does_not_score_predictions(): void
    {
        $tournament = $this->createTournament();
        $this->configureApiFootball();
        $home = $this->createApiTeam(10, 'Argentina', 'ARG');
        $away = $this->createApiTeam(20, 'Brazil', 'BRA');
        $match = TournamentMatch::factory()->create([
            'tournament_id' => $tournament->id,
            'team_a_id' => $home->id,
            'team_b_id' => $away->id,
            'api_provider' => 'api-football',
            'api_fixture_id' => 1004,
            'status' => TournamentMatch::STATUS_OPEN,
            'team_a_score' => null,
            'team_b_score' => null,
        ]);
        $prediction = Prediction::factory()->create([
            'match_id' => $match->id,
            'status' => Prediction::STATUS_SUBMITTED,
            'points_awarded' => null,
        ]);

        Http::fake([
            'https://example.test/fixtures*' => Http::response([
                'results' => 1,
                'response' => [
                    $this->fixturePayload(1004, 10, 20, status: 'FT', homeGoals: null, awayGoals: null),
                ],
            ]),
        ]);

        $this->artisan('api-football:sync-fixtures --force')
            ->assertSuccessful();

        $match->refresh();
        $prediction->refresh();

        $this->assertSame(TournamentMatch::STATUS_FINISHED, $match->status);
        $this->assertNull($match->team_a_score);
        $this->assertNull($match->team_b_score);
        $this->assertSame(Prediction::STATUS_SUBMITTED, $prediction->status);
        $this->assertNull($prediction->points_awarded);
    }

    public function test_unknown_api_status_does_not_settle_or_store_scores_prematurely(): void
    {
        $this->createTournament();
        $this->configureApiFootball();
        $this->createApiTeam(10, 'Argentina', 'ARG');
        $this->createApiTeam(20, 'Brazil', 'BRA');

        Http::fake([
            'https://example.test/fixtures*' => Http::response([
                'results' => 1,
                'response' => [
                    $this->fixturePayload(1005, 10, 20, status: 'ABD', homeGoals: 2, awayGoals: 1),
                ],
            ]),
        ]);

        $this->artisan('api-football:sync-fixtures --force')
            ->assertSuccessful();

        $this->assertDatabaseHas('matches', [
            'api_provider' => 'api-football',
            'api_fixture_id' => 1005,
            'api_status' => 'ABD',
            'status' => TournamentMatch::STATUS_SCHEDULED,
            'team_a_score' => null,
            'team_b_score' => null,
        ]);
    }

    public function test_command_makes_only_expected_api_request_with_overrides(): void
    {
        $this->createTournament();
        $this->configureApiFootball();
        $this->createApiTeam(10, 'Argentina', 'ARG');
        $this->createApiTeam(20, 'Brazil', 'BRA');
        $this->fakeFixturesResponse();

        $this->artisan('api-football:sync-fixtures --season=2022 --league=1 --force')
            ->assertSuccessful();

        Http::assertSentCount(1);
        Http::assertSent(fn (Request $request): bool => $request->url() === 'https://example.test/fixtures?league=1&season=2022'
            && $request->hasHeader('x-apisports-key', 'fake-api-key'));
    }

    private function configureApiFootball(): void
    {
        config([
            'services.api_football.base_url' => 'https://example.test',
            'services.api_football.key' => 'fake-api-key',
            'services.api_football.world_cup_league_id' => 1,
            'services.api_football.world_cup_season' => 2026,
            'services.api_football.allow_production_sync' => false,
        ]);
    }

    private function configureProtectedEnvironment(bool $allowProductionSync): void
    {
        $this->app->detectEnvironment(fn (): string => 'production');

        config([
            'app.env' => 'production',
            'app.mode' => 'live',
            'services.api_football.allow_production_sync' => $allowProductionSync,
        ]);
    }

    private function createTournament(): Tournament
    {
        return Tournament::factory()->create([
            'name' => 'FIFA World Cup 2026',
            'slug' => 'fifa-world-cup-2026',
            'year' => 2026,
        ]);
    }

    private function createApiTeam(int $apiTeamId, string $name, string $shortName): Team
    {
        return Team::factory()->create([
            'name' => $name,
            'short_name' => $shortName,
            'api_provider' => 'api-football',
            'api_team_id' => $apiTeamId,
        ]);
    }

    private function fakeFixturesResponse(): void
    {
        Http::fake([
            'https://example.test/fixtures*' => Http::response([
                'results' => 1,
                'response' => [
                    $this->fixturePayload(1001, 10, 20),
                ],
            ]),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function fixturePayload(
        int $fixtureId,
        int $homeTeamId,
        int $awayTeamId,
        string $date = '2026-06-11T20:00:00+00:00',
        string $status = 'NS',
        ?int $homeGoals = null,
        ?int $awayGoals = null,
        string $round = 'Group Stage - 1',
        array $score = [],
    ): array {
        return [
            'fixture' => [
                'id' => $fixtureId,
                'date' => $date,
                'venue' => [
                    'name' => 'MetLife Stadium',
                    'city' => 'East Rutherford',
                ],
                'status' => [
                    'short' => $status,
                ],
            ],
            'league' => [
                'id' => 1,
                'season' => 2026,
                'round' => $round,
            ],
            'teams' => [
                'home' => [
                    'id' => $homeTeamId,
                    'name' => 'Argentina',
                ],
                'away' => [
                    'id' => $awayTeamId,
                    'name' => 'Brazil',
                ],
            ],
            'goals' => [
                'home' => $homeGoals,
                'away' => $awayGoals,
            ],
            'score' => $score,
        ];
    }
}
