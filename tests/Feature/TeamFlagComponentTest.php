<?php

namespace Tests\Feature;

use App\Models\Team;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class TeamFlagComponentTest extends TestCase
{
    public function test_component_renders_img_when_flag_path_exists(): void
    {
        $team = new Team([
            'name' => 'Argentina',
            'short_name' => 'ARG',
            'flag_path' => 'flags/arg.svg',
        ]);

        $html = Blade::render('<x-team-flag :team="$team" />', ['team' => $team]);

        $this->assertStringContainsString('flags/arg.svg', $html);
        $this->assertStringContainsString('alt="Bandera de Argentina"', $html);
        $this->assertStringContainsString('<img', $html);
    }

    public function test_component_falls_back_to_short_name_when_flag_path_is_missing(): void
    {
        $team = new Team([
            'name' => 'Argentina',
            'short_name' => 'ARG',
            'flag_path' => null,
        ]);

        $html = Blade::render('<x-team-flag :team="$team" />', ['team' => $team]);

        $this->assertStringNotContainsString('<img', $html);
        $this->assertStringContainsString('aria-label="Bandera de Argentina"', $html);
        $this->assertStringContainsString('ARG', $html);
    }

    public function test_component_handles_null_team_as_placeholder(): void
    {
        $html = Blade::render('<x-team-flag />');

        $this->assertStringNotContainsString('<img', $html);
        $this->assertStringContainsString('aria-label="Equipo por definir"', $html);
        $this->assertStringContainsString('TBD', $html);
    }
}
