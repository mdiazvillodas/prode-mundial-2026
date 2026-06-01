<?php

namespace App\Http\Controllers;

use App\Models\LeagueJoinRequest;
use App\Models\LeagueMembership;
use App\Models\PrivateLeague;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

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

    public function show(Request $request, PrivateLeague $privateLeague): View
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
        ]);

        return view('private-leagues.show', [
            'privateLeague' => $privateLeague,
        ]);
    }

    public function storeJoinRequest(Request $request, PrivateLeague $privateLeague): RedirectResponse
    {
        $user = $request->user();

        if ($privateLeague->owner_id === $user->id) {
            return back()->withErrors(['league' => __('No podes solicitar acceso a tu propia liga.')]);
        }

        if ($this->isActiveMember($privateLeague, $user->id)) {
            return back()->withErrors(['league' => __('Ya sos miembro activo de esta liga.')]);
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
}
