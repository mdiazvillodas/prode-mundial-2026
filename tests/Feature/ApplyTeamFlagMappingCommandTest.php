<?php

namespace Tests\Feature;

use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplyTeamFlagMappingCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_dry_run_does_not_mutate_database(): void
    {
        Team::factory()->create([
            'name' => 'Argentina',
            'short_name' => 'ARG',
            'country_code' => null,
            'flag_path' => null,
        ]);

        $this->artisan('teams:apply-flag-mapping --dry-run --force')
            ->expectsOutputToContain('Dry run complete')
            ->assertSuccessful();

        $this->assertDatabaseHas('teams', [
            'short_name' => 'ARG',
            'country_code' => null,
            'flag_path' => null,
        ]);
    }

    public function test_command_applies_flags_to_mapped_teams(): void
    {
        Team::factory()->create([
            'name' => 'Brazil',
            'short_name' => 'BRA',
            'country_code' => null,
            'flag_path' => null,
        ]);

        $this->artisan('teams:apply-flag-mapping --force')
            ->expectsOutputToContain('updated=1')
            ->assertSuccessful();

        $this->assertDatabaseHas('teams', [
            'short_name' => 'BRA',
            'country_code' => 'BRA',
            'flag_path' => 'flags/bra.svg',
        ]);
    }

    public function test_command_applies_flags_to_2026_missing_team_codes_by_short_name(): void
    {
        $codes = [
            'ALG', 'AUT', 'BIH', 'CPV', 'COL', 'CGO', 'CUR', 'CZE',
            'EGY', 'HAI', 'IRQ', 'CIV', 'JOR', 'NZL', 'NOR', 'PAN',
            'PAR', 'SCO', 'RSA', 'SWE', 'TUR', 'UZB',
        ];

        foreach ($codes as $code) {
            Team::factory()->create([
                'name' => 'Team '.$code,
                'short_name' => $code,
                'country_code' => null,
                'flag_path' => null,
            ]);
        }

        $this->artisan('teams:apply-flag-mapping --force')
            ->expectsOutputToContain('updated=22')
            ->assertSuccessful();

        foreach ($codes as $code) {
            $this->assertDatabaseHas('teams', [
                'short_name' => $code,
                'country_code' => $code,
                'flag_path' => 'flags/'.strtolower($code).'.svg',
            ]);
        }
    }

    public function test_command_reports_unmapped_teams(): void
    {
        Team::factory()->create([
            'name' => 'Atlantis',
            'short_name' => 'ATL',
            'country_code' => null,
            'flag_path' => null,
        ]);

        $this->artisan('teams:apply-flag-mapping --force')
            ->expectsOutputToContain('missing_mapping=1')
            ->assertSuccessful();
    }

    public function test_command_does_not_overwrite_existing_flag_path_by_default(): void
    {
        Team::factory()->create([
            'name' => 'Spain',
            'short_name' => 'ESP',
            'country_code' => 'LOC',
            'flag_path' => 'flags/manual.svg',
        ]);

        $this->artisan('teams:apply-flag-mapping --force')
            ->expectsOutputToContain('skipped_already_set=1')
            ->assertSuccessful();

        $this->assertDatabaseHas('teams', [
            'short_name' => 'ESP',
            'country_code' => 'LOC',
            'flag_path' => 'flags/manual.svg',
        ]);
    }

    public function test_force_update_overwrites_existing_flag_fields(): void
    {
        Team::factory()->create([
            'name' => 'Spain',
            'short_name' => 'ESP',
            'country_code' => 'LOC',
            'flag_path' => 'flags/manual.svg',
        ]);

        $this->artisan('teams:apply-flag-mapping --force --force-update')
            ->expectsOutputToContain('updated=1')
            ->assertSuccessful();

        $this->assertDatabaseHas('teams', [
            'short_name' => 'ESP',
            'country_code' => 'ESP',
            'flag_path' => 'flags/esp.svg',
        ]);
    }
}
