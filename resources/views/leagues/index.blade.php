<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Ligas') }}
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    {{ __('Compara la tabla de posiciones de la Liga general y de tus ligas de amigos.') }}
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
        <div class="mx-auto max-w-6xl space-y-6 px-4 sm:px-6 lg:px-8">
            <section class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-gray-100 sm:p-5">
                <div class="flex gap-2 overflow-x-auto pb-1" role="tablist" aria-label="{{ __('Ligas disponibles') }}">
                    <button
                        type="button"
                        data-league-tab="general"
                        class="league-tab-button whitespace-nowrap rounded-full bg-indigo-600 px-4 py-2 text-sm font-bold text-white shadow-sm transition"
                        aria-selected="true"
                    >
                        {{ __('Liga general') }}
                    </button>

                    @foreach ($privateLeagues as $privateLeague)
                        <button
                            type="button"
                            data-league-tab="league-{{ $privateLeague->id }}"
                            class="league-tab-button whitespace-nowrap rounded-full bg-gray-100 px-4 py-2 text-sm font-bold text-gray-700 transition hover:bg-gray-200"
                            aria-selected="false"
                        >
                            {{ $privateLeague->name }}
                        </button>
                    @endforeach
                </div>

                @if ($privateLeagues->isEmpty())
                    <div class="mt-5 rounded-xl border border-dashed border-indigo-200 bg-indigo-50/60 p-5">
                        <h3 class="text-base font-bold text-gray-950">
                            {{ __('Sumate a una liga privada') }}
                        </h3>
                        <p class="mt-2 text-sm text-gray-600">
                            {{ __('La liga general ya esta activa. Tambien podes crear una liga para tu grupo o buscar una existente.') }}
                        </p>
                        <div class="mt-4 flex flex-col gap-3 sm:flex-row">
                            <a
                                href="{{ route('private-leagues.create') }}"
                                class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                            >
                                {{ __('Crear liga') }}
                            </a>
                            <a
                                href="{{ route('private-leagues.search') }}"
                                class="inline-flex items-center justify-center rounded-md border border-indigo-200 bg-white px-4 py-2 text-sm font-semibold text-indigo-700 transition hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                            >
                                {{ __('Buscar liga') }}
                            </a>
                        </div>
                    </div>
                @endif
            </section>

            <section data-league-panel="general" class="league-tab-panel space-y-4">
                <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-sm font-bold uppercase tracking-wide text-indigo-700">
                            {{ __('Liga general') }}
                        </p>
                        <h3 class="text-2xl font-black text-gray-950">
                            {{ __('Tabla de posiciones') }}
                        </h3>
                    </div>
                    <p class="text-sm text-gray-500">
                        {{ __('Todos los usuarios con predicciones puntuadas') }}
                    </p>
                </div>

                @forelse ($globalLeaderboard as $entry)
                    <article @class([
                        'overflow-hidden rounded-2xl bg-white shadow-sm ring-1',
                        'ring-amber-300 shadow-amber-100' => $loop->first,
                        'ring-gray-100' => ! $loop->first,
                    ])>
                        <div @class([
                            'p-5',
                            'bg-amber-50' => $loop->first,
                            'bg-white' => ! $loop->first,
                        ])>
                            <div class="flex items-center justify-between gap-4">
                                <div class="flex min-w-0 items-center gap-4">
                                    <div @class([
                                        'flex h-12 w-12 shrink-0 items-center justify-center rounded-full text-lg font-black',
                                        'bg-amber-500 text-white' => $loop->first,
                                        'bg-gray-950 text-white' => ! $loop->first,
                                    ])>
                                        {{ $loop->iteration }}
                                    </div>
                                    <div class="min-w-0">
                                        <h4 class="truncate text-lg font-black text-gray-950">
                                            {{ '@'.$entry->username }}
                                        </h4>
                                        <p class="text-xs font-bold uppercase tracking-wide text-gray-500">
                                            {{ $loop->first ? __('Primer puesto') : __('Liga general') }}
                                        </p>
                                    </div>
                                </div>

                                <div class="text-right">
                                    <p class="text-3xl font-black text-gray-950">
                                        {{ (int) $entry->total_points }}
                                    </p>
                                    <p class="text-xs font-bold uppercase tracking-wide text-gray-500">
                                    {{ __('Puntos') }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <dl class="grid grid-cols-3 gap-2 border-t border-gray-100 p-4 text-center">
                            <div class="rounded-xl bg-emerald-50 px-2 py-3">
                                <dt class="text-[11px] font-bold uppercase tracking-wide text-emerald-700">
                                    {{ __('Resultados exactos') }}
                                </dt>
                                <dd class="mt-1 text-xl font-black text-emerald-950">
                                    {{ (int) $entry->exact_results_count }}
                                </dd>
                            </div>
                            <div class="rounded-xl bg-indigo-50 px-2 py-3">
                                <dt class="text-[11px] font-bold uppercase tracking-wide text-indigo-700">
                                    {{ __('Tendencias') }}
                                </dt>
                                <dd class="mt-1 text-xl font-black text-indigo-950">
                                    {{ (int) $entry->trend_count }}
                                </dd>
                            </div>
                            <div class="rounded-xl bg-gray-50 px-2 py-3">
                                <dt class="text-[11px] font-bold uppercase tracking-wide text-gray-500">
                                    {{ __('Predicciones puntuadas') }}
                                </dt>
                                <dd class="mt-1 text-xl font-black text-gray-950">
                                    {{ (int) $entry->scored_predictions_count }}
                                </dd>
                            </div>
                        </dl>
                    </article>
                @empty
                    <div class="rounded-2xl bg-white p-8 text-center shadow-sm ring-1 ring-gray-100">
                        <h3 class="text-lg font-bold text-gray-950">
                            {{ __('Todavia no hay tabla de posiciones') }}
                        </h3>
                        <p class="mt-2 text-sm text-gray-600">
                            {{ __('La tabla de posiciones se va a completar cuando haya predicciones puntuadas.') }}
                        </p>
                    </div>
                @endforelse
            </section>

            @foreach ($privateLeagues as $privateLeague)
                <section data-league-panel="league-{{ $privateLeague->id }}" class="league-tab-panel hidden space-y-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="text-sm font-bold uppercase tracking-wide text-indigo-700">
                                {{ __('Liga privada') }}
                            </p>
                            <h3 class="text-2xl font-black text-gray-950">
                                {{ $privateLeague->name }}
                            </h3>
                            <p class="mt-1 text-sm text-gray-500">
                                {{ __('Tabla de posiciones de la liga') }}
                            </p>
                        </div>
                        <a
                            href="{{ route('private-leagues.show', $privateLeague) }}"
                            class="inline-flex items-center justify-center rounded-md border border-indigo-200 bg-white px-4 py-2 text-sm font-semibold text-indigo-700 transition hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                        >
                            {{ __('Ver liga') }}
                        </a>
                    </div>

                    @foreach ($privateLeaderboards[$privateLeague->id] as $entry)
                        <article @class([
                            'rounded-2xl border p-4 shadow-sm',
                            'border-amber-200 bg-amber-50' => $loop->first,
                            'border-gray-100 bg-white' => ! $loop->first,
                        ])>
                            <div class="flex items-start gap-3">
                                <div @class([
                                    'flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-sm font-black',
                                    'bg-amber-500 text-white' => $loop->first,
                                    'bg-gray-950 text-white' => ! $loop->first,
                                ])>
                                    {{ $loop->iteration }}
                                </div>

                                <div class="min-w-0 flex-1">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <h4 class="truncate font-black text-gray-950">
                                                {{ '@'.$entry->username }}
                                            </h4>
                                            <p class="text-xs font-bold uppercase tracking-wide text-gray-500">
                                                {{ $loop->first ? __('Primer puesto') : __('Miembro activo') }}
                                            </p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-2xl font-black text-gray-950">
                                                {{ (int) $entry->total_points }}
                                            </p>
                                            <p class="text-xs font-bold uppercase tracking-wide text-gray-500">
                                                {{ __('Puntos') }}
                                            </p>
                                        </div>
                                    </div>

                                    <dl class="mt-4 grid grid-cols-3 gap-2 text-center">
                                        <div class="rounded-xl bg-white px-2 py-3 ring-1 ring-gray-100">
                                            <dt class="text-[11px] font-bold uppercase tracking-wide text-gray-500">
                                                {{ __('Resultados exactos') }}
                                            </dt>
                                            <dd class="mt-1 text-lg font-black text-gray-950">
                                                {{ (int) $entry->exact_results_count }}
                                            </dd>
                                        </div>
                                        <div class="rounded-xl bg-white px-2 py-3 ring-1 ring-gray-100">
                                            <dt class="text-[11px] font-bold uppercase tracking-wide text-gray-500">
                                                {{ __('Tendencias') }}
                                            </dt>
                                            <dd class="mt-1 text-lg font-black text-gray-950">
                                                {{ (int) $entry->trend_count }}
                                            </dd>
                                        </div>
                                        <div class="rounded-xl bg-white px-2 py-3 ring-1 ring-gray-100">
                                            <dt class="text-[11px] font-bold uppercase tracking-wide text-gray-500">
                                                {{ __('Predicciones puntuadas') }}
                                            </dt>
                                            <dd class="mt-1 text-lg font-black text-gray-950">
                                                {{ (int) $entry->scored_predictions_count }}
                                            </dd>
                                        </div>
                                    </dl>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </section>
            @endforeach
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const buttons = document.querySelectorAll('[data-league-tab]');
            const panels = document.querySelectorAll('[data-league-panel]');

            buttons.forEach((button) => {
                button.addEventListener('click', () => {
                    const activePanel = button.dataset.leagueTab;

                    buttons.forEach((currentButton) => {
                        const isActive = currentButton === button;
                        currentButton.setAttribute('aria-selected', isActive ? 'true' : 'false');
                        currentButton.classList.toggle('bg-indigo-600', isActive);
                        currentButton.classList.toggle('text-white', isActive);
                        currentButton.classList.toggle('shadow-sm', isActive);
                        currentButton.classList.toggle('bg-gray-100', ! isActive);
                        currentButton.classList.toggle('text-gray-700', ! isActive);
                        currentButton.classList.toggle('hover:bg-gray-200', ! isActive);
                    });

                    panels.forEach((panel) => {
                        panel.classList.toggle('hidden', panel.dataset.leaguePanel !== activePanel);
                    });
                });
            });
        });
    </script>
</x-app-layout>
