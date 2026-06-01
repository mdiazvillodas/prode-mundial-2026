<?php

namespace Tests\Feature;

use App\Models\LeagueJoinRequest;
use App\Models\LeagueMembership;
use App\Models\PrivateLeague;
use App\Models\TournamentMatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HardenValidationAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_authenticated_prediction_routes(): void
    {
        $response = $this->get('/predictions');

        $response->assertRedirect('/login');
    }

    public function test_user_cannot_store_prediction_for_non_predictable_match(): void
    {
        $user = User::factory()->create();
        $match = TournamentMatch::factory()->state([
            'status' => TournamentMatch::STATUS_FINISHED,
        ])->create();

        $response = $this->actingAs($user)
            ->post("/matches/{$match->id}/prediction", [
                'team_a_score' => 1,
                'team_b_score' => 2,
            ]);

        $response->assertStatus(403);
    }

    public function test_user_cannot_bulk_store_predictions_for_non_predictable_match(): void
    {
        $user = User::factory()->create();
        $match = TournamentMatch::factory()->state([
            'status' => TournamentMatch::STATUS_FINISHED,
        ])->create();

        $response = $this->actingAs($user)
            ->from('/predictions')
            ->post('/predictions/bulk', [
                'predictions' => [
                    $match->id => [
                        'changed' => '1',
                        'team_a_score' => 1,
                        'team_b_score' => 2,
                    ],
                ],
            ]);

        $response->assertSessionHasErrors(["predictions.{$match->id}.team_a_score"]);
    }

    public function test_removed_user_cannot_request_private_league_access(): void
    {
        $owner = User::factory()->create();
        $user = User::factory()->create();

        $league = PrivateLeague::create([
            'owner_id' => $owner->id,
            'name' => 'Removed League',
            'status' => PrivateLeague::STATUS_ACTIVE,
            'code' => 'REMOVED01',
        ]);

        $league->memberships()->create([
            'user_id' => $user->id,
            'status' => LeagueMembership::STATUS_REMOVED,
            'joined_at' => now(),
        ]);

        $response = $this->actingAs($user)
            ->post("/private-leagues/{$league->id}/join-requests", []);

        $response->assertSessionHasErrors(['league']);
    }

    public function test_private_league_owner_cannot_request_access_to_own_league(): void
    {
        $owner = User::factory()->create();

        $league = PrivateLeague::create([
            'owner_id' => $owner->id,
            'name' => 'Owner League',
            'status' => PrivateLeague::STATUS_ACTIVE,
            'code' => 'OWNER001',
        ]);

        $response = $this->actingAs($owner)
            ->post("/private-leagues/{$league->id}/join-requests", []);

        $response->assertSessionHasErrors(['league']);
    }

    public function test_non_member_cannot_view_private_league_detail(): void
    {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();

        $league = PrivateLeague::create([
            'owner_id' => $owner->id,
            'name' => 'Restricted League',
            'status' => PrivateLeague::STATUS_ACTIVE,
            'code' => 'RESTRICT1',
        ]);

        $response = $this->actingAs($viewer)
            ->get("/private-leagues/{$league->id}");

        $response->assertStatus(403);
    }

    public function test_owner_and_active_member_can_view_private_league_detail(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();

        $league = PrivateLeague::create([
            'owner_id' => $owner->id,
            'name' => 'Member League',
            'status' => PrivateLeague::STATUS_ACTIVE,
            'code' => 'MEMBER01',
        ]);

        $league->memberships()->create([
            'user_id' => $member->id,
            'status' => LeagueMembership::STATUS_ACTIVE,
            'joined_at' => now(),
        ]);

        $ownerResponse = $this->actingAs($owner)
            ->get("/private-leagues/{$league->id}");
        $ownerResponse->assertOk();

        $memberResponse = $this->actingAs($member)
            ->get("/private-leagues/{$league->id}");
        $memberResponse->assertOk();
    }

    public function test_admin_routes_are_admin_only(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->admin()->create();

        $match = TournamentMatch::factory()->create();

        $nonAdminResponse = $this->actingAs($user)
            ->get("/admin/matches/{$match->id}/result");
        $nonAdminResponse->assertStatus(403);

        $adminResponse = $this->actingAs($admin)
            ->get("/admin/matches/{$match->id}/result");
        $adminResponse->assertOk();
    }

    public function test_admin_cannot_update_result_for_placeholder_match(): void
    {
        $admin = User::factory()->admin()->create();
        $match = TournamentMatch::factory()->placeholder()->create();

        $response = $this->actingAs($admin)
            ->from("/admin/matches/{$match->id}/result")
            ->post("/admin/matches/{$match->id}/result", [
                'team_a_score' => 1,
                'team_b_score' => 2,
            ]);

        $response->assertSessionHasErrors(['result']);
    }

    public function test_admin_result_update_validates_score_integers(): void
    {
        $admin = User::factory()->admin()->create();
        $match = TournamentMatch::factory()->create();

        $response = $this->actingAs($admin)
            ->from("/admin/matches/{$match->id}/result")
            ->post("/admin/matches/{$match->id}/result", [
                'team_a_score' => -1,
                'team_b_score' => 200,
            ]);

        $response->assertSessionHasErrors(['team_a_score', 'team_b_score']);
    }
}
