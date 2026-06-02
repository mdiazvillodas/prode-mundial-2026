<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\TournamentMatch;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CalendarController extends Controller
{
    public function index(Request $request): View
    {
        $teams = Team::query()
            ->orderBy('name')
            ->get();

        $requestedTeamId = $request->integer('team_id') ?: null;
        $selectedTeam = $requestedTeamId
            ? $teams->firstWhere('id', $requestedTeamId)
            : null;

        $matches = collect();

        if ($selectedTeam) {
            $matches = TournamentMatch::query()
                ->with(['teamA', 'teamB', 'winnerTeam', 'tournament'])
                ->where(function ($query) use ($selectedTeam): void {
                    $query
                        ->where('team_a_id', $selectedTeam->id)
                        ->orWhere('team_b_id', $selectedTeam->id);
                })
                ->orderByRaw('CASE WHEN starts_at IS NULL THEN 1 ELSE 0 END')
                ->orderBy('starts_at')
                ->orderBy('id')
                ->get();
        }

        return view('calendar.index', [
            'matches' => $matches,
            'requestedTeamId' => $requestedTeamId,
            'selectedTeam' => $selectedTeam,
            'teams' => $teams,
        ]);
    }
}
