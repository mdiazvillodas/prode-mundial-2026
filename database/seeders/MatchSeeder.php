<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\Tournament;
use App\Models\TournamentMatch;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

class MatchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tournament = Tournament::query()
            ->where('slug', 'fifa-world-cup-2026')
            ->firstOrFail();

        $teams = Team::query()
            ->whereIn('short_name', ['ARG', 'BRA', 'FRA', 'ESP', 'URU', 'USA'])
            ->get()
            ->keyBy('short_name');

        $scheduledStart = CarbonImmutable::now()->addDays(15)->setTime(21, 0);
        $openStart = CarbonImmutable::now()->addDays(2)->setTime(18, 0);
        $lockedStart = CarbonImmutable::now()->addMinutes(2);
        $finishedStart = CarbonImmutable::now()->subDays(1)->setTime(20, 0);
        $placeholderStart = CarbonImmutable::now()->addMonth()->setTime(21, 0);

        TournamentMatch::query()->create([
            'tournament_id' => $tournament->id,
            'team_a_id' => $teams['ARG']->id,
            'team_b_id' => $teams['USA']->id,
            'starts_at' => $scheduledStart,
            'prediction_closes_at' => $scheduledStart->subMinutes(5),
            'stage' => 'group',
            'group' => 'A',
            'status' => 'scheduled',
        ]);

        TournamentMatch::query()->create([
            'tournament_id' => $tournament->id,
            'team_a_id' => $teams['BRA']->id,
            'team_b_id' => $teams['ESP']->id,
            'starts_at' => $openStart,
            'prediction_closes_at' => $openStart->subMinutes(5),
            'stage' => 'group',
            'group' => 'B',
            'status' => 'open',
        ]);

        TournamentMatch::query()->create([
            'tournament_id' => $tournament->id,
            'team_a_id' => $teams['FRA']->id,
            'team_b_id' => $teams['URU']->id,
            'starts_at' => $lockedStart,
            'prediction_closes_at' => $lockedStart->subMinutes(5),
            'stage' => 'group',
            'group' => 'C',
            'status' => 'locked',
        ]);

        TournamentMatch::query()->create([
            'tournament_id' => $tournament->id,
            'team_a_id' => $teams['ARG']->id,
            'team_b_id' => $teams['FRA']->id,
            'starts_at' => $finishedStart,
            'prediction_closes_at' => $finishedStart->subMinutes(5),
            'stage' => 'group',
            'group' => 'D',
            'status' => 'finished',
            'team_a_score' => 2,
            'team_b_score' => 1,
            'winner_team_id' => $teams['ARG']->id,
        ]);

        TournamentMatch::query()->create([
            'tournament_id' => $tournament->id,
            'team_a_id' => null,
            'team_b_id' => null,
            'starts_at' => $placeholderStart,
            'prediction_closes_at' => null,
            'stage' => 'round_of_16',
            'group' => null,
            'status' => 'placeholder',
        ]);
    }
}
