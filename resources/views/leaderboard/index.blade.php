<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Liga general') }}
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    {{ __('Tabla de posiciones de la liga general, ordenada por puntos de predicciones puntuadas.') }}
                </p>
            </div>

            <a
                href="{{ route('predictions.index') }}"
                class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
            >
                {{ __('Cargar predicciones') }}
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-6xl space-y-5 px-4 sm:px-6 lg:px-8">
            @forelse ($leaderboard as $entry)
                @php
                    $isFirstPlace = $loop->first;
                @endphp

                <article @class([
                    'overflow-hidden rounded-lg bg-white shadow-sm ring-1',
                    'ring-amber-300 shadow-amber-100' => $isFirstPlace,
                    'ring-gray-100' => ! $isFirstPlace,
                ])>
                    <div @class([
                        'px-5 py-4',
                        'bg-amber-50' => $isFirstPlace,
                        'bg-white' => ! $isFirstPlace,
                    ])>
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex items-center gap-4">
                                <div @class([
                                    'flex h-12 w-12 shrink-0 items-center justify-center rounded-full text-lg font-bold',
                                    'bg-amber-500 text-white' => $isFirstPlace,
                                    'bg-gray-100 text-gray-800' => ! $isFirstPlace,
                                ])>
                                    {{ $loop->iteration }}
                                </div>

                                <div class="min-w-0">
                                    <p class="truncate text-lg font-semibold text-gray-950">
                                        {{ '@'.$entry->username }}
                                    </p>
                                    @if ($isFirstPlace)
                                        <p class="text-sm font-medium text-amber-700">
                                            {{ __('Primer puesto') }}
                                        </p>
                                    @else
                                        <p class="text-sm text-gray-500">
                                            {{ __('Liga general') }}
                                        </p>
                                    @endif
                                </div>
                            </div>

                            <div class="rounded-md bg-gray-950 px-4 py-3 text-center text-white sm:min-w-32">
                                <p class="text-xs font-medium uppercase tracking-wide text-gray-300">
                                    {{ __('Puntos') }}
                                </p>
                                <p class="text-3xl font-bold">
                                    {{ $entry->total_points }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-3 border-t border-gray-100 p-5 sm:grid-cols-3">
                        <div class="rounded-md bg-emerald-50 p-4">
                            <p class="text-xs font-medium uppercase tracking-wide text-emerald-700">
                                {{ __('Resultados exactos') }}
                            </p>
                            <p class="mt-2 text-2xl font-bold text-emerald-950">
                                {{ $entry->exact_results_count }}
                            </p>
                        </div>

                        <div class="rounded-md bg-indigo-50 p-4">
                            <p class="text-xs font-medium uppercase tracking-wide text-indigo-700">
                                {{ __('Tendencias') }}
                            </p>
                            <p class="mt-2 text-2xl font-bold text-indigo-950">
                                {{ $entry->trend_count }}
                            </p>
                        </div>

                        <div class="rounded-md bg-gray-50 p-4">
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-600">
                                {{ __('Predicciones puntuadas') }}
                            </p>
                            <p class="mt-2 text-2xl font-bold text-gray-950">
                                {{ $entry->scored_predictions_count }}
                            </p>
                        </div>
                    </div>
                </article>
            @empty
                <div class="rounded-lg bg-white p-8 text-center shadow-sm ring-1 ring-gray-100">
                    <h3 class="text-lg font-semibold text-gray-900">
                        {{ __('Todavia no hay tabla de posiciones') }}
                    </h3>
                    <p class="mt-2 text-sm text-gray-600">
                        {{ __('La tabla de posiciones se va a completar cuando haya predicciones puntuadas.') }}
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
