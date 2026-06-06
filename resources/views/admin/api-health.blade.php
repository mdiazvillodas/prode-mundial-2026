@php
    $statusClasses = [
        'ok' => 'bg-emerald-100 text-emerald-800 ring-emerald-200',
        'warning' => 'bg-amber-100 text-amber-800 ring-amber-200',
        'error' => 'bg-red-100 text-red-800 ring-red-200',
        'success' => 'bg-emerald-100 text-emerald-800 ring-emerald-200',
        'failed' => 'bg-red-100 text-red-800 ring-red-200',
        'skipped' => 'bg-gray-100 text-gray-700 ring-gray-200',
    ];

    $formatLogTime = fn ($log) => $log?->finished_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') ?? 'n/a';
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Estado API-Football') }}
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    {{ __('Sync, cuota y datos importados') }}
                </p>
            </div>

            <a
                href="{{ route('admin.dashboard') }}"
                class="inline-flex items-center justify-center rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm ring-1 ring-gray-200 transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
            >
                {{ __('Volver al admin') }}
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <section class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-gray-100">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-medium uppercase tracking-wide text-gray-500">
                            {{ __('Estado general') }}
                        </p>
                        <p class="mt-2 text-2xl font-black text-gray-950">
                            {{ $overallStatus === 'ok' ? __('OK') : ($overallStatus === 'warning' ? __('Atencion') : __('Error')) }}
                        </p>
                    </div>
                    <span class="{{ $statusClasses[$overallStatus] }} inline-flex w-fit items-center rounded-full px-3 py-1 text-sm font-bold ring-1">
                        {{ $overallStatus === 'ok' ? __('Ultimos sync correctos') : ($overallStatus === 'warning' ? __('Revisar frescura') : __('Ultimo sync fallido')) }}
                    </span>
                </div>
                <p class="mt-3 text-sm text-gray-600">
                    {{ __('Aviso de frescura: :minutes minutos sin sync exitoso reciente.', ['minutes' => $warningMinutes]) }}
                </p>
            </section>

            <section class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <article class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-gray-100">
                    <p class="text-sm font-medium text-gray-500">{{ __('Ultimo sync equipos') }}</p>
                    <div class="mt-3 flex items-center justify-between gap-3">
                        <p class="text-lg font-black text-gray-950">{{ $formatLogTime($lastSuccessfulTeamsSync) }}</p>
                        <span class="{{ $statusClasses[$indicators['teams']['status']] }} rounded-full px-3 py-1 text-xs font-bold ring-1">
                            {{ $indicators['teams']['label'] }}
                        </span>
                    </div>
                </article>

                <article class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-gray-100">
                    <p class="text-sm font-medium text-gray-500">{{ __('Ultimo sync fixtures') }}</p>
                    <div class="mt-3 flex items-center justify-between gap-3">
                        <p class="text-lg font-black text-gray-950">{{ $formatLogTime($lastSuccessfulFixturesSync) }}</p>
                        <span class="{{ $statusClasses[$indicators['fixtures']['status']] }} rounded-full px-3 py-1 text-xs font-bold ring-1">
                            {{ $indicators['fixtures']['label'] }}
                        </span>
                    </div>
                </article>

                <article class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-gray-100">
                    <p class="text-sm font-medium text-gray-500">{{ __('Ultimo fallo') }}</p>
                    <p class="mt-3 text-lg font-black text-gray-950">{{ $formatLogTime($lastFailedSync) }}</p>
                    <p class="mt-2 line-clamp-2 text-sm text-gray-600">{{ $lastFailedSync?->error_message ?: __('Sin fallos registrados') }}</p>
                </article>

                <article class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-gray-100">
                    <p class="text-sm font-medium text-gray-500">{{ __('Equipos API en DB') }}</p>
                    <p class="mt-2 text-3xl font-black text-gray-950">{{ $counts['apiTeams'] }}</p>
                </article>

                <article class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-gray-100">
                    <p class="text-sm font-medium text-gray-500">{{ __('Fixtures API en DB') }}</p>
                    <p class="mt-2 text-3xl font-black text-gray-950">{{ $counts['apiFixtures'] }}</p>
                </article>

                <article class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-gray-100">
                    <p class="text-sm font-medium text-gray-500">{{ __('Equipos sin flag_path') }}</p>
                    <p class="mt-2 text-3xl font-black text-gray-950">{{ $counts['teamsMissingFlags'] }}</p>
                </article>
            </section>

            <section class="grid gap-4 lg:grid-cols-3">
                <article class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-gray-100 lg:col-span-2">
                    <h3 class="text-lg font-bold text-gray-950">{{ __('Logs recientes') }}</h3>

                    <div class="mt-4 overflow-hidden rounded-lg border border-gray-100">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-100 text-sm">
                                <thead class="bg-gray-50 text-left text-xs font-bold uppercase tracking-wide text-gray-500">
                                    <tr>
                                        <th class="px-4 py-3">{{ __('Hora') }}</th>
                                        <th class="px-4 py-3">{{ __('Tipo') }}</th>
                                        <th class="px-4 py-3">{{ __('Estado') }}</th>
                                        <th class="px-4 py-3">{{ __('Duracion') }}</th>
                                        <th class="px-4 py-3">{{ __('Datos') }}</th>
                                        <th class="px-4 py-3">{{ __('Cuota') }}</th>
                                        <th class="px-4 py-3">{{ __('Error') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    @forelse ($recentLogs as $log)
                                        <tr>
                                            <td class="whitespace-nowrap px-4 py-3 text-gray-700">{{ $formatLogTime($log) }}</td>
                                            <td class="whitespace-nowrap px-4 py-3 font-semibold text-gray-950">{{ $log->sync_type }}</td>
                                            <td class="whitespace-nowrap px-4 py-3">
                                                <span class="{{ $statusClasses[$log->status] ?? $statusClasses['skipped'] }} rounded-full px-2.5 py-1 text-xs font-bold ring-1">
                                                    {{ $log->status }}
                                                </span>
                                            </td>
                                            <td class="whitespace-nowrap px-4 py-3 text-gray-700">{{ $log->duration_ms ?? 0 }}ms</td>
                                            <td class="whitespace-nowrap px-4 py-3 text-gray-700">
                                                {{ $log->items_received ?? 0 }}/{{ $log->items_created ?? 0 }}/{{ $log->items_updated ?? 0 }}/{{ $log->items_skipped ?? 0 }}
                                            </td>
                                            <td class="whitespace-nowrap px-4 py-3 text-gray-700">{{ $log->requests_remaining ?? $log->rate_limit_remaining ?? 'n/a' }}</td>
                                            <td class="max-w-xs truncate px-4 py-3 text-gray-600">{{ $log->error_message ?: 'n/a' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                                {{ __('Todavia no hay logs de sync.') }}
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <p class="mt-3 text-xs text-gray-500">{{ __('Datos: recibidos/creados/actualizados/omitidos.') }}</p>
                </article>

                <aside class="space-y-4">
                    <article class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-gray-100">
                        <h3 class="text-lg font-bold text-gray-950">{{ __('Fixtures por estado') }}</h3>
                        <div class="mt-4 space-y-2">
                            @forelse ($fixturesByStatus as $status => $total)
                                <div class="flex items-center justify-between rounded-lg bg-gray-50 px-3 py-2">
                                    <span class="font-semibold text-gray-700">{{ $status ?: __('Sin estado') }}</span>
                                    <span class="font-black text-gray-950">{{ $total }}</span>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500">{{ __('Sin fixtures API registrados.') }}</p>
                            @endforelse
                        </div>
                    </article>

                    <article class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-gray-100">
                        <h3 class="text-lg font-bold text-gray-950">{{ __('Comandos utiles') }}</h3>
                        <div class="mt-4 space-y-3">
                            <code class="block overflow-x-auto rounded-md bg-gray-950 px-3 py-2 text-xs font-semibold text-white">php artisan api-football:sync-teams --season=2026 --force</code>
                            <code class="block overflow-x-auto rounded-md bg-gray-950 px-3 py-2 text-xs font-semibold text-white">php artisan api-football:sync-fixtures --season=2026 --force</code>
                        </div>
                    </article>
                </aside>
            </section>
        </div>
    </div>
</x-app-layout>
