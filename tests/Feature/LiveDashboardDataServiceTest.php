<?php

namespace Tests\Feature;

use App\Models\LeagueMembership;
use App\Models\Prediction;
use App\Models\PrivateLeague;
use App\Models\Team;
use App\Models\Tournament;
use App\Models\TournamentMatch;
use App\Models\User;
use App\Services\Dashboard\LiveDashboardDataService;
use App\Services\PredictionScoringService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class LiveDashboardDataServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-06-10 12:00:00', 'UTC'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_pending_predictions_returns_only_missing_matches_from_nearest_local_day(): void
    {
        [$tournament, $teams] = $this->seedTournamentAndTeams();
        $user = User::factory()->create();

        $predictedMatch = $this->match($tournament, $teams['ARG'], $teams['USA'], '2026-06-17 01:00:00');
        $missingSameLocalDay = $this->match($tournament, $teams['BRA'], $teams['ESP'], '2026-06-17 19:00:00');
        $missingFutureDay = $this->match($tournament, $teams['FRA'], $teams['MEX'], '2026-06-18 18:00:00');

        Prediction::factory()->create([
            'user_id' => $user->id,
            'match_id' => $predictedMatch->id,
            'team_a_score' => 2,
            'team_b_score' => 0,
        ]);

        $data = app(LiveDashboardDataService::class)->forUser($user, 'Europe/Madrid');

        $this->assertSame('2026-06-17', $data['pending_predictions']['local_date']);
        $this->assertSame([$missingSameLocalDay->id], collect($data['pending_predictions']['matches'])->pluck('id')->all());
        $this->assertStringContainsString('date=2026-06-17', $data['pending_predictions']['prediction_url']);
        $this->assertStringContainsString('tz=Europe%2FMadrid', $data['pending_predictions']['prediction_url']);
        $this->assertFalse(collect($data['pending_predictions']['matches'])->pluck('id')->contains($missingFutureDay->id));
    }

    public function test_pending_predictions_returns_null_when_user_has_no_missing_predictions(): void
    {
        [$tournament, $teams] = $this->seedTournamentAndTeams();
        $user = User::factory()->create();
        $match = $this->match($tournament, $teams['ARG'], $teams['USA'], '2026-06-17 18:00:00');

        Prediction::factory()->create([
            'user_id' => $user->id,
            'match_id' => $match->id,
        ]);

        $data = app(LiveDashboardDataService::class)->forUser($user, 'UTC');

        $this->assertNull($data['pending_predictions']);
    }

    public function test_liveish_matches_return_provisional_prediction_states(): void
    {
        [$tournament, $teams] = $this->seedTournamentAndTeams();
        $user = User::factory()->create();

        $exact = $this->liveMatch($tournament, $teams['ARG'], $teams['USA'], 1, 0, '1H');
        $trend = $this->liveMatch($tournament, $teams['BRA'], $teams['ESP'], 2, 1, '2H');
        $incorrect = $this->liveMatch($tournament, $teams['FRA'], $teams['MEX'], 0, 2, 'LIVE');
        $none = $this->liveMatch($tournament, $teams['GER'], $teams['JPN'], 0, 0, 'HT');

        Prediction::factory()->create(['user_id' => $user->id, 'match_id' => $exact->id, 'team_a_score' => 1, 'team_b_score' => 0]);
        Prediction::factory()->create(['user_id' => $user->id, 'match_id' => $trend->id, 'team_a_score' => 3, 'team_b_score' => 1]);
        Prediction::factory()->create(['user_id' => $user->id, 'match_id' => $incorrect->id, 'team_a_score' => 1, 'team_b_score' => 0]);

        $states = collect(app(LiveDashboardDataService::class)->forUser($user, 'UTC')['live_matches'])
            ->pluck('provisional_state', 'id');

        $this->assertSame('exact', $states[$exact->id]);
        $this->assertSame('trend', $states[$trend->id]);
        $this->assertSame('incorrect', $states[$incorrect->id]);
        $this->assertSame('none', $states[$none->id]);
    }

    public function test_friend_activity_deduplicates_users_sorts_by_completion_and_hides_prediction_values(): void
    {
        [$tournament, $teams] = $this->seedTournamentAndTeams();
        $user = User::factory()->create(['username' => 'current']);
        $full = User::factory()->create(['name' => 'Full Friend', 'username' => 'full_friend']);
        $partial = User::factory()->create(['name' => 'Partial Friend', 'username' => 'partial_friend']);
        $none = User::factory()->create(['name' => 'None Friend', 'username' => 'none_friend']);
        $otherOwner = User::factory()->create(['name' => 'Other Owner', 'username' => 'other_owner']);

        $leagueA = $this->privateLeague($user, 'Liga A');
        $leagueB = $this->privateLeague($otherOwner, 'Liga B');

        $this->addMember($leagueA, $full);
        $this->addMember($leagueA, $partial);
        $this->addMember($leagueA, $none);
        $this->addMember($leagueB, $user);
        $this->addMember($leagueB, $full);

        $matches = [
            $this->match($tournament, $teams['ARG'], $teams['USA'], '2026-06-17 18:00:00'),
            $this->match($tournament, $teams['BRA'], $teams['ESP'], '2026-06-17 21:00:00'),
            $this->match($tournament, $teams['FRA'], $teams['MEX'], '2026-06-17 23:00:00'),
        ];

        foreach ($matches as $match) {
            Prediction::factory()->create(['user_id' => $full->id, 'match_id' => $match->id, 'team_a_score' => 1, 'team_b_score' => 0]);
        }

        Prediction::factory()->create(['user_id' => $partial->id, 'match_id' => $matches[0]->id, 'team_a_score' => 2, 'team_b_score' => 1]);

        $friends = app(LiveDashboardDataService::class)->forUser($user, 'UTC')['friend_activity']['friends'];

        $this->assertSame([$full->id, $partial->id, $none->id, $otherOwner->id], collect($friends)->pluck('id')->all());
        $this->assertSame([3, 1, 0, 0], collect($friends)->pluck('completed_count')->all());
        $this->assertSame([3, 3, 3, 3], collect($friends)->pluck('total_matches')->all());
        $this->assertArrayNotHasKey('predictions', $friends[0]);
        $this->assertArrayNotHasKey('team_a_score', $friends[0]);
    }

    public function test_goal_averages_are_calculated_from_finished_matches(): void
    {
        [$tournament, $teams] = $this->seedTournamentAndTeams();
        $user = User::factory()->create();

        $this->finishedMatch($tournament, $teams['ARG'], $teams['USA'], 2, 0);
        $this->finishedMatch($tournament, $teams['ARG'], $teams['BRA'], 1, 1);

        $pending = $this->match($tournament, $teams['ARG'], $teams['ESP'], '2026-06-17 18:00:00');

        $match = collect(app(LiveDashboardDataService::class)->forUser($user, 'UTC')['pending_predictions']['matches'])
            ->firstWhere('id', $pending->id);

        $this->assertSame(1.5, $match['team_a']['goals_for_avg']);
        $this->assertSame(0.5, $match['team_a']['goals_against_avg']);
        $this->assertSame(2, $match['team_a']['finished_matches_count']);
        $this->assertNull($match['team_b']['goals_for_avg']);
        $this->assertNull($match['team_b']['goals_against_avg']);
    }

    public function test_compact_league_summary_preserves_existing_ranking_order(): void
    {
        [$tournament, $teams] = $this->seedTournamentAndTeams();
        $user = User::factory()->create(['username' => 'current']);
        $leader = User::factory()->create(['username' => 'leader']);
        $league = $this->privateLeague($user, 'Liga Ranking');
        $this->addMember($league, $leader);

        $finished = $this->finishedMatch($tournament, $teams['ARG'], $teams['USA'], 2, 0);

        Prediction::factory()->create([
            'user_id' => $leader->id,
            'match_id' => $finished->id,
            'team_a_score' => 2,
            'team_b_score' => 0,
            'points_awarded' => PredictionScoringService::POINTS_EXACT_RESULT,
            'status' => Prediction::STATUS_SCORED,
        ]);
        Prediction::factory()->create([
            'user_id' => $user->id,
            'match_id' => $finished->id,
            'team_a_score' => 1,
            'team_b_score' => 0,
            'points_awarded' => PredictionScoringService::POINTS_CORRECT_OUTCOME,
            'status' => Prediction::STATUS_SCORED,
        ]);

        $summary = app(LiveDashboardDataService::class)->forUser($user, 'UTC')['league_summary'];

        $this->assertSame(2, $summary['general']['position']);
        $this->assertSame(3, $summary['general']['points']);
        $this->assertSame('Liga Ranking', $summary['private_leagues'][0]['name']);
        $this->assertSame(2, $summary['private_leagues'][0]['position']);
        $this->assertSame(3, $summary['private_leagues'][0]['points']);
    }

    public function test_pending_prediction_local_date_uses_requested_timezone(): void
    {
        [$tournament, $teams] = $this->seedTournamentAndTeams();
        $user = User::factory()->create();

        $this->match($tournament, $teams['ARG'], $teams['USA'], '2026-06-16 23:30:00');

        $data = app(LiveDashboardDataService::class)->forUser($user, 'Europe/Madrid');

        $this->assertSame('2026-06-17', $data['pending_predictions']['local_date']);
        $this->assertSame('01:30', $data['pending_predictions']['matches'][0]['kickoff_local_time']);
        $this->assertSame('01:25', $data['pending_predictions']['matches'][0]['prediction_closes_local_time']);
    }

    /**
     * @return array{0: Tournament, 1: array<string, Team>}
     */
    private function seedTournamentAndTeams(): array
    {
        $tournament = Tournament::factory()->create([
            'slug' => 'world-cup-2026-test',
        ]);

        $teams = collect([
            'ARG' => 'Argentina',
            'USA' => 'United States',
            'BRA' => 'Brazil',
            'ESP' => 'Spain',
            'FRA' => 'France',
            'MEX' => 'Mexico',
            'GER' => 'Germany',
            'JPN' => 'Japan',
        ])->mapWithKeys(fn (string $name, string $code): array => [
            $code => Team::factory()->create([
                'name' => $name,
                'short_name' => $code,
                'country_code' => $code,
                'flag_path' => 'flags/'.strtolower($code).'.svg',
            ]),
        ])->all();

        return [$tournament, $teams];
    }

    private function match(Tournament $tournament, Team $teamA, Team $teamB, string $startsAt): TournamentMatch
    {
        $startsAt = Carbon::parse($startsAt, 'UTC');

        return TournamentMatch::factory()->create([
            'tournament_id' => $tournament->id,
            'team_a_id' => $teamA->id,
            'team_b_id' => $teamB->id,
            'starts_at' => $startsAt,
            'prediction_closes_at' => $startsAt->copy()->subMinutes(5),
            'stage' => 'group',
            'group' => 'A',
            'status' => TournamentMatch::STATUS_OPEN,
        ]);
    }

    private function liveMatch(Tournament $tournament, Team $teamA, Team $teamB, int $scoreA, int $scoreB, string $apiStatus): TournamentMatch
    {
        $startsAt = Carbon::parse('2026-06-10 11:00:00', 'UTC');

        return TournamentMatch::factory()->create([
            'tournament_id' => $tournament->id,
            'team_a_id' => $teamA->id,
            'team_b_id' => $teamB->id,
            'starts_at' => $startsAt,
            'prediction_closes_at' => $startsAt->copy()->subMinutes(5),
            'stage' => 'group',
            'status' => TournamentMatch::STATUS_LOCKED,
            'team_a_score' => $scoreA,
            'team_b_score' => $scoreB,
            'api_status' => $apiStatus,
            'last_synced_at' => Carbon::parse('2026-06-10 11:55:00', 'UTC'),
        ]);
    }

    private function finishedMatch(Tournament $tournament, Team $teamA, Team $teamB, int $scoreA, int $scoreB): TournamentMatch
    {
        return TournamentMatch::factory()->create([
            'tournament_id' => $tournament->id,
            'team_a_id' => $teamA->id,
            'team_b_id' => $teamB->id,
            'starts_at' => Carbon::parse('2026-06-09 18:00:00', 'UTC'),
            'prediction_closes_at' => Carbon::parse('2026-06-09 17:55:00', 'UTC'),
            'stage' => 'group',
            'status' => TournamentMatch::STATUS_FINISHED,
            'team_a_score' => $scoreA,
            'team_b_score' => $scoreB,
            'winner_team_id' => $scoreA > $scoreB ? $teamA->id : ($scoreB > $scoreA ? $teamB->id : null),
        ]);
    }

    private function privateLeague(User $owner, string $name): PrivateLeague
    {
        return PrivateLeague::query()->create([
            'owner_id' => $owner->id,
            'name' => $name,
            'code' => str($name)->slug()->upper()->substr(0, 8)->toString(),
            'status' => PrivateLeague::STATUS_ACTIVE,
        ]);
    }

    private function addMember(PrivateLeague $league, User $user): void
    {
        LeagueMembership::query()->updateOrCreate(
            ['private_league_id' => $league->id, 'user_id' => $user->id],
            ['status' => LeagueMembership::STATUS_ACTIVE, 'joined_at' => now()],
        );
    }
}
