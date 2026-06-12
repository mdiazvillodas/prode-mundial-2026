@props([
    'match',
    'name',
    'idPrefix',
    'selectedId' => null,
    'error' => null,
])

@php
    $options = [
        [$match->team_a_id, $match->teamA?->name ?? __('Equipo A'), $match->teamA],
        [$match->team_b_id, $match->teamB?->name ?? __('Equipo B'), $match->teamB],
    ];
@endphp

<div
    class="mt-5 rounded-2xl border border-blue-100 bg-blue-50/70 p-4"
    data-qualified-selector
    data-team-a="{{ $match->team_a_id }}"
    data-team-b="{{ $match->team_b_id }}"
>
    <p class="text-sm font-semibold text-blue-900">
        {{ __("En eliminatorias, ingresá el resultado final jugado. Si hay alargue, cuenta el resultado al final de los 120'. Si pronosticás empate, elegí quién pasa por penales.") }}
    </p>

    <p class="mt-3 text-sm font-black text-blue-950">
        {{ __('¿Quién clasifica?') }}
    </p>

    <div class="mt-3 grid grid-cols-2 gap-3">
        @foreach ($options as [$teamId, $teamName, $team])
            <label class="cursor-pointer">
                <input
                    type="radio"
                    name="{{ $name }}"
                    id="{{ $idPrefix }}-qualified-{{ $teamId }}"
                    value="{{ $teamId }}"
                    class="peer sr-only"
                    data-qualified-radio
                    data-prediction-input
                    @checked((string) $selectedId === (string) $teamId)
                >
                <span class="flex items-center gap-2 rounded-2xl border-2 border-blue-100 bg-white px-3 py-3 text-sm font-bold text-blue-950 shadow-sm transition peer-checked:border-blue-600 peer-checked:bg-blue-600 peer-checked:text-white peer-focus-visible:ring-4 peer-focus-visible:ring-blue-100">
                    <x-team-flag :team="$team" size="sm" />
                    <span class="truncate">{{ $teamName }}</span>
                </span>
            </label>
        @endforeach
    </div>

    <p class="mt-2 text-xs font-medium text-blue-700" data-qualified-auto hidden>
        {{ __('Clasifica automáticamente según el resultado que cargaste.') }}
    </p>
    <p class="mt-2 text-xs font-medium text-blue-700" data-qualified-draw hidden>
        {{ __('Empate: elegí quién pasa por penales.') }}
    </p>

    @if ($error)
        <p class="mt-2 text-xs font-semibold text-red-600">{{ $error }}</p>
    @endif
</div>
