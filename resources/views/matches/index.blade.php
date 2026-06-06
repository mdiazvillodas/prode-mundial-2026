<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Partidos') }}
            </h2>
            <p class="text-sm text-gray-500">
                {{ __('Proximos partidos y estados actuales del Mundial 2026.') }}
            </p>
            <div class="mt-2 flex flex-wrap gap-3">
                <a href="{{ route('predictions.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
                    {{ __('Cargar predicciones') }}
                </a>
                <a href="{{ route('calendar.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
                    {{ __('Ver calendario completo') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            @if ($matches->isEmpty())
                <div class="bg-white border border-gray-200 shadow-sm sm:rounded-lg">
                    <div class="p-6 text-sm text-gray-700">
                        {{ __('Todavia no hay partidos cargados.') }}
                    </div>
                </div>
            @else
                <div class="space-y-4">
                    @foreach ($matches as $match)
                        @php
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

                            $isPlaceholder = $match->status === 'placeholder' || ! $match->teamA || ! $match->teamB;
                            $teamAName = $match->teamA?->name ?? 'Equipo por definir';
                            $teamBName = $match->teamB?->name ?? 'Equipo por definir';
                            $statusLabel = $statusLabels[$match->status] ?? ucfirst($match->status);
                            $statusClass = $statusClasses[$match->status] ?? 'bg-gray-100 text-gray-700 ring-gray-500/20';
                        @endphp

                        <article class="bg-white border border-gray-200 shadow-sm sm:rounded-lg">
                            <div class="p-4 sm:p-5">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                    <div class="min-w-0">
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

                                        <div class="mt-3 grid grid-cols-[1fr_auto_1fr] items-center gap-3">
                                            <div class="min-w-0">
                                                <div class="flex items-center gap-2">
                                                    <x-team-flag :team="$match->teamA" size="sm" />
                                                    <div class="min-w-0">
                                                        <p class="truncate text-base font-semibold text-gray-900">{{ $teamAName }}</p>
                                                        @if ($match->teamA?->short_name)
                                                            <p class="text-xs uppercase tracking-wide text-gray-500">{{ $match->teamA->short_name }}</p>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="text-center text-sm font-semibold text-gray-500">
                                                @if ($match->status === 'finished')
                                                    <span class="inline-flex min-w-14 justify-center rounded-md bg-gray-900 px-2 py-1 text-white">
                                                        {{ $match->team_a_score }} - {{ $match->team_b_score }}
                                                    </span>
                                                @else
                                                    <span>{{ __('vs') }}</span>
                                                @endif
                                            </div>

                                            <div class="min-w-0 text-right">
                                                <div class="flex items-center justify-end gap-2">
                                                    <div class="min-w-0">
                                                        <p class="truncate text-base font-semibold text-gray-900">{{ $teamBName }}</p>
                                                        @if ($match->teamB?->short_name)
                                                            <p class="text-xs uppercase tracking-wide text-gray-500">{{ $match->teamB->short_name }}</p>
                                                        @endif
                                                    </div>
                                                    <x-team-flag :team="$match->teamB" size="sm" />
                                                </div>
                                            </div>
                                        </div>

                                        @if ($isPlaceholder)
                                            <p class="mt-3 text-sm text-gray-600">
                                                {{ __('Este partido se completara cuando los equipos esten definidos.') }}
                                            </p>
                                        @elseif ($match->status === 'finished' && $match->winnerTeam)
                                            <p class="mt-3 text-sm text-gray-600">
                                                {{ __('Ganador') }}: {{ $match->winnerTeam->name }}
                                            </p>
                                        @endif
                                    </div>

                                    <div class="flex shrink-0 items-center gap-2">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset {{ $statusClass }}">
                                            {{ $statusLabel }}
                                        </span>

                                        @if ($match->isPredictable())
                                            <a href="{{ route('predictions.show', $match) }}" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-xs font-semibold text-white shadow-sm hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                                {{ $match->predictions->isNotEmpty() ? __('Editar prediccion') : __('Cargar prediccion') }}
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
