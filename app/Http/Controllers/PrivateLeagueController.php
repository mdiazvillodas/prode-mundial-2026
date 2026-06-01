<?php

namespace App\Http\Controllers;

use App\Models\PrivateLeague;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PrivateLeagueController extends Controller
{
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
        abort_unless($privateLeague->owner_id === $request->user()->id, 403);

        $privateLeague->load('owner');

        return view('private-leagues.show', [
            'privateLeague' => $privateLeague,
        ]);
    }
}
