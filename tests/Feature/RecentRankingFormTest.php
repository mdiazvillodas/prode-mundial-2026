<?php

namespace Tests\Feature;

use App\Models\LeagueMembership;
use App\Models\Prediction;
use App\Models\PrivateLeague;
use App\Models\Team;
use App\Models\Tournament;
use App\Models\TournamentMatch;
use App\Models\User;
use App\Services\PredictionScoringService;
use App\Services\Rankings\RecentFormService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RecentRankingFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_recent_form_uses_same_latest_finished_match_sequence_for_all_users(): void
    {
        [$users, $matches] = $this->seedRankingScenario();

        $entries = $this->rankingEntries([$users['leader'], $users['chaser']]);
        $withForm = app(RecentFormService::class)->attachToEntries($entries);

        $expectedMatchIds = collect($matches)->slice(1)->pluck('id')->values()->all();

        $this->assertSame($expectedMatchIds, collect($withForm[0]->recent_form)->pluck('match_id')->all());
        $this->assertSame($expectedMatchIds, collect($withForm[1]->recent_form)->pluck('match_id')->all());
    }

    public function test_recent_form_states_are_computed_without_prediction_values(): void
    {
        [$users] = $this->seedRankingScenario();

        $entry = app(RecentFormService::class)
            ->attachToEntries($this->rankingEntries([$users['leader']]))
            ->first();

        $this->assertSame([
            RecentFormService::STATE_EXACT,
            RecentFormService::STATE_TREND,
            RecentFormService::STATE_INCORRECT,
            RecentFormService::STATE_NONE,
            RecentFormService::STATE_EXACT,
        ], collect($entry->recent_form)->pluck('state')->all());

        $this->assertArrayNotHasKey('team_a_score', $entry->recent_form[0]);
        $this->assertArrayNotHasKey('team_b_score', $entry->recent_form[0]);
    }

    public function test_ranking_order_remains_unchanged_when_form_is_attached(): void
    {
        [$users] = $this->seedRankingScenario();

        $entries = $this->rankingEntries([$users['leader'], $users['chaser']]);
        $withForm = app(RecentFormService::class)->attachToEntries($entries);

        $this->assertSame(
            $entries->pluck('id')->all(),
            $withForm->pluck('id')->all(),
        );
    }

    public function test_general_and_private_ranking_pages_render_recent_form_without_prediction_values(): void
    {
        [$users] = $this->seedRankingScenario();
        $league = $this->privateLeague($users['leader'], 'Liga Forma');
        $this->addMember($league, $users['chaser']);
        $this->addMember($league, $users['removed'], LeagueMembership::STATUS_REMOVED);

        $this->actingAs($users['leader'])
            ->get(route('leagues.index'))
            ->assertOk()
            ->assertSee('Racha reciente')
            ->assertSee('Exacto')
            ->assertSee('Tendencia')
            ->assertSee('Incorrecto')
            ->assertSee('Sin pronóstico')
            ->assertSee('Leader User')
            ->assertSee('@leader')
            ->assertSee('Chaser User')
            ->assertSee('@chaser')
            ->assertDontSee('4-3');

        $this->actingAs($users['leader'])
            ->get(route('private-leagues.show', $league))
            ->assertOk()
            ->assertSee('Racha reciente')
            ->assertSee('Leader User')
            ->assertSee('@leader')
            ->assertSee('Chaser User')
            ->assertSee('@chaser')
            ->assertDontSee('Removed User')
            ->assertDontSee('@removed')
            ->assertDontSee('4-3');

        $this->actingAs($users['leader'])
            ->get(route('leaderboard.index'))
            ->assertOk()
            ->assertSee('Racha reciente')
            ->assertDontSee('4-3');
    }

    /**
     * @return array{0: array<string, User>, 1: \Illuminate\Support\Collection<int, TournamentMatch>}
     */
    private function seedRankingScenario(): array
    {
        $tournament = Tournament::factory()->create();
        $teamA = Team::factory()->create(['name' => 'Argentina', 'short_name' => 'ARG']);
        $teamB = Team::factory()->create(['name' => 'Brazil', 'short_name' => 'BRA']);

        $users = [
            'leader' => User::factory()->create(['name' => 'Leader User', 'username' => 'leader']),
            'chaser' => User::factory()->create(['name' => 'Chaser User', 'username' => 'chaser']),
            'removed' => User::factory()->create(['name' => 'Removed User', 'username' => 'removed']),
        ];

        $matches = collect(range(0, 5))->map(fn (int $index): TournamentMatch => $this->finishedMatch(
            $tournament,
            $teamA,
            $teamB,
            Carbon::parse('2026-06-10 18:00:00', 'UTC')->addDays($index),
        ));

        $latestFive = $matches->slice(1)->values();

        $this->scoredPrediction($users['leader'], $matches[0], PredictionScoringService::POINTS_EXACT_RESULT, 4, 3);
        $this->scoredPrediction($users['leader'], $latestFive[0], PredictionScoringService::POINTS_EXACT_RESULT, 4, 3);
        $this->scoredPrediction($users['leader'], $latestFive[1], PredictionScoringService::POINTS_CORRECT_OUTCOME, 2, 1);
        $this->scoredPrediction($users['leader'], $latestFive[2], 0, 0, 2);
        $this->scoredPrediction($users['leader'], $latestFive[4], PredictionScoringService::POINTS_EXACT_RESULT, 1, 1);

        foreach ($latestFive as $match) {
            $this->scoredPrediction($users['chaser'], $match, PredictionScoringService::POINTS_CORRECT_OUTCOME, 1, 0);
        }

        $this->scoredPrediction($users['removed'], $latestFive[0], PredictionScoringService::POINTS_EXACT_RESULT, 3, 0);

        return [$users, $matches];
    }

    /**
     * @param  array<int, User>  $users
     */
    private function rankingEntries(array $users)
    {
        return DB::table('users')
            ->leftJoin('predictions', function ($join): void {
                $join->on('predictions.user_id', '=', 'users.id')
                    ->whereNotNull('predictions.points_awarded');
            })
            ->whereIn('users.id', collect($users)->pluck('id')->all())
            ->select([
                'users.id',
                'users.name',
                'users.username',
                'users.profile_avatar_key',
                DB::raw('COALESCE(SUM(predictions.points_awarded), 0) as total_points'),
                DB::raw('SUM(CASE WHEN predictions.points_awarded = '.PredictionScoringService::POINTS_EXACT_RESULT.' THEN 1 ELSE 0 END) as exact_results_count'),
                DB::raw('SUM(CASE WHEN predictions.points_awarded = '.PredictionScoringService::POINTS_CORRECT_OUTCOME.' THEN 1 ELSE 0 END) as trend_count'),
                DB::raw('COUNT(predictions.id) as scored_predictions_count'),
            ])
            ->groupBy('users.id', 'users.name', 'users.username', 'users.profile_avatar_key')
            ->orderByDesc('total_points')
            ->orderByDesc('exact_results_count')
            ->orderByDesc('trend_count')
            ->orderBy('users.username')
            ->get();
    }

    private function finishedMatch(Tournament $tournament, Team $teamA, Team $teamB, Carbon $startsAt): TournamentMatch
    {
        return TournamentMatch::factory()->create([
            'tournament_id' => $tournament->id,
            'team_a_id' => $teamA->id,
            'team_b_id' => $teamB->id,
            'starts_at' => $startsAt,
            'prediction_closes_at' => $startsAt->copy()->subMinutes(5),
            'status' => TournamentMatch::STATUS_FINISHED,
            'team_a_score' => 2,
            'team_b_score' => 1,
            'winner_team_id' => $teamA->id,
        ]);
    }

    private function scoredPrediction(User $user, TournamentMatch $match, int $points, int $scoreA, int $scoreB): void
    {
        Prediction::factory()->create([
            'user_id' => $user->id,
            'match_id' => $match->id,
            'team_a_score' => $scoreA,
            'team_b_score' => $scoreB,
            'status' => Prediction::STATUS_SCORED,
            'points_awarded' => $points,
        ]);
    }

    private function privateLeague(User $owner, string $name): PrivateLeague
    {
        return PrivateLeague::query()->create([
            'owner_id' => $owner->id,
            'name' => $name,
            'code' => 'FORM2026',
            'status' => PrivateLeague::STATUS_ACTIVE,
        ]);
    }

    private function addMember(PrivateLeague $league, User $user, string $status = LeagueMembership::STATUS_ACTIVE): void
    {
        LeagueMembership::query()->updateOrCreate(
            ['private_league_id' => $league->id, 'user_id' => $user->id],
            ['status' => $status, 'joined_at' => now()],
        );
    }
}
