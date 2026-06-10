<?php

namespace Tests\Feature;

use App\Models\LeagueMembership;
use App\Models\Prediction;
use App\Models\TournamentMatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaderboardRankingRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_leaderboard_orders_by_points_exact_trends_and_username_tie_breaker(): void
    {
        $viewer = User::factory()->create();
        $pointsLeader = User::factory()->create(['username' => 'points_leader']);
        $exactTie = User::factory()->create(['username' => 'exact_tie']);
        $trendTie = User::factory()->create(['username' => 'trend_tie']);
        $alphaTie = User::factory()->create(['username' => 'alpha_tie']);
        $betaTie = User::factory()->create(['username' => 'beta_tie']);
        User::factory()->create(['username' => 'no_scored_predictions']);

        $this->scoredPrediction($pointsLeader, 6);
        $this->scoredPrediction($pointsLeader, 3);
        $this->scoredPrediction($exactTie, 6);
        $this->scoredPrediction($trendTie, 3);
        $this->scoredPrediction($trendTie, 3);
        $this->scoredPrediction($betaTie, 0);
        $this->scoredPrediction($alphaTie, 0);

        $this->actingAs($viewer)
            ->get(route('leaderboard.index'))
            ->assertOk()
            ->assertSeeInOrder([
                '@points_leader',
                '@exact_tie',
                '@trend_tie',
                '@alpha_tie',
                '@beta_tie',
            ])
            ->assertDontSee('@no_scored_predictions');
    }

    public function test_leagues_hub_renders_expected_general_and_private_ranking_order(): void
    {
        $owner = User::factory()->create(['username' => 'league_owner']);
        $globalLeader = User::factory()->create(['username' => 'global_leader']);
        $privateLeader = User::factory()->create(['username' => 'private_leader']);
        $privateSecond = User::factory()->create(['username' => 'private_second']);
        $removedMember = User::factory()->create(['username' => 'removed_member']);
        $league = $owner->ownedPrivateLeague()->create(['name' => 'Ranking QA']);

        foreach ([$privateLeader, $privateSecond] as $member) {
            $league->memberships()->create([
                'user_id' => $member->id,
                'status' => LeagueMembership::STATUS_ACTIVE,
                'joined_at' => now(),
            ]);
        }

        $league->memberships()->create([
            'user_id' => $removedMember->id,
            'status' => LeagueMembership::STATUS_REMOVED,
            'joined_at' => now(),
        ]);

        $this->scoredPrediction($globalLeader, 6);
        $this->scoredPrediction($globalLeader, 3);
        $this->scoredPrediction($privateLeader, 6);
        $this->scoredPrediction($privateSecond, 3);
        $this->scoredPrediction($removedMember, 6);

        $this->actingAs($owner)
            ->get(route('leagues.index'))
            ->assertOk()
            ->assertSee('Ranking QA')
            ->assertSeeInOrder(['@global_leader', '@private_leader', '@removed_member', '@private_second'])
            ->assertSeeInOrder(['@private_leader', '6', '@private_second', '3', '@league_owner', '0']);
    }

    public function test_private_league_detail_renders_expected_order_and_zero_prediction_members(): void
    {
        $owner = User::factory()->create(['username' => 'owner_rank']);
        $leader = User::factory()->create(['username' => 'leader_rank']);
        $exactTie = User::factory()->create(['username' => 'exact_rank']);
        $trendTie = User::factory()->create(['username' => 'trend_rank']);
        $zeroMember = User::factory()->create(['username' => 'zero_rank']);
        $removedMember = User::factory()->create(['username' => 'removed_rank']);
        $league = $owner->ownedPrivateLeague()->create(['name' => 'Detalle Ranking QA']);

        foreach ([$leader, $exactTie, $trendTie, $zeroMember] as $member) {
            $league->memberships()->create([
                'user_id' => $member->id,
                'status' => LeagueMembership::STATUS_ACTIVE,
                'joined_at' => now(),
            ]);
        }

        $league->memberships()->create([
            'user_id' => $removedMember->id,
            'status' => LeagueMembership::STATUS_REMOVED,
            'joined_at' => now(),
        ]);

        $this->scoredPrediction($leader, 6);
        $this->scoredPrediction($leader, 3);
        $this->scoredPrediction($exactTie, 6);
        $this->scoredPrediction($trendTie, 3);
        $this->scoredPrediction($trendTie, 3);
        $this->scoredPrediction($removedMember, 6);

        $this->actingAs($owner)
            ->get(route('private-leagues.show', $league))
            ->assertOk()
            ->assertSeeInOrder([
                '@leader_rank',
                '@exact_rank',
                '@trend_rank',
                '@owner_rank',
                '@zero_rank',
            ])
            ->assertDontSee('@removed_rank');
    }

    private function scoredPrediction(User $user, int $points): Prediction
    {
        return Prediction::factory()->create([
            'user_id' => $user->id,
            'match_id' => TournamentMatch::factory()->create([
                'status' => TournamentMatch::STATUS_FINISHED,
                'team_a_score' => 1,
                'team_b_score' => 0,
            ])->id,
            'status' => Prediction::STATUS_SCORED,
            'points_awarded' => $points,
        ]);
    }
}
