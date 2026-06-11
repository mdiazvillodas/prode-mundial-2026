<?php

namespace Tests\Feature;

use App\Models\LeagueMembership;
use App\Models\Prediction;
use App\Models\TournamentMatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaguesHubTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_leagues_hub(): void
    {
        $this->get(route('leagues.index'))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_user_without_private_leagues_sees_general_hub_actions(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('leagues.index'))
            ->assertOk()
            ->assertSee('Liga general')
            ->assertSee('+ Crear mi liga')
            ->assertSee('Buscar liga');
    }

    public function test_member_without_owned_private_league_sees_create_action_next_to_tabs(): void
    {
        $user = User::factory()->create(['username' => 'league_member']);
        $league = $this->activeLeagueFor($user, 'Amigos Invitados', now()->subDay());

        $this->actingAs($user)
            ->get(route('leagues.index'))
            ->assertOk()
            ->assertSee('Liga general')
            ->assertSee('Amigos Invitados')
            ->assertSee(route('private-leagues.show', $league), false)
            ->assertSee('+ Crear mi liga')
            ->assertSee(route('private-leagues.create'), false);
    }

    public function test_user_who_already_owns_private_league_does_not_see_create_action(): void
    {
        $user = User::factory()->create();
        $league = $user->ownedPrivateLeague()->create(['name' => 'Mi Liga Propia']);

        $this->actingAs($user)
            ->get(route('leagues.index'))
            ->assertOk()
            ->assertSee('Mi Liga Propia')
            ->assertSee(route('private-leagues.show', $league), false)
            ->assertDontSee('+ Crear mi liga')
            ->assertDontSee(route('private-leagues.create'), false);
    }

    public function test_leagues_hub_shows_global_ranking_and_up_to_five_active_private_leagues(): void
    {
        $user = User::factory()->create(['username' => 'hub_user']);
        $topUser = User::factory()->create(['name' => 'Top Global', 'username' => 'top_global']);
        $secondUser = User::factory()->create(['name' => 'Second Global', 'username' => 'second_global']);

        $this->scoredPredictionFor($topUser, 6);
        $this->scoredPredictionFor($secondUser, 3);

        $firstLeague = $this->activeLeagueFor($user, 'Amigos Norte', now()->subDays(4));
        $this->activeLeagueFor($user, 'Amigos Sur', now()->subDays(3));
        $this->activeLeagueFor($user, 'Oficina', now()->subDays(2));
        $this->activeLeagueFor($user, 'Cuarta Liga', now()->subDay());
        $this->activeLeagueFor($user, 'Quinta Liga', now());
        $this->activeLeagueFor($user, 'Sexta Liga', now()->addMinute());

        $this->scoredPredictionFor($user, 6);

        $this->actingAs($user)
            ->get(route('leagues.index'))
            ->assertOk()
            ->assertSee('Top Global')
            ->assertSee('@top_global')
            ->assertSee('Second Global')
            ->assertSee('@second_global')
            ->assertSee('Amigos Norte')
            ->assertSee('Amigos Sur')
            ->assertSee('Oficina')
            ->assertSee('Cuarta Liga')
            ->assertSee('Quinta Liga')
            ->assertDontSee('Sexta Liga')
            ->assertSee(route('private-leagues.show', $firstLeague), false)
            ->assertSee('+ Crear mi liga')
            ->assertSee('@hub_user');
    }

    private function activeLeagueFor(User $member, string $name, $joinedAt)
    {
        $owner = User::factory()->create();
        $league = $owner->ownedPrivateLeague()->create(['name' => $name]);

        $league->memberships()->create([
            'user_id' => $member->id,
            'status' => LeagueMembership::STATUS_ACTIVE,
            'joined_at' => $joinedAt,
        ]);

        return $league;
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
