<?php

namespace Tests\Feature;

use App\Models\ApiSyncLog;
use App\Models\Team;
use App\Models\TournamentMatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminApiHealthTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_api_health_page_is_restricted_to_admin_users(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->admin()->create();

        $this->get(route('admin.api-health'))
            ->assertRedirect('/login');

        $this->actingAs($user)
            ->get(route('admin.api-health'))
            ->assertStatus(403);

        $this->actingAs($admin)
            ->get(route('admin.api-health'))
            ->assertOk();
    }

    public function test_admin_api_health_page_shows_summary_information(): void
    {
        $admin = User::factory()->admin()->create();

        Team::factory()->create([
            'api_provider' => 'api-football',
            'api_team_id' => 10,
            'flag_path' => 'flags/arg.svg',
        ]);
        Team::factory()->create([
            'api_provider' => 'api-football',
            'api_team_id' => 20,
            'flag_path' => null,
        ]);
        TournamentMatch::factory()->create([
            'api_provider' => 'api-football',
            'api_fixture_id' => 1001,
            'api_status' => 'NS',
        ]);

        ApiSyncLog::query()->create([
            'provider' => 'api-football',
            'sync_type' => 'teams',
            'status' => 'success',
            'finished_at' => now(),
            'items_received' => 2,
            'items_created' => 2,
            'items_updated' => 0,
            'items_skipped' => 0,
        ]);
        ApiSyncLog::query()->create([
            'provider' => 'api-football',
            'sync_type' => 'fixtures',
            'status' => 'success',
            'finished_at' => now(),
            'items_received' => 1,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.api-health'))
            ->assertOk()
            ->assertSee('Estado API-Football')
            ->assertSee('Equipos API en DB')
            ->assertSee('Fixtures API en DB')
            ->assertSee('Equipos sin flag_path')
            ->assertSee('Logs recientes')
            ->assertSee('teams')
            ->assertSee('fixtures')
            ->assertSee('NS');
    }
}
