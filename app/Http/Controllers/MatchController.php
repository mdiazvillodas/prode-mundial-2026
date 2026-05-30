<?php

namespace App\Http\Controllers;

use App\Models\TournamentMatch;
use Illuminate\View\View;

class MatchController extends Controller
{
    public function index(): View
    {
        $today = now()->startOfDay();
        $recentFinishedSince = now()->subDays(7)->startOfDay();

        $matches = TournamentMatch::query()
            ->with(['teamA', 'teamB', 'winnerTeam', 'tournament'])
            ->where(function ($query) use ($today, $recentFinishedSince) {
                $query
                    ->whereNull('starts_at')
                    ->orWhere('starts_at', '>=', $today)
                    ->orWhere(function ($query) use ($recentFinishedSince) {
                        $query
                            ->where('status', 'finished')
                            ->where('starts_at', '>=', $recentFinishedSince);
                    });
            })
            ->orderByRaw(
                'CASE WHEN starts_at IS NULL THEN 2 WHEN starts_at >= ? THEN 0 ELSE 1 END',
                [$today->toDateTimeString()]
            )
            ->orderBy('starts_at')
            ->get();

        return view('matches.index', [
            'matches' => $matches,
        ]);
    }
}
