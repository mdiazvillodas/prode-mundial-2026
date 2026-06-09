<?php

namespace Tests\Feature;

use App\Models\Prediction;
use App\Models\PrivateLeague;
use App\Models\Team;
use App\Models\TournamentMatch;
use App\Models\User;
use Database\Seeders\StagingDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardUxTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_dashboard(): void
    {
        $this->get(route('dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_dashboard_loads_user_summary_and_primary_links(): void
    {
        $user = User::factory()->create([
            'name' => 'Mariano Demo',
            'role' => User::ROLE_USER,
        ]);
        $otherUser = User::factory()->create(['username' => 'leader']);
        $teamA = Team::factory()->create(['name' => 'Argentina', 'short_name' => 'ARG']);
        $teamB = Team::factory()->create(['name' => 'United States', 'short_name' => 'USA']);

        Prediction::factory()->create([
            'user_id' => $user->id,
            'match_id' => TournamentMatch::factory()->create()->id,
            'status' => Prediction::STATUS_SCORED,
            'points_awarded' => 6,
        ]);
        Prediction::factory()->create([
            'user_id' => $otherUser->id,
            'match_id' => TournamentMatch::factory()->create()->id,
            'status' => Prediction::STATUS_SCORED,
            'points_awarded' => 3,
        ]);

        TournamentMatch::factory()->create([
            'team_a_id' => $teamA->id,
            'team_b_id' => $teamB->id,
            'status' => TournamentMatch::STATUS_OPEN,
            'starts_at' => now()->addDay(),
            'prediction_closes_at' => now()->addDay()->subMinutes(5),
        ]);

        $user->ownedPrivateLeague()->create(['name' => 'Liga Demo Palermo']);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Mi Prode')
            ->assertSee('Te falta pronosticar')
            ->assertSee('Jornada Mundial')
            ->assertSee('ARG')
            ->assertSee('USA')
            ->assertSee('General')
            ->assertSee('6 pts')
            ->assertSee('#1')
            ->assertSee('Predicciones')
            ->assertDontSee('Hola, Mariano Demo')
            ->assertDontSee('Cargar predicciones')
            ->assertSee('Ligas')
            ->assertSee('Historial')
            ->assertSee('Calendario')
            ->assertSee('Liga Demo Palermo')
            ->assertDontSee('Panel admin');
    }

    public function test_admin_shortcut_is_visible_only_for_admin_users(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $user = User::factory()->create(['role' => User::ROLE_USER]);

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Panel admin')
            ->assertSee(route('admin.dashboard'), false);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Panel admin')
            ->assertDontSee(route('admin.dashboard'), false);
    }

    public function test_dashboard_shows_private_league_onboarding_for_users_without_active_private_leagues(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_USER]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Jugá con tus amigos')
            ->assertSee('Creá tu propia liga, compartí el link y competí con tu grupo durante el Mundial.')
            ->assertSee('Crear mi liga')
            ->assertSee('Buscar liga')
            ->assertSee(route('private-leagues.create'), false)
            ->assertSee(route('private-leagues.search'), false);
    }

    public function test_dashboard_hides_private_league_onboarding_for_users_with_active_private_leagues(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_USER]);

        PrivateLeague::query()->create([
            'owner_id' => $user->id,
            'name' => 'Liga Activa',
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Jugá con tus amigos')
            ->assertDontSee('Crear mi liga')
            ->assertDontSee('Tus amigos se suman desde un link');
    }

    public function test_dashboard_renders_live_and_friend_activity_modules_from_demo_data(): void
    {
        $this->seed(StagingDemoSeeder::class);

        $user = User::query()->where('email', 'mariano@prode.test')->firstOrFail();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Te falta pronosticar')
            ->assertSee('Jornada Mundial')
            ->assertSee('Tus amigos en la jornada')
            ->assertSee('Ana Demo')
            ->assertDontSee('Estado provisional según última sincronización')
            ->assertDontSee('Hola, Mariano Demo');
    }
}
