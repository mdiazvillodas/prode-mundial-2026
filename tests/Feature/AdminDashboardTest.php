<?php

namespace Tests\Feature;

use App\Models\Prediction;
use App\Models\Team;
use App\Models\TournamentMatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_admin_dashboard(): void
    {
        $this->get(route('admin.dashboard'))
            ->assertRedirect('/login');
    }

    public function test_normal_user_cannot_access_admin_dashboard(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_USER]);

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_admin_can_access_dashboard_and_see_environment_mode_counts_and_links(): void
    {
        config(['app.mode' => 'test']);

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        TournamentMatch::factory()->create();
        Prediction::factory()->create();
        User::factory()->create()->ownedPrivateLeague()->create(['name' => 'Liga Admin']);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Administracion')
            ->assertSee('Entorno')
            ->assertSee(config('app.env'))
            ->assertSee('Modo prueba')
            ->assertSee('Usuarios')
            ->assertSee('Partidos')
            ->assertSee('Predicciones')
            ->assertSee('Ligas privadas')
            ->assertSee((string) User::count())
            ->assertSee((string) TournamentMatch::count())
            ->assertSee((string) Prediction::count())
            ->assertSee('Admin partidos')
            ->assertSee('Ranking general');
    }

    public function test_guest_is_redirected_from_admin_matches(): void
    {
        $this->get(route('admin.matches.index'))
            ->assertRedirect('/login');
    }

    public function test_normal_user_cannot_access_admin_matches(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_USER]);

        $this->actingAs($user)
            ->get(route('admin.matches.index'))
            ->assertForbidden();
    }

    public function test_admin_can_access_admin_matches_listing_and_see_created_match(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $teamA = Team::factory()->create(['name' => 'Argentina', 'country_code' => 'ARG']);
        $teamB = Team::factory()->create(['name' => 'Canada', 'country_code' => 'CAN']);

        TournamentMatch::factory()->create([
            'team_a_id' => $teamA->id,
            'team_b_id' => $teamB->id,
            'stage' => 'group',
            'group' => 'A',
            'status' => TournamentMatch::STATUS_OPEN,
            'starts_at' => now()->addDay(),
            'prediction_closes_at' => now()->addDay()->subMinutes(5),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.matches.index'))
            ->assertOk()
            ->assertSee('Admin partidos')
            ->assertSee('Argentina')
            ->assertSee('Canada')
            ->assertSee('Abierto')
            ->assertSee('Grupo')
            ->assertSee('Cargar resultado');
    }
}
