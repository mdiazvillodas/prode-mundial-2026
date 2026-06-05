<?php

namespace Database\Seeders;

use App\Models\LeagueJoinRequest;
use App\Models\LeagueMembership;
use App\Models\Prediction;
use App\Models\PrivateLeague;
use App\Models\Team;
use App\Models\Tournament;
use App\Models\TournamentMatch;
use App\Models\User;
use App\Services\MatchPredictionSettlementService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

class StagingDemoSeeder extends Seeder
{
    public function run(): void
    {
        $tournament = $this->seedTournament();
        $teams = $this->seedTeams();
        $users = $this->seedUsers();
        $matches = $this->seedMatches($tournament, $teams);

        $this->seedPrivateLeagues($users);
        $this->seedPredictions($users, $matches, $teams);
    }

    private function seedTournament(): Tournament
    {
        return Tournament::query()->updateOrCreate(
            ['slug' => 'fifa-world-cup-2026'],
            [
                'name' => 'FIFA World Cup 2026',
                'year' => 2026,
                'starts_at' => '2026-06-11',
                'ends_at' => '2026-07-19',
                'status' => 'scheduled',
            ],
        );
    }

    /**
     * @return array<string, Team>
     */
    private function seedTeams(): array
    {
        $teams = [
            ['name' => 'Argentina', 'short_name' => 'ARG', 'country_code' => 'ARG', 'flag_path' => 'flags/arg.svg'],
            ['name' => 'Brazil', 'short_name' => 'BRA', 'country_code' => 'BRA', 'flag_path' => 'flags/bra.svg'],
            ['name' => 'France', 'short_name' => 'FRA', 'country_code' => 'FRA', 'flag_path' => 'flags/fra.svg'],
            ['name' => 'Spain', 'short_name' => 'ESP', 'country_code' => 'ESP', 'flag_path' => 'flags/esp.svg'],
            ['name' => 'Uruguay', 'short_name' => 'URU', 'country_code' => 'URY', 'flag_path' => 'flags/ury.svg'],
            ['name' => 'United States', 'short_name' => 'USA', 'country_code' => 'USA', 'flag_path' => 'flags/usa.svg'],
            ['name' => 'Germany', 'short_name' => 'GER', 'country_code' => 'GER', 'flag_path' => 'flags/ger.svg'],
            ['name' => 'Mexico', 'short_name' => 'MEX', 'country_code' => 'MEX', 'flag_path' => 'flags/mex.svg'],
            ['name' => 'England', 'short_name' => 'ENG', 'country_code' => 'ENG', 'flag_path' => 'flags/eng.svg'],
            ['name' => 'Japan', 'short_name' => 'JPN', 'country_code' => 'JPN', 'flag_path' => 'flags/jpn.svg'],
        ];

        $seeded = [];

        foreach ($teams as $team) {
            $seeded[$team['short_name']] = Team::query()->updateOrCreate(
                ['short_name' => $team['short_name']],
                $team,
            );
        }

        return $seeded;
    }

    /**
     * @return array<string, User>
     */
    private function seedUsers(): array
    {
        $users = [
            'admin' => [
                'name' => 'Demo Admin',
                'username' => 'demo_admin',
                'email' => 'admin@prode.test',
                'role' => User::ROLE_ADMIN,
            ],
            'mariano' => [
                'name' => 'Mariano Demo',
                'username' => 'mariano_demo',
                'email' => 'mariano@prode.test',
                'role' => User::ROLE_USER,
            ],
            'ana' => [
                'name' => 'Ana Demo',
                'username' => 'ana_demo',
                'email' => 'ana@prode.test',
                'role' => User::ROLE_USER,
            ],
            'juan' => [
                'name' => 'Juan Demo',
                'username' => 'juan_demo',
                'email' => 'juan@prode.test',
                'role' => User::ROLE_USER,
            ],
        ];

        $seeded = [];

        foreach ($users as $key => $user) {
            $seeded[$key] = User::query()->updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'username' => $user['username'],
                    'password' => 'password',
                    'role' => $user['role'],
                    'email_verified_at' => now(),
                ],
            );
        }

        return $seeded;
    }

    /**
     * @param  array<string, Team>  $teams
     * @return array<string, TournamentMatch>
     */
    private function seedMatches(Tournament $tournament, array $teams): array
    {
        $now = CarbonImmutable::now();

        $matches = [
            'arg_usa_open' => [
                'lookup' => ['stage' => 'group', 'group' => 'A', 'team_a_id' => $teams['ARG']->id, 'team_b_id' => $teams['USA']->id],
                'values' => ['starts_at' => $now->addDays(2)->setTime(18, 0), 'status' => TournamentMatch::STATUS_OPEN],
            ],
            'bra_esp_open' => [
                'lookup' => ['stage' => 'group', 'group' => 'B', 'team_a_id' => $teams['BRA']->id, 'team_b_id' => $teams['ESP']->id],
                'values' => ['starts_at' => $now->addDays(3)->setTime(21, 0), 'status' => TournamentMatch::STATUS_OPEN],
            ],
            'fra_uru_scheduled' => [
                'lookup' => ['stage' => 'group', 'group' => 'C', 'team_a_id' => $teams['FRA']->id, 'team_b_id' => $teams['URU']->id],
                'values' => ['starts_at' => $now->addDays(5)->setTime(16, 0), 'status' => TournamentMatch::STATUS_SCHEDULED],
            ],
            'ger_mex_locked' => [
                'lookup' => ['stage' => 'group', 'group' => 'D', 'team_a_id' => $teams['GER']->id, 'team_b_id' => $teams['MEX']->id],
                'values' => ['starts_at' => $now->addHour(), 'prediction_closes_at' => $now->subMinutes(10), 'status' => TournamentMatch::STATUS_LOCKED],
            ],
            'eng_jpn_closing' => [
                'lookup' => ['stage' => 'group', 'group' => 'E', 'team_a_id' => $teams['ENG']->id, 'team_b_id' => $teams['JPN']->id],
                'values' => ['starts_at' => $now->addMinutes(30), 'prediction_closes_at' => $now->addMinutes(25), 'status' => TournamentMatch::STATUS_OPEN],
            ],
            'arg_fra_finished' => [
                'lookup' => ['stage' => 'group', 'group' => 'A', 'team_a_id' => $teams['ARG']->id, 'team_b_id' => $teams['FRA']->id],
                'values' => ['starts_at' => $now->subDay()->setTime(20, 0), 'status' => TournamentMatch::STATUS_FINISHED, 'team_a_score' => 2, 'team_b_score' => 1, 'winner_team_id' => $teams['ARG']->id],
            ],
            'bra_uru_finished' => [
                'lookup' => ['stage' => 'group', 'group' => 'B', 'team_a_id' => $teams['BRA']->id, 'team_b_id' => $teams['URU']->id],
                'values' => ['starts_at' => $now->subDays(2)->setTime(19, 0), 'status' => TournamentMatch::STATUS_FINISHED, 'team_a_score' => 1, 'team_b_score' => 1, 'winner_team_id' => null],
            ],
            'esp_usa_knockout' => [
                'lookup' => ['stage' => 'round_of_16', 'group' => null, 'team_a_id' => $teams['ESP']->id, 'team_b_id' => $teams['USA']->id],
                'values' => ['starts_at' => $now->addDays(18)->setTime(21, 0), 'status' => TournamentMatch::STATUS_OPEN],
            ],
        ];

        $seeded = [];

        foreach ($matches as $key => $match) {
            $startsAt = $match['values']['starts_at'];
            $values = array_merge([
                'tournament_id' => $tournament->id,
                'prediction_closes_at' => $startsAt->subMinutes(5),
                'team_a_score' => null,
                'team_b_score' => null,
                'winner_team_id' => null,
            ], $match['values']);

            $seeded[$key] = TournamentMatch::query()->updateOrCreate(
                array_merge(['tournament_id' => $tournament->id], $match['lookup']),
                $values,
            );
        }

        $seeded['round_of_16_placeholder'] = TournamentMatch::query()->updateOrCreate(
            [
                'tournament_id' => $tournament->id,
                'stage' => 'round_of_16',
                'group' => null,
                'team_a_id' => null,
                'team_b_id' => null,
            ],
            [
                'starts_at' => $now->addDays(19)->setTime(18, 0),
                'prediction_closes_at' => null,
                'status' => TournamentMatch::STATUS_PLACEHOLDER,
                'team_a_score' => null,
                'team_b_score' => null,
                'winner_team_id' => null,
            ],
        );

        return $seeded;
    }

    /**
     * @param  array<string, User>  $users
     */
    private function seedPrivateLeagues(array $users): void
    {
        $league = PrivateLeague::query()->updateOrCreate(
            ['owner_id' => $users['mariano']->id],
            [
                'name' => 'Liga Demo Palermo',
                'code' => 'DEMO2026',
                'status' => PrivateLeague::STATUS_ACTIVE,
            ],
        );

        LeagueMembership::query()->updateOrCreate(
            ['private_league_id' => $league->id, 'user_id' => $users['mariano']->id],
            ['status' => LeagueMembership::STATUS_ACTIVE, 'joined_at' => now()],
        );

        LeagueMembership::query()->updateOrCreate(
            ['private_league_id' => $league->id, 'user_id' => $users['ana']->id],
            ['status' => LeagueMembership::STATUS_ACTIVE, 'joined_at' => now()],
        );

        LeagueJoinRequest::query()->updateOrCreate(
            [
                'private_league_id' => $league->id,
                'user_id' => $users['juan']->id,
                'status' => LeagueJoinRequest::STATUS_PENDING,
            ],
            [
                'decided_at' => null,
                'decided_by' => null,
            ],
        );
    }

    /**
     * @param  array<string, User>  $users
     * @param  array<string, TournamentMatch>  $matches
     * @param  array<string, Team>  $teams
     */
    private function seedPredictions(array $users, array $matches, array $teams): void
    {
        $submittedPredictions = [
            ['user' => 'mariano', 'match' => 'arg_usa_open', 'a' => 2, 'b' => 0],
            ['user' => 'ana', 'match' => 'arg_usa_open', 'a' => 1, 'b' => 1],
            ['user' => 'juan', 'match' => 'bra_esp_open', 'a' => 1, 'b' => 2],
            ['user' => 'mariano', 'match' => 'eng_jpn_closing', 'a' => 2, 'b' => 1],
            ['user' => 'ana', 'match' => 'esp_usa_knockout', 'a' => 1, 'b' => 0, 'qualified' => 'ESP'],
            ['user' => 'juan', 'match' => 'esp_usa_knockout', 'a' => 2, 'b' => 1, 'qualified' => 'ESP'],
        ];

        foreach ($submittedPredictions as $prediction) {
            $this->upsertPrediction(
                $users[$prediction['user']],
                $matches[$prediction['match']],
                $prediction['a'],
                $prediction['b'],
                Prediction::STATUS_SUBMITTED,
                null,
                isset($prediction['qualified']) ? $teams[$prediction['qualified']]->id : null,
            );
        }

        $scoredPredictions = [
            ['user' => 'mariano', 'match' => 'arg_fra_finished', 'a' => 2, 'b' => 1],
            ['user' => 'ana', 'match' => 'arg_fra_finished', 'a' => 1, 'b' => 0],
            ['user' => 'juan', 'match' => 'arg_fra_finished', 'a' => 0, 'b' => 2],
            ['user' => 'mariano', 'match' => 'bra_uru_finished', 'a' => 1, 'b' => 1],
            ['user' => 'ana', 'match' => 'bra_uru_finished', 'a' => 2, 'b' => 2],
            ['user' => 'juan', 'match' => 'bra_uru_finished', 'a' => 2, 'b' => 0],
        ];

        foreach ($scoredPredictions as $prediction) {
            $this->upsertPrediction(
                $users[$prediction['user']],
                $matches[$prediction['match']],
                $prediction['a'],
                $prediction['b'],
                Prediction::STATUS_SUBMITTED,
            );
        }

        app(MatchPredictionSettlementService::class)->score($matches['arg_fra_finished']->refresh());
        app(MatchPredictionSettlementService::class)->score($matches['bra_uru_finished']->refresh());
    }

    private function upsertPrediction(
        User $user,
        TournamentMatch $match,
        int $teamAScore,
        int $teamBScore,
        string $status,
        ?int $pointsAwarded = null,
        ?int $predictedQualifiedTeamId = null,
    ): Prediction {
        return Prediction::query()->updateOrCreate(
            ['user_id' => $user->id, 'match_id' => $match->id],
            [
                'team_a_score' => $teamAScore,
                'team_b_score' => $teamBScore,
                'predicted_qualified_team_id' => $predictedQualifiedTeamId,
                'status' => $status,
                'points_awarded' => $pointsAwarded,
            ],
        );
    }
}
