<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Calendario') }}
            </h2>
            <p class="text-sm text-gray-500">
                {{ __('Elegí una selección y seguí su agenda conocida del Mundial 2026.') }}
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-5xl space-y-6 px-4 sm:px-6 lg:px-8">
            <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
                <form method="GET" action="{{ route('calendar.index') }}" class="space-y-4 sm:flex sm:items-end sm:gap-4 sm:space-y-0">
                    <div class="flex-1">
                        <label for="team_id" class="text-sm font-bold uppercase tracking-wide text-indigo-700">
                            {{ __('Selección') }}
                        </label>
                        <select
                            id="team_id"
                            name="team_id"
                            data-team-selector
                            class="mt-2 block w-full rounded-xl border-gray-300 bg-white text-base font-semibold text-gray-950 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                            <option value="">{{ __('Elegí una selección') }}</option>
                            @foreach ($teams as $team)
                                <option value="{{ $team->id }}" @selected($selectedTeam?->id === $team->id)>
                                    {{ $team->name }}{{ $team->country_code ? ' · '.$team->country_code : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <button
                        type="submit"
                        class="inline-flex w-full items-center justify-center rounded-xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:w-auto"
                    >
                        {{ __('Ver calendario') }}
                    </button>
                </form>

                @if ($selectedTeam)
                    <div class="mt-5 flex items-center gap-3 rounded-xl bg-indigo-50 p-4">
                        <x-team-flag :team="$selectedTeam" class="h-12 w-12 bg-white ring-1 ring-indigo-100" />
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wide text-indigo-700">
                                {{ __('Agenda seleccionada') }}
                            </p>
                            <h3 class="text-lg font-black text-gray-950">
                                {{ $selectedTeam->name }}
                            </h3>
                        </div>
                    </div>
                @endif
            </section>

            @if ($requestedTeamId && ! $selectedTeam)
                <section class="rounded-2xl bg-white p-8 text-center shadow-sm ring-1 ring-red-100">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-red-50 text-xl font-black text-red-700">
                        !
                    </div>
                    <h3 class="mt-4 text-lg font-black text-gray-950">
                        {{ __('Selección no encontrada') }}
                    </h3>
                    <p class="mt-2 text-sm text-gray-600">
                        {{ __('Elegí una selección válida para ver su calendario.') }}
                    </p>
                </section>
            @elseif (! $selectedTeam)
                <section class="rounded-2xl bg-white p-8 text-center shadow-sm ring-1 ring-gray-100">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-indigo-50 text-xl font-black text-indigo-700">
                        ?
                    </div>
                    <h3 class="mt-4 text-lg font-black text-gray-950">
                        {{ __('Elegí una selección') }}
                    </h3>
                    <p class="mt-2 text-sm text-gray-600">
                        {{ __('El calendario se va a mostrar con partidos confirmados, resultados y próximos cruces conocidos.') }}
                    </p>
                </section>
            @elseif ($matches->isEmpty())
                <section class="rounded-2xl bg-white p-8 text-center shadow-sm ring-1 ring-gray-100">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-gray-100 text-xl font-black text-gray-700">
                        0
                    </div>
                    <h3 class="mt-4 text-lg font-black text-gray-950">
                        {{ __('Sin partidos conocidos') }}
                    </h3>
                    <p class="mt-2 text-sm text-gray-600">
                        {{ __('Todavía no hay partidos cargados para esta selección. Los cruces de eliminatorias aparecerán cuando el equipo esté asignado al partido.') }}
                    </p>
                </section>
            @else
                <section class="space-y-4">
                    @foreach ($matches as $match)
                        @php
                            $statusLabels = [
                                'scheduled' => 'Programado',
                                'open' => 'Abierto',
                                'locked' => 'Cerrado',
                                'finished' => 'Finalizado',
                                'placeholder' => 'Por definir',
                            ];

                            $statusClasses = [
                                'scheduled' => 'bg-sky-50 text-sky-700 ring-sky-600/20',
                                'open' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
                                'locked' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
                                'finished' => 'bg-gray-100 text-gray-700 ring-gray-500/20',
                                'placeholder' => 'bg-blue-50 text-blue-700 ring-blue-600/20',
                            ];

                            $selectedTeamIsA = $match->team_a_id === $selectedTeam->id;
                            $opponent = $selectedTeamIsA ? $match->teamB : $match->teamA;
                            $selectedScore = $selectedTeamIsA ? $match->team_a_score : $match->team_b_score;
                            $opponentScore = $selectedTeamIsA ? $match->team_b_score : $match->team_a_score;
                            $statusLabel = $statusLabels[$match->status] ?? ucfirst($match->status);
                            $statusClass = $statusClasses[$match->status] ?? 'bg-gray-100 text-gray-700 ring-gray-500/20';
                            $stageLabel = $match->stage ? str_replace('_', ' ', $match->stage) : null;
                        @endphp

                        <article class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-100">
                            <div class="flex items-start justify-between gap-3 border-b border-gray-100 p-4">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2 text-xs font-bold uppercase tracking-wide text-gray-500">
                                        @if ($stageLabel)
                                            <span>{{ $stageLabel }}</span>
                                        @endif
                                        @if ($match->group)
                                            <span class="text-gray-300">·</span>
                                            <span>{{ __('Grupo') }} {{ $match->group }}</span>
                                        @endif
                                    </div>
                                    <p class="mt-1 text-sm font-semibold text-gray-700">
                                        @if ($match->starts_at)
                                            <span data-local-date="{{ $match->starts_at->toIso8601String() }}">
                                                {{ $match->starts_at->format('d/m/Y') }}
                                            </span>
                                            <span class="text-gray-300">·</span>
                                            <span data-local-time="{{ $match->starts_at->toIso8601String() }}">
                                                {{ $match->starts_at->format('H:i') }}
                                            </span>
                                        @else
                                            {{ __('Fecha por definir') }}
                                        @endif
                                    </p>
                                </div>

                                <span class="inline-flex shrink-0 items-center rounded-full px-2.5 py-1 text-xs font-bold ring-1 ring-inset {{ $statusClass }}">
                                    {{ $statusLabel }}
                                </span>
                            </div>

                            <div class="p-5">
                                <div class="grid grid-cols-[1fr_auto_1fr] items-center gap-3">
                                    <div class="min-w-0 text-center">
                                        <x-team-flag :team="$selectedTeam" size="lg" class="mx-auto ring-2 ring-indigo-100" />
                                        <h4 class="mt-2 truncate text-sm font-black text-gray-950 sm:text-base">
                                            {{ $selectedTeam->name }}
                                        </h4>
                                        <p class="text-xs font-semibold uppercase tracking-wide text-indigo-700">
                                            {{ __('Selección') }}
                                        </p>
                                    </div>

                                    <div class="text-center">
                                        @if ($match->status === 'finished')
                                            <div class="rounded-2xl bg-gray-950 px-4 py-3 text-white shadow-sm">
                                                <p class="text-3xl font-black">
                                                    {{ $selectedScore }} - {{ $opponentScore }}
                                                </p>
                                                <p class="mt-1 text-[11px] font-bold uppercase tracking-wide text-gray-300">
                                                    {{ __('Resultado') }}
                                                </p>
                                            </div>
                                        @else
                                            <div class="rounded-2xl bg-gray-50 px-4 py-3 ring-1 ring-gray-100">
                                                <p class="text-2xl font-black text-gray-950">
                                                    {{ __('vs') }}
                                                </p>
                                                <p class="mt-1 text-[11px] font-bold uppercase tracking-wide text-gray-500">
                                                    {{ __('Próximo') }}
                                                </p>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="min-w-0 text-center">
                                        <x-team-flag :team="$opponent" size="lg" class="mx-auto ring-2 ring-gray-100" />
                                        <h4 class="mt-2 truncate text-sm font-black text-gray-950 sm:text-base">
                                            {{ $opponent?->name ?? __('Rival por definir') }}
                                        </h4>
                                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                            {{ __('Rival') }}
                                        </p>
                                    </div>
                                </div>

                                <div class="mt-5 rounded-xl bg-gray-50 p-4 text-sm text-gray-700">
                                    @if ($match->status === 'finished')
                                        @if ($match->winnerTeam)
                                            {{ __('Ganador') }}: <span class="font-bold text-gray-950">{{ $match->winnerTeam->name }}</span>
                                        @else
                                            {{ __('Partido finalizado en empate.') }}
                                        @endif
                                    @elseif ($match->starts_at)
                                        {{ __('Horario mostrado en tu zona local cuando el navegador lo permite.') }}
                                    @else
                                        {{ __('El horario de este partido todavía no está definido.') }}
                                    @endif
                                </div>
                            </div>
                        </article>
                    @endforeach
                </section>
            @endif
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const selector = document.querySelector('[data-team-selector]');
            const storageKey = 'prode.calendar.selectedTeamId';

            if (selector) {
                const params = new URLSearchParams(window.location.search);
                const currentTeamId = params.get('team_id');

                if (currentTeamId) {
                    window.localStorage.setItem(storageKey, currentTeamId);
                } else {
                    const savedTeamId = window.localStorage.getItem(storageKey);

                    if (savedTeamId && selector.querySelector(`option[value="${savedTeamId}"]`)) {
                        selector.value = savedTeamId;
                    }
                }

                selector.addEventListener('change', () => {
                    if (selector.value) {
                        window.localStorage.setItem(storageKey, selector.value);
                    } else {
                        window.localStorage.removeItem(storageKey);
                    }
                });
            }

            document.querySelectorAll('[data-local-date], [data-local-time]').forEach((element) => {
                const isoDate = element.dataset.localDate || element.dataset.localTime;
                const date = new Date(isoDate);

                if (Number.isNaN(date.getTime())) {
                    return;
                }

                if (element.dataset.localDate) {
                    element.textContent = new Intl.DateTimeFormat(undefined, {
                        day: '2-digit',
                        month: '2-digit',
                        year: 'numeric',
                    }).format(date);
                }

                if (element.dataset.localTime) {
                    element.textContent = new Intl.DateTimeFormat(undefined, {
                        hour: '2-digit',
                        minute: '2-digit',
                    }).format(date);
                }
            });
        });
    </script>
</x-app-layout>
