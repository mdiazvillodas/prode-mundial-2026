<?php

namespace App\Http\Controllers;

use App\Models\TournamentMatch;
use Illuminate\View\View;

class CalendarController extends Controller
{
    public function index(): View
    {
        $matches = TournamentMatch::query()
            ->with(['teamA', 'teamB', 'winnerTeam', 'tournament'])
            ->orderByRaw('CASE WHEN starts_at IS NULL THEN 1 ELSE 0 END')
            ->orderBy('starts_at')
            ->orderBy('id')
            ->get();

        $matchesByDate = $matches->groupBy(function (TournamentMatch $match): string {
            return $match->starts_at?->toDateString() ?? 'date_pending';
        });

        return view('calendar.index', [
            'matchesByDate' => $matchesByDate,
        ]);
    }
}
