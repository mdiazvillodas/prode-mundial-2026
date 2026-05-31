<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Resultado del partido') }}
            </h2>
            <p class="text-sm text-gray-500">
                {{ __('Carga manual para desarrollo, correccion o fallback si la API falla o se demora.') }}
            </p>
            <a href="{{ route('admin.matches.index') }}" class="mt-2 text-sm font-medium text-indigo-600 hover:text-indigo-800">
                {{ __('Volver a partidos admin') }}
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            @if ($errors->has('result'))
                <div class="mb-4 rounded-md bg-red-50 p-4 text-sm font-medium text-red-700">
                    {{ $errors->first('result') }}
                </div>
            @endif

            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="p-4 sm:p-6">
                    <div class="rounded-md bg-amber-50 p-4 text-sm text-amber-900">
                        {{ __('Esta herramienta es un fallback manual. La fuente principal esperada para resultados reales sera una integracion con API externa en un ticket posterior.') }}
                    </div>

                    <div class="mt-6 flex flex-col gap-2 text-sm text-gray-500">
                        <span>{{ $tournamentMatch->starts_at ? $tournamentMatch->starts_at->format('d/m/Y H:i') : __('Fecha por definir') }}</span>

                        @if ($tournamentMatch->stage)
                            <span>{{ __('Fase') }}: {{ str_replace('_', ' ', $tournamentMatch->stage) }}</span>
                        @endif

                        @if ($tournamentMatch->group)
                            <span>{{ __('Grupo') }} {{ $tournamentMatch->group }}</span>
                        @endif
                    </div>

                    <div class="mt-5 grid grid-cols-[1fr_auto_1fr] items-center gap-3">
                        <div class="min-w-0">
                            <p class="truncate text-lg font-semibold text-gray-900">
                                {{ $tournamentMatch->teamA?->name ?? __('Equipo por definir') }}
                            </p>
                            @if ($tournamentMatch->teamA?->country_code)
                                <p class="text-xs uppercase tracking-wide text-gray-500">{{ $tournamentMatch->teamA->country_code }}</p>
                            @endif
                        </div>

                        <span class="text-sm font-semibold text-gray-500">{{ __('vs') }}</span>

                        <div class="min-w-0 text-right">
                            <p class="truncate text-lg font-semibold text-gray-900">
                                {{ $tournamentMatch->teamB?->name ?? __('Equipo por definir') }}
                            </p>
                            @if ($tournamentMatch->teamB?->country_code)
                                <p class="text-xs uppercase tracking-wide text-gray-500">{{ $tournamentMatch->teamB->country_code }}</p>
                            @endif
                        </div>
                    </div>

                    @unless ($canLoadResult)
                        <div class="mt-6 rounded-md bg-gray-50 p-4 text-sm text-gray-700">
                            {{ __('Este partido todavia no tiene los dos equipos definidos. No se puede cargar resultado manualmente hasta completar los equipos.') }}
                        </div>
                    @else
                        <form method="POST" action="{{ route('admin.matches.result.update', $tournamentMatch) }}" class="mt-6 space-y-5">
                            @csrf

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="team_a_score" :value="$tournamentMatch->teamA->short_name ?: $tournamentMatch->teamA->name" />
                                    <x-text-input
                                        id="team_a_score"
                                        name="team_a_score"
                                        type="number"
                                        min="0"
                                        max="99"
                                        inputmode="numeric"
                                        class="mt-1 block w-full text-center font-semibold"
                                        :value="old('team_a_score', $tournamentMatch->team_a_score)"
                                        required
                                    />
                                    <x-input-error :messages="$errors->get('team_a_score')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="team_b_score" :value="$tournamentMatch->teamB->short_name ?: $tournamentMatch->teamB->name" class="text-right" />
                                    <x-text-input
                                        id="team_b_score"
                                        name="team_b_score"
                                        type="number"
                                        min="0"
                                        max="99"
                                        inputmode="numeric"
                                        class="mt-1 block w-full text-center font-semibold"
                                        :value="old('team_b_score', $tournamentMatch->team_b_score)"
                                        required
                                    />
                                    <x-input-error :messages="$errors->get('team_b_score')" class="mt-2" />
                                </div>
                            </div>

                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <p class="text-sm text-gray-500">
                                    {{ __('Guardar este resultado marcara el partido como terminado. No se aplicara scoring en este ticket.') }}
                                </p>

                                <x-primary-button>
                                    {{ __('Guardar resultado') }}
                                </x-primary-button>
                            </div>
                        </form>
                    @endunless
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
