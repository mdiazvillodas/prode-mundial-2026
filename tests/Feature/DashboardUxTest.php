<?php

namespace Tests\Feature;

use App\Models\Prediction;
use App\Models\Team;
use App\Models\TournamentMatch;
use App\Models\User;
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
        $teamA = Team::factory()->create(['name' => 'Argentina']);
        $teamB = Team::factory()->create(['name' => 'United States']);

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
            ->assertSee('Hola, Mariano Demo')
            ->assertSee('Liga general')
            ->assertSee('Puntos')
            ->assertSee('#1')
            ->assertSee('Predicciones')
            ->assertSee('Cargar predicciones')
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
}
