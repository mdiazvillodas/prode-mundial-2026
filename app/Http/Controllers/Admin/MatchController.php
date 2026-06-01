<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\TournamentMatch;
use App\Services\MatchPredictionSettlementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
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

    public function editTeams(TournamentMatch $tournamentMatch): View
    {
        if ($tournamentMatch->team_a_id && $tournamentMatch->team_b_id && $tournamentMatch->status !== TournamentMatch::STATUS_PLACEHOLDER) {
            return redirect()
                ->route('admin.matches.index')
                ->with('status', __('Este partido ya tiene equipos asignados.'));
        }

        $teams = Team::orderBy('name')->get();

        return view('admin.matches.teams', [
            'tournamentMatch' => $tournamentMatch,
            'teams' => $teams,
        ]);
    }

    public function updateTeams(Request $request, TournamentMatch $tournamentMatch): RedirectResponse
    {
        if ($tournamentMatch->team_a_id && $tournamentMatch->team_b_id && $tournamentMatch->status !== TournamentMatch::STATUS_PLACEHOLDER) {
            return redirect()
                ->route('admin.matches.index')
                ->with('status', __('Este partido ya tiene equipos asignados.'));
        }

        $validated = $request->validate([
            'team_a_id' => ['required', 'integer', Rule::exists('teams', 'id')],
            'team_b_id' => ['required', 'integer', Rule::exists('teams', 'id'), 'different:team_a_id'],
        ]);

        $updateData = [
            'team_a_id' => $validated['team_a_id'],
            'team_b_id' => $validated['team_b_id'],
        ];

        if ($tournamentMatch->status === TournamentMatch::STATUS_PLACEHOLDER) {
            $updateData['status'] = TournamentMatch::STATUS_SCHEDULED;
        }

        $tournamentMatch->update($updateData);

        return redirect()
            ->route('admin.matches.index')
            ->with('status', __('Equipos asignados. El partido ya puede recibir predicciones cuando sea elegible.'));
    }

    public function editResult(TournamentMatch $tournamentMatch): View
    {
        $tournamentMatch->load(['teamA', 'teamB', 'winnerTeam', 'tournament']);

        return view('admin.matches.result', [
            'canLoadResult' => $this->canLoadResult($tournamentMatch),
            'tournamentMatch' => $tournamentMatch,
        ]);
    }

    public function updateResult(
        Request $request,
        TournamentMatch $tournamentMatch,
        MatchPredictionSettlementService $settlement,
    ): RedirectResponse
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

        $scoredPredictions = $settlement->score($tournamentMatch);

        return redirect()
            ->route('admin.matches.index')
            ->with(
                'status',
                $scoredPredictions > 0
                    ? __('Resultado guardado. Predicciones puntuadas: :count.', ['count' => $scoredPredictions])
                    : __('Resultado guardado. No habia predicciones para puntuar.'),
            );
    }

    private function canLoadResult(TournamentMatch $tournamentMatch): bool
    {
        return (bool) $tournamentMatch->team_a_id && (bool) $tournamentMatch->team_b_id;
    }
}
