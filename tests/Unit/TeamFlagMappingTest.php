<?php

namespace Tests\Unit;

use App\Models\Team;
use App\Support\TeamFlagMapping;
use Tests\TestCase;

class TeamFlagMappingTest extends TestCase
{
    public function test_known_team_code_maps_to_country_code_and_flag_path(): void
    {
        $mapping = TeamFlagMapping::forCode('ARG');

        $this->assertSame([
            'country_code' => 'ARG',
            'flag_path' => 'flags/arg.svg',
        ], $mapping);
        $this->assertTrue(TeamFlagMapping::assetExists($mapping['flag_path']));
    }

    public function test_api_football_costa_rica_alias_maps_to_crc_flag(): void
    {
        $mapping = TeamFlagMapping::forCode('COS');

        $this->assertSame([
            'country_code' => 'CRC',
            'flag_path' => 'flags/crc.svg',
        ], $mapping);
        $this->assertTrue(TeamFlagMapping::assetExists($mapping['flag_path']));
    }

    public function test_england_and_wales_have_explicit_local_flags(): void
    {
        $this->assertSame('flags/eng.svg', TeamFlagMapping::forCode('ENG')['flag_path']);
        $this->assertSame('flags/wal.svg', TeamFlagMapping::forCode('WAL')['flag_path']);
    }

    public function test_team_display_helpers_prefer_local_flag_and_short_name(): void
    {
        $team = new Team([
            'name' => 'Argentina',
            'short_name' => 'ARG',
            'country_code' => 'ARG',
            'flag_path' => 'flags/arg.svg',
        ]);

        $this->assertTrue($team->hasFlag());
        $this->assertSame(asset('flags/arg.svg'), $team->flagUrl());
        $this->assertSame('ARG', $team->displayCode());
    }
}
