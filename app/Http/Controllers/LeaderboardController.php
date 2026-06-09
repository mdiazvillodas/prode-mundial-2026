<?php

namespace App\Http\Controllers;

use App\Services\PredictionScoringService;
use App\Services\Rankings\RecentFormService;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LeaderboardController extends Controller
{
    public function index(RecentFormService $recentForm): View
    {
        $leaderboard = DB::table('users')
            ->join('predictions', 'predictions.user_id', '=', 'users.id')
            ->whereNotNull('predictions.points_awarded')
            ->select([
                'users.id',
                'users.name',
                'users.username',
                'users.profile_avatar_key',
                DB::raw('SUM(predictions.points_awarded) as total_points'),
                DB::raw('SUM(CASE WHEN predictions.points_awarded = '.PredictionScoringService::POINTS_EXACT_RESULT.' THEN 1 ELSE 0 END) as exact_results_count'),
                DB::raw('SUM(CASE WHEN predictions.points_awarded = '.PredictionScoringService::POINTS_CORRECT_OUTCOME.' THEN 1 ELSE 0 END) as trend_count'),
                DB::raw('COUNT(predictions.id) as scored_predictions_count'),
            ])
            ->groupBy('users.id', 'users.name', 'users.username', 'users.profile_avatar_key')
            ->orderByDesc('total_points')
            ->orderByDesc('exact_results_count')
            ->orderByDesc('trend_count')
            ->orderBy('users.username')
            ->get();

        return view('leaderboard.index', [
            'leaderboard' => $recentForm->attachToEntries($leaderboard),
        ]);
    }
}
