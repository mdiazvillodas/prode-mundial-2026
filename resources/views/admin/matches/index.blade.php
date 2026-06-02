<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Admin partidos') }}
            </h2>
            <p class="text-sm text-gray-500">
                {{ __('Listado minimo para revisar partidos y preparar la carga de resultados.') }}
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            @if ($matches->isEmpty())
                <div class="rounded-lg border border-gray-200 bg-white p-6 text-sm text-gray-700 shadow-sm">
                    {{ __('Todavia no hay partidos cargados.') }}
                </div>
            @else
                <div class="space-y-4">
                    @foreach ($matches as $match)
                        @php
                            $teamAName = $match->teamA?->name ?? 'Equipo por definir';
                            $teamBName = $match->teamB?->name ?? 'Equipo por definir';
                            $teamACode = $match->teamA?->country_code ?? 'TBD';
                            $teamBCode = $match->teamB?->country_code ?? 'TBD';
                            $isPlaceholder = $match->status === 'placeholder' || ! $match->teamA || ! $match->teamB;
                            $hasResult = $match->team_a_score !== null && $match->team_b_score !== null;

                            $statusLabels = [
                                'scheduled' => 'Programado',
                                'open' => 'Abierto',
                                'locked' => 'Cerrado',
                                'finished' => 'Terminado',
                                'placeholder' => 'Por definir',
                            ];

                            $statusClasses = [
                                'scheduled' => 'bg-sky-50 text-sky-700 ring-sky-600/20',
                                'open' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
                                'locked' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
                                'finished' => 'bg-gray-100 text-gray-700 ring-gray-500/20',
                                'placeholder' => 'bg-violet-50 text-violet-700 ring-violet-600/20',
                            ];

                            $statusLabel = $statusLabels[$match->status] ?? ucfirst($match->status);
                            $statusClass = $statusClasses[$match->status] ?? 'bg-gray-100 text-gray-700 ring-gray-500/20';
                        @endphp

                        <article class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                            <div class="p-4 sm:p-5">
                                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-2 text-xs text-gray-500">
                                            <span>{{ $match->starts_at ? $match->starts_at->format('d/m/Y H:i') : __('Fecha por definir') }}</span>

                                            @if ($match->stage)
                                                <span aria-hidden="true">-</span>
                                                <span>{{ __('Fase') }}: {{ str_replace('_', ' ', $match->stage) }}</span>
                                            @endif

                                            @if ($match->group)
                                                <span aria-hidden="true">-</span>
                                                <span>{{ __('Grupo') }} {{ $match->group }}</span>
                                            @endif
                                        </div>

                                        <div class="mt-4 grid grid-cols-[1fr_auto_1fr] items-center gap-3">
                                            <div class="min-w-0">
                                                <div class="flex items-center gap-2">
                                                    <span class="inline-flex h-8 w-10 shrink-0 items-center justify-center rounded-md bg-gray-100 text-xs font-bold uppercase text-gray-600">
                                                        {{ $teamACode }}
                                                    </span>
                                                    <div class="min-w-0">
                                                        <p class="truncate text-base font-semibold text-gray-900">{{ $teamAName }}</p>
                                                        @if ($match->teamA?->short_name)
                                                            <p class="text-xs uppercase tracking-wide text-gray-500">{{ $match->teamA->short_name }}</p>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="text-center text-sm font-semibold text-gray-500">
                                                @if ($hasResult)
                                                    <span class="inline-flex min-w-14 justify-center rounded-md bg-gray-900 px-2 py-1 text-white">
                                                        {{ $match->team_a_score }} - {{ $match->team_b_score }}
                                                    </span>
                                                @else
                                                    <span>{{ __('vs') }}</span>
                                                @endif
                                            </div>

                                            <div class="min-w-0">
                                                <div class="flex items-center justify-end gap-2">
                                                    <div class="min-w-0 text-right">
                                                        <p class="truncate text-base font-semibold text-gray-900">{{ $teamBName }}</p>
                                                        @if ($match->teamB?->short_name)
                                                            <p class="text-xs uppercase tracking-wide text-gray-500">{{ $match->teamB->short_name }}</p>
                                                        @endif
                                                    </div>
                                                    <span class="inline-flex h-8 w-10 shrink-0 items-center justify-center rounded-md bg-gray-100 text-xs font-bold uppercase text-gray-600">
                                                        {{ $teamBCode }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        @if ($isPlaceholder)
                                            <p class="mt-3 text-sm text-gray-600">
                                                {{ __('Placeholder: los equipos se definiran mas adelante.') }}
                                            </p>
                                        @elseif ($hasResult && $match->winnerTeam)
                                            <p class="mt-3 text-sm text-gray-600">
                                                {{ __('Ganador') }}: {{ $match->winnerTeam->name }}
                                            </p>
                                        @elseif ($hasResult)
                                            <p class="mt-3 text-sm text-gray-600">
                                                {{ __('Resultado cargado con empate.') }}
                                            </p>
                                        @endif
                                    </div>

                                    <div class="flex shrink-0 flex-col gap-3 sm:flex-row lg:flex-col lg:items-end">
                                        <span class="inline-flex w-fit items-center rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset {{ $statusClass }}">
                                            {{ $statusLabel }}
                                        </span>

                                        @if ($isPlaceholder)
                                            <a
                                                href="{{ route('admin.matches.teams.edit', $match) }}"
                                                class="inline-flex items-center justify-center rounded-md border border-indigo-300 bg-indigo-50 px-3 py-2 text-xs font-semibold text-indigo-700 shadow-sm transition hover:bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                            >
                                                {{ __('Asignar equipos') }}
                                            </a>
                                        @else
                                            <a
                                                href="{{ route('admin.matches.result.edit', $match) }}"
                                                class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                            >
                                                {{ $hasResult ? __('Editar resultado') : __('Cargar resultado') }}
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
