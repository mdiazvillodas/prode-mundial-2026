<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Prediccion') }}
            </h2>
            <p class="text-sm text-gray-500">
                {{ __('Carga tu marcador antes del cierre de predicciones.') }}
            </p>
            <a href="{{ route('matches.index') }}" class="mt-2 text-sm font-medium text-indigo-600 hover:text-indigo-800">
                {{ __('Volver a partidos') }}
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white border border-gray-200 shadow-sm sm:rounded-lg">
                <div class="p-4 sm:p-6">
                    <div class="flex flex-col gap-2 text-sm text-gray-500">
                        <span>{{ $tournamentMatch->starts_at ? $tournamentMatch->starts_at->format('d/m/Y H:i') : __('Fecha por definir') }}</span>

                        @if ($tournamentMatch->stage)
                            <span>{{ __('Fase') }}: {{ str_replace('_', ' ', $tournamentMatch->stage) }}</span>
                        @endif

                        @if ($tournamentMatch->group)
                            <span>{{ __('Grupo') }} {{ $tournamentMatch->group }}</span>
                        @endif

                        @if ($tournamentMatch->predictionClosesAt())
                            <span>{{ __('Cierre') }}: {{ $tournamentMatch->predictionClosesAt()->format('d/m/Y H:i') }}</span>
                        @endif
                    </div>

                    <div class="mt-5 grid grid-cols-[1fr_auto_1fr] items-center gap-3">
                        <div class="min-w-0">
                            <p class="truncate text-lg font-semibold text-gray-900">{{ $tournamentMatch->teamA->name }}</p>
                            @if ($tournamentMatch->teamA->short_name)
                                <p class="text-xs uppercase tracking-wide text-gray-500">{{ $tournamentMatch->teamA->short_name }}</p>
                            @endif
                        </div>

                        <span class="text-sm font-semibold text-gray-500">{{ __('vs') }}</span>

                        <div class="min-w-0 text-right">
                            <p class="truncate text-lg font-semibold text-gray-900">{{ $tournamentMatch->teamB->name }}</p>
                            @if ($tournamentMatch->teamB->short_name)
                                <p class="text-xs uppercase tracking-wide text-gray-500">{{ $tournamentMatch->teamB->short_name }}</p>
                            @endif
                        </div>
                    </div>

                    <form method="POST" action="{{ route('predictions.store', $tournamentMatch) }}" class="mt-6 space-y-5">
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
                                    class="mt-1 block w-full"
                                    :value="old('team_a_score', $prediction?->team_a_score)"
                                    required
                                />
                                <x-input-error :messages="$errors->get('team_a_score')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="team_b_score" :value="$tournamentMatch->teamB->short_name ?: $tournamentMatch->teamB->name" />
                                <x-text-input
                                    id="team_b_score"
                                    name="team_b_score"
                                    type="number"
                                    min="0"
                                    max="99"
                                    inputmode="numeric"
                                    class="mt-1 block w-full"
                                    :value="old('team_b_score', $prediction?->team_b_score)"
                                    required
                                />
                                <x-input-error :messages="$errors->get('team_b_score')" class="mt-2" />
                            </div>
                        </div>

                        @if ($tournamentMatch->requiresQualifiedTeamPrediction())
                            <div>
                                <x-input-label for="predicted_qualified_team_id" :value="__('Equipo clasificado')" />
                                <select
                                    id="predicted_qualified_team_id"
                                    name="predicted_qualified_team_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-white text-base shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    required
                                >
                                    <option value="">{{ __('Selecciona el equipo clasificado') }}</option>
                                    <option value="{{ $tournamentMatch->team_a_id }}" {{ old('predicted_qualified_team_id', $prediction?->predicted_qualified_team_id) == $tournamentMatch->team_a_id ? 'selected' : '' }}>
                                        {{ $tournamentMatch->teamA->name }}
                                    </option>
                                    <option value="{{ $tournamentMatch->team_b_id }}" {{ old('predicted_qualified_team_id', $prediction?->predicted_qualified_team_id) == $tournamentMatch->team_b_id ? 'selected' : '' }}>
                                        {{ $tournamentMatch->teamB->name }}
                                    </option>
                                </select>
                                <x-input-error :messages="$errors->get('predicted_qualified_team_id')" class="mt-2" />
                            </div>
                        @endif

                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <p class="text-sm text-gray-500">
                                {{ $prediction ? __('Ya tenes una prediccion cargada para este partido.') : __('Tu prediccion se puede editar hasta el cierre.') }}
                            </p>

                            <x-primary-button>
                                {{ __('Guardar prediccion') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
