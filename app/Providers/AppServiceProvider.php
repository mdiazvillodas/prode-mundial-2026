<?php

namespace App\Providers;

use App\Models\LeagueJoinRequest;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (app()->environment(['production', 'staging']) || env('RAILWAY_ENVIRONMENT') || env('RAILWAY_PROJECT_ID')) {
            URL::forceScheme('https');
        }

        View::composer('layouts.navigation', function ($view): void {
            $user = Auth::user();
            $pendingLeague = null;

            if ($user) {
                $pendingLeague = $user->ownedPrivateLeague()
                    ->with([
                        'joinRequests' => fn ($query) => $query
                            ->where('status', LeagueJoinRequest::STATUS_PENDING)
                            ->with('user')
                            ->latest(),
                    ])
                    ->first();

                if ($pendingLeague?->joinRequests->isEmpty()) {
                    $pendingLeague = null;
                }
            }

            $view->with('headerPendingJoinRequestsLeague', $pendingLeague);
        });
    }
}
