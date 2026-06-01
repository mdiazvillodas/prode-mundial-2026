<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Prediction;
use App\Models\PrivateLeague;
use App\Models\TournamentMatch;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'appEnvironment' => config('app.env'),
            'appMode' => config('app.mode', 'test'),
            'counts' => [
                'users' => User::query()->count(),
                'matches' => TournamentMatch::query()->count(),
                'predictions' => Prediction::query()->count(),
                'privateLeagues' => PrivateLeague::query()->count(),
            ],
        ]);
    }
}
