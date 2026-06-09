@php
    $dashboardData = $liveDashboardData ?? [];
    $pendingPredictions = $dashboardData['pending_predictions'] ?? null;
    $dailyMatches = $dashboardData['daily_matches'] ?? null;
    $dailyMatchRows = collect($dailyMatches['matches'] ?? []);
    $friendActivity = $dashboardData['friend_activity'] ?? null;
    $friends = collect($friendActivity['friends'] ?? []);
    $hasActivePrivateLeagues = (bool) ($dashboardData['has_active_private_leagues'] ?? false);
    $leagueSummary = $dashboardData['league_summary'] ?? [];
    $generalSummary = $leagueSummary['general'] ?? null;
    $privateLeagueSummaries = collect($leagueSummary['private_leagues'] ?? []);
    $showCompactGeneralSidebar = ! $hasActivePrivateLeagues && $generalSummary;
    $hasDashboardSidebar = $dailyMatchRows->isNotEmpty() || $friends->isNotEmpty() || $showCompactGeneralSidebar;
    $timezone = $dashboardData['timezone'] ?? config('app.timezone');
    $dailyLastSyncedMinutes = $dailyMatchRows
        ->pluck('last_synced_minutes_ago')
        ->filter(fn ($minutes) => $minutes !== null)
        ->map(fn ($minutes) => (int) $minutes)
        ->min();

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
            'label' => __('Sin predicción'),
            'isDot' => true,
            'color' => 'border border-slate-400 bg-white',
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
                    <div class="min-w-0">
                        <p class="truncate text-xs font-black uppercase tracking-[0.16em] text-sky-200">
                            {{ __('Inicio') }}
                        </p>
                        <h1 class="truncate text-xl font-black leading-tight sm:text-2xl">
                            {{ __('Mi Prode') }}
                        </h1>
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

            @if ($pendingPredictions || $hasDashboardSidebar || ! $hasActivePrivateLeagues || $generalSummary || $privateLeagueSummaries->isNotEmpty())
                <div class="hidden gap-4 lg:grid lg:grid-cols-12 lg:items-start">
                    <main class="{{ $hasDashboardSidebar ? 'lg:col-span-8' : 'lg:col-span-12' }} space-y-4">
                        @if ($pendingPredictions)
                            <section class="rounded-2xl bg-white p-5 shadow-sm shadow-blue-900/5 ring-1 ring-blue-100">
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
                                                            @php
                                                                $team = $match[$teamKey] ?? null;
                                                            @endphp
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
                                                            @php
                                                                $team = $match[$teamKey] ?? null;
                                                            @endphp
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

                        @unless ($hasActivePrivateLeagues)
                            <section class="overflow-hidden rounded-2xl bg-blue-950 text-white shadow-lg shadow-blue-950/10 ring-1 ring-blue-900/20">
                                <div class="grid gap-5 p-6 xl:grid-cols-[minmax(0,1fr)_auto] xl:items-center">
                                    <div class="min-w-0">
                                        <p class="text-xs font-black uppercase tracking-[0.16em] text-emerald-200">
                                            {{ __('Liga privada') }}
                                        </p>
                                        <h2 class="mt-2 text-3xl font-black leading-tight">
                                            {{ __('Jugá con tus amigos') }}
                                        </h2>
                                        <p class="mt-2 max-w-2xl text-sm font-semibold leading-6 text-sky-100">
                                            {{ __('Creá tu propia liga, compartí el link y competí con tu grupo durante el Mundial.') }}
                                        </p>
                                    </div>

                                    <div class="flex flex-col gap-3 xl:flex-row xl:justify-end">
                                        <a
                                            href="{{ route('private-leagues.create') }}"
                                            class="inline-flex items-center justify-center rounded-xl bg-emerald-400 px-4 py-3 text-sm font-black text-blue-950 shadow-sm transition hover:bg-emerald-300 focus:outline-none focus:ring-4 focus:ring-emerald-200/40"
                                        >
                                            {{ __('Crear mi liga') }}
                                        </a>
                                        <a
                                            href="{{ route('private-leagues.search') }}"
                                            class="inline-flex items-center justify-center rounded-xl bg-white/10 px-4 py-3 text-sm font-black text-white ring-1 ring-white/15 transition hover:bg-white/15 focus:outline-none focus:ring-4 focus:ring-sky-200/30"
                                        >
                                            {{ __('Buscar liga') }}
                                        </a>
                                    </div>
                                </div>

                                <div class="border-t border-white/10 bg-white/[0.03] px-6 py-5">
                                    <div class="grid gap-3 md:grid-cols-2">
                                        @foreach ([
                                            ['title' => __('Creá tu liga'), 'body' => __('Elegí el nombre que más te guste.'), 'path' => 'M12 5v14m7-7H5'],
                                            ['title' => __('Copiá el link'), 'body' => __('El sistema genera un link para invitar.'), 'path' => 'M10 13a5 5 0 0 0 7.1 0l1.4-1.4a5 5 0 0 0-7.1-7.1L10 5.9m4 5.1a5 5 0 0 0-7.1 0l-1.4 1.4a5 5 0 0 0 7.1 7.1L14 18.1'],
                                            ['title' => __('Compartilo con tus amigos'), 'body' => __('Mandalo por WhatsApp, Telegram o donde quieras.'), 'path' => 'M8 12h8m-8-4h8m-8 8h5m8-4a9 9 0 1 1-4.2-7.6L21 3v6h-6l2.2-2.2'],
                                            ['title' => __('Compitan en su ranking'), 'body' => __('Tus amigos piden entrar y juegan en la misma tabla.'), 'path' => 'M7 20V10m5 10V4m5 16v-7M5 20h14'],
                                        ] as $step)
                                            <div class="rounded-xl bg-white/[0.08] p-3 ring-1 ring-white/10">
                                                <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-emerald-300/15 text-emerald-200 ring-1 ring-emerald-200/20">
                                                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true" class="h-5 w-5">
                                                        <path d="{{ $step['path'] }}" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                    </svg>
                                                </span>
                                                <h3 class="mt-3 text-sm font-black text-white">{{ $step['title'] }}</h3>
                                                <p class="mt-1 text-xs font-semibold leading-5 text-sky-100/85">{{ $step['body'] }}</p>
                                            </div>
                                        @endforeach
                                    </div>

                                    <p class="mt-4 text-xs font-bold text-emerald-100">
                                        {{ __('Tus amigos se suman desde un link y vos aprobás el ingreso.') }}
                                    </p>
                                </div>
                            </section>
                        @endunless

                        @if ($hasActivePrivateLeagues && ($generalSummary || $privateLeagueSummaries->isNotEmpty()))
                            <section class="rounded-2xl bg-white p-5 shadow-sm shadow-blue-900/5 ring-1 ring-blue-100">
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
                    </main>

                    @if ($hasDashboardSidebar)
                        <aside class="space-y-4 lg:col-span-4">
                            @if ($dailyMatchRows->isNotEmpty())
                                <section class="rounded-2xl bg-white p-4 shadow-sm shadow-blue-900/5 ring-1 ring-blue-100">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <h2 class="text-lg font-black text-blue-950">{{ __('Jornada Mundial') }}</h2>
                                            @if (! empty($dailyMatches['local_date']))
                                                <p class="mt-1 text-xs font-bold uppercase tracking-[0.12em] text-slate-500">
                                                    {{ $formatLocalDate($dailyMatches['local_date']) }}
                                                </p>
                                            @endif
                                            @if ($dailyLastSyncedMinutes !== null)
                                                <p class="mt-1 text-[11px] font-bold text-slate-400">
                                                    {{ __('Actualizado hace :minutes min', ['minutes' => $dailyLastSyncedMinutes]) }}
                                                </p>
                                            @endif
                                        </div>
                                        <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-black text-blue-800 whitespace-nowrap">
                                            {{ trans_choice(':count partido|:count partidos', $dailyMatchRows->count(), ['count' => $dailyMatchRows->count()]) }}
                                        </span>
                                    </div>

                                    <div class="mt-3 space-y-2">
                                        @foreach ($dailyMatchRows as $match)
                                            @php
                                                $displayState = $match['display_state'] ?? 'scheduled';
                                                $state = $stateIndicators[$match['provisional_state'] ?? 'none'] ?? $stateIndicators['none'];
                                                $teamA = $match['team_a'] ?? [];
                                                $teamB = $match['team_b'] ?? [];
                                                $centerLabel = $displayState === 'scheduled'
                                                    ? ($match['kickoff_local_time'] ?? '--:--')
                                                    : (($match['score']['team_a'] ?? '-').'-'.($match['score']['team_b'] ?? '-'));
                                                $predictionLabel = $match['user_prediction']
                                                    ? __('Tu :a-:b', ['a' => $match['user_prediction']['team_a_score'], 'b' => $match['user_prediction']['team_b_score']])
                                                    : __('Sin predicción');
                                                if ($displayState === 'live') {
                                                    $statusLabel = $match['status_label'] ?? __('En juego');
                                                } elseif ($displayState === 'finished') {
                                                    $statusLabel = __('Finalizado');
                                                } else {
                                                    $statusLabel = __('Programado');
                                                }
                                            @endphp
                                            <article class="rounded-2xl border border-slate-100 bg-slate-50/80 px-3 py-3">
                                                <div class="grid grid-cols-[minmax(0,1fr)_auto_minmax(0,1fr)_auto] items-center gap-2">
                                                    <div class="flex min-w-0 items-center gap-1.5">
                                                        <span class="inline-flex h-6 w-6 shrink-0 items-center justify-center overflow-hidden rounded-full bg-white text-[9px] font-black text-slate-500 ring-1 ring-slate-200">
                                                            @if (! empty($teamA['flag_path']))
                                                                <img src="{{ asset($teamA['flag_path']) }}" alt="{{ __('Bandera de :team', ['team' => $teamA['name'] ?? $teamA['short_name'] ?? __('equipo')]) }}" class="h-full w-full object-cover" loading="lazy">
                                                            @else
                                                                {{ $teamA['short_name'] ?? 'TBD' }}
                                                            @endif
                                                        </span>
                                                        <span class="truncate text-xs font-black text-blue-950">{{ $teamA['short_name'] ?? $teamA['name'] ?? __('TBD') }}</span>
                                                    </div>

                                                    <span class="justify-self-center rounded-full bg-white px-2.5 py-1 text-sm font-black tabular-nums text-blue-950 ring-1 ring-slate-200">
                                                        {{ $centerLabel }}
                                                    </span>

                                                    <div class="flex min-w-0 items-center justify-end gap-1.5">
                                                        <span class="truncate text-right text-xs font-black text-blue-950">{{ $teamB['short_name'] ?? $teamB['name'] ?? __('TBD') }}</span>
                                                        <span class="inline-flex h-6 w-6 shrink-0 items-center justify-center overflow-hidden rounded-full bg-white text-[9px] font-black text-slate-500 ring-1 ring-slate-200">
                                                            @if (! empty($teamB['flag_path']))
                                                                <img src="{{ asset($teamB['flag_path']) }}" alt="{{ __('Bandera de :team', ['team' => $teamB['name'] ?? $teamB['short_name'] ?? __('equipo')]) }}" class="h-full w-full object-cover" loading="lazy">
                                                            @else
                                                                {{ $teamB['short_name'] ?? 'TBD' }}
                                                            @endif
                                                        </span>
                                                    </div>

                                                    @if ($state['isDot'] ?? false)
                                                        <span
                                                            class="h-2.5 w-2.5 shrink-0 rounded-full {{ $state['color'] }}"
                                                            title="{{ $state['label'] }}"
                                                            aria-label="{{ $state['label'] }}"
                                                        >
                                                            <span class="sr-only">{{ $state['label'] }}</span>
                                                        </span>
                                                    @else
                                                        <span
                                                            class="{{ $state['color'] }} shrink-0 text-sm leading-none"
                                                            title="{{ $state['label'] }}"
                                                            aria-label="{{ $state['label'] }}"
                                                        >
                                                            {{ $state['symbol'] }}
                                                            <span class="sr-only">{{ $state['label'] }}</span>
                                                        </span>
                                                    @endif
                                                </div>

                                                <div class="mt-2 flex flex-wrap items-center justify-center gap-x-2 gap-y-1 text-[11px] font-bold text-slate-500">
                                                    <span class="rounded-full bg-white px-2 py-0.5 ring-1 ring-slate-200">{{ $statusLabel }}</span>
                                                    <span>{{ $predictionLabel }}</span>
                                                    @if ($displayState === 'live' && ($match['last_synced_minutes_ago'] ?? null) !== null)
                                                        <span>{{ __('Hace :minutes min', ['minutes' => (int) $match['last_synced_minutes_ago']]) }}</span>
                                                    @endif
                                                </div>
                                            </article>
                                        @endforeach
                                    </div>
                                </section>
                            @endif

                            @if ($showCompactGeneralSidebar)
                                <section class="rounded-2xl bg-blue-950 p-4 text-white shadow-sm shadow-blue-900/10 ring-1 ring-blue-900/20">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <p class="text-xs font-black uppercase tracking-[0.14em] text-sky-200">{{ __('Liga general') }}</p>
                                            <h2 class="mt-1 text-lg font-black">{{ __('Tu posición') }}</h2>
                                        </div>
                                        <a href="{{ route('leagues.index') }}" class="text-xs font-black text-sky-200 hover:text-white">
                                            {{ __('Ver tabla') }}
                                        </a>
                                    </div>

                                    <div class="mt-4 grid grid-cols-2 gap-2">
                                        <div class="rounded-xl bg-white/10 px-3 py-3 ring-1 ring-white/10">
                                            <p class="text-[11px] font-black uppercase tracking-[0.12em] text-sky-200">{{ __('Puntos') }}</p>
                                            <p class="mt-1 text-2xl font-black">{{ (int) ($generalSummary['points'] ?? 0) }}</p>
                                        </div>
                                        <div class="rounded-xl bg-white/10 px-3 py-3 ring-1 ring-white/10">
                                            <p class="text-[11px] font-black uppercase tracking-[0.12em] text-sky-200">{{ __('Puesto') }}</p>
                                            <p class="mt-1 text-2xl font-black">{{ ($generalSummary['position'] ?? null) ? '#'.$generalSummary['position'] : '-' }}</p>
                                        </div>
                                    </div>
                                </section>
                            @endif

                            @if ($friends->isNotEmpty())
                                <section class="rounded-2xl bg-white p-4 shadow-sm shadow-blue-900/5 ring-1 ring-blue-100">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <h2 class="text-lg font-black text-blue-950">{{ __('Tus amigos en la jornada') }}</h2>
                                            <p class="mt-1 text-xs font-semibold leading-5 text-slate-500">
                                                {{ __('Predicciones cargadas en los próximos 4 partidos.') }}
                                            </p>
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
                                                    @php
                                                        $friendUser = new \App\Models\User([
                                                            'name' => $friend['name'],
                                                            'profile_avatar_key' => $friend['avatar']['key'] ?? null,
                                                        ]);
                                                        $completedFriendPredictions = min(4, max(0, (int) ($friend['completed_count'] ?? 0)));
                                                        $totalFriendPredictions = min(4, max(0, (int) ($friend['total_matches'] ?? 4)));
                                                        $friendIndicatorSlots = max(1, $totalFriendPredictions);
                                                    @endphp
                                                    <x-profile-avatar :user="$friendUser" size="sm" />
                                                    <div class="min-w-0">
                                                        <p class="truncate text-sm font-black text-blue-950">{{ $friend['display_name'] ?? $friend['name'] ?? '@'.$friend['username'] }}</p>
                                                        @if (! empty($friend['username']) && trim((string) ($friend['name'] ?? '')) !== '')
                                                            <p class="truncate text-xs font-bold text-slate-500">{{ '@'.$friend['username'] }}</p>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div
                                                    class="flex shrink-0 items-center gap-1.5"
                                                    aria-label="{{ __(':completed de :total predicciones cargadas', ['completed' => $completedFriendPredictions, 'total' => $totalFriendPredictions]) }}"
                                                >
                                                    <span class="sr-only">{{ __(':completed de :total predicciones cargadas', ['completed' => $completedFriendPredictions, 'total' => $totalFriendPredictions]) }}</span>
                                                    <span class="flex items-center gap-1" aria-hidden="true">
                                                        @for ($slot = 1; $slot <= $friendIndicatorSlots; $slot++)
                                                            <span class="h-2.5 w-2.5 rounded-full {{ $slot <= $completedFriendPredictions ? 'bg-emerald-500 ring-1 ring-emerald-600/20' : 'bg-white ring-1 ring-slate-300' }}"></span>
                                                        @endfor
                                                    </span>
                                                    <span class="text-[11px] font-black tabular-nums text-slate-500">
                                                        {{ $completedFriendPredictions }}/{{ $totalFriendPredictions }}
                                                    </span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </section>
                            @endif
                        </aside>
                    @endif
                </div>
            @endif

            <div class="space-y-4 lg:hidden">
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
                                                        @php
                                                            $team = $match[$teamKey] ?? null;
                                                        @endphp
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
                                                        @php
                                                            $team = $match[$teamKey] ?? null;
                                                        @endphp
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
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <h2 class="text-lg font-black text-blue-950">{{ __('Jornada Mundial') }}</h2>
                                            @if (! empty($dailyMatches['local_date']))
                                                <p class="mt-1 text-xs font-bold uppercase tracking-[0.12em] text-slate-500">
                                                    {{ $formatLocalDate($dailyMatches['local_date']) }}
                                                </p>
                                            @endif
                                            @if ($dailyLastSyncedMinutes !== null)
                                                <p class="mt-1 text-[11px] font-bold text-slate-400">
                                                    {{ __('Actualizado hace :minutes min', ['minutes' => $dailyLastSyncedMinutes]) }}
                                                </p>
                                            @endif
                                        </div>
                                        <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-black text-blue-800 whitespace-nowrap">
                                            {{ trans_choice(':count partido|:count partidos', $dailyMatchRows->count(), ['count' => $dailyMatchRows->count()]) }}
                                        </span>
                                    </div>

                                    <div class="mt-3 space-y-2">
                                        @foreach ($dailyMatchRows as $match)
                                            @php
                                                $displayState = $match['display_state'] ?? 'scheduled';
                                                $state = $stateIndicators[$match['provisional_state'] ?? 'none'] ?? $stateIndicators['none'];
                                                $teamA = $match['team_a'] ?? [];
                                                $teamB = $match['team_b'] ?? [];
                                                $centerLabel = $displayState === 'scheduled'
                                                    ? ($match['kickoff_local_time'] ?? '--:--')
                                                    : (($match['score']['team_a'] ?? '-').'-'.($match['score']['team_b'] ?? '-'));
                                                $predictionLabel = $match['user_prediction']
                                                    ? __('Tu :a-:b', ['a' => $match['user_prediction']['team_a_score'], 'b' => $match['user_prediction']['team_b_score']])
                                                    : __('Sin predicción');
                                                if ($displayState === 'live') {
                                                    $statusLabel = $match['status_label'] ?? __('En juego');
                                                } elseif ($displayState === 'finished') {
                                                    $statusLabel = __('Finalizado');
                                                } else {
                                                    $statusLabel = __('Programado');
                                                }
                                            @endphp
                                            <article class="rounded-2xl border border-slate-100 bg-slate-50/80 px-3 py-3">
                                                <div class="grid grid-cols-[minmax(0,1fr)_auto_minmax(0,1fr)_auto] items-center gap-2">
                                                    <div class="flex min-w-0 items-center gap-1.5">
                                                        <span class="inline-flex h-6 w-6 shrink-0 items-center justify-center overflow-hidden rounded-full bg-white text-[9px] font-black text-slate-500 ring-1 ring-slate-200">
                                                            @if (! empty($teamA['flag_path']))
                                                                <img src="{{ asset($teamA['flag_path']) }}" alt="{{ __('Bandera de :team', ['team' => $teamA['name'] ?? $teamA['short_name'] ?? __('equipo')]) }}" class="h-full w-full object-cover" loading="lazy">
                                                            @else
                                                                {{ $teamA['short_name'] ?? 'TBD' }}
                                                            @endif
                                                        </span>
                                                        <span class="truncate text-xs font-black text-blue-950">{{ $teamA['short_name'] ?? $teamA['name'] ?? __('TBD') }}</span>
                                                    </div>

                                                    <span class="justify-self-center rounded-full bg-white px-2.5 py-1 text-sm font-black tabular-nums text-blue-950 ring-1 ring-slate-200">
                                                        {{ $centerLabel }}
                                                    </span>

                                                    <div class="flex min-w-0 items-center justify-end gap-1.5">
                                                        <span class="truncate text-right text-xs font-black text-blue-950">{{ $teamB['short_name'] ?? $teamB['name'] ?? __('TBD') }}</span>
                                                        <span class="inline-flex h-6 w-6 shrink-0 items-center justify-center overflow-hidden rounded-full bg-white text-[9px] font-black text-slate-500 ring-1 ring-slate-200">
                                                            @if (! empty($teamB['flag_path']))
                                                                <img src="{{ asset($teamB['flag_path']) }}" alt="{{ __('Bandera de :team', ['team' => $teamB['name'] ?? $teamB['short_name'] ?? __('equipo')]) }}" class="h-full w-full object-cover" loading="lazy">
                                                            @else
                                                                {{ $teamB['short_name'] ?? 'TBD' }}
                                                            @endif
                                                        </span>
                                                    </div>

                                                    @if ($state['isDot'] ?? false)
                                                        <span
                                                            class="h-2.5 w-2.5 shrink-0 rounded-full {{ $state['color'] }}"
                                                            title="{{ $state['label'] }}"
                                                            aria-label="{{ $state['label'] }}"
                                                        >
                                                            <span class="sr-only">{{ $state['label'] }}</span>
                                                        </span>
                                                    @else
                                                        <span
                                                            class="{{ $state['color'] }} shrink-0 text-sm leading-none"
                                                            title="{{ $state['label'] }}"
                                                            aria-label="{{ $state['label'] }}"
                                                        >
                                                            {{ $state['symbol'] }}
                                                            <span class="sr-only">{{ $state['label'] }}</span>
                                                        </span>
                                                    @endif
                                                </div>

                                                <div class="mt-2 flex flex-wrap items-center justify-center gap-x-2 gap-y-1 text-[11px] font-bold text-slate-500">
                                                    <span class="rounded-full bg-white px-2 py-0.5 ring-1 ring-slate-200">{{ $statusLabel }}</span>
                                                    <span>{{ $predictionLabel }}</span>
                                                    @if ($displayState === 'live' && ($match['last_synced_minutes_ago'] ?? null) !== null)
                                                        <span>{{ __('Hace :minutes min', ['minutes' => (int) $match['last_synced_minutes_ago']]) }}</span>
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
                                            <h2 class="text-lg font-black text-blue-950">{{ __('Tus amigos en la jornada') }}</h2>
                                            <p class="mt-1 text-xs font-semibold leading-5 text-slate-500">
                                                {{ __('Predicciones cargadas en los próximos 4 partidos.') }}
                                            </p>
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
                                                    @php
                                                        $friendUser = new \App\Models\User([
                                                            'name' => $friend['name'],
                                                            'profile_avatar_key' => $friend['avatar']['key'] ?? null,
                                                        ]);
                                                        $completedFriendPredictions = min(4, max(0, (int) ($friend['completed_count'] ?? 0)));
                                                        $totalFriendPredictions = min(4, max(0, (int) ($friend['total_matches'] ?? 4)));
                                                        $friendIndicatorSlots = max(1, $totalFriendPredictions);
                                                    @endphp
                                                    <x-profile-avatar :user="$friendUser" size="sm" />
                                                    <div class="min-w-0">
                                                        <p class="truncate text-sm font-black text-blue-950">{{ $friend['display_name'] ?? $friend['name'] ?? '@'.$friend['username'] }}</p>
                                                        @if (! empty($friend['username']) && trim((string) ($friend['name'] ?? '')) !== '')
                                                            <p class="truncate text-xs font-bold text-slate-500">{{ '@'.$friend['username'] }}</p>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div
                                                    class="flex shrink-0 items-center gap-1.5"
                                                    aria-label="{{ __(':completed de :total predicciones cargadas', ['completed' => $completedFriendPredictions, 'total' => $totalFriendPredictions]) }}"
                                                >
                                                    <span class="sr-only">{{ __(':completed de :total predicciones cargadas', ['completed' => $completedFriendPredictions, 'total' => $totalFriendPredictions]) }}</span>
                                                    <span class="flex items-center gap-1" aria-hidden="true">
                                                        @for ($slot = 1; $slot <= $friendIndicatorSlots; $slot++)
                                                            <span class="h-2.5 w-2.5 rounded-full {{ $slot <= $completedFriendPredictions ? 'bg-emerald-500 ring-1 ring-emerald-600/20' : 'bg-white ring-1 ring-slate-300' }}"></span>
                                                        @endfor
                                                    </span>
                                                    <span class="text-[11px] font-black tabular-nums text-slate-500">
                                                        {{ $completedFriendPredictions }}/{{ $totalFriendPredictions }}
                                                    </span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </section>
                            @endif
                        </aside>
                    @endif
                </div>
            @endif

            @unless ($hasActivePrivateLeagues)
                <section class="overflow-hidden rounded-2xl bg-blue-950 text-white shadow-lg shadow-blue-950/10 ring-1 ring-blue-900/20">
                    <div class="grid gap-5 p-5 sm:p-6 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-center">
                        <div class="min-w-0">
                            <p class="text-xs font-black uppercase tracking-[0.16em] text-emerald-200">
                                {{ __('Liga privada') }}
                            </p>
                            <h2 class="mt-2 text-2xl font-black leading-tight sm:text-3xl">
                                {{ __('Jugá con tus amigos') }}
                            </h2>
                            <p class="mt-2 max-w-2xl text-sm font-semibold leading-6 text-sky-100">
                                {{ __('Creá tu propia liga, compartí el link y competí con tu grupo durante el Mundial.') }}
                            </p>
                        </div>

                        <div class="flex flex-col gap-3 sm:flex-row lg:justify-end">
                            <a
                                href="{{ route('private-leagues.create') }}"
                                class="inline-flex items-center justify-center rounded-xl bg-emerald-400 px-4 py-3 text-sm font-black text-blue-950 shadow-sm transition hover:bg-emerald-300 focus:outline-none focus:ring-4 focus:ring-emerald-200/40"
                            >
                                {{ __('Crear mi liga') }}
                            </a>
                            <a
                                href="{{ route('private-leagues.search') }}"
                                class="inline-flex items-center justify-center rounded-xl bg-white/10 px-4 py-3 text-sm font-black text-white ring-1 ring-white/15 transition hover:bg-white/15 focus:outline-none focus:ring-4 focus:ring-sky-200/30"
                            >
                                {{ __('Buscar liga') }}
                            </a>
                        </div>
                    </div>

                    <div class="border-t border-white/10 bg-white/[0.03] px-5 py-5 sm:px-6">
                        <div class="grid gap-3 md:grid-cols-4">
                            @foreach ([
                                ['title' => __('Creá tu liga'), 'body' => __('Elegí el nombre que más te guste.'), 'path' => 'M12 5v14m7-7H5'],
                                ['title' => __('Copiá el link'), 'body' => __('El sistema genera un link para invitar.'), 'path' => 'M10 13a5 5 0 0 0 7.1 0l1.4-1.4a5 5 0 0 0-7.1-7.1L10 5.9m4 5.1a5 5 0 0 0-7.1 0l-1.4 1.4a5 5 0 0 0 7.1 7.1L14 18.1'],
                                ['title' => __('Compartilo con tus amigos'), 'body' => __('Mandalo por WhatsApp, Telegram o donde quieras.'), 'path' => 'M8 12h8m-8-4h8m-8 8h5m8-4a9 9 0 1 1-4.2-7.6L21 3v6h-6l2.2-2.2'],
                                ['title' => __('Compitan en su ranking'), 'body' => __('Tus amigos piden entrar y juegan en la misma tabla.'), 'path' => 'M7 20V10m5 10V4m5 16v-7M5 20h14'],
                            ] as $step)
                                <div class="rounded-xl bg-white/[0.08] p-3 ring-1 ring-white/10">
                                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-emerald-300/15 text-emerald-200 ring-1 ring-emerald-200/20">
                                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true" class="h-5 w-5">
                                            <path d="{{ $step['path'] }}" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </span>
                                    <h3 class="mt-3 text-sm font-black text-white">{{ $step['title'] }}</h3>
                                    <p class="mt-1 text-xs font-semibold leading-5 text-sky-100/85">{{ $step['body'] }}</p>
                                </div>
                            @endforeach
                        </div>

                        <p class="mt-4 text-xs font-bold text-emerald-100">
                            {{ __('Tus amigos se suman desde un link y vos aprobás el ingreso.') }}
                        </p>
                    </div>
                </section>
            @endunless

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
            </div>

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
