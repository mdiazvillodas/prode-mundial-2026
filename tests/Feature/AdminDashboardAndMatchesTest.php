<?php

namespace Tests\Feature;

use App\Models\Prediction;
use App\Models\PrivateLeague;
use App\Models\Team;
use App\Models\TournamentMatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardAndMatchesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_access_is_restricted_and_shows_environment_mode_and_counts(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $normalUser = User::factory()->create(['role' => User::ROLE_USER]);
        TournamentMatch::factory()->count(2)->create();
        Prediction::factory()->create();
        $admin->ownedPrivateLeague()->create(['name' => 'Admin Liga']);

        $this->get('/admin')->assertRedirect('/login');

        $this->actingAs($normalUser)
            ->get('/admin')
            ->assertForbidden();

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk()
            ->assertSee('Entorno')
            ->assertSee(config('app.env'))
            ->assertSee('Modo')
            ->assertSee(config('app.mode', 'test'))
            ->assertSee('Usuarios')
            ->assertSee((string) User::count())
            ->assertSee('Partidos')
            ->assertSee((string) TournamentMatch::count())
            ->assertSee('Predicciones')
            ->assertSee((string) Prediction::count())
            ->assertSee('Ligas privadas')
            ->assertSee((string) PrivateLeague::count())
            ->assertSee(route('admin.matches.index'), false);
    }

    public function test_admin_matches_listing_access_is_restricted_and_shows_matches(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $normalUser = User::factory()->create(['role' => User::ROLE_USER]);
        $match = TournamentMatch::factory()->create([
            'stage' => 'group',
            'group' => 'A',
            'status' => TournamentMatch::STATUS_OPEN,
        ]);

        $this->get(route('admin.matches.index'))->assertRedirect('/login');

        $this->actingAs($normalUser)
            ->get(route('admin.matches.index'))
            ->assertForbidden();

        $this->actingAs($admin)
            ->get(route('admin.matches.index'))
            ->assertOk()
            ->assertSee($match->teamA->name)
            ->assertSee($match->teamB->name)
            ->assertSee('open');
    }

    public function test_admin_can_open_manual_result_form_for_valid_match(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $match = TournamentMatch::factory()->create([
            'status' => TournamentMatch::STATUS_OPEN,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.matches.result.edit', $match))
            ->assertOk()
            ->assertSee('team_a_score', false)
            ->assertSee('team_b_score', false);
    }

    public function test_admin_cannot_load_result_for_placeholder_or_missing_team_match(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $placeholder = TournamentMatch::factory()->placeholder()->create();

        $this->actingAs($admin)
            ->post(route('admin.matches.result.update', $placeholder), [
                'team_a_score' => 1,
                'team_b_score' => 0,
            ])
            ->assertSessionHasErrors(['result']);
    }

    public function test_admin_can_assign_teams_to_placeholder_match_and_status_becomes_scheduled(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $teamA = Team::factory()->create();
        $teamB = Team::factory()->create();
        $placeholder = TournamentMatch::factory()->placeholder()->create();

        $this->actingAs($admin)
            ->post(route('admin.matches.teams.update', $placeholder), [
                'team_a_id' => $teamA->id,
                'team_b_id' => $teamB->id,
            ])
            ->assertRedirect(route('admin.matches.index'));

        $this->assertDatabaseHas('matches', [
            'id' => $placeholder->id,
            'team_a_id' => $teamA->id,
            'team_b_id' => $teamB->id,
            'status' => TournamentMatch::STATUS_SCHEDULED,
        ]);
    }

    public function test_admin_cannot_assign_same_team_twice_to_placeholder_match(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $team = Team::factory()->create();
        $placeholder = TournamentMatch::factory()->placeholder()->create();

        $this->actingAs($admin)
            ->post(route('admin.matches.teams.update', $placeholder), [
                'team_a_id' => $team->id,
                'team_b_id' => $team->id,
            ])
            ->assertSessionHasErrors(['team_b_id']);
    }
}
