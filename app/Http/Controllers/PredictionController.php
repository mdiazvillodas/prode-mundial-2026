<?php

namespace App\Http\Controllers;

use App\Models\Prediction;
use App\Models\TournamentMatch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PredictionController extends Controller
{
    public function show(Request $request, TournamentMatch $tournamentMatch): View
    {
        abort_unless($tournamentMatch->isPredictable(), 403);

        $tournamentMatch->load(['teamA', 'teamB', 'tournament']);

        $prediction = $request->user()
            ->predictions()
            ->where('match_id', $tournamentMatch->id)
            ->first();

        return view('predictions.show', [
            'prediction' => $prediction,
            'tournamentMatch' => $tournamentMatch,
        ]);
    }

    public function store(Request $request, TournamentMatch $tournamentMatch): RedirectResponse
    {
        abort_unless($tournamentMatch->isPredictable(), 403);

        $validated = $request->validate([
            'team_a_score' => ['required', 'integer', 'min:0', 'max:99'],
            'team_b_score' => ['required', 'integer', 'min:0', 'max:99'],
        ]);

        $request->user()->predictions()->updateOrCreate(
            ['match_id' => $tournamentMatch->id],
            [
                'team_a_score' => $validated['team_a_score'],
                'team_b_score' => $validated['team_b_score'],
                'status' => Prediction::STATUS_SUBMITTED,
                'points_awarded' => null,
            ],
        );

        return redirect()
            ->route('predictions.show', $tournamentMatch)
            ->with('status', __('Prediccion guardada.'));
    }
}
