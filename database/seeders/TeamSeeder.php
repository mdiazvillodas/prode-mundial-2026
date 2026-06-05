<?php

namespace Database\Seeders;

use App\Models\Team;
use Illuminate\Database\Seeder;

class TeamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teams = [
            ['name' => 'Argentina', 'short_name' => 'ARG', 'country_code' => 'ARG', 'flag_path' => 'flags/arg.svg'],
            ['name' => 'Brazil', 'short_name' => 'BRA', 'country_code' => 'BRA', 'flag_path' => 'flags/bra.svg'],
            ['name' => 'France', 'short_name' => 'FRA', 'country_code' => 'FRA', 'flag_path' => 'flags/fra.svg'],
            ['name' => 'Spain', 'short_name' => 'ESP', 'country_code' => 'ESP', 'flag_path' => 'flags/esp.svg'],
            ['name' => 'Uruguay', 'short_name' => 'URU', 'country_code' => 'URY', 'flag_path' => 'flags/uru.svg'],
            ['name' => 'United States', 'short_name' => 'USA', 'country_code' => 'USA', 'flag_path' => 'flags/usa.svg'],
        ];

        foreach ($teams as $team) {
            Team::query()->create($team);
        }
    }
}
