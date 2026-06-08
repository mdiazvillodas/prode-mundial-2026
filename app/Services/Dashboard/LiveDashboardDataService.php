<?php

namespace App\Services\Dashboard;

use App\Models\LeagueMembership;
use App\Models\Prediction;
use App\Models\PrivateLeague;
use App\Models\Team;
use App\Models\TournamentMatch;
use App\Models\User;
use App\Services\PredictionScoringService;
use Carbon\CarbonInterface;
use DateTimeZone;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LiveDashboardDataService
{
    private const LIVE_API_STATUSES = [
        '1H',
        '2H',
        'HT',
        'ET',
        'BT',
        'P',
        'SUSP',
        'INT',
        'LIVE',
    ];

    /**
     * @return array<string, mixed>
     */
    public function forUser(User $user, ?string $timezone = null): array
    {
        $timezone = $this->resolveTimezone($timezone);
        $teamAverages = $this->teamGoalAverages();
        $leagueSummary = $this->leagueSummary($user);

        return [
            'timezone' => $timezone,
            'pending_predictions' => $this->pendingPredictions($user, $timezone, $teamAverages),
            'daily_matches' => $this->dailyMatches($user, $timezone),
            'live_matches' => $this->liveMatches($user, $timezone),
            'friend_activity' => $this->friendActivity($user, $timezone),
            'league_summary' => $leagueSummary,
            'has_active_private_leagues' => ! empty($leagueSummary['private_leagues']),
        ];
    }

    /**
     * @param  array<int, array{gf: float, gc: float, played: int}>  $teamAverages
     * @return array<string, mixed>|null
     */
    private function pendingPredictions(User $user, string $timezone, array $teamAverages): ?array
    {
        $matches = TournamentMatch::query()
            ->with([
                'teamA',
                'teamB',
                'predictions' => fn ($query) => $query->where('user_id', $user->id),
            ])
            ->whereNotNull('team_a_id')
            ->whereNotNull('team_b_id')
            ->orderBy('starts_at')
            ->get()
            ->filter(fn (TournamentMatch $match): bool => $match->isPredictable())
            ->filter(fn (TournamentMatch $match): bool => $match->predictions->isEmpty())
            ->groupBy(fn (TournamentMatch $match): string => $this->localDate($match->starts_at, $timezone))
            ->sortKeys();

        if ($matches->isEmpty()) {
            return null;
        }

        $localDate = (string) $matches->keys()->first();
        $dayMatches = $matches->first()->values();

        return [
            'local_date' => $localDate,
            'prediction_url' => route('predictions.index', [
                'date' => $localDate,
                'tz' => $timezone,
            ]),
            'matches' => $dayMatches
                ->map(fn (TournamentMatch $match): array => $this->matchSummary($match, $timezone, $teamAverages))
                ->values()
                ->all(),
            ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function dailyMatches(User $user, string $timezone): ?array
    {
        $matchesByDate = TournamentMatch::query()
            ->with([
                'teamA',
                'teamB',
                'predictions' => fn ($query) => $query->where('user_id', $user->id),
            ])
            ->whereNotNull('starts_at')
            ->orderBy('starts_at')
            ->get()
            ->groupBy(fn (TournamentMatch $match): string => $this->localDate($match->starts_at, $timezone))
            ->sortKeys();

        if ($matchesByDate->isEmpty()) {
            return null;
        }

        $today = now()->timezone($timezone)->toDateString();
        $localDate = $this->relevantLocalDate($matchesByDate->keys(), $today);
        $matches = $matchesByDate->get($localDate, collect())->values();

        if ($matches->isEmpty()) {
            return null;
        }

        return [
            'local_date' => $localDate,
            'matches' => $matches
                ->map(fn (TournamentMatch $match): array => $this->dailyMatchSummary($match, $timezone))
                ->values()
                ->all(),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function liveMatches(User $user, string $timezone): array
    {
        return TournamentMatch::query()
            ->with([
                'teamA',
                'teamB',
                'predictions' => fn ($query) => $query->where('user_id', $user->id),
            ])
            ->whereIn('api_status', self::LIVE_API_STATUSES)
            ->orderBy('starts_at')
            ->get()
            ->map(function (TournamentMatch $match) use ($timezone): array {
                $prediction = $match->predictions->first();

                return [
                    'id' => $match->id,
                    'local_date' => $this->localDate($match->starts_at, $timezone),
                    'kickoff_local_time' => $this->localTime($match->starts_at, $timezone),
                    'team_a' => $this->teamSummary($match->teamA),
                    'team_b' => $this->teamSummary($match->teamB),
                    'score' => [
                        'team_a' => $match->team_a_score,
                        'team_b' => $match->team_b_score,
                    ],
                    'status' => $match->status,
                    'api_status' => $match->api_status,
                    'status_label' => $match->api_status ?? $match->status,
                    'user_prediction' => $prediction ? [
                        'team_a_score' => $prediction->team_a_score,
                        'team_b_score' => $prediction->team_b_score,
                    ] : null,
                    'provisional_state' => $this->provisionalPredictionState($match, $prediction),
                    'last_synced_at' => $match->last_synced_at?->toIso8601String(),
                    'last_synced_minutes_ago' => $match->last_synced_at?->diffInMinutes(now()),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>|null
     */
    private function friendActivity(User $user, string $timezone): ?array
    {
        $matches = TournamentMatch::query()
            ->whereNotNull('team_a_id')
            ->whereNotNull('team_b_id')
            ->orderBy('starts_at')
            ->get()
            ->filter(fn (TournamentMatch $match): bool => $match->isPredictable())
            ->groupBy(fn (TournamentMatch $match): string => $this->localDate($match->starts_at, $timezone))
            ->sortKeys();

        if ($matches->isEmpty()) {
            return null;
        }

        $localDate = (string) $matches->keys()->first();
        $matchIds = $matches->first()->pluck('id')->all();
        $totalMatches = count($matchIds);

        if ($totalMatches === 0) {
            return null;
        }

        $friendIds = LeagueMembership::query()
            ->join('league_memberships as current_memberships', 'current_memberships.private_league_id', '=', 'league_memberships.private_league_id')
            ->where('current_memberships.user_id', $user->id)
            ->where('current_memberships.status', LeagueMembership::STATUS_ACTIVE)
            ->where('league_memberships.status', LeagueMembership::STATUS_ACTIVE)
            ->where('league_memberships.user_id', '!=', $user->id)
            ->distinct()
            ->pluck('league_memberships.user_id');

        if ($friendIds->isEmpty()) {
            return [
                'local_date' => $localDate,
                'total_matches' => $totalMatches,
                'friends' => [],
            ];
        }

        $completionCounts = Prediction::query()
            ->whereIn('user_id', $friendIds)
            ->whereIn('match_id', $matchIds)
            ->select('user_id', DB::raw('COUNT(*) as completed_count'))
            ->groupBy('user_id')
            ->pluck('completed_count', 'user_id');

        $friends = User::query()
            ->whereIn('id', $friendIds)
            ->get()
            ->map(fn (User $friend): array => [
                'id' => $friend->id,
                'name' => $friend->name,
                'username' => $friend->username,
                'avatar' => $friend->profileAvatar(),
                'completed_count' => (int) ($completionCounts[$friend->id] ?? 0),
                'total_matches' => $totalMatches,
            ])
            ->sortBy([
                ['completed_count', 'desc'],
                ['username', 'asc'],
                ['name', 'asc'],
            ])
            ->values()
            ->all();

        return [
            'local_date' => $localDate,
            'total_matches' => $totalMatches,
            'friends' => $friends,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function dailyMatchSummary(TournamentMatch $match, string $timezone): array
    {
        $prediction = $match->predictions->first();

        return [
            'id' => $match->id,
            'local_date' => $this->localDate($match->starts_at, $timezone),
            'kickoff_local_time' => $this->localTime($match->starts_at, $timezone),
            'team_a' => $this->teamSummary($match->teamA),
            'team_b' => $this->teamSummary($match->teamB),
            'score' => [
                'team_a' => $match->team_a_score,
                'team_b' => $match->team_b_score,
            ],
            'status' => $match->status,
            'api_status' => $match->api_status,
            'display_state' => $this->displayState($match),
            'status_label' => $this->dailyStatusLabel($match),
            'user_prediction' => $prediction ? [
                'team_a_score' => $prediction->team_a_score,
                'team_b_score' => $prediction->team_b_score,
            ] : null,
            'provisional_state' => $this->provisionalPredictionState($match, $prediction),
            'last_synced_at' => $match->last_synced_at?->toIso8601String(),
            'last_synced_minutes_ago' => $match->last_synced_at?->diffInMinutes(now()),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function leagueSummary(User $user): array
    {
        $globalLeaderboard = $this->globalLeaderboard();
        $globalEntry = $globalLeaderboard->firstWhere('id', $user->id);

        $privateLeagues = $user->leagueMemberships()
            ->where('status', LeagueMembership::STATUS_ACTIVE)
            ->with('privateLeague')
            ->orderBy('joined_at')
            ->get()
            ->pluck('privateLeague')
            ->filter()
            ->values();

        return [
            'general' => [
                'position' => $this->positionInLeaderboard($globalLeaderboard, $user->id),
                'points' => (int) ($globalEntry?->total_points ?? 0),
            ],
            'private_leagues' => $privateLeagues
                ->map(function (PrivateLeague $privateLeague) use ($user): array {
                    $leaderboard = $this->privateLeagueLeaderboard($privateLeague->id);
                    $entry = $leaderboard->firstWhere('id', $user->id);

                    return [
                        'id' => $privateLeague->id,
                        'name' => $privateLeague->name,
                        'position' => $this->positionInLeaderboard($leaderboard, $user->id),
                        'points' => (int) ($entry?->total_points ?? 0),
                    ];
                })
                ->values()
                ->all(),
        ];
    }

    /**
     * @param  array<int, array{gf: float, gc: float, played: int}>  $teamAverages
     * @return array<string, mixed>
     */
    private function matchSummary(TournamentMatch $match, string $timezone, array $teamAverages): array
    {
        $localDate = $this->localDate($match->starts_at, $timezone);

        return [
            'id' => $match->id,
            'local_date' => $localDate,
            'kickoff_local_time' => $this->localTime($match->starts_at, $timezone),
            'prediction_closes_local_time' => $this->localTime($match->predictionClosesAt(), $timezone),
            'stage' => $match->stage,
            'group' => $match->group,
            'team_a' => $this->teamSummary($match->teamA, $teamAverages),
            'team_b' => $this->teamSummary($match->teamB, $teamAverages),
            'prediction_url' => route('predictions.index', [
                'date' => $localDate,
                'tz' => $timezone,
            ]),
        ];
    }

    /**
     * @param  array<int, array{gf: float, gc: float, played: int}>  $teamAverages
     * @return array<string, mixed>|null
     */
    private function teamSummary(?Team $team, array $teamAverages = []): ?array
    {
        if (! $team) {
            return null;
        }

        return [
            'id' => $team->id,
            'name' => $team->name,
            'short_name' => $team->short_name,
            'flag_path' => $team->flag_path,
            'goals_for_avg' => $teamAverages[$team->id]['gf'] ?? null,
            'goals_against_avg' => $teamAverages[$team->id]['gc'] ?? null,
            'finished_matches_count' => $teamAverages[$team->id]['played'] ?? 0,
        ];
    }

    private function provisionalPredictionState(TournamentMatch $match, ?Prediction $prediction): string
    {
        if (! $prediction) {
            return 'none';
        }

        if ($match->requiresQualifiedTeamPrediction()) {
            return 'none';
        }

        if ($match->team_a_score === null || $match->team_b_score === null) {
            return 'none';
        }

        if (
            $prediction->team_a_score === $match->team_a_score
            && $prediction->team_b_score === $match->team_b_score
        ) {
            return 'exact';
        }

        if ($this->outcome($prediction->team_a_score, $prediction->team_b_score) === $this->outcome($match->team_a_score, $match->team_b_score)) {
            return 'trend';
        }

        return 'incorrect';
    }

    private function outcome(int $teamAScore, int $teamBScore): string
    {
        if ($teamAScore > $teamBScore) {
            return 'team_a';
        }

        if ($teamBScore > $teamAScore) {
            return 'team_b';
        }

        return 'draw';
    }

    private function displayState(TournamentMatch $match): string
    {
        if (in_array($match->api_status, self::LIVE_API_STATUSES, true)) {
            return 'live';
        }

        if ($match->status === TournamentMatch::STATUS_FINISHED) {
            return 'finished';
        }

        return 'scheduled';
    }

    private function dailyStatusLabel(TournamentMatch $match): string
    {
        if (in_array($match->api_status, self::LIVE_API_STATUSES, true)) {
            return $match->api_status ?? 'En juego';
        }

        if ($match->status === TournamentMatch::STATUS_FINISHED) {
            return 'Finalizado';
        }

        return 'Programado';
    }

    /**
     * @return array<int, array{gf: float, gc: float, played: int}>
     */
    private function teamGoalAverages(): array
    {
        $totals = [];

        TournamentMatch::query()
            ->where('status', TournamentMatch::STATUS_FINISHED)
            ->whereNotNull('team_a_id')
            ->whereNotNull('team_b_id')
            ->whereNotNull('team_a_score')
            ->whereNotNull('team_b_score')
            ->get()
            ->each(function (TournamentMatch $match) use (&$totals): void {
                $this->addTeamResult($totals, $match->team_a_id, $match->team_a_score, $match->team_b_score);
                $this->addTeamResult($totals, $match->team_b_id, $match->team_b_score, $match->team_a_score);
            });

        return collect($totals)
            ->map(fn (array $total): array => [
                'gf' => round($total['gf'] / $total['played'], 2),
                'gc' => round($total['gc'] / $total['played'], 2),
                'played' => $total['played'],
            ])
            ->all();
    }

    /**
     * @param  array<int, array{gf: int, gc: int, played: int}>  $totals
     */
    private function addTeamResult(array &$totals, int $teamId, int $goalsFor, int $goalsAgainst): void
    {
        $totals[$teamId] ??= ['gf' => 0, 'gc' => 0, 'played' => 0];
        $totals[$teamId]['gf'] += $goalsFor;
        $totals[$teamId]['gc'] += $goalsAgainst;
        $totals[$teamId]['played']++;
    }

    private function globalLeaderboard(): Collection
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

    private function privateLeagueLeaderboard(int $privateLeagueId): Collection
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

    private function positionInLeaderboard(Collection $leaderboard, int $userId): ?int
    {
        $index = $leaderboard->search(fn ($entry): bool => $entry->id === $userId);

        return $index === false ? null : $index + 1;
    }

    private function localDate(?CarbonInterface $date, string $timezone): ?string
    {
        return $date?->copy()->timezone($timezone)->toDateString();
    }

    private function localTime(?CarbonInterface $date, string $timezone): ?string
    {
        return $date?->copy()->timezone($timezone)->format('H:i');
    }

    /**
     * @param  Collection<int, string>  $localDates
     */
    private function relevantLocalDate(Collection $localDates, string $today): string
    {
        $dates = $localDates->values();

        return $dates->first(fn (string $date): bool => $date >= $today)
            ?? (string) $dates->last();
    }

    private function resolveTimezone(?string $timezone): string
    {
        if ($timezone) {
            try {
                new DateTimeZone($timezone);

                return $timezone;
            } catch (\Throwable) {
                //
            }
        }

        return config('app.timezone', 'UTC');
    }
}
