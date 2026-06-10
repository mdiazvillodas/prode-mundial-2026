<?php

namespace Tests\Feature;

use App\Models\Team;
use App\Models\TournamentMatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
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

    public function test_calendar_uses_server_rendered_madrid_summer_time_matching_predictions(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-10 12:00:00', 'UTC'));
        $user = User::factory()->create();
        $france = Team::factory()->create(['name' => 'France', 'country_code' => 'FRA']);
        $uruguay = Team::factory()->create(['name' => 'Uruguay', 'country_code' => 'URU']);

        TournamentMatch::factory()->create([
            'team_a_id' => $france->id,
            'team_b_id' => $uruguay->id,
            'starts_at' => Carbon::parse('2026-06-11 14:00:00', 'UTC'),
            'prediction_closes_at' => Carbon::parse('2026-06-11 13:55:00', 'UTC'),
            'stage' => 'group',
            'group' => 'A',
            'status' => TournamentMatch::STATUS_OPEN,
        ]);

        $this->actingAs($user)
            ->get(route('calendar.index', ['team_id' => $france->id, 'tz' => 'Europe/Madrid']))
            ->assertOk()
            ->assertSee('France')
            ->assertSee('Uruguay')
            ->assertSee('11/06/2026')
            ->assertSee('16:00')
            ->assertDontSee('18:00')
            ->assertDontSee('data-local-time', false)
            ->assertDontSee('data-local-date', false);

        $this->actingAs($user)
            ->get('/predictions?date=2026-06-11&tz=Europe/Madrid')
            ->assertOk()
            ->assertSee('France')
            ->assertSee('Uruguay')
            ->assertSee('16:00')
            ->assertSee('15:55')
            ->assertDontSee('18:00');

        Carbon::setTestNow();
    }

    public function test_calendar_without_timezone_query_uses_product_default_timezone(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-10 12:00:00', 'UTC'));
        $user = User::factory()->create();
        $france = Team::factory()->create(['name' => 'France', 'country_code' => 'FRA']);
        $uruguay = Team::factory()->create(['name' => 'Uruguay', 'country_code' => 'URU']);

        TournamentMatch::factory()->create([
            'team_a_id' => $france->id,
            'team_b_id' => $uruguay->id,
            'starts_at' => Carbon::parse('2026-06-11 14:00:00', 'UTC'),
            'prediction_closes_at' => Carbon::parse('2026-06-11 13:55:00', 'UTC'),
            'stage' => 'group',
            'group' => 'A',
            'status' => TournamentMatch::STATUS_OPEN,
        ]);

        $this->actingAs($user)
            ->get(route('calendar.index', ['team_id' => $france->id]))
            ->assertOk()
            ->assertSee('France')
            ->assertSee('Uruguay')
            ->assertSee('11/06/2026')
            ->assertSee('16:00')
            ->assertSee('name="tz" value="Europe/Madrid"', false)
            ->assertDontSee('18:00')
            ->assertDontSee('data-local-time', false)
            ->assertDontSee('data-local-date', false);

        Carbon::setTestNow();
    }

    public function test_calendar_uses_viewer_local_date_across_utc_midnight(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-10 12:00:00', 'UTC'));
        $user = User::factory()->create();
        $argentina = Team::factory()->create(['name' => 'Argentina', 'country_code' => 'ARG']);
        $algeria = Team::factory()->create(['name' => 'Algeria', 'country_code' => 'ALG']);

        TournamentMatch::factory()->create([
            'team_a_id' => $argentina->id,
            'team_b_id' => $algeria->id,
            'starts_at' => Carbon::parse('2026-06-16 23:30:00', 'UTC'),
            'prediction_closes_at' => Carbon::parse('2026-06-16 23:25:00', 'UTC'),
            'stage' => 'group',
            'group' => 'B',
            'status' => TournamentMatch::STATUS_OPEN,
        ]);

        $this->actingAs($user)
            ->get(route('calendar.index', ['team_id' => $argentina->id, 'tz' => 'Europe/Madrid']))
            ->assertOk()
            ->assertSee('Argentina')
            ->assertSee('Algeria')
            ->assertSee('17/06/2026')
            ->assertSee('01:30')
            ->assertDontSee('16/06/2026')
            ->assertDontSee('23:30');

        Carbon::setTestNow();
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
