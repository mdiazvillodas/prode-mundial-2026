<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TournamentMatch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

    public function editResult(TournamentMatch $tournamentMatch): View
    {
        $tournamentMatch->load(['teamA', 'teamB', 'winnerTeam', 'tournament']);

        return view('admin.matches.result', [
            'canLoadResult' => $this->canLoadResult($tournamentMatch),
            'tournamentMatch' => $tournamentMatch,
        ]);
    }

    public function updateResult(Request $request, TournamentMatch $tournamentMatch): RedirectResponse
    {
        $tournamentMatch->load(['teamA', 'teamB']);

        if (! $this->canLoadResult($tournamentMatch)) {
            return back()
                ->withErrors(['result' => __('No se puede cargar resultado para un partido sin equipos definidos.')])
                ->withInput();
        }

        $validated = $request->validate([
            'team_a_score' => ['required', 'integer', 'min:0', 'max:99'],
            'team_b_score' => ['required', 'integer', 'min:0', 'max:99'],
        ]);

        $teamAScore = (int) $validated['team_a_score'];
        $teamBScore = (int) $validated['team_b_score'];

        $winnerTeamId = null;

        if ($teamAScore > $teamBScore) {
            $winnerTeamId = $tournamentMatch->team_a_id;
        } elseif ($teamBScore > $teamAScore) {
            $winnerTeamId = $tournamentMatch->team_b_id;
        }

        $tournamentMatch->update([
            'team_a_score' => $teamAScore,
            'team_b_score' => $teamBScore,
            'winner_team_id' => $winnerTeamId,
            'status' => TournamentMatch::STATUS_FINISHED,
        ]);

        return redirect()
            ->route('admin.matches.index')
            ->with('status', __('Resultado guardado.'));
    }

    private function canLoadResult(TournamentMatch $tournamentMatch): bool
    {
        return (bool) $tournamentMatch->team_a_id && (bool) $tournamentMatch->team_b_id;
    }
}
