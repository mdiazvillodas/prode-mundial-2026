<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Administracion') }}
            </h2>

            <a
                href="{{ route('admin.matches.index') }}"
                class="inline-flex items-center justify-center rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
            >
                {{ __('Admin partidos') }}
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <section class="grid gap-4 sm:grid-cols-2">
                <article class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-gray-100">
                    <p class="text-sm font-medium uppercase tracking-wide text-gray-500">
                        {{ __('Entorno') }}
                    </p>
                    <p class="mt-2 text-2xl font-black text-gray-950">
                        {{ $appEnvironment }}
                    </p>
                </article>

                <article class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-gray-100">
                    <p class="text-sm font-medium uppercase tracking-wide text-gray-500">
                        {{ __('Modo') }}
                    </p>
                    <div class="mt-2 flex items-center gap-3">
                        <span class="{{ $appMode === 'live' ? 'bg-amber-100 text-amber-800' : 'bg-indigo-100 text-indigo-800' }} rounded-full px-3 py-1 text-sm font-bold uppercase tracking-wide">
                            {{ $appMode === 'live' ? __('Modo live') : __('Modo prueba') }}
                        </span>
                        <span class="text-sm text-gray-500">
                            {{ $appMode }}
                        </span>
                    </div>
                </article>
            </section>

            <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <article class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-gray-100">
                    <p class="text-sm font-medium text-gray-500">{{ __('Usuarios') }}</p>
                    <p class="mt-2 text-3xl font-black text-gray-950">{{ $counts['users'] }}</p>
                </article>

                <article class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-gray-100">
                    <p class="text-sm font-medium text-gray-500">{{ __('Partidos') }}</p>
                    <p class="mt-2 text-3xl font-black text-gray-950">{{ $counts['matches'] }}</p>
                </article>

                <article class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-gray-100">
                    <p class="text-sm font-medium text-gray-500">{{ __('Predicciones') }}</p>
                    <p class="mt-2 text-3xl font-black text-gray-950">{{ $counts['predictions'] }}</p>
                </article>

                <article class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-gray-100">
                    <p class="text-sm font-medium text-gray-500">{{ __('Ligas privadas') }}</p>
                    <p class="mt-2 text-3xl font-black text-gray-950">{{ $counts['privateLeagues'] }}</p>
                </article>
            </section>

            <section class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-100">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-medium uppercase tracking-wide text-emerald-700">
                            {{ __('Accesos operativos') }}
                        </p>
                        <h3 class="mt-1 text-lg font-semibold text-gray-900">
                            {{ __('Herramientas disponibles') }}
                        </h3>
                    </div>
                </div>

                <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <a
                        href="{{ route('admin.matches.index') }}"
                        class="rounded-lg border border-emerald-100 bg-emerald-50 p-4 transition hover:bg-emerald-100 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
                    >
                        <p class="font-bold text-emerald-950">{{ __('Admin partidos') }}</p>
                        <p class="mt-1 text-sm text-emerald-800">{{ __('Resultados manuales y estado de partidos') }}</p>
                    </a>

                    <a
                        href="{{ route('admin.api-health') }}"
                        class="rounded-lg border border-sky-100 bg-sky-50 p-4 transition hover:bg-sky-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2"
                    >
                        <p class="font-bold text-sky-950">{{ __('Estado API-Football') }}</p>
                        <p class="mt-1 text-sm text-sky-800">{{ __('Salud de sync y logs recientes') }}</p>
                    </a>

                    <a
                        href="{{ route('admin.users.index') }}"
                        class="rounded-lg border border-blue-100 bg-blue-50 p-4 transition hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                    >
                        <p class="font-bold text-blue-950">{{ __('Usuarios / Emails') }}</p>
                        <p class="mt-1 text-sm text-blue-800">{{ __('Verificación manual de email') }}</p>
                    </a>

                    <a
                        href="{{ route('leaderboard.index') }}"
                        class="rounded-lg border border-amber-100 bg-amber-50 p-4 transition hover:bg-amber-100 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2"
                    >
                        <p class="font-bold text-amber-950">{{ __('Liga general') }}</p>
                        <p class="mt-1 text-sm text-amber-800">{{ __('Puntos y posiciones globales') }}</p>
                    </a>

                    <a
                        href="{{ route('predictions.index') }}"
                        class="rounded-lg border border-indigo-100 bg-indigo-50 p-4 transition hover:bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        <p class="font-bold text-indigo-950">{{ __('Predicciones') }}</p>
                        <p class="mt-1 text-sm text-indigo-800">{{ __('Carga inline de predicciones') }}</p>
                    </a>

                    <div class="rounded-lg border border-dashed border-gray-300 bg-gray-50 p-4">
                        <p class="font-bold text-gray-950">{{ __('Ligas privadas') }}</p>
                        <p class="mt-1 text-sm text-gray-600">{{ __('La vista admin de ligas queda para un ticket futuro.') }}</p>
                    </div>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
