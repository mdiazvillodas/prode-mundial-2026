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
                            <div class="flex items-center gap-3">
                                <x-team-flag :team="$tournamentMatch->teamA" />
                                <div class="min-w-0">
                                    <p class="truncate text-lg font-semibold text-gray-900">
                                        {{ $tournamentMatch->teamA?->name ?? __('Equipo por definir') }}
                                    </p>
                                    @if ($tournamentMatch->teamA?->short_name)
                                        <p class="text-xs uppercase tracking-wide text-gray-500">{{ $tournamentMatch->teamA->short_name }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <span class="text-sm font-semibold text-gray-500">{{ __('vs') }}</span>

                        <div class="min-w-0 text-right">
                            <div class="flex items-center justify-end gap-3">
                                <div class="min-w-0">
                                    <p class="truncate text-lg font-semibold text-gray-900">
                                        {{ $tournamentMatch->teamB?->name ?? __('Equipo por definir') }}
                                    </p>
                                    @if ($tournamentMatch->teamB?->short_name)
                                        <p class="text-xs uppercase tracking-wide text-gray-500">{{ $tournamentMatch->teamB->short_name }}</p>
                                    @endif
                                </div>
                                <x-team-flag :team="$tournamentMatch->teamB" />
                            </div>
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

                            @if ($tournamentMatch->requiresQualifiedTeamPrediction())
                                <div class="rounded-md bg-slate-50 p-4">
                                    <x-input-label for="winner_team_id" :value="__('Clasificado si el resultado queda empatado')" />
                                    <select
                                        id="winner_team_id"
                                        name="winner_team_id"
                                        class="mt-1 block w-full rounded-md border-gray-300 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        <option value="">{{ __('Elegir equipo clasificado') }}</option>
                                        <option value="{{ $tournamentMatch->team_a_id }}" @selected((string) old('winner_team_id', $tournamentMatch->winner_team_id) === (string) $tournamentMatch->team_a_id)>
                                            {{ $tournamentMatch->teamA->name }}
                                        </option>
                                        <option value="{{ $tournamentMatch->team_b_id }}" @selected((string) old('winner_team_id', $tournamentMatch->winner_team_id) === (string) $tournamentMatch->team_b_id)>
                                            {{ $tournamentMatch->teamB->name }}
                                        </option>
                                    </select>
                                    <x-input-error :messages="$errors->get('winner_team_id')" class="mt-2" />
                                </div>
                            @endif

                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <p class="text-sm text-gray-500">
                                    {{ __('Guardar este resultado marcara el partido como terminado y recalculara los puntos de sus predicciones.') }}
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
