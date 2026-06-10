<?php

namespace Tests\Feature;

use App\Models\LeagueMembership;
use App\Models\Prediction;
use App\Models\TournamentMatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrivateLeagueLeaderboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_private_league_leaderboard_shows_active_members_and_orders_by_scored_points(): void
    {
        $owner = User::factory()->create(['username' => 'owner_user']);
        $topMember = User::factory()->create(['name' => 'Top Member', 'username' => 'top_member']);
        $lowerMember = User::factory()->create(['name' => 'Lower Member', 'username' => 'lower_member']);
        $zeroMember = User::factory()->create(['username' => 'zero_member']);
        $league = $owner->ownedPrivateLeague()->create(['name' => 'Ranking Privado']);

        foreach ([$topMember, $lowerMember, $zeroMember] as $member) {
            $league->memberships()->create([
                'user_id' => $member->id,
                'status' => LeagueMembership::STATUS_ACTIVE,
                'joined_at' => now(),
            ]);
        }

        $this->scoredPredictionFor($topMember, 6);
        $this->scoredPredictionFor($topMember, 3);
        $this->scoredPredictionFor($lowerMember, 6);
        $this->scoredPredictionFor($owner, 0);

        $this->actingAs($owner)
            ->get(route('private-leagues.show', $league))
            ->assertOk()
            ->assertSeeInOrder(['Top Member', '@top_member', '9', 'Lower Member', '@lower_member', '6', '@owner_user', '0', '@zero_member', '0'])
            ->assertDontSee('Primer puesto')
            ->assertDontSee('Miembro activo');
    }

    public function test_private_league_leaderboard_excludes_removed_members(): void
    {
        $owner = User::factory()->create(['username' => 'owner_user']);
        $activeMember = User::factory()->create(['name' => 'Active Member', 'username' => 'active_member']);
        $removedMember = User::factory()->create(['name' => 'Removed Member', 'username' => 'removed_member']);
        $league = $owner->ownedPrivateLeague()->create(['name' => 'Sin Removidos']);

        $league->memberships()->create([
            'user_id' => $activeMember->id,
            'status' => LeagueMembership::STATUS_ACTIVE,
            'joined_at' => now(),
        ]);
        $league->memberships()->create([
            'user_id' => $removedMember->id,
            'status' => LeagueMembership::STATUS_REMOVED,
            'joined_at' => now(),
        ]);

        $this->scoredPredictionFor($removedMember, 6);
        $this->scoredPredictionFor($activeMember, 3);

        $this->actingAs($owner)
            ->get(route('private-leagues.show', $league))
            ->assertOk()
            ->assertSee('Active Member')
            ->assertSee('@active_member')
            ->assertDontSee('Removed Member')
            ->assertDontSee('@removed_member');
    }

    private function scoredPredictionFor(User $user, int $points): Prediction
    {
        return Prediction::factory()->create([
            'user_id' => $user->id,
            'match_id' => TournamentMatch::factory()->create()->id,
            'status' => Prediction::STATUS_SCORED,
            'points_awarded' => $points,
        ]);
    }
}
