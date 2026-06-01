<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\MatchController as AdminMatchController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\MatchController;
use App\Http\Controllers\PredictionController;
use App\Http\Controllers\PrivateLeagueController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::get('/leaderboard', [LeaderboardController::class, 'index'])->name('leaderboard.index');
    Route::get('/matches', [MatchController::class, 'index'])->name('matches.index');
    Route::get('/predictions', [PredictionController::class, 'index'])->name('predictions.index');
    Route::post('/predictions/bulk', [PredictionController::class, 'bulkStore'])->name('predictions.bulk-store');
    Route::get('/my-predictions', [PredictionController::class, 'history'])->name('predictions.history');
    Route::get('/matches/{tournamentMatch}/prediction', [PredictionController::class, 'show'])->name('predictions.show');
    Route::post('/matches/{tournamentMatch}/prediction', [PredictionController::class, 'store'])->name('predictions.store');
    Route::get('/private-leagues/search', [PrivateLeagueController::class, 'search'])->name('private-leagues.search');
    Route::get('/private-leagues/create', [PrivateLeagueController::class, 'create'])->name('private-leagues.create');
    Route::get('/private-leagues/invite/{code}', [PrivateLeagueController::class, 'invite'])->name('private-leagues.invite');
    Route::post('/private-leagues', [PrivateLeagueController::class, 'store'])->name('private-leagues.store');
    Route::post('/private-leagues/{privateLeague}/join-requests', [PrivateLeagueController::class, 'storeJoinRequest'])->name('private-leagues.join-requests.store');
    Route::post('/private-leagues/{privateLeague}/join-requests/{leagueJoinRequest}/accept', [PrivateLeagueController::class, 'acceptJoinRequest'])->name('private-leagues.join-requests.accept');
    Route::post('/private-leagues/{privateLeague}/join-requests/{leagueJoinRequest}/reject', [PrivateLeagueController::class, 'rejectJoinRequest'])->name('private-leagues.join-requests.reject');
    Route::delete('/private-leagues/{privateLeague}/members/{user}/remove', [PrivateLeagueController::class, 'removeMember'])->name('private-leagues.members.remove');
    Route::get('/private-leagues/{privateLeague}', [PrivateLeagueController::class, 'show'])->name('private-leagues.show');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', AdminDashboardController::class)->name('dashboard');
        Route::get('/matches', [AdminMatchController::class, 'index'])->name('matches.index');
        Route::get('/matches/{tournamentMatch}/result', [AdminMatchController::class, 'editResult'])->name('matches.result.edit');
        Route::post('/matches/{tournamentMatch}/result', [AdminMatchController::class, 'updateResult'])->name('matches.result.update');
    });

require __DIR__.'/auth.php';
