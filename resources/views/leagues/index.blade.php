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

                <x-ranking-table
                    :entries="$globalLeaderboard"
                    :context-label="__('Liga general')"
                />
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

                    <x-ranking-table
                        :entries="$privateLeaderboards[$privateLeague->id]"
                        :context-label="__('Miembro activo')"
                    />
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
