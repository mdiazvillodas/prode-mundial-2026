<?php

namespace App\Http\Controllers;

use App\Models\Prediction;
use App\Models\TournamentMatch;
use App\Support\MatchDisplayTime;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PredictionController extends Controller
{
    public function index(Request $request): View
    {
        $timezone = $this->viewerTimezone($request);
        $dateOptions = $this->matchDateOptions($timezone);
        $selectedDate = $this->selectedMatchDate((string) $request->query('date', ''), $dateOptions, $timezone);

        $matches = collect();

        if ($selectedDate !== null) {
            $localStart = Carbon::parse($selectedDate, $timezone)->startOfDay();
            $localEnd = $localStart->copy()->endOfDay();

            $matches = TournamentMatch::query()
                ->with([
                    'teamA',
                    'teamB',
                    'winnerTeam',
                    'tournament',
                    'predictions' => fn ($query) => $query->where('user_id', $request->user()->id),
                ])
                ->whereNotNull('starts_at')
                ->whereBetween('starts_at', [
                    $localStart->copy()->utc(),
                    $localEnd->copy()->utc(),
                ])
                ->orderBy('starts_at')
                ->orderBy('id')
                ->get();
        }

        $matchDisplayTimes = $matches
            ->mapWithKeys(fn (TournamentMatch $match): array => [
                $match->id => [
                    'kickoff_time' => MatchDisplayTime::localTime($match->starts_at, $timezone),
                    'prediction_closes_time' => MatchDisplayTime::localTime($match->predictionClosesAt(), $timezone),
                ],
            ])
            ->all();

        return view('predictions.index', [
            'dateOptions' => $dateOptions,
            'matchDisplayTimes' => $matchDisplayTimes,
            'matches' => $matches,
            'selectedDate' => $selectedDate,
            'timezone' => $timezone,
        ]);
    }

    private function viewerTimezone(Request $request): string
    {
        $requestedTimezone = (string) $request->query('tz', '');

        if ($requestedTimezone !== '' && in_array($requestedTimezone, timezone_identifiers_list(), true)) {
            return $requestedTimezone;
        }

        return (string) config('app.timezone');
    }

    /**
     * @return Collection<int, array{date: string, count: int, date_label: string, mobile_label: string}>
     */
    private function matchDateOptions(string $timezone): Collection
    {
        return TournamentMatch::query()
            ->whereNotNull('starts_at')
            ->orderBy('starts_at')
            ->get(['starts_at'])
            ->groupBy(fn (TournamentMatch $match): string => MatchDisplayTime::localDate($match->starts_at, $timezone))
            ->map(fn (Collection $matches, string $date): array => [
                'date' => $date,
                'count' => $matches->count(),
                'date_label' => Carbon::parse($date, $timezone)->translatedFormat('D d M'),
                'mobile_label' => Carbon::parse($date, $timezone)->translatedFormat('D d'),
            ])
            ->values();
    }

    /**
     * @param  Collection<int, array{date: string, count: int, date_label: string, mobile_label: string}>  $dateOptions
     */
    private function selectedMatchDate(string $requestedDate, Collection $dateOptions, string $timezone): ?string
    {
        if ($dateOptions->isEmpty()) {
            return null;
        }

        if ($requestedDate !== '' && $dateOptions->contains('date', $requestedDate)) {
            return $requestedDate;
        }

        $today = now($timezone)->toDateString();

        if ($dateOptions->contains('date', $today)) {
            return $today;
        }

        $nextDate = $dateOptions
            ->first(fn (array $option): bool => $option['date'] > $today);

        return $nextDate['date'] ?? $dateOptions->first()['date'];
    }

    public function history(Request $request): View
    {
        $predictions = $request->user()
            ->predictions()
            ->with([
                'predictedQualifiedTeam',
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

        $rules = [
            'team_a_score' => ['required', 'integer', 'min:0', 'max:99'],
            'team_b_score' => ['required', 'integer', 'min:0', 'max:99'],
        ];

        if ($tournamentMatch->requiresQualifiedTeamPrediction()) {
            $rules['predicted_qualified_team_id'] = [
                'required',
                'integer',
                Rule::in([$tournamentMatch->team_a_id, $tournamentMatch->team_b_id]),
            ];
        }

        $validated = $request->validate($rules);

        $request->user()->predictions()->updateOrCreate(
            ['match_id' => $tournamentMatch->id],
            [
                'team_a_score' => $validated['team_a_score'],
                'team_b_score' => $validated['team_b_score'],
                'predicted_qualified_team_id' => $validated['predicted_qualified_team_id'] ?? null,
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
                    || filled($prediction['team_b_score'] ?? null)
                    || filled($prediction['predicted_qualified_team_id'] ?? null);
            });

        if ($submittedPredictions->isEmpty()) {
            return redirect()
                ->route('predictions.index', $this->predictionIndexQuery($request))
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

            $rules = [
                'team_a_score' => ['required', 'integer', 'min:0', 'max:99'],
                'team_b_score' => ['required', 'integer', 'min:0', 'max:99'],
            ];

            if ($match->requiresQualifiedTeamPrediction()) {
                $rules['predicted_qualified_team_id'] = [
                    'required',
                    'integer',
                    Rule::in([$match->team_a_id, $match->team_b_id]),
                ];
            }

            $validator = Validator::make($prediction, $rules);

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
                    'predicted_qualified_team_id' => $prediction['predicted_qualified_team_id'] ?? null,
                    'status' => Prediction::STATUS_SUBMITTED,
                    'points_awarded' => null,
                ],
            );
        }

        return redirect()
            ->route('predictions.index', $this->predictionIndexQuery($request))
            ->with('status', __('Predicciones guardadas.'));
    }

    /**
     * @return array{date?: string, tz?: string}
     */
    private function predictionIndexQuery(Request $request): array
    {
        $query = [];
        $date = (string) $request->query('date', '');
        $timezone = (string) $request->query('tz', '');

        if ($date !== '') {
            $query['date'] = $date;
        }

        if ($timezone !== '' && in_array($timezone, timezone_identifiers_list(), true)) {
            $query['tz'] = $timezone;
        }

        return $query;
    }
}
