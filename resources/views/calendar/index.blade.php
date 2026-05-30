<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Calendario') }}
            </h2>
            <p class="text-sm text-gray-500">
                {{ __('Todos los partidos conocidos del Mundial 2026 en orden cronologico.') }}
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            @if ($matchesByDate->isEmpty())
                <div class="bg-white border border-gray-200 shadow-sm sm:rounded-lg">
                    <div class="p-6 text-sm text-gray-700">
                        {{ __('Todavia no hay partidos cargados en el calendario.') }}
                    </div>
                </div>
            @else
                <div class="space-y-6">
                    @foreach ($matchesByDate as $date => $matches)
                        <section class="space-y-3">
                            <div class="sticky top-0 z-10 -mx-4 border-y border-gray-200 bg-gray-50 px-4 py-2 sm:static sm:mx-0 sm:rounded-md sm:border">
                                <h3 class="text-sm font-semibold text-gray-800">
                                    @if ($date === 'date_pending')
                                        {{ __('Fecha por definir') }}
                                    @else
                                        {{ \Illuminate\Support\Carbon::parse($date)->format('d/m/Y') }}
                                    @endif
                                </h3>
                            </div>

                            <div class="space-y-3">
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
                                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                                <div class="flex items-start gap-4">
                                                    <div class="w-14 shrink-0 rounded-md bg-gray-100 px-2 py-2 text-center text-sm font-semibold text-gray-800">
                                                        {{ $match->starts_at ? $match->starts_at->format('H:i') : '--:--' }}
                                                    </div>

                                                    <div class="min-w-0">
                                                        <div class="flex flex-wrap items-center gap-2 text-xs text-gray-500">
                                                            @if ($match->stage)
                                                                <span>{{ __('Fase') }}: {{ str_replace('_', ' ', $match->stage) }}</span>
                                                            @endif

                                                            @if ($match->group)
                                                                <span aria-hidden="true">-</span>
                                                                <span>{{ __('Grupo') }} {{ $match->group }}</span>
                                                            @endif
                                                        </div>

                                                        <div class="mt-2 flex flex-wrap items-center gap-2 text-base font-semibold text-gray-900">
                                                            <span>{{ $teamAName }}</span>
                                                            @if ($match->status === 'finished')
                                                                <span class="rounded-md bg-gray-900 px-2 py-1 text-sm text-white">
                                                                    {{ $match->team_a_score }} - {{ $match->team_b_score }}
                                                                </span>
                                                            @else
                                                                <span class="text-sm text-gray-500">{{ __('vs') }}</span>
                                                            @endif
                                                            <span>{{ $teamBName }}</span>
                                                        </div>

                                                        @if ($isPlaceholder)
                                                            <p class="mt-2 text-sm text-gray-600">
                                                                {{ __('Partido pendiente de equipos definidos.') }}
                                                            </p>
                                                        @elseif ($match->status === 'finished' && $match->winnerTeam)
                                                            <p class="mt-2 text-sm text-gray-600">
                                                                {{ __('Ganador') }}: {{ $match->winnerTeam->name }}
                                                            </p>
                                                        @endif
                                                    </div>
                                                </div>

                                                <div class="shrink-0">
                                                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset {{ $statusClass }}">
                                                        {{ $statusLabel }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        </section>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
