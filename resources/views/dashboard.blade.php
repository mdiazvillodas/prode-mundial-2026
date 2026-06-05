<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2">
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-blue-600">
                {{ __('Prode Mundial 2026') }}
            </p>
            <h2 class="text-2xl font-black leading-tight text-blue-950">
                {{ __('Inicio') }}
            </h2>
            <p class="text-sm text-slate-500">
                {{ __('Tu resumen para seguir jugando la Liga general y tus ligas de amigos.') }}
            </p>
        </div>
    </x-slot>

    <div class="min-h-screen bg-gradient-to-b from-sky-50 via-white to-blue-50/70 py-6">
        <div class="mx-auto max-w-6xl space-y-6 px-4 sm:px-6 lg:px-8">
            <section class="overflow-hidden rounded-[1.75rem] bg-blue-950 text-white shadow-xl shadow-blue-950/15">
                <div class="grid gap-6 p-6 sm:p-8 lg:grid-cols-[1.3fr_0.7fr] lg:items-center">
                    <div>
                        <p class="text-sm font-bold uppercase tracking-[0.18em] text-emerald-300">
                            {{ __('Panel personal') }}
                        </p>
                        <h3 class="mt-3 text-3xl font-black leading-tight sm:text-4xl">
                            {{ __('Hola, :name', ['name' => auth()->user()->name]) }}
                        </h3>
                        <p class="mt-3 max-w-2xl text-sm font-medium leading-6 text-blue-100 sm:text-base">
                            {{ __('Revisa tus puntos, completa predicciones abiertas y entra a tus ligas desde un solo lugar.') }}
                        </p>

                        <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                            <a
                                href="{{ route('predictions.index') }}"
                                class="inline-flex items-center justify-center rounded-2xl bg-emerald-500 px-5 py-3 text-sm font-black text-white shadow-lg shadow-emerald-950/20 transition hover:bg-emerald-400 focus:outline-none focus:ring-4 focus:ring-emerald-200"
                            >
                                {{ __('Cargar predicciones') }}
                            </a>
                            <a
                                href="{{ route('leagues.index') }}"
                                class="inline-flex items-center justify-center rounded-2xl border border-white/20 bg-white/10 px-5 py-3 text-sm font-black text-white transition hover:bg-white/15 focus:outline-none focus:ring-4 focus:ring-blue-200"
                            >
                                {{ __('Ver ligas') }}
                            </a>
                        </div>
                    </div>

                    <div class="rounded-[1.5rem] bg-white/10 p-5 ring-1 ring-white/15">
                        <p class="text-sm font-bold uppercase tracking-wide text-blue-100">
                            {{ __('Liga general') }}
                        </p>
                        <div class="mt-4 grid grid-cols-2 gap-3">
                            <div>
                                <p class="text-4xl font-black">{{ (int) $totalPoints }}</p>
                                <p class="mt-1 text-xs font-bold uppercase tracking-wide text-blue-100">
                                    {{ __('Puntos') }}
                                </p>
                            </div>
                            <div>
                                <p class="text-4xl font-black">
                                    {{ $currentUserPosition ? '#'.$currentUserPosition : '-' }}
                                </p>
                                <p class="mt-1 text-xs font-bold uppercase tracking-wide text-blue-100">
                                    {{ __('Posicion') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="grid gap-4 sm:grid-cols-3">
                <article class="rounded-2xl bg-white p-5 shadow-sm shadow-blue-900/5 ring-1 ring-blue-100">
                    <p class="text-sm font-bold uppercase tracking-wide text-emerald-700">
                        {{ __('Pendientes') }}
                    </p>
                    <p class="mt-3 text-3xl font-black text-blue-950">
                        {{ $openPredictionsCount }}
                    </p>
                    <p class="mt-1 text-sm text-slate-500">
                        {{ __('Predicciones abiertas por completar') }}
                    </p>
                </article>

                <article class="rounded-2xl bg-white p-5 shadow-sm shadow-blue-900/5 ring-1 ring-blue-100">
                    <p class="text-sm font-bold uppercase tracking-wide text-blue-700">
                        {{ __('Historial') }}
                    </p>
                    <p class="mt-3 text-3xl font-black text-blue-950">
                        {{ (int) $scoredPredictionsCount }}
                    </p>
                    <p class="mt-1 text-sm text-slate-500">
                        {{ __('Predicciones puntuadas') }}
                    </p>
                </article>

                <article class="rounded-2xl bg-white p-5 shadow-sm shadow-blue-900/5 ring-1 ring-blue-100">
                    <p class="text-sm font-bold uppercase tracking-wide text-indigo-700">
                        {{ __('Ligas privadas') }}
                    </p>
                    <p class="mt-3 text-3xl font-black text-blue-950">
                        {{ $activePrivateLeagues->count() }}
                    </p>
                    <p class="mt-1 text-sm text-slate-500">
                        {{ __('Ligas de amigos activas') }}
                    </p>
                </article>
            </section>

            <section class="grid gap-4 lg:grid-cols-[1fr_1fr]">
                <div class="rounded-2xl bg-white p-5 shadow-sm shadow-blue-900/5 ring-1 ring-blue-100 sm:p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-bold uppercase tracking-wide text-blue-700">
                                {{ __('Siguiente paso') }}
                            </p>
                            <h3 class="mt-1 text-xl font-black text-blue-950">
                                {{ __('Completa tus pronosticos') }}
                            </h3>
                        </div>
                        <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-black text-emerald-800">
                            {{ __('Abierto') }}
                        </span>
                    </div>

                    <p class="mt-3 text-sm leading-6 text-slate-600">
                        {{ __('En Predicciones ves todos los partidos, cargas marcadores y guardas varios cambios juntos.') }}
                    </p>

                    <div class="mt-5 flex flex-col gap-3 sm:flex-row">
                        <a
                            href="{{ route('predictions.index') }}"
                            class="inline-flex items-center justify-center rounded-2xl bg-blue-700 px-5 py-3 text-sm font-black text-white shadow-lg shadow-blue-700/20 transition hover:bg-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-200"
                        >
                            {{ __('Ir a predicciones') }}
                        </a>
                        <a
                            href="{{ route('predictions.history') }}"
                            class="inline-flex items-center justify-center rounded-2xl border border-blue-100 bg-blue-50 px-5 py-3 text-sm font-black text-blue-800 transition hover:bg-blue-100 focus:outline-none focus:ring-4 focus:ring-blue-100"
                        >
                            {{ __('Ver historial') }}
                        </a>
                    </div>
                </div>

                <div class="rounded-2xl bg-white p-5 shadow-sm shadow-blue-900/5 ring-1 ring-blue-100 sm:p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-bold uppercase tracking-wide text-indigo-700">
                                {{ __('Ligas') }}
                            </p>
                            <h3 class="mt-1 text-xl font-black text-blue-950">
                                {{ __('Tabla de posiciones') }}
                            </h3>
                        </div>
                        <a href="{{ route('leagues.index') }}" class="text-sm font-black text-blue-700 hover:text-blue-600">
                            {{ __('Ver ligas') }}
                        </a>
                    </div>

                    @if ($activePrivateLeagues->isEmpty())
                        <p class="mt-3 text-sm leading-6 text-slate-600">
                            {{ __('Ya participas en la Liga general. Tambien puedes crear o buscar una liga de amigos.') }}
                        </p>
                    @else
                        <div class="mt-4 space-y-2">
                            @foreach ($activePrivateLeagues as $privateLeague)
                                <a
                                    href="{{ route('private-leagues.show', $privateLeague) }}"
                                    class="flex items-center justify-between rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3 transition hover:bg-blue-50"
                                >
                                    <span class="font-black text-blue-950">{{ $privateLeague->name }}</span>
                                    <span class="text-xs font-bold uppercase tracking-wide text-slate-500">{{ __('Liga privada') }}</span>
                                </a>
                            @endforeach
                        </div>
                    @endif

                    <div class="mt-5 flex flex-col gap-3 sm:flex-row">
                        <a
                            href="{{ route('private-leagues.create') }}"
                            class="inline-flex items-center justify-center rounded-2xl border border-emerald-100 bg-emerald-50 px-5 py-3 text-sm font-black text-emerald-800 transition hover:bg-emerald-100 focus:outline-none focus:ring-4 focus:ring-emerald-100"
                        >
                            {{ __('Crear liga') }}
                        </a>
                        <a
                            href="{{ route('private-leagues.search') }}"
                            class="inline-flex items-center justify-center rounded-2xl border border-blue-100 bg-white px-5 py-3 text-sm font-black text-blue-800 transition hover:bg-blue-50 focus:outline-none focus:ring-4 focus:ring-blue-100"
                        >
                            {{ __('Buscar liga') }}
                        </a>
                    </div>
                </div>
            </section>

            <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <a href="{{ route('calendar.index') }}" class="rounded-2xl bg-white p-5 shadow-sm shadow-blue-900/5 ring-1 ring-blue-100 transition hover:-translate-y-0.5 hover:shadow-lg hover:shadow-blue-900/10">
                    <p class="text-sm font-bold uppercase tracking-wide text-blue-700">{{ __('Calendario') }}</p>
                    <p class="mt-2 text-lg font-black text-blue-950">{{ __('Agenda por equipo') }}</p>
                    <p class="mt-2 text-sm text-slate-500">{{ __('Consulta los partidos conocidos de una seleccion.') }}</p>
                </a>

                <a href="{{ route('predictions.history') }}" class="rounded-2xl bg-white p-5 shadow-sm shadow-blue-900/5 ring-1 ring-blue-100 transition hover:-translate-y-0.5 hover:shadow-lg hover:shadow-blue-900/10">
                    <p class="text-sm font-bold uppercase tracking-wide text-blue-700">{{ __('Historial') }}</p>
                    <p class="mt-2 text-lg font-black text-blue-950">{{ __('Tus predicciones') }}</p>
                    <p class="mt-2 text-sm text-slate-500">{{ __('Revisa resultados y puntos obtenidos.') }}</p>
                </a>

                <a href="{{ route('leagues.index') }}" class="rounded-2xl bg-white p-5 shadow-sm shadow-blue-900/5 ring-1 ring-blue-100 transition hover:-translate-y-0.5 hover:shadow-lg hover:shadow-blue-900/10">
                    <p class="text-sm font-bold uppercase tracking-wide text-blue-700">{{ __('Ligas') }}</p>
                    <p class="mt-2 text-lg font-black text-blue-950">{{ __('Liga general') }}</p>
                    <p class="mt-2 text-sm text-slate-500">{{ __('Mira posiciones globales y ligas privadas.') }}</p>
                </a>

                @if (auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="rounded-2xl bg-emerald-50 p-5 shadow-sm shadow-emerald-900/5 ring-1 ring-emerald-100 transition hover:-translate-y-0.5 hover:shadow-lg hover:shadow-emerald-900/10">
                        <p class="text-sm font-bold uppercase tracking-wide text-emerald-700">{{ __('Administracion') }}</p>
                        <p class="mt-2 text-lg font-black text-emerald-950">{{ __('Panel admin') }}</p>
                        <p class="mt-2 text-sm text-emerald-800">{{ __('Resultados manuales y control operativo.') }}</p>
                    </a>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>
