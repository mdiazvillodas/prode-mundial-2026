<?php

namespace App\Http\Controllers;

use App\Models\Prediction;
use App\Models\TournamentMatch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PredictionController extends Controller
{
    public function index(Request $request): View
    {
        $matches = TournamentMatch::query()
            ->with([
                'teamA',
                'teamB',
                'winnerTeam',
                'tournament',
                'predictions' => fn ($query) => $query->where('user_id', $request->user()->id),
            ])
            ->orderByRaw('starts_at IS NULL')
            ->orderBy('starts_at')
            ->orderBy('id')
            ->get()
            ->groupBy(fn (TournamentMatch $match) => $match->starts_at?->format('Y-m-d') ?? 'unscheduled');

        return view('predictions.index', [
            'matchesByDate' => $matches,
        ]);
    }

    public function history(Request $request): View
    {
        $predictions = $request->user()
            ->predictions()
            ->with([
                'match.teamA',
                'match.teamB',
                'match.winnerTeam',
                'match.tournament',
            ])
            ->orderByDesc(
                TournamentMatch::query()
                    ->select('starts_at')
                    ->whereColumn('matches.id', 'predictions.match_id')
                    ->limit(1),
            )
            ->orderByDesc('created_at')
            ->get();

        return view('predictions.history', [
            'predictions' => $predictions,
        ]);
    }

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

    public function bulkStore(Request $request): RedirectResponse
    {
        $submittedPredictions = collect($request->input('predictions', []))
            ->filter(function (mixed $prediction): bool {
                if (! is_array($prediction)) {
                    return false;
                }

                return ($prediction['changed'] ?? '0') === '1'
                    || filled($prediction['team_a_score'] ?? null)
                    || filled($prediction['team_b_score'] ?? null);
            });

        if ($submittedPredictions->isEmpty()) {
            return redirect()
                ->route('predictions.index')
                ->with('status', __('No hay cambios para guardar.'));
        }

        $matches = TournamentMatch::query()
            ->whereIn('id', $submittedPredictions->keys())
            ->get()
            ->keyBy('id');

        $errors = [];
        $validPredictions = [];

        foreach ($submittedPredictions as $matchId => $prediction) {
            $match = $matches->get((int) $matchId);

            if (! $match || ! $match->isPredictable()) {
                $errors["predictions.{$matchId}.team_a_score"][] = __('Este partido no permite predicciones.');

                continue;
            }

            $validator = Validator::make($prediction, [
                'team_a_score' => ['required', 'integer', 'min:0', 'max:99'],
                'team_b_score' => ['required', 'integer', 'min:0', 'max:99'],
            ]);

            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $field => $messages) {
                    $errors["predictions.{$matchId}.{$field}"] = $messages;
                }

                continue;
            }

            $validPredictions[(int) $matchId] = $validator->validated();
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        foreach ($validPredictions as $matchId => $prediction) {
            $request->user()->predictions()->updateOrCreate(
                ['match_id' => $matchId],
                [
                    'team_a_score' => $prediction['team_a_score'],
                    'team_b_score' => $prediction['team_b_score'],
                    'status' => Prediction::STATUS_SUBMITTED,
                    'points_awarded' => null,
                ],
            );
        }

        return redirect()
            ->route('predictions.index')
            ->with('status', __('Predicciones guardadas.'));
    }
}
