<?php

namespace App\Support;

use Illuminate\Console\Command;

class ApiFootballProductionSyncGuard
{
    public static function allowsSync(): bool
    {
        return ! self::isProtectedEnvironment()
            || (bool) config('services.api_football.allow_production_sync');
    }

    public static function warnIfAllowed(Command $command): void
    {
        if (! self::isProtectedEnvironment() || ! (bool) config('services.api_football.allow_production_sync')) {
            return;
        }

        $command->warn('API-Football production/live sync is enabled by API_FOOTBALL_ALLOW_PRODUCTION_SYNC=true.');
        $command->warn('Proceeding in APP_ENV='.app()->environment().' and APP_MODE='.config('app.mode').'.');
    }

    private static function isProtectedEnvironment(): bool
    {
        return app()->environment('production') || config('app.mode') === 'live';
    }
}
