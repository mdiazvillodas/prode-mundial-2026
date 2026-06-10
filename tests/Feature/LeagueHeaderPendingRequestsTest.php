<?php

namespace Tests\Feature;

use App\Models\LeagueJoinRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeagueHeaderPendingRequestsTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_with_pending_requests_sees_header_alert_and_modal_details(): void
    {
        $owner = User::factory()->create();
        $league = $owner->ownedPrivateLeague()->create(['name' => 'Liga Header']);
        $firstRequester = User::factory()->create(['name' => 'Ana Soler', 'username' => 'ana_soler']);
        $secondRequester = User::factory()->create(['name' => 'Beto Ruiz', 'username' => 'beto_ruiz']);

        $firstRequest = $league->joinRequests()->create([
            'user_id' => $firstRequester->id,
            'status' => LeagueJoinRequest::STATUS_PENDING,
        ]);
        $secondRequest = $league->joinRequests()->create([
            'user_id' => $secondRequester->id,
            'status' => LeagueJoinRequest::STATUS_PENDING,
        ]);

        $this->actingAs($owner)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Ver solicitudes pendientes: 2', false)
            ->assertSee('Solicitudes pendientes')
            ->assertSee('Liga Header')
            ->assertSee('Ana Soler')
            ->assertSee('@ana_soler')
            ->assertSee('Beto Ruiz')
            ->assertSee('@beto_ruiz')
            ->assertSee(route('private-leagues.join-requests.accept', [$league, $firstRequest]), false)
            ->assertSee(route('private-leagues.join-requests.reject', [$league, $firstRequest]), false)
            ->assertSee(route('private-leagues.join-requests.accept', [$league, $secondRequest]), false)
            ->assertSee(route('private-leagues.join-requests.reject', [$league, $secondRequest]), false);
    }

    public function test_owner_without_pending_requests_does_not_see_header_alert(): void
    {
        $owner = User::factory()->create();
        $owner->ownedPrivateLeague()->create(['name' => 'Liga Sin Pendientes']);

        $this->actingAs($owner)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Ver solicitudes pendientes')
            ->assertDontSee('Solicitudes pendientes');
    }

    public function test_non_owner_does_not_see_pending_requests_alert(): void
    {
        $owner = User::factory()->create();
        $nonOwner = User::factory()->create();
        $league = $owner->ownedPrivateLeague()->create(['name' => 'Liga Reservada']);
        $requester = User::factory()->create(['name' => 'Usuario Pendiente', 'username' => 'pendiente']);

        $league->joinRequests()->create([
            'user_id' => $requester->id,
            'status' => LeagueJoinRequest::STATUS_PENDING,
        ]);

        $this->actingAs($nonOwner)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Ver solicitudes pendientes')
            ->assertDontSee('Solicitudes pendientes')
            ->assertDontSee('Liga Reservada')
            ->assertDontSee('Usuario Pendiente')
            ->assertDontSee('@pendiente');
    }
}
