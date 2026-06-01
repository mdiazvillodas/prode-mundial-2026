<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Asignar equipos') }}
            </h2>
            <p class="text-sm text-gray-500">
                {{ __('Define los equipos de un partido placeholder para que pueda convertirse en un fixture predecible.') }}
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
                    <div class="rounded-md bg-sky-50 p-4 text-sm text-sky-900">
                        {{ __('Asigna los dos equipos del partido. El partido cambiara de placeholder a programado cuando ambos equipos esten definidos.') }}
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

                    <form method="POST" action="{{ route('admin.matches.teams.update', $tournamentMatch) }}" class="mt-6 space-y-6">
                        @csrf

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <x-input-label for="team_a_id" :value="__('Equipo A')" />
                                <select
                                    id="team_a_id"
                                    name="team_a_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-white py-2 px-3 text-base shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                                    required
                                >
                                    <option value="">{{ __('Selecciona un equipo') }}</option>
                                    @foreach ($teams as $team)
                                        <option value="{{ $team->id }}" @selected(old('team_a_id', $tournamentMatch->team_a_id) == $team->id)>
                                            {{ $team->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('team_a_id')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="team_b_id" :value="__('Equipo B')" />
                                <select
                                    id="team_b_id"
                                    name="team_b_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-white py-2 px-3 text-base shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                                    required
                                >
                                    <option value="">{{ __('Selecciona un equipo') }}</option>
                                    @foreach ($teams as $team)
                                        <option value="{{ $team->id }}" @selected(old('team_b_id', $tournamentMatch->team_b_id) == $team->id)>
                                            {{ $team->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('team_b_id')" class="mt-2" />
                            </div>
                        </div>

                        <div class="rounded-md bg-gray-50 p-4 text-sm text-gray-700">
                            {{ __('No modifiques los goles ni el ganador. Esta acción solo define los equipos del partido.') }}
                        </div>

                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <p class="text-sm text-gray-500">
                                {{ __('El partido seguira usando la misma fecha y el mismo cierre de predicciones.') }}
                            </p>

                            <x-primary-button>
                                {{ __('Guardar equipos') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
