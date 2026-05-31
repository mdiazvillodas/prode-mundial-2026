<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Mis predicciones') }}
            </h2>

            <a
                href="{{ route('predictions.index') }}"
                class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
            >
                {{ __('Cargar predicciones') }}
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-5xl space-y-5 px-4 sm:px-6 lg:px-8">
            <div class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-gray-100">
                <p class="text-sm text-gray-600">
                    {{ __('Revisa tus predicciones, el resultado real del partido y los puntos obtenidos cuando ya fueron calculados.') }}
                </p>
            </div>

            @forelse ($predictions as $prediction)
                @php
                    $match = $prediction->match;
                    $teamAName = $match->teamA?->name ?? __('Equipo por definir');
                    $teamBName = $match->teamB?->name ?? __('Equipo por definir');
                    $teamACode = $match->teamA?->country_code ?? '---';
                    $teamBCode = $match->teamB?->country_code ?? '---';
                    $statusLabels = [
                        'submitted' => __('Pendiente'),
                        'locked' => __('Cerrada'),
                        'scored' => __('Puntuada'),
                        'failed' => __('Con error'),
                    ];
                    $statusClasses = [
                        'submitted' => 'bg-amber-100 text-amber-800 ring-amber-500/20',
                        'locked' => 'bg-gray-100 text-gray-700 ring-gray-500/20',
                        'scored' => 'bg-emerald-100 text-emerald-800 ring-emerald-500/20',
                        'failed' => 'bg-rose-100 text-rose-800 ring-rose-500/20',
                    ];
                    $statusLabel = $statusLabels[$prediction->status] ?? ucfirst($prediction->status);
                    $statusClass = $statusClasses[$prediction->status] ?? 'bg-gray-100 text-gray-700 ring-gray-500/20';
                    $hasResult = $match->status === 'finished'
                        && $match->team_a_score !== null
                        && $match->team_b_score !== null;
                @endphp

                <article class="overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-gray-100">
                    <div class="border-b border-gray-100 bg-gray-50 px-5 py-3">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <div class="text-sm text-gray-600">
                                <span class="font-medium text-gray-900">
                                    {{ $match->starts_at?->format('d/m/Y H:i') ?? __('Fecha a definir') }}
                                </span>

                                @if ($match->stage)
                                    <span class="mx-2 text-gray-300">|</span>
                                    <span>{{ $match->stage }}</span>
                                @endif

                                @if ($match->group)
                                    <span class="mx-2 text-gray-300">|</span>
                                    <span>{{ __('Grupo :group', ['group' => $match->group]) }}</span>
                                @endif
                            </div>

                            <span class="inline-flex w-fit items-center rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset {{ $statusClass }}">
                                {{ $statusLabel }}
                            </span>
                        </div>
                    </div>

                    <div class="space-y-5 p-5">
                        <div class="grid grid-cols-[1fr_auto_1fr] items-center gap-3">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex h-8 w-10 shrink-0 items-center justify-center rounded bg-gray-100 text-xs font-semibold text-gray-700">
                                        {{ $teamACode }}
                                    </span>
                                    <p class="truncate text-sm font-semibold text-gray-900">{{ $teamAName }}</p>
                                </div>
                            </div>

                            <div class="text-center text-xs font-semibold uppercase tracking-wide text-gray-400">
                                {{ __('vs') }}
                            </div>

                            <div class="min-w-0">
                                <div class="flex items-center justify-end gap-2">
                                    <p class="truncate text-right text-sm font-semibold text-gray-900">{{ $teamBName }}</p>
                                    <span class="inline-flex h-8 w-10 shrink-0 items-center justify-center rounded bg-gray-100 text-xs font-semibold text-gray-700">
                                        {{ $teamBCode }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-3">
                            <div class="rounded-md bg-indigo-50 p-4">
                                <p class="text-xs font-medium uppercase tracking-wide text-indigo-700">
                                    {{ __('Tu prediccion') }}
                                </p>
                                <p class="mt-2 text-2xl font-bold text-indigo-950">
                                    {{ $prediction->team_a_score }} - {{ $prediction->team_b_score }}
                                </p>
                            </div>

                            <div class="rounded-md bg-gray-50 p-4">
                                <p class="text-xs font-medium uppercase tracking-wide text-gray-600">
                                    {{ __('Resultado') }}
                                </p>
                                <p class="mt-2 text-2xl font-bold text-gray-950">
                                    @if ($hasResult)
                                        {{ $match->team_a_score }} - {{ $match->team_b_score }}
                                    @else
                                        {{ __('Pendiente') }}
                                    @endif
                                </p>
                            </div>

                            <div class="rounded-md bg-emerald-50 p-4">
                                <p class="text-xs font-medium uppercase tracking-wide text-emerald-700">
                                    {{ __('Puntos') }}
                                </p>
                                <p class="mt-2 text-2xl font-bold text-emerald-950">
                                    @if ($prediction->points_awarded !== null)
                                        {{ $prediction->points_awarded }}
                                    @else
                                        {{ __('Pendiente') }}
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </article>
            @empty
                <div class="rounded-lg bg-white p-8 text-center shadow-sm ring-1 ring-gray-100">
                    <h3 class="text-lg font-semibold text-gray-900">
                        {{ __('Todavia no cargaste predicciones') }}
                    </h3>
                    <p class="mt-2 text-sm text-gray-600">
                        {{ __('Cuando guardes tus predicciones, van a aparecer aca junto con su resultado y puntos.') }}
                    </p>
                    <a
                        href="{{ route('predictions.index') }}"
                        class="mt-5 inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        {{ __('Ir a predicciones') }}
                    </a>
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
