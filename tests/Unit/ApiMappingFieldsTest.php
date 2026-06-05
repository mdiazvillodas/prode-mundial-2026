<?php

namespace Tests\Unit;

use App\Models\Team;
use App\Models\Tournament;
use App\Models\TournamentMatch;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiMappingFieldsTest extends TestCase
{
    use RefreshDatabase;
    public function test_team_can_store_api_provider(): void
    {
        $team = Team::factory()->create([
            'api_provider' => 'api-football',
        ]);

        $this->assertSame('api-football', $team->api_provider);
        $this->assertDatabaseHas('teams', [
            'id' => $team->id,
            'api_provider' => 'api-football',
        ]);
    }

    public function test_team_can_store_api_team_id(): void
    {
        $team = Team::factory()->create([
            'api_team_id' => 1234567,
        ]);

        $this->assertSame(1234567, $team->api_team_id);
        $this->assertDatabaseHas('teams', [
            'id' => $team->id,
            'api_team_id' => 1234567,
        ]);
    }

    public function test_team_can_store_country(): void
    {
        $team = Team::factory()->create([
            'country' => 'Argentina',
        ]);

        $this->assertSame('Argentina', $team->country);
        $this->assertDatabaseHas('teams', [
            'id' => $team->id,
            'country' => 'Argentina',
        ]);
    }

    public function test_team_can_store_country_code_and_flag_path(): void
    {
        $team = Team::factory()->create([
            'country_code' => 'ARG',
            'flag_path' => 'flags/ar.svg',
        ]);

        $this->assertSame('ARG', $team->country_code);
        $this->assertSame('flags/ar.svg', $team->flag_path);
        $this->assertDatabaseHas('teams', [
            'id' => $team->id,
            'country_code' => 'ARG',
            'flag_path' => 'flags/ar.svg',
        ]);
    }

    public function test_team_can_store_logo_url(): void
    {
        $team = Team::factory()->create([
            'logo_url' => 'https://example.com/teams/1234567.png',
        ]);

        $this->assertSame('https://example.com/teams/1234567.png', $team->logo_url);
        $this->assertDatabaseHas('teams', [
            'id' => $team->id,
            'logo_url' => 'https://example.com/teams/1234567.png',
        ]);
    }

    public function test_team_can_store_last_synced_at(): void
    {
        $now = Carbon::now();
        $team = Team::factory()->create([
            'last_synced_at' => $now,
        ]);

        $this->assertInstanceOf(Carbon::class, $team->last_synced_at);
        $this->assertTrue($team->last_synced_at->diffInSeconds($now) <= 1);
    }

    public function test_team_api_fields_are_nullable(): void
    {
        $team = Team::factory()->create();

        $this->assertNull($team->api_provider);
        $this->assertNull($team->api_team_id);
        $this->assertNull($team->country);
        $this->assertNull($team->logo_url);
        $this->assertNull($team->last_synced_at);
    }

    public function test_tournament_match_can_store_api_provider(): void
    {
        $match = TournamentMatch::factory()->create([
            'api_provider' => 'api-football',
        ]);

        $this->assertSame('api-football', $match->api_provider);
        $this->assertDatabaseHas('matches', [
            'id' => $match->id,
            'api_provider' => 'api-football',
        ]);
    }

    public function test_tournament_match_can_store_api_fixture_id(): void
    {
        $match = TournamentMatch::factory()->create([
            'api_fixture_id' => 9876543,
        ]);

        $this->assertSame(9876543, $match->api_fixture_id);
        $this->assertDatabaseHas('matches', [
            'id' => $match->id,
            'api_fixture_id' => 9876543,
        ]);
    }

    public function test_tournament_match_can_store_api_status(): void
    {
        $match = TournamentMatch::factory()->create([
            'api_status' => '1H',
        ]);

        $this->assertSame('1H', $match->api_status);
        $this->assertDatabaseHas('matches', [
            'id' => $match->id,
            'api_status' => '1H',
        ]);
    }

    public function test_tournament_match_can_store_round(): void
    {
        $match = TournamentMatch::factory()->create([
            'round' => '1/8',
        ]);

        $this->assertSame('1/8', $match->round);
        $this->assertDatabaseHas('matches', [
            'id' => $match->id,
            'round' => '1/8',
        ]);
    }

    public function test_tournament_match_can_store_venue_name(): void
    {
        $match = TournamentMatch::factory()->create([
            'venue_name' => 'Estadio Azteca',
        ]);

        $this->assertSame('Estadio Azteca', $match->venue_name);
        $this->assertDatabaseHas('matches', [
            'id' => $match->id,
            'venue_name' => 'Estadio Azteca',
        ]);
    }

    public function test_tournament_match_can_store_venue_city(): void
    {
        $match = TournamentMatch::factory()->create([
            'venue_city' => 'Mexico City',
        ]);

        $this->assertSame('Mexico City', $match->venue_city);
        $this->assertDatabaseHas('matches', [
            'id' => $match->id,
            'venue_city' => 'Mexico City',
        ]);
    }

    public function test_tournament_match_can_store_last_synced_at(): void
    {
        $now = Carbon::now();
        $match = TournamentMatch::factory()->create([
            'last_synced_at' => $now,
        ]);

        $this->assertInstanceOf(Carbon::class, $match->last_synced_at);
        $this->assertTrue($match->last_synced_at->diffInSeconds($now) <= 1);
    }

    public function test_tournament_match_api_fields_are_nullable(): void
    {
        $match = TournamentMatch::factory()->create();

        $this->assertNull($match->api_provider);
        $this->assertNull($match->api_fixture_id);
        $this->assertNull($match->api_status);
        $this->assertNull($match->round);
        $this->assertNull($match->venue_name);
        $this->assertNull($match->venue_city);
        $this->assertNull($match->last_synced_at);
    }

    public function test_team_unique_constraint_on_api_provider_and_api_team_id(): void
    {
        $team1 = Team::factory()->create([
            'api_provider' => 'api-football',
            'api_team_id' => 1111,
        ]);

        // Creating another team with the same api_provider and api_team_id should fail
        $this->expectException(\Illuminate\Database\QueryException::class);
        Team::factory()->create([
            'api_provider' => 'api-football',
            'api_team_id' => 1111,
        ]);
    }

    public function test_tournament_match_unique_constraint_on_api_provider_and_api_fixture_id(): void
    {
        $match1 = TournamentMatch::factory()->create([
            'api_provider' => 'api-football',
            'api_fixture_id' => 2222,
        ]);

        // Creating another match with the same api_provider and api_fixture_id should fail
        $this->expectException(\Illuminate\Database\QueryException::class);
        TournamentMatch::factory()->create([
            'api_provider' => 'api-football',
            'api_fixture_id' => 2222,
        ]);
    }

    public function test_team_without_api_fields_can_be_created(): void
    {
        // Existing seeders should still work
        $team = Team::factory()->create();

        $this->assertNotNull($team->name);
        $this->assertNull($team->api_provider);
        $this->assertNull($team->api_team_id);
        $this->assertDatabaseHas('teams', [
            'id' => $team->id,
        ]);
    }

    public function test_tournament_match_without_api_fields_can_be_created(): void
    {
        // Existing seeders should still work
        $match = TournamentMatch::factory()->create();

        $this->assertNotNull($match->tournament_id);
        $this->assertNull($match->api_provider);
        $this->assertNull($match->api_fixture_id);
        $this->assertDatabaseHas('matches', [
            'id' => $match->id,
        ]);
    }
}
