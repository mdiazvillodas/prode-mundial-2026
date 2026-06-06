<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiSyncLog;
use App\Models\Team;
use App\Models\TournamentMatch;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ApiHealthController extends Controller
{
    public function __invoke(): View
    {
        $warningMinutes = (int) config('services.api_football.sync_health_warning_minutes', 15);
        $latestTeamsSync = $this->latestLog('teams');
        $latestFixturesSync = $this->latestLog('fixtures');
        $lastSuccessfulTeamsSync = $this->latestSuccessfulLog('teams');
        $lastSuccessfulFixturesSync = $this->latestSuccessfulLog('fixtures');
        $lastFailedSync = ApiSyncLog::query()
            ->where('provider', 'api-football')
            ->where('status', 'failed')
            ->latest()
            ->first();

        $indicators = [
            'teams' => $this->indicator($latestTeamsSync, $lastSuccessfulTeamsSync, $warningMinutes),
            'fixtures' => $this->indicator($latestFixturesSync, $lastSuccessfulFixturesSync, $warningMinutes),
        ];

        return view('admin.api-health', [
            'warningMinutes' => $warningMinutes,
            'latestTeamsSync' => $latestTeamsSync,
            'latestFixturesSync' => $latestFixturesSync,
            'lastSuccessfulTeamsSync' => $lastSuccessfulTeamsSync,
            'lastSuccessfulFixturesSync' => $lastSuccessfulFixturesSync,
            'lastFailedSync' => $lastFailedSync,
            'indicators' => $indicators,
            'overallStatus' => $this->overallStatus($indicators),
            'counts' => [
                'apiTeams' => Team::query()->where('api_provider', 'api-football')->count(),
                'apiFixtures' => TournamentMatch::query()->where('api_provider', 'api-football')->count(),
                'teamsMissingFlags' => Team::query()
                    ->where('api_provider', 'api-football')
                    ->where(function ($query) {
                        $query->whereNull('flag_path')->orWhere('flag_path', '');
                    })
                    ->count(),
            ],
            'fixturesByStatus' => TournamentMatch::query()
                ->select('api_status', DB::raw('count(*) as total'))
                ->where('api_provider', 'api-football')
                ->groupBy('api_status')
                ->orderBy('api_status')
                ->pluck('total', 'api_status'),
            'recentLogs' => ApiSyncLog::query()
                ->where('provider', 'api-football')
                ->latest()
                ->limit(20)
                ->get(),
        ]);
    }

    private function latestLog(string $syncType): ?ApiSyncLog
    {
        return ApiSyncLog::query()
            ->where('provider', 'api-football')
            ->where('sync_type', $syncType)
            ->latest()
            ->first();
    }

    private function latestSuccessfulLog(string $syncType): ?ApiSyncLog
    {
        return ApiSyncLog::query()
            ->where('provider', 'api-football')
            ->where('sync_type', $syncType)
            ->where('status', 'success')
            ->latest()
            ->first();
    }

    /**
     * @return array{status: string, label: string}
     */
    private function indicator(?ApiSyncLog $latestLog, ?ApiSyncLog $lastSuccessfulLog, int $warningMinutes): array
    {
        if ($latestLog?->status === 'failed') {
            return ['status' => 'error', 'label' => 'Error'];
        }

        if (! $lastSuccessfulLog?->finished_at) {
            return ['status' => 'warning', 'label' => 'Sin sync exitoso'];
        }

        if ($lastSuccessfulLog->finished_at->lt(now()->subMinutes($warningMinutes))) {
            return ['status' => 'warning', 'label' => 'Desactualizado'];
        }

        return ['status' => 'ok', 'label' => 'OK'];
    }

    /**
     * @param  array<string, array{status: string, label: string}>  $indicators
     */
    private function overallStatus(array $indicators): string
    {
        if (collect($indicators)->contains(fn (array $indicator): bool => $indicator['status'] === 'error')) {
            return 'error';
        }

        if (collect($indicators)->contains(fn (array $indicator): bool => $indicator['status'] === 'warning')) {
            return 'warning';
        }

        return 'ok';
    }
}
