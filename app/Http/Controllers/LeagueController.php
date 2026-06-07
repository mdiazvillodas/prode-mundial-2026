<?php

namespace App\Http\Controllers;

use App\Models\LeagueMembership;
use App\Services\PredictionScoringService;
use App\Services\Rankings\RecentFormService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LeagueController extends Controller
{
    public function index(Request $request, RecentFormService $recentForm): View
    {
        $privateLeagues = $request->user()
            ->leagueMemberships()
            ->where('status', LeagueMembership::STATUS_ACTIVE)
            ->with('privateLeague.owner')
            ->orderBy('joined_at')
            ->limit(3)
            ->get()
            ->pluck('privateLeague')
            ->filter()
            ->values();

        $privateLeaderboards = $privateLeagues->mapWithKeys(fn ($privateLeague) => [
            $privateLeague->id => $recentForm->attachToEntries($this->privateLeagueLeaderboard($privateLeague->id)),
        ]);

        return view('leagues.index', [
            'globalLeaderboard' => $recentForm->attachToEntries($this->globalLeaderboard()),
            'privateLeaderboards' => $privateLeaderboards,
            'privateLeagues' => $privateLeagues,
        ]);
    }

    private function globalLeaderboard()
    {
        return DB::table('users')
            ->join('predictions', 'predictions.user_id', '=', 'users.id')
            ->whereNotNull('predictions.points_awarded')
            ->select([
                'users.id',
                'users.name',
                'users.username',
                DB::raw('SUM(predictions.points_awarded) as total_points'),
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

    private function privateLeagueLeaderboard(int $privateLeagueId)
    {
        return DB::table('league_memberships')
            ->join('users', 'users.id', '=', 'league_memberships.user_id')
            ->leftJoin('predictions', function ($join): void {
                $join->on('predictions.user_id', '=', 'users.id')
                    ->whereNotNull('predictions.points_awarded');
            })
            ->where('league_memberships.private_league_id', $privateLeagueId)
            ->where('league_memberships.status', LeagueMembership::STATUS_ACTIVE)
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
}
