<?php

namespace App\Http\Controllers;

use App\Models\LeagueJoinRequest;
use App\Models\LeagueMembership;
use App\Models\LeagueAuditLog;
use App\Models\PrivateLeague;
use App\Models\User;
use App\Services\PredictionScoringService;
use App\Services\Rankings\RecentFormService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PrivateLeagueController extends Controller
{
    private const MAX_ACTIVE_MEMBERSHIPS = 3;

    public function search(Request $request): View
    {
        $query = trim((string) $request->query('q', ''));
        $user = $request->user();

        $privateLeagues = collect();

        if ($query !== '') {
            $privateLeagues = PrivateLeague::query()
                ->with([
                    'owner',
                    'memberships' => fn ($membershipQuery) => $membershipQuery
                        ->where('user_id', $user->id),
                    'joinRequests' => fn ($joinRequestQuery) => $joinRequestQuery
                        ->where('user_id', $user->id)
                        ->latest(),
                ])
                ->where('status', PrivateLeague::STATUS_ACTIVE)
                ->where(function ($leagueQuery) use ($query): void {
                    $leagueQuery
                        ->where('name', 'like', "%{$query}%")
                        ->orWhere('code', 'like', '%'.strtoupper($query).'%');
                })
                ->orderBy('name')
                ->limit(25)
                ->get();
        }

        return view('private-leagues.search', [
            'activeMembershipsCount' => $this->activeMembershipsCount($user->id),
            'privateLeagues' => $privateLeagues,
            'query' => $query,
        ]);
    }

    public function create(Request $request): View|RedirectResponse
    {
        $ownedPrivateLeague = $request->user()->ownedPrivateLeague;

        if ($ownedPrivateLeague) {
            return redirect()
                ->route('private-leagues.show', $ownedPrivateLeague)
                ->with('status', __('Ya tenes una liga privada creada.'));
        }

        return view('private-leagues.create');
    }

    public function store(Request $request): RedirectResponse
    {
        if ($request->user()->ownedPrivateLeague()->exists()) {
            return redirect()
                ->route('private-leagues.show', $request->user()->ownedPrivateLeague)
                ->withErrors(['name' => __('Solo podes crear una liga privada.')]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $privateLeague = $request->user()->ownedPrivateLeague()->create([
            'name' => $validated['name'],
            'status' => PrivateLeague::STATUS_ACTIVE,
        ]);

        return redirect()
            ->route('private-leagues.show', $privateLeague)
            ->with('status', __('Liga privada creada.'));
    }

    public function show(Request $request, PrivateLeague $privateLeague, RecentFormService $recentForm): View
    {
        abort_unless($this->canViewLeague($request, $privateLeague), 403);

        $privateLeague->load([
            'owner',
            'memberships' => fn ($query) => $query
                ->where('status', LeagueMembership::STATUS_ACTIVE)
                ->with('user')
                ->orderBy('joined_at'),
            'joinRequests' => fn ($query) => $query
                ->where('status', LeagueJoinRequest::STATUS_PENDING)
                ->with('user')
                ->latest(),
            'auditLogs' => fn ($query) => $query
                ->with(['actor', 'target'])
                ->latest()
                ->limit(10),
        ]);

        return view('private-leagues.show', [
            'invitationUrl' => route('private-leagues.invite', $privateLeague->code),
            'leaderboard' => $recentForm->attachToEntries($this->privateLeagueLeaderboard($privateLeague->id)),
            'privateLeague' => $privateLeague,
        ]);
    }

    public function invite(Request $request, string $code): View
    {
        $privateLeague = PrivateLeague::query()
            ->with('owner')
            ->where('code', strtoupper($code))
            ->where('status', PrivateLeague::STATUS_ACTIVE)
            ->first();

        if (! $privateLeague) {
            throw new NotFoundHttpException();
        }

        $user = $request->user();
        $membership = $privateLeague->memberships()
            ->where('user_id', $user->id)
            ->first();
        $pendingJoinRequest = $privateLeague->joinRequests()
            ->where('user_id', $user->id)
            ->where('status', LeagueJoinRequest::STATUS_PENDING)
            ->first();
        $activeMembershipsCount = $this->activeMembershipsCount($user->id);

        $isOwner = $privateLeague->owner_id === $user->id;
        $isActiveMember = $membership?->status === LeagueMembership::STATUS_ACTIVE;
        $wasRemoved = $membership?->status === LeagueMembership::STATUS_REMOVED;
        $canRequestAccess = ! $isOwner
            && ! $isActiveMember
            && ! $pendingJoinRequest
            && ! $wasRemoved
            && $activeMembershipsCount < self::MAX_ACTIVE_MEMBERSHIPS;

        return view('private-leagues.invite', [
            'activeMembershipsCount' => $activeMembershipsCount,
            'canRequestAccess' => $canRequestAccess,
            'isActiveMember' => $isActiveMember,
            'isOwner' => $isOwner,
            'pendingJoinRequest' => $pendingJoinRequest,
            'privateLeague' => $privateLeague,
            'wasRemoved' => $wasRemoved,
        ]);
    }

    public function storeJoinRequest(Request $request, PrivateLeague $privateLeague): RedirectResponse
    {
        $user = $request->user();

        if ($privateLeague->status !== PrivateLeague::STATUS_ACTIVE) {
            return back()->withErrors(['league' => __('No podes solicitar acceso a esta liga.')]);
        }

        if ($privateLeague->owner_id === $user->id) {
            return back()->withErrors(['league' => __('No podes solicitar acceso a tu propia liga.')]);
        }

        $membership = $privateLeague->memberships()
            ->where('user_id', $user->id)
            ->first();

        if ($membership?->status === LeagueMembership::STATUS_ACTIVE) {
            return back()->withErrors(['league' => __('Ya sos miembro activo de esta liga.')]);
        }

        if ($membership?->status === LeagueMembership::STATUS_REMOVED) {
            return back()->withErrors(['league' => __('No podes solicitar acceso despues de haber sido removido de esta liga.')]);
        }

        if ($this->activeMembershipsCount($user->id) >= self::MAX_ACTIVE_MEMBERSHIPS) {
            return back()->withErrors(['league' => __('Ya alcanzaste el limite de 3 ligas privadas activas.')]);
        }

        $pendingRequestExists = $privateLeague->joinRequests()
            ->where('user_id', $user->id)
            ->where('status', LeagueJoinRequest::STATUS_PENDING)
            ->exists();

        if ($pendingRequestExists) {
            return back()->with('status', __('Ya tenes una solicitud pendiente para esta liga.'));
        }

        $privateLeague->joinRequests()->create([
            'user_id' => $user->id,
            'status' => LeagueJoinRequest::STATUS_PENDING,
        ]);

        return back()->with('status', __('Solicitud enviada.'));
    }

    public function acceptJoinRequest(
        Request $request,
        PrivateLeague $privateLeague,
        LeagueJoinRequest $leagueJoinRequest,
    ): RedirectResponse {
        $this->authorizeJoinRequestDecision($request, $privateLeague, $leagueJoinRequest);

        if ($this->activeMembershipsCount($leagueJoinRequest->user_id) >= self::MAX_ACTIVE_MEMBERSHIPS) {
            return back()->withErrors(['league' => __('El usuario ya pertenece a 3 ligas privadas activas.')]);
        }

        DB::transaction(function () use ($request, $privateLeague, $leagueJoinRequest): void {
            $privateLeague->memberships()->updateOrCreate(
                ['user_id' => $leagueJoinRequest->user_id],
                [
                    'status' => LeagueMembership::STATUS_ACTIVE,
                    'joined_at' => now(),
                ],
            );

            $leagueJoinRequest->update([
                'status' => LeagueJoinRequest::STATUS_ACCEPTED,
                'decided_at' => now(),
                'decided_by' => $request->user()->id,
            ]);
        });

        return back()->with('status', __('Solicitud aceptada.'));
    }

    public function rejectJoinRequest(
        Request $request,
        PrivateLeague $privateLeague,
        LeagueJoinRequest $leagueJoinRequest,
    ): RedirectResponse {
        $this->authorizeJoinRequestDecision($request, $privateLeague, $leagueJoinRequest);

        $leagueJoinRequest->update([
            'status' => LeagueJoinRequest::STATUS_REJECTED,
            'decided_at' => now(),
            'decided_by' => $request->user()->id,
        ]);

        return back()->with('status', __('Solicitud rechazada.'));
    }

    public function removeMember(Request $request, PrivateLeague $privateLeague, User $user): RedirectResponse
    {
        abort_unless($privateLeague->owner_id === $request->user()->id, 403);

        if ($privateLeague->owner_id === $user->id) {
            return back()->withErrors(['member' => __('No podes removerte de tu propia liga.')]);
        }

        $membership = $privateLeague->memberships()
            ->where('user_id', $user->id)
            ->where('status', LeagueMembership::STATUS_ACTIVE)
            ->first();

        if (! $membership) {
            return back()->withErrors(['member' => __('Ese usuario no es miembro activo de esta liga.')]);
        }

        DB::transaction(function () use ($request, $privateLeague, $user, $membership): void {
            $membership->update([
                'status' => LeagueMembership::STATUS_REMOVED,
            ]);

            $privateLeague->auditLogs()->create([
                'actor_user_id' => $request->user()->id,
                'target_user_id' => $user->id,
                'action' => LeagueAuditLog::ACTION_MEMBER_REMOVED,
                'details' => [
                    'message' => 'Member removed from private league.',
                    'removed_username' => $user->username,
                ],
            ]);
        });

        return back()->with('status', __('Miembro removido de la liga.'));
    }

    private function authorizeJoinRequestDecision(
        Request $request,
        PrivateLeague $privateLeague,
        LeagueJoinRequest $leagueJoinRequest,
    ): void {
        abort_unless($privateLeague->owner_id === $request->user()->id, 403);
        abort_unless($leagueJoinRequest->private_league_id === $privateLeague->id, 404);
        abort_unless($leagueJoinRequest->status === LeagueJoinRequest::STATUS_PENDING, 403);
    }

    private function canViewLeague(Request $request, PrivateLeague $privateLeague): bool
    {
        if ($privateLeague->owner_id === $request->user()->id) {
            return true;
        }

        return $this->isActiveMember($privateLeague, $request->user()->id);
    }

    private function isActiveMember(PrivateLeague $privateLeague, int $userId): bool
    {
        return $privateLeague->memberships()
            ->where('user_id', $userId)
            ->where('status', LeagueMembership::STATUS_ACTIVE)
            ->exists();
    }

    private function activeMembershipsCount(int $userId): int
    {
        return LeagueMembership::query()
            ->where('user_id', $userId)
            ->where('status', LeagueMembership::STATUS_ACTIVE)
            ->count();
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
                'users.profile_avatar_key',
                DB::raw('COALESCE(SUM(predictions.points_awarded), 0) as total_points'),
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
    }
}
