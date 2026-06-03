<?php

namespace Tests\Feature;

use App\Models\LeagueAuditLog;
use App\Models\LeagueJoinRequest;
use App\Models\LeagueMembership;
use App\Models\PrivateLeague;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrivateLeagueFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_one_private_league(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('private-leagues.store'), [
                'name' => 'Amigos del Mundial',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('private_leagues', [
            'owner_id' => $user->id,
            'name' => 'Amigos del Mundial',
            'status' => PrivateLeague::STATUS_ACTIVE,
        ]);
    }

    public function test_second_owned_private_league_creation_attempt_is_blocked(): void
    {
        $user = User::factory()->create();
        $user->ownedPrivateLeague()->create(['name' => 'Primera liga']);

        $this->actingAs($user)
            ->post(route('private-leagues.store'), [
                'name' => 'Segunda liga',
            ])
            ->assertSessionHasErrors(['name']);

        $this->assertSame(1, PrivateLeague::where('owner_id', $user->id)->count());
    }

    public function test_duplicate_league_names_are_allowed_for_different_users(): void
    {
        $firstUser = User::factory()->create();
        $secondUser = User::factory()->create();

        $firstUser->ownedPrivateLeague()->create(['name' => 'La Banda']);
        $secondUser->ownedPrivateLeague()->create(['name' => 'La Banda']);

        $this->assertSame(2, PrivateLeague::where('name', 'La Banda')->count());
    }

    public function test_created_private_leagues_receive_unique_visible_codes(): void
    {
        $firstLeague = User::factory()->create()->ownedPrivateLeague()->create(['name' => 'Uno']);
        $secondLeague = User::factory()->create()->ownedPrivateLeague()->create(['name' => 'Dos']);

        $this->assertNotEmpty($firstLeague->code);
        $this->assertNotEmpty($secondLeague->code);
        $this->assertNotSame($firstLeague->code, $secondLeague->code);
    }

    public function test_owner_is_automatically_added_as_active_member(): void
    {
        $owner = User::factory()->create();
        $league = $owner->ownedPrivateLeague()->create(['name' => 'Mi Liga']);

        $this->assertDatabaseHas('league_memberships', [
            'private_league_id' => $league->id,
            'user_id' => $owner->id,
            'status' => LeagueMembership::STATUS_ACTIVE,
        ]);
    }

    public function test_guests_cannot_access_private_league_creation_or_search_routes(): void
    {
        $this->get(route('private-leagues.create'))->assertRedirect('/login');
        $this->get(route('private-leagues.search'))->assertRedirect('/login');
    }

    public function test_authenticated_users_can_search_private_leagues_by_name_and_visible_code(): void
    {
        $user = User::factory()->create();
        $league = User::factory()->create()->ownedPrivateLeague()->create([
            'name' => 'Copa Amigos',
        ]);

        $this->actingAs($user)
            ->get(route('private-leagues.search', ['q' => 'Copa']))
            ->assertOk()
            ->assertSee('Copa Amigos')
            ->assertSee($league->code);

        $this->actingAs($user)
            ->get(route('private-leagues.search', ['q' => strtolower($league->code)]))
            ->assertOk()
            ->assertSee('Copa Amigos')
            ->assertSee($league->code);
    }

    public function test_user_can_request_to_join_an_eligible_league(): void
    {
        $user = User::factory()->create();
        $league = User::factory()->create()->ownedPrivateLeague()->create(['name' => 'Liga Norte']);

        $this->actingAs($user)
            ->post(route('private-leagues.join-requests.store', $league))
            ->assertRedirect();

        $this->assertDatabaseHas('league_join_requests', [
            'private_league_id' => $league->id,
            'user_id' => $user->id,
            'status' => LeagueJoinRequest::STATUS_PENDING,
        ]);
    }

    public function test_duplicate_pending_join_requests_are_blocked(): void
    {
        $user = User::factory()->create();
        $league = User::factory()->create()->ownedPrivateLeague()->create(['name' => 'Liga Sur']);

        $this->actingAs($user)->post(route('private-leagues.join-requests.store', $league));
        $this->actingAs($user)->post(route('private-leagues.join-requests.store', $league));

        $this->assertSame(1, LeagueJoinRequest::where([
            'private_league_id' => $league->id,
            'user_id' => $user->id,
            'status' => LeagueJoinRequest::STATUS_PENDING,
        ])->count());
    }

    public function test_user_cannot_request_own_league_or_active_member_league(): void
    {
        $owner = User::factory()->create();
        $league = $owner->ownedPrivateLeague()->create(['name' => 'Propia']);

        $this->actingAs($owner)
            ->post(route('private-leagues.join-requests.store', $league))
            ->assertSessionHasErrors(['league']);

        $member = User::factory()->create();
        $league->memberships()->create([
            'user_id' => $member->id,
            'status' => LeagueMembership::STATUS_ACTIVE,
            'joined_at' => now(),
        ]);

        $this->actingAs($member)
            ->post(route('private-leagues.join-requests.store', $league))
            ->assertSessionHasErrors(['league']);
    }

    public function test_owner_can_accept_and_reject_join_requests(): void
    {
        $owner = User::factory()->create();
        $league = $owner->ownedPrivateLeague()->create(['name' => 'Aceptaciones']);
        $acceptedUser = User::factory()->create();
        $rejectedUser = User::factory()->create();

        $acceptedRequest = $league->joinRequests()->create([
            'user_id' => $acceptedUser->id,
            'status' => LeagueJoinRequest::STATUS_PENDING,
        ]);
        $rejectedRequest = $league->joinRequests()->create([
            'user_id' => $rejectedUser->id,
            'status' => LeagueJoinRequest::STATUS_PENDING,
        ]);

        $this->actingAs($owner)
            ->post(route('private-leagues.join-requests.accept', [$league, $acceptedRequest]))
            ->assertRedirect();

        $this->actingAs($owner)
            ->post(route('private-leagues.join-requests.reject', [$league, $rejectedRequest]))
            ->assertRedirect();

        $this->assertDatabaseHas('league_join_requests', [
            'id' => $acceptedRequest->id,
            'status' => LeagueJoinRequest::STATUS_ACCEPTED,
            'decided_by' => $owner->id,
        ]);
        $this->assertDatabaseHas('league_memberships', [
            'private_league_id' => $league->id,
            'user_id' => $acceptedUser->id,
            'status' => LeagueMembership::STATUS_ACTIVE,
        ]);
        $this->assertDatabaseHas('league_join_requests', [
            'id' => $rejectedRequest->id,
            'status' => LeagueJoinRequest::STATUS_REJECTED,
            'decided_by' => $owner->id,
        ]);
    }

    public function test_non_owner_cannot_accept_or_reject_join_requests(): void
    {
        $owner = User::factory()->create();
        $nonOwner = User::factory()->create();
        $league = $owner->ownedPrivateLeague()->create(['name' => 'Solo Owner']);
        $joinRequest = $league->joinRequests()->create([
            'user_id' => User::factory()->create()->id,
            'status' => LeagueJoinRequest::STATUS_PENDING,
        ]);

        $this->actingAs($nonOwner)
            ->post(route('private-leagues.join-requests.accept', [$league, $joinRequest]))
            ->assertForbidden();

        $this->actingAs($nonOwner)
            ->post(route('private-leagues.join-requests.reject', [$league, $joinRequest]))
            ->assertForbidden();
    }

    public function test_accepted_request_activates_existing_removed_membership_when_pending_request_exists(): void
    {
        $owner = User::factory()->create();
        $user = User::factory()->create();
        $league = $owner->ownedPrivateLeague()->create(['name' => 'Reactivar']);
        $league->memberships()->create([
            'user_id' => $user->id,
            'status' => LeagueMembership::STATUS_REMOVED,
            'joined_at' => now()->subDay(),
        ]);
        $joinRequest = $league->joinRequests()->create([
            'user_id' => $user->id,
            'status' => LeagueJoinRequest::STATUS_PENDING,
        ]);

        $this->actingAs($owner)
            ->post(route('private-leagues.join-requests.accept', [$league, $joinRequest]))
            ->assertRedirect();

        $this->assertDatabaseHas('league_memberships', [
            'private_league_id' => $league->id,
            'user_id' => $user->id,
            'status' => LeagueMembership::STATUS_ACTIVE,
        ]);
    }

    public function test_user_cannot_exceed_three_active_private_league_memberships(): void
    {
        $user = User::factory()->create();
        $owners = User::factory()->count(4)->create();

        foreach ($owners as $owner) {
            $league = $owner->ownedPrivateLeague()->create(['name' => 'Liga Limite']);

            if ($owners->search($owner) < 3) {
                $league->memberships()->create([
                    'user_id' => $user->id,
                    'status' => LeagueMembership::STATUS_ACTIVE,
                    'joined_at' => now(),
                ]);
            }
        }

        $targetLeague = $owners[3]->ownedPrivateLeague;

        $this->actingAs($user)
            ->post(route('private-leagues.join-requests.store', $targetLeague))
            ->assertSessionHasErrors(['league']);

        $this->assertDatabaseMissing('league_join_requests', [
            'private_league_id' => $targetLeague->id,
            'user_id' => $user->id,
            'status' => LeagueJoinRequest::STATUS_PENDING,
        ]);
    }

    public function test_owner_and_active_members_can_view_detail_but_non_members_cannot(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $nonMember = User::factory()->create();
        $league = $owner->ownedPrivateLeague()->create(['name' => 'Detalle']);
        $league->memberships()->create([
            'user_id' => $member->id,
            'status' => LeagueMembership::STATUS_ACTIVE,
            'joined_at' => now(),
        ]);

        $this->actingAs($owner)->get(route('private-leagues.show', $league))->assertOk();
        $this->actingAs($member)->get(route('private-leagues.show', $league))->assertOk();
        $this->actingAs($nonMember)->get(route('private-leagues.show', $league))->assertForbidden();
    }

    public function test_invitation_link_page_handles_owner_member_pending_and_new_requester_states(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $pendingUser = User::factory()->create();
        $newRequester = User::factory()->create();
        $league = $owner->ownedPrivateLeague()->create(['name' => 'Invitacion']);
        $league->memberships()->create([
            'user_id' => $member->id,
            'status' => LeagueMembership::STATUS_ACTIVE,
            'joined_at' => now(),
        ]);
        $league->joinRequests()->create([
            'user_id' => $pendingUser->id,
            'status' => LeagueJoinRequest::STATUS_PENDING,
        ]);

        $url = route('private-leagues.invite', $league->code);

        $this->actingAs($owner)->get($url)->assertOk()->assertSee('Esta es tu liga');
        $this->actingAs($member)->get($url)->assertOk()->assertSee('Ya sos miembro activo');
        $this->actingAs($pendingUser)->get($url)->assertOk()->assertSee('solicitud de ingreso pendiente');
        $this->actingAs($newRequester)->get($url)->assertOk()->assertSee('Solicitar ingreso');
    }

    public function test_owner_can_remove_member_and_removal_is_audited(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $league = $owner->ownedPrivateLeague()->create(['name' => 'Auditoria']);
        $league->memberships()->create([
            'user_id' => $member->id,
            'status' => LeagueMembership::STATUS_ACTIVE,
            'joined_at' => now()->subDay(),
        ]);

        $this->actingAs($owner)
            ->delete(route('private-leagues.members.remove', [$league, $member]))
            ->assertRedirect();

        $this->assertDatabaseHas('league_memberships', [
            'private_league_id' => $league->id,
            'user_id' => $member->id,
            'status' => LeagueMembership::STATUS_REMOVED,
        ]);
        $this->assertDatabaseHas('league_audit_logs', [
            'private_league_id' => $league->id,
            'actor_user_id' => $owner->id,
            'target_user_id' => $member->id,
            'action' => LeagueAuditLog::ACTION_MEMBER_REMOVED,
        ]);
        $this->actingAs($member)->get(route('private-leagues.show', $league))->assertForbidden();
    }

    public function test_owner_cannot_remove_themselves_and_non_owner_cannot_remove_members(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $nonOwner = User::factory()->create();
        $league = $owner->ownedPrivateLeague()->create(['name' => 'Remocion']);
        $league->memberships()->create([
            'user_id' => $member->id,
            'status' => LeagueMembership::STATUS_ACTIVE,
            'joined_at' => now(),
        ]);

        $this->actingAs($owner)
            ->delete(route('private-leagues.members.remove', [$league, $owner]))
            ->assertSessionHasErrors(['member']);

        $this->actingAs($nonOwner)
            ->delete(route('private-leagues.members.remove', [$league, $member]))
            ->assertForbidden();

        $this->assertDatabaseHas('league_memberships', [
            'private_league_id' => $league->id,
            'user_id' => $member->id,
            'status' => LeagueMembership::STATUS_ACTIVE,
        ]);
    }
}
