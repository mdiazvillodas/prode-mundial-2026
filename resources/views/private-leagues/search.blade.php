<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Buscar ligas privadas') }}
            </h2>

            @php
                $ownedPrivateLeague = auth()->user()->ownedPrivateLeague;
            @endphp

            <a
                href="{{ $ownedPrivateLeague ? route('private-leagues.show', $ownedPrivateLeague) : route('private-leagues.create') }}"
                class="inline-flex items-center justify-center rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
            >
                {{ $ownedPrivateLeague ? __('Mi liga') : __('Crear liga') }}
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-4xl space-y-5 px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="rounded-md bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 ring-1 ring-emerald-200">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded-md bg-red-50 px-4 py-3 text-sm font-medium text-red-800 ring-1 ring-red-200">
                    {{ $errors->first() }}
                </div>
            @endif

            <section class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-gray-100">
                <form method="GET" action="{{ route('private-leagues.search') }}" class="space-y-4">
                    <div>
                        <label for="q" class="block text-sm font-semibold text-gray-900">
                            {{ __('Nombre o codigo de liga') }}
                        </label>
                        <input
                            id="q"
                            name="q"
                            type="search"
                            value="{{ $query }}"
                            class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="{{ __('Ejemplo: Amigos 2026 o ABC123') }}"
                        >
                    </div>

                    <button
                        type="submit"
                        class="inline-flex w-full items-center justify-center rounded-md bg-gray-950 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:w-auto"
                    >
                        {{ __('Buscar liga') }}
                    </button>
                </form>
            </section>

            <section class="space-y-3">
                @if ($query === '')
                    <div class="rounded-lg border border-dashed border-gray-300 bg-white p-6 text-sm text-gray-600">
                        {{ __('Busca por nombre o codigo para encontrar una liga privada y solicitar acceso.') }}
                    </div>
                @elseif ($privateLeagues->isEmpty())
                    <div class="rounded-lg border border-dashed border-gray-300 bg-white p-6 text-sm text-gray-600">
                        {{ __('No encontramos ligas con ese nombre o codigo.') }}
                    </div>
                @endif

                @foreach ($privateLeagues as $privateLeague)
                    @php
                        $membership = $privateLeague->memberships->first();
                        $latestRequest = $privateLeague->joinRequests->first();
                        $isOwner = $privateLeague->owner_id === auth()->id();
                        $isActiveMember = $membership?->status === App\Models\LeagueMembership::STATUS_ACTIVE;
                        $hasPendingRequest = $latestRequest?->status === App\Models\LeagueJoinRequest::STATUS_PENDING;
                        $canRequestAccess = ! $isOwner && ! $isActiveMember && ! $hasPendingRequest && $activeMembershipsCount < 3;
                    @endphp

                    <article class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-gray-100">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="text-lg font-bold text-gray-950">
                                        {{ $privateLeague->name }}
                                    </h3>
                                    <span class="rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-semibold uppercase tracking-wide text-indigo-700">
                                        {{ $privateLeague->code }}
                                    </span>
                                </div>

                                <p class="mt-2 text-sm text-gray-600">
                                    {{ __('Owner') }}: {{ '@'.$privateLeague->owner->username }}
                                </p>
                            </div>

                            <div class="flex flex-col gap-2 sm:items-end">
                                @if ($isOwner)
                                    <a
                                        href="{{ route('private-leagues.show', $privateLeague) }}"
                                        class="inline-flex items-center justify-center rounded-md border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-800 transition hover:bg-emerald-100 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
                                    >
                                        {{ __('Ver mi liga') }}
                                    </a>
                                @elseif ($isActiveMember)
                                    <a
                                        href="{{ route('private-leagues.show', $privateLeague) }}"
                                        class="inline-flex items-center justify-center rounded-md border border-indigo-200 bg-indigo-50 px-4 py-2 text-sm font-semibold text-indigo-800 transition hover:bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                    >
                                        {{ __('Ver liga') }}
                                    </a>
                                @elseif ($hasPendingRequest)
                                    <span class="inline-flex items-center justify-center rounded-md bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-800 ring-1 ring-amber-200">
                                        {{ __('Solicitud pendiente') }}
                                    </span>
                                @elseif ($activeMembershipsCount >= 3)
                                    <span class="inline-flex items-center justify-center rounded-md bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-700">
                                        {{ __('Limite de ligas alcanzado') }}
                                    </span>
                                @endif

                                @if ($canRequestAccess)
                                    <form method="POST" action="{{ route('private-leagues.join-requests.store', $privateLeague) }}">
                                        @csrf
                                        <button
                                            type="submit"
                                            class="inline-flex w-full items-center justify-center rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
                                        >
                                            {{ __('Solicitar acceso') }}
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </section>
        </div>
    </div>
</x-app-layout>
