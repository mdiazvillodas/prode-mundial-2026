<?php

namespace App\Http\Controllers;

use App\Models\LeagueMembership;
use App\Models\Prediction;
use App\Models\PrivateLeague;
use App\Models\TournamentMatch;
use App\Services\Dashboard\LiveDashboardDataService;
use App\Services\PredictionScoringService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, LiveDashboardDataService $liveDashboardData): View
    {
        $user = $request->user();
        $leaderboard = $this->globalLeaderboard();
        $currentUserEntry = $leaderboard->firstWhere('id', $user->id);
        $currentUserPosition = $currentUserEntry
            ? $leaderboard->search(fn ($entry) => $entry->id === $user->id) + 1
            : null;

        $activePrivateLeagues = $user->leagueMemberships()
            ->where('status', LeagueMembership::STATUS_ACTIVE)
            ->with('privateLeague')
            ->orderBy('joined_at')
            ->limit(PrivateLeague::MAX_ACTIVE_MEMBERSHIPS_PER_USER)
            ->get()
            ->pluck('privateLeague')
            ->filter()
            ->values();

        return view('dashboard', [
            'activePrivateLeagues' => $activePrivateLeagues,
            'currentUserPosition' => $currentUserPosition,
            'liveDashboardData' => $liveDashboardData->forUser($user, $request->query('tz')),
            'openPredictionsCount' => $this->openPredictionsCount($user->id),
            'scoredPredictionsCount' => $currentUserEntry?->scored_predictions_count ?? 0,
            'totalPoints' => $currentUserEntry?->total_points ?? 0,
        ]);
    }

    private function globalLeaderboard()
    {
        return DB::table('users')
            ->leftJoin('predictions', function ($join): void {
                $join->on('predictions.user_id', '=', 'users.id')
                    ->whereNotNull('predictions.points_awarded');
            })
            ->select([
                'users.id',
                'users.name',
                'users.username',
                DB::raw('COALESCE(SUM(predictions.points_awarded), 0) as total_points'),
                DB::raw('SUM(CASE WHEN predictions.points_awarded = '.PredictionScoringService::POINTS_EXACT_RESULT.' THEN 1 ELSE 0 END) as exact_results_count'),
                DB::raw('SUM(CASE WHEN predictions.points_awarded = '.PredictionScoringService::POINTS_CORRECT_OUTCOME.' THEN 1 ELSE 0 END) as trend_count'),
                DB::raw('COUNT(predictions.id) as scored_predictions_count'),
            ])
            ->groupBy('users.id', 'users.name', 'users.username')
            ->orderByDesc('total_points')
            ->orderByDesc('exact_results_count')
            ->orderByDesc('trend_count')
            ->orderBy('users.username')
            ->get();
    }

    private function openPredictionsCount(int $userId): int
    {
        return TournamentMatch::query()
            ->with(['predictions' => fn ($query) => $query->where('user_id', $userId)])
            ->whereNotNull('team_a_id')
            ->whereNotNull('team_b_id')
            ->whereNotIn('status', [
                TournamentMatch::STATUS_PLACEHOLDER,
                TournamentMatch::STATUS_FINISHED,
                TournamentMatch::STATUS_LOCKED,
            ])
            ->get()
            ->filter(fn (TournamentMatch $match): bool => $match->isPredictable())
            ->filter(fn (TournamentMatch $match): bool => $match->predictions->isEmpty())
            ->count();
    }
}
