<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TournamentMatch;
use Illuminate\View\View;

class MatchController extends Controller
{
    public function index(): View
    {
        $matches = TournamentMatch::query()
            ->with(['teamA', 'teamB', 'winnerTeam', 'tournament'])
            ->orderByRaw('starts_at IS NULL')
            ->orderBy('starts_at')
            ->orderBy('id')
            ->get();

        return view('admin.matches.index', [
            'matches' => $matches,
        ]);
    }
}
