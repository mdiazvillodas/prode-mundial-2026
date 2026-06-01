<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-4">
                    <p>{{ __('Ya estas dentro.') }}</p>

                    <div class="flex flex-col gap-3 sm:flex-row">
                        @php
                            $ownedPrivateLeague = auth()->user()->ownedPrivateLeague;
                        @endphp

                        @if (auth()->user()->isAdmin())
                            <a
                                href="{{ route('admin.matches.index') }}"
                                class="inline-flex items-center justify-center rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
                            >
                                {{ __('Admin partidos') }}
                            </a>
                        @endif

                        <a
                            href="{{ route('predictions.index') }}"
                            class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                        >
                            {{ __('Cargar predicciones') }}
                        </a>

                        <a
                            href="{{ route('predictions.history') }}"
                            class="inline-flex items-center justify-center rounded-md border border-indigo-200 bg-indigo-50 px-4 py-2 text-sm font-semibold text-indigo-800 transition hover:bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                        >
                            {{ __('Mis predicciones') }}
                        </a>

                        <a
                            href="{{ route('leaderboard.index') }}"
                            class="inline-flex items-center justify-center rounded-md bg-amber-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2"
                        >
                            {{ __('Ver ranking') }}
                        </a>

                        <a
                            href="{{ $ownedPrivateLeague ? route('private-leagues.show', $ownedPrivateLeague) : route('private-leagues.create') }}"
                            class="inline-flex items-center justify-center rounded-md border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-800 transition hover:bg-emerald-100 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
                        >
                            {{ $ownedPrivateLeague ? __('Mi liga') : __('Crear liga') }}
                        </a>

                        <a
                            href="{{ route('matches.index') }}"
                            class="inline-flex items-center justify-center rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                        >
                            {{ __('Ver proximos partidos') }}
                        </a>

                        <a
                            href="{{ route('calendar.index') }}"
                            class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-800 transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                        >
                            {{ __('Ver calendario') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
