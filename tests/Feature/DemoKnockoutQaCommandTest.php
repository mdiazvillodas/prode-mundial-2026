<?php

namespace Tests\Feature;

use App\Models\Prediction;
use App\Models\PrivateLeague;
use App\Models\TournamentMatch;
use App\Models\User;
use Database\Seeders\StagingDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DemoKnockoutQaCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_staging_demo_seeder_creates_controlled_knockout_qa_matches(): void
    {
        $this->seed(StagingDemoSeeder::class);

        $rounds = TournamentMatch::query()
            ->where('round', 'like', 'E20 knockout QA%')
            ->pluck('round')
            ->all();

        $this->assertContains('E20 knockout QA open UX', $rounds);
        $this->assertContains('E20 knockout QA closed read-only', $rounds);
        $this->assertContains('E20 knockout QA FT 2-1', $rounds);
        $this->assertContains('E20 knockout QA AET 2-1', $rounds);
        $this->assertContains('E20 knockout QA PEN team A', $rounds);
        $this->assertContains('E20 knockout QA PEN team B', $rounds);
    }

    public function test_knockout_qa_simulation_refuses_production_live_environment(): void
    {
        $this->app->detectEnvironment(fn (): string => 'production');
        config([
            'app.env' => 'production',
            'app.mode' => 'live',
        ]);

        $this->artisan('demo:simulate-results', [
            '--scenario' => 'knockout-qa',
            '--force' => true,
        ])
            ->expectsOutputToContain('Refusing to simulate demo results outside local/testing/staging or when APP_MODE=live.')
            ->expectsOutputToContain('Current APP_ENV: production')
            ->expectsOutputToContain('Current APP_MODE: live')
            ->assertFailed();
    }

    public function test_open_knockout_ux_scenario_exists_and_is_predictable(): void
    {
        $this->seed(StagingDemoSeeder::class);

        $match = $this->matchByRound('E20 knockout QA open UX');

        $this->assertSame(TournamentMatch::STATUS_OPEN, $match->status);
        $this->assertSame(TournamentMatch::STAGE_ROUND_OF_16, $match->stage);
        $this->assertTrue($match->requiresQualifiedTeamPrediction());
        $this->assertTrue($match->isPredictable());
        $this->assertSame(2, $match->predictions()->count());
    }

    public function test_closed_knockout_read_only_scenario_has_submitted_qualified_prediction(): void
    {
        $this->seed(StagingDemoSeeder::class);

        $match = $this->matchByRound('E20 knockout QA closed read-only');

        $this->assertSame(TournamentMatch::STATUS_LOCKED, $match->status);
        $this->assertFalse($match->isPredictable());
        $this->assertSame(1, $match->predictions()->whereNotNull('predicted_qualified_team_id')->count());
    }

    public function test_finished_ft_and_aet_knockout_qa_scenarios_are_scored(): void
    {
        $this->seed(StagingDemoSeeder::class);
        $this->simulateKnockoutQa();

        $ft = $this->matchByRound('E20 knockout QA FT 2-1')->refresh();
        $aet = $this->matchByRound('E20 knockout QA AET 2-1')->refresh();

        $this->assertSame('FT', $ft->api_status);
        $this->assertSame(TournamentMatch::STATUS_FINISHED, $ft->status);
        $this->assertSame($ft->team_a_id, $ft->winner_team_id);
        $this->assertSame([
            'ana_demo' => 5,
            'juan_demo' => 2,
            'lucia_demo' => 0,
            'mariano_demo' => 8,
        ], $this->pointsByUsername($ft));

        $this->assertSame('AET', $aet->api_status);
        $this->assertSame(TournamentMatch::STATUS_FINISHED, $aet->status);
        $this->assertSame($aet->team_a_id, $aet->winner_team_id);
        $this->assertSame([
            'ana_demo' => 5,
            'mariano_demo' => 8,
        ], $this->pointsByUsername($aet));
    }

    public function test_finished_pen_team_a_knockout_qa_scenario_covers_expanded_matrix(): void
    {
        $this->seed(StagingDemoSeeder::class);
        $this->simulateKnockoutQa();

        $match = $this->matchByRound('E20 knockout QA PEN team A')->refresh();

        $this->assertSame('PEN', $match->api_status);
        $this->assertSame(TournamentMatch::STATUS_FINISHED, $match->status);
        $this->assertSame($match->team_a_id, $match->winner_team_id);
        $this->assertSame([
            'ana_demo' => 5,
            'diego_demo' => 2,
            'juan_demo' => 5,
            'lucia_demo' => 3,
            'mariano_demo' => 8,
            'sofia_demo' => 0,
        ], $this->pointsByUsername($match));
    }

    public function test_finished_pen_team_b_knockout_qa_scenario_scores_inverse_qualified_team(): void
    {
        $this->seed(StagingDemoSeeder::class);
        $this->simulateKnockoutQa();

        $match = $this->matchByRound('E20 knockout QA PEN team B')->refresh();

        $this->assertSame('PEN', $match->api_status);
        $this->assertSame(TournamentMatch::STATUS_FINISHED, $match->status);
        $this->assertSame($match->team_b_id, $match->winner_team_id);
        $this->assertSame([
            'ana_demo' => 5,
            'juan_demo' => 5,
            'mariano_demo' => 8,
        ], $this->pointsByUsername($match));
    }

    public function test_knockout_qa_simulation_is_idempotent(): void
    {
        $this->seed(StagingDemoSeeder::class);
        $this->simulateKnockoutQa();

        $firstRun = $this->qaPredictionSnapshot();

        $this->simulateKnockoutQa();

        $this->assertSame($firstRun, $this->qaPredictionSnapshot());
    }

    public function test_finished_match_consistency_checker_is_clean_after_knockout_qa_simulation(): void
    {
        $this->seed(StagingDemoSeeder::class);
        $this->simulateKnockoutQa();

        $this->artisan('prode:check-finished-matches')
            ->expectsOutputToContain('No finished-match inconsistencies found.')
            ->assertSuccessful();
    }

    public function test_knockout_qa_points_flow_into_global_and_private_leaderboards(): void
    {
        $this->seed(StagingDemoSeeder::class);
        $this->simulateKnockoutQa();

        $mariano = User::query()->where('username', 'mariano_demo')->firstOrFail();
        $league = PrivateLeague::query()->where('name', 'Liga Demo Palermo')->firstOrFail();

        $this->assertSame(47, (int) Prediction::query()
            ->where('user_id', $mariano->id)
            ->whereNotNull('points_awarded')
            ->sum('points_awarded'));

        $this->actingAs($mariano)
            ->get(route('leaderboard.index'))
            ->assertOk()
            ->assertSee('@mariano_demo')
            ->assertSee('47');

        $this->actingAs($mariano)
            ->get(route('private-leagues.show', $league))
            ->assertOk()
            ->assertSee('@mariano_demo')
            ->assertSee('47');
    }

    private function simulateKnockoutQa(): void
    {
        $this->artisan('demo:simulate-results', [
            '--scenario' => 'knockout-qa',
            '--force' => true,
        ])->assertSuccessful();
    }

    private function matchByRound(string $round): TournamentMatch
    {
        return TournamentMatch::query()
            ->where('round', $round)
            ->firstOrFail();
    }

    /**
     * @return array<string, int>
     */
    private function pointsByUsername(TournamentMatch $match): array
    {
        return Prediction::query()
            ->join('users', 'users.id', '=', 'predictions.user_id')
            ->where('predictions.match_id', $match->id)
            ->orderBy('users.username')
            ->pluck('predictions.points_awarded', 'users.username')
            ->map(fn (?int $points): int => (int) $points)
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function qaPredictionSnapshot(): array
    {
        return DB::table('predictions')
            ->join('matches', 'matches.id', '=', 'predictions.match_id')
            ->join('users', 'users.id', '=', 'predictions.user_id')
            ->where('matches.round', 'like', 'E20 knockout QA%')
            ->where('matches.status', TournamentMatch::STATUS_FINISHED)
            ->orderBy('matches.round')
            ->orderBy('users.username')
            ->get([
                'matches.round',
                'users.username',
                'predictions.status',
                'predictions.points_awarded',
            ])
            ->map(fn ($row): array => (array) $row)
            ->all();
    }
}
