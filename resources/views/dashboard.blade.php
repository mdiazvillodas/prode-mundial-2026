@php
    $dashboardData = $liveDashboardData ?? [];
    $pendingPredictions = $dashboardData['pending_predictions'] ?? null;
    $dailyMatches = $dashboardData['daily_matches'] ?? null;
    $dailyMatchRows = collect($dailyMatches['matches'] ?? []);
    $friendActivity = $dashboardData['friend_activity'] ?? null;
    $friends = collect($friendActivity['friends'] ?? []);
    $hasDashboardSidebar = $dailyMatchRows->isNotEmpty() || $friends->isNotEmpty();
    $leagueSummary = $dashboardData['league_summary'] ?? [];
    $generalSummary = $leagueSummary['general'] ?? null;
    $privateLeagueSummaries = collect($leagueSummary['private_leagues'] ?? []);
    $timezone = $dashboardData['timezone'] ?? config('app.timezone');

    $stateIndicators = [
        'exact' => [
            'label' => __('Exacto'),
            'isDot' => false,
            'symbol' => '★',
            'color' => 'text-violet-800',
        ],
        'trend' => [
            'label' => __('Tendencia'),
            'isDot' => true,
            'color' => 'bg-emerald-700',
        ],
        'incorrect' => [
            'label' => __('No va'),
            'isDot' => true,
            'color' => 'bg-red-700',
        ],
        'none' => [
            'label' => __('Sin pronóstico'),
            'isDot' => true,
            'color' => 'bg-slate-500',
        ],
    ];

    $formatLocalDate = function (?string $date): ?string {
        return $date ? \Illuminate\Support\Carbon::parse($date)->translatedFormat('D d M') : null;
    };
@endphp

<x-app-layout>
    <div class="min-h-screen bg-slate-50">
        <div class="mx-auto max-w-5xl space-y-4 px-4 py-4 sm:px-6 sm:py-6 lg:px-8">
            <header class="rounded-2xl bg-blue-950 px-4 py-4 text-white shadow-lg shadow-blue-950/10 ring-1 ring-blue-900/20 sm:px-5">
                <div class="flex items-center justify-between gap-4">
                    <div class="flex min-w-0 items-center gap-3">
                        <img
                            src="{{ asset('brand/p26-logo.svg') }}"
                            alt="{{ __('Prode') }}"
                            class="h-10 w-auto shrink-0"
                        >
                        <div class="min-w-0">
                            <p class="truncate text-xs font-black uppercase tracking-[0.16em] text-sky-200">
                                {{ __('Inicio') }}
                            </p>
                            <h1 class="truncate text-xl font-black leading-tight sm:text-2xl">
                                {{ __('Mi Prode') }}
                            </h1>
                        </div>
                    </div>

                    <div class="flex shrink-0 items-center gap-2">
                        @if (auth()->user()->isAdmin())
                            <a
                                href="{{ route('admin.dashboard') }}"
                                class="hidden rounded-full bg-white/10 px-3 py-2 text-xs font-black text-white ring-1 ring-white/15 transition hover:bg-white/15 sm:inline-flex"
                            >
                                {{ __('Admin') }}
                            </a>
                        @endif

                        <a href="{{ route('profile.edit') }}" class="rounded-full focus:outline-none focus:ring-4 focus:ring-sky-200/40">
                            <x-profile-avatar :user="auth()->user()" size="md" class="ring-2 ring-white/30" />
                        </a>
                    </div>
                </div>
            </header>

            @if ($pendingPredictions || $hasDashboardSidebar)
                <div class="grid gap-4 lg:grid-cols-12 lg:items-start">
                    @if ($pendingPredictions)
                        <section class="{{ $hasDashboardSidebar ? 'lg:col-span-8' : 'lg:col-span-12' }} rounded-2xl bg-white p-4 shadow-sm shadow-blue-900/5 ring-1 ring-blue-100 sm:p-5">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-xs font-black uppercase tracking-[0.14em] text-orange-600">
                                        {{ $formatLocalDate($pendingPredictions['local_date'] ?? null) }}
                                    </p>
                                    <h2 class="mt-1 text-xl font-black text-blue-950">
                                        {{ __('Te falta pronosticar') }}
                                    </h2>
                                </div>
                                <span class="rounded-full bg-orange-100 px-3 py-1 text-xs font-black text-orange-800">
                                    {{ count($pendingPredictions['matches'] ?? []) }}
                                </span>
                            </div>

                            <div class="mt-4 divide-y divide-slate-100">
                                @foreach ($pendingPredictions['matches'] as $match)
                                    <a
                                        href="{{ $match['prediction_url'] ?? $pendingPredictions['prediction_url'] }}"
                                        class="group block rounded-xl px-2 py-3 transition hover:bg-blue-50 focus:outline-none focus:ring-4 focus:ring-blue-100"
                                    >
                                        <div class="flex items-center justify-between gap-3">
                                            <div class="min-w-0 flex-1">
                                                <div class="flex items-center gap-2">
                                                    @foreach (['team_a', 'team_b'] as $teamKey)
                                                        @php($team = $match[$teamKey] ?? null)
                                                        <div class="flex min-w-0 items-center gap-2">
                                                            <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center overflow-hidden rounded-full bg-slate-100 text-[10px] font-black text-slate-600 ring-1 ring-white">
                                                                @if (! empty($team['flag_path']))
                                                                    <img src="{{ asset($team['flag_path']) }}" alt="{{ __('Bandera de :team', ['team' => $team['name']]) }}" class="h-full w-full object-cover" loading="lazy">
                                                                @else
                                                                    {{ $team['short_name'] ?? 'TBD' }}
                                                                @endif
                                                            </span>
                                                            <span class="truncate text-sm font-black text-blue-950">{{ $team['short_name'] ?? $team['name'] ?? __('TBD') }}</span>
                                                        </div>
                                                        @if ($teamKey === 'team_a')
                                                            <span class="text-xs font-black text-slate-400">vs</span>
                                                        @endif
                                                    @endforeach
                                                </div>

                                                <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs font-bold text-slate-500">
                                                    @if (! empty($match['kickoff_local_time']))
                                                        <span>{{ __('Juega :time', ['time' => $match['kickoff_local_time']]) }}</span>
                                                    @endif
                                                    @if (! empty($match['prediction_closes_local_time']))
                                                        <span>{{ __('Editás hasta :time', ['time' => $match['prediction_closes_local_time']]) }}</span>
                                                    @endif
                                                    @if (! empty($match['group']))
                                                        <span>{{ __('Grupo :group', ['group' => $match['group']]) }}</span>
                                                    @endif
                                                </div>

                                                <div class="mt-2 flex flex-wrap gap-2 text-[11px] font-black text-slate-500">
                                                    @foreach (['team_a', 'team_b'] as $teamKey)
                                                        @php($team = $match[$teamKey] ?? null)
                                                        @if (($team['goals_for_avg'] ?? null) !== null && ($team['goals_against_avg'] ?? null) !== null)
                                                            <span class="rounded-full bg-slate-100 px-2 py-1">
                                                                {{ $team['short_name'] }} GF {{ $team['goals_for_avg'] }} · GC {{ $team['goals_against_avg'] }}
                                                            </span>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            </div>

                                            <span class="text-lg font-black text-blue-700 transition group-hover:translate-x-0.5">›</span>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </section>
                    @endif

                    @if ($hasDashboardSidebar)
                        <aside class="{{ $pendingPredictions ? 'lg:col-span-4' : 'lg:col-span-12' }} space-y-4">
                            @if ($dailyMatchRows->isNotEmpty())
                                <section class="rounded-2xl bg-white p-4 shadow-sm shadow-blue-900/5 ring-1 ring-blue-100">
                                    <div class="flex items-center justify-between gap-3">
                                        <div>
                                            <h2 class="text-lg font-black text-blue-950">{{ __('Hoy en el Mundial') }}</h2>
                                            @if (! empty($dailyMatches['local_date']))
                                                <p class="mt-1 text-xs font-bold uppercase tracking-[0.12em] text-slate-500">
                                                    {{ $formatLocalDate($dailyMatches['local_date']) }}
                                                </p>
                                            @endif
                                        </div>
                                        <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-black text-blue-800">
                                            {{ $dailyMatchRows->count() }}
                                        </span>
                                    </div>

                                    <div class="mt-3 space-y-2">
                                        @foreach ($dailyMatchRows as $match)
                                            @php($state = $stateIndicators[$match['provisional_state'] ?? 'none'] ?? $stateIndicators['none'])
                                            <article class="rounded-xl border border-slate-100 bg-slate-50 px-3 py-3">
                                                <div class="flex items-center justify-between gap-3">
                                                    <div class="min-w-0 flex-1">
                                                        <div class="flex items-center gap-2 text-sm font-black text-blue-950">
                                                            <span class="truncate">{{ $match['team_a']['short_name'] ?? $match['team_a']['name'] ?? __('TBD') }}</span>
                                                            @if (($match['display_state'] ?? 'scheduled') === 'scheduled')
                                                                <span class="rounded-lg bg-white px-2 py-1 text-xs tabular-nums text-slate-600 ring-1 ring-slate-200">
                                                                    {{ $match['kickoff_local_time'] ?? '--:--' }}
                                                                </span>
                                                            @else
                                                                <span class="rounded-lg bg-white px-2 py-1 text-base tabular-nums ring-1 ring-slate-200">
                                                                    {{ $match['score']['team_a'] ?? '-' }}-{{ $match['score']['team_b'] ?? '-' }}
                                                                </span>
                                                            @endif
                                                            <span class="truncate">{{ $match['team_b']['short_name'] ?? $match['team_b']['name'] ?? __('TBD') }}</span>
                                                        </div>

                                                        <div class="mt-2 flex flex-wrap items-center gap-2 text-xs font-bold text-slate-500">
                                                            @if (! empty($match['status_label']) && ($match['display_state'] ?? null) !== 'scheduled')
                                                                <span class="rounded-full bg-white px-2 py-1 ring-1 ring-slate-200">{{ $match['status_label'] }}</span>
                                                            @endif
                                                            @if ($match['user_prediction'])
                                                                <span>{{ __('Tu :a-:b', ['a' => $match['user_prediction']['team_a_score'], 'b' => $match['user_prediction']['team_b_score']]) }}</span>
                                                            @endif
                                                            @if (($match['display_state'] ?? null) === 'live' && ($match['last_synced_minutes_ago'] ?? null) !== null)
                                                                <span>{{ __('Actualizado hace :minutes min', ['minutes' => (int) $match['last_synced_minutes_ago']]) }}</span>
                                                            @endif
                                                        </div>
                                                    </div>

                                                    @if (($match['display_state'] ?? null) !== 'scheduled')
                                                        @if ($state['isDot'] ?? false)
                                                            <span
                                                                class="shrink-0 h-2.5 w-2.5 rounded-full {{ $state['color'] }}"
                                                                title="{{ $state['label'] }}"
                                                                aria-label="{{ $state['label'] }}"
                                                            >
                                                                <span class="sr-only">{{ $state['label'] }}</span>
                                                            </span>
                                                        @else
                                                            <span
                                                                class="shrink-0 {{ $state['color'] }} text-sm leading-none"
                                                                title="{{ $state['label'] }}"
                                                                aria-label="{{ $state['label'] }}"
                                                            >
                                                                {{ $state['symbol'] }}
                                                                <span class="sr-only">{{ $state['label'] }}</span>
                                                            </span>
                                                        @endif
                                                    @endif
                                                </div>
                                            </article>
                                        @endforeach
                                    </div>
                                </section>
                            @endif

                            @if ($friends->isNotEmpty())
                                <section class="rounded-2xl bg-white p-4 shadow-sm shadow-blue-900/5 ring-1 ring-blue-100">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <h2 class="text-lg font-black text-blue-950">{{ __('Tus amigos ya se movieron') }}</h2>
                                            @if (! empty($friendActivity['local_date']))
                                                <p class="mt-1 text-xs font-bold uppercase tracking-[0.12em] text-slate-500">
                                                    {{ $formatLocalDate($friendActivity['local_date']) }}
                                                </p>
                                            @endif
                                        </div>
                                        <a href="{{ route('leagues.index') }}" class="text-xs font-black text-blue-700 hover:text-blue-600">
                                            {{ __('Ligas') }}
                                        </a>
                                    </div>

                                    <div class="mt-4 max-h-80 space-y-2 overflow-y-auto pr-1">
                                        @foreach ($friends->take(6) as $friend)
                                            <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-100 bg-slate-50 px-3 py-2">
                                                <div class="flex min-w-0 items-center gap-3">
                                                    @php($friendUser = new \App\Models\User([
                                                        'name' => $friend['name'],
                                                        'profile_avatar_key' => $friend['avatar']['key'] ?? null,
                                                    ]))
                                                    <x-profile-avatar :user="$friendUser" size="sm" />
                                                    <div class="min-w-0">
                                                        <p class="truncate text-sm font-black text-blue-950">{{ $friend['name'] }}</p>
                                                        <p class="truncate text-xs font-bold text-slate-500">{{ '@'.$friend['username'] }}</p>
                                                    </div>
                                                </div>
                                                <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-black text-blue-800">
                                                    {{ $friend['completed_count'] }}/{{ $friend['total_matches'] }}
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                </section>
                            @endif
                        </aside>
                    @endif
                </div>
            @endif

            @if ($generalSummary || $privateLeagueSummaries->isNotEmpty())
                <section class="rounded-2xl bg-white p-4 shadow-sm shadow-blue-900/5 ring-1 ring-blue-100 sm:p-5">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="text-lg font-black text-blue-950">{{ __('Ligas') }}</h2>
                        <a href="{{ route('leagues.index') }}" class="text-xs font-black text-blue-700 hover:text-blue-600">
                            {{ __('Ver tabla') }}
                        </a>
                    </div>

                    <div class="mt-4 grid gap-2 sm:grid-cols-2">
                        @if ($generalSummary)
                            <div class="rounded-xl bg-blue-950 px-4 py-3 text-white">
                                <p class="text-xs font-black uppercase tracking-[0.14em] text-sky-200">{{ __('General') }}</p>
                                <div class="mt-2 flex items-end justify-between gap-3">
                                    <p class="text-2xl font-black">{{ (int) ($generalSummary['points'] ?? 0) }} pts</p>
                                    <p class="text-sm font-black">{{ ($generalSummary['position'] ?? null) ? '#'.$generalSummary['position'] : '-' }}</p>
                                </div>
                            </div>
                        @endif

                        @foreach ($privateLeagueSummaries->take(3) as $league)
                            <div class="rounded-xl border border-slate-100 bg-slate-50 px-4 py-3">
                                <p class="truncate text-sm font-black text-blue-950">{{ $league['name'] }}</p>
                                <div class="mt-2 flex items-center justify-between gap-3 text-sm font-black text-slate-600">
                                    <span>{{ (int) ($league['points'] ?? 0) }} pts</span>
                                    <span>{{ ($league['position'] ?? null) ? '#'.$league['position'] : '-' }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            <div class="grid gap-3 sm:grid-cols-3">
                <a href="{{ route('predictions.history') }}" class="rounded-2xl bg-white px-4 py-3 text-sm font-black text-blue-800 shadow-sm shadow-blue-900/5 ring-1 ring-blue-100 transition hover:bg-blue-50">
                    {{ __('Historial') }}
                </a>
                <a href="{{ route('calendar.index') }}" class="rounded-2xl bg-white px-4 py-3 text-sm font-black text-blue-800 shadow-sm shadow-blue-900/5 ring-1 ring-blue-100 transition hover:bg-blue-50">
                    {{ __('Calendario') }}
                </a>
                @if (auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="rounded-2xl bg-emerald-50 px-4 py-3 text-sm font-black text-emerald-800 shadow-sm shadow-emerald-900/5 ring-1 ring-emerald-100 transition hover:bg-emerald-100">
                        {{ __('Panel admin') }}
                    </a>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
