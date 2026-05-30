<?php

namespace Database\Seeders;

use App\Models\Tournament;
use Illuminate\Database\Seeder;

class TournamentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Tournament::query()->create([
            'name' => 'FIFA World Cup 2026',
            'slug' => 'fifa-world-cup-2026',
            'year' => 2026,
            'starts_at' => '2026-06-11',
            'ends_at' => '2026-07-19',
            'status' => 'scheduled',
        ]);
    }
}
