<?php

namespace Tests\Feature;

use App\Models\Team;
use App\Models\TournamentMatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalendarTeamScheduleTest extends TestCase
{
    use RefreshDatabase;

    public function test_calendar_shows_team_selector_and_no_team_selected_state(): void
    {
        $user = User::factory()->create();
        Team::factory()->create(['name' => 'Argentina', 'country_code' => 'ARG']);

        $this->actingAs($user)
            ->get(route('calendar.index'))
            ->assertOk()
            ->assertSee('Elegí una selección')
            ->assertSee('Argentina')
            ->assertDontSee('Partidos');
    }

    public function test_calendar_filters_matches_for_selected_team_only(): void
    {
        $user = User::factory()->create();
        $argentina = Team::factory()->create(['name' => 'Argentina', 'country_code' => 'ARG']);
        $mexico = Team::factory()->create(['name' => 'Mexico', 'country_code' => 'MEX']);
        $canada = Team::factory()->create(['name' => 'Canada', 'country_code' => 'CAN']);
        $japan = Team::factory()->create(['name' => 'Japan', 'country_code' => 'JPN']);

        TournamentMatch::factory()->create([
            'team_a_id' => $argentina->id,
            'team_b_id' => $mexico->id,
            'starts_at' => '2026-06-11 19:00:00',
            'stage' => 'group',
            'group' => 'A',
            'status' => TournamentMatch::STATUS_SCHEDULED,
        ]);

        TournamentMatch::factory()->create([
            'team_a_id' => $canada->id,
            'team_b_id' => $japan->id,
            'starts_at' => '2026-06-12 19:00:00',
            'stage' => 'group',
            'group' => 'Z',
            'status' => TournamentMatch::STATUS_SCHEDULED,
        ]);

        $this->actingAs($user)
            ->get(route('calendar.index', ['team_id' => $argentina->id]))
            ->assertOk()
            ->assertSee('Agenda seleccionada')
            ->assertSee('Argentina')
            ->assertSee('Mexico')
            ->assertSee('Grupo A')
            ->assertDontSee('Grupo Z');
    }

    public function test_finished_selected_team_match_shows_result(): void
    {
        $user = User::factory()->create();
        $argentina = Team::factory()->create(['name' => 'Argentina', 'country_code' => 'ARG']);
        $mexico = Team::factory()->create(['name' => 'Mexico', 'country_code' => 'MEX']);

        TournamentMatch::factory()->create([
            'team_a_id' => $argentina->id,
            'team_b_id' => $mexico->id,
            'starts_at' => '2026-06-11 19:00:00',
            'status' => TournamentMatch::STATUS_FINISHED,
            'team_a_score' => 2,
            'team_b_score' => 1,
            'winner_team_id' => $argentina->id,
        ]);

        $this->actingAs($user)
            ->get(route('calendar.index', ['team_id' => $argentina->id]))
            ->assertOk()
            ->assertSee('2 - 1')
            ->assertSee('Ganador')
            ->assertSee('Argentina');
    }

    public function test_calendar_handles_invalid_or_empty_team_schedule(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create(['name' => 'Sin Fixture']);

        $this->actingAs($user)
            ->get(route('calendar.index', ['team_id' => 999999]))
            ->assertOk()
            ->assertSee('Selección no encontrada');

        $this->actingAs($user)
            ->get(route('calendar.index', ['team_id' => $team->id]))
            ->assertOk()
            ->assertSee('Sin partidos conocidos');
    }

    public function test_matches_route_still_works_directly(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('matches.index'))
            ->assertOk();
    }
}
