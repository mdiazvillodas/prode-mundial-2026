<?php

namespace Tests\Feature;

use App\Models\LeagueMembership;
use App\Models\Prediction;
use App\Models\PrivateLeague;
use App\Models\TournamentMatch;
use App\Models\User;
use Database\Seeders\StagingDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StagingDemoSeederEngagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_staging_demo_data_includes_avatar_choice_scenarios(): void
    {
        $this->seed(StagingDemoSeeder::class);

        $this->assertDatabaseHas('users', [
            'email' => 'sofia@prode.test',
            'profile_avatar_key' => null,
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'juan@prode.test',
            'profile_avatar_key' => 'default',
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'ana@prode.test',
            'profile_avatar_key' => 'avatar-01',
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'lucia@prode.test',
            'profile_avatar_key' => 'avatar-02',
        ]);
    }

    public function test_staging_demo_data_includes_private_league_with_multiple_active_members(): void
    {
        $this->seed(StagingDemoSeeder::class);

        $league = PrivateLeague::query()
            ->where('code', 'DEMO2026')
            ->firstOrFail();

        $this->assertSame(5, LeagueMembership::query()
            ->where('private_league_id', $league->id)
            ->where('status', LeagueMembership::STATUS_ACTIVE)
            ->count());
    }

    public function test_staging_demo_data_includes_next_match_day_completion_scenarios(): void
    {
        $this->seed(StagingDemoSeeder::class);

        $engagementMatches = TournamentMatch::query()
            ->where('round', 'E17 engagement next match day')
            ->orderBy('starts_at')
            ->get();

        $this->assertCount(4, $engagementMatches);

        $completionCounts = $this->completionCountsFor($engagementMatches->pluck('id')->all());

        $this->assertSame(2, $completionCounts['mariano@prode.test']);
        $this->assertSame(4, $completionCounts['ana@prode.test']);
        $this->assertSame(3, $completionCounts['juan@prode.test']);
        $this->assertSame(2, $completionCounts['lucia@prode.test']);
        $this->assertSame(0, $completionCounts['diego@prode.test']);
    }

    public function test_staging_demo_data_includes_liveish_and_finished_engagement_matches(): void
    {
        $this->seed(StagingDemoSeeder::class);

        $liveMatches = TournamentMatch::query()
            ->whereIn('api_status', ['1H', '2H', 'LIVE'])
            ->get();

        $this->assertCount(4, $liveMatches);
        $this->assertSame(4, $liveMatches->whereNotNull('team_a_score')->whereNotNull('team_b_score')->count());

        $mariano = User::query()->where('email', 'mariano@prode.test')->firstOrFail();

        $this->assertSame(3, Prediction::query()
            ->where('user_id', $mariano->id)
            ->whereIn('match_id', $liveMatches->pluck('id'))
            ->count());

        $this->assertSame(4, TournamentMatch::query()
            ->where('status', TournamentMatch::STATUS_FINISHED)
            ->whereHas('predictions', fn ($query) => $query->whereNotNull('points_awarded'))
            ->count());
    }

    /**
     * @param  array<int, int>  $matchIds
     * @return array<string, int>
     */
    private function completionCountsFor(array $matchIds): array
    {
        return User::query()
            ->whereIn('email', [
                'mariano@prode.test',
                'ana@prode.test',
                'juan@prode.test',
                'lucia@prode.test',
                'diego@prode.test',
            ])
            ->get()
            ->mapWithKeys(fn (User $user): array => [
                $user->email => Prediction::query()
                    ->where('user_id', $user->id)
                    ->whereIn('match_id', $matchIds)
                    ->count(),
            ])
            ->all();
    }
}
