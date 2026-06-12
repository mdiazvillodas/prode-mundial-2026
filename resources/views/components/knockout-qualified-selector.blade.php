@props([
    'match',
    'name',
    'idPrefix',
    'selectedId' => null,
    'error' => null,
    'teamAScore' => null,
    'teamBScore' => null,
])

@php
    $options = [
        [$match->team_a_id, $match->teamA?->name ?? __('Equipo A'), $match->teamA],
        [$match->team_b_id, $match->teamB?->name ?? __('Equipo B'), $match->teamB],
    ];

    $hasBoth = is_numeric($teamAScore) && is_numeric($teamBScore);
    $isDraw = $hasBoth && (int) $teamAScore === (int) $teamBScore;
    $isNonDraw = $hasBoth && ! $isDraw;

    $inferredId = $isNonDraw
        ? ((int) $teamAScore > (int) $teamBScore ? $match->team_a_id : $match->team_b_id)
        : null;
    $inferredName = $inferredId === $match->team_a_id
        ? ($match->teamA?->name ?? __('Equipo A'))
        : ($inferredId === $match->team_b_id ? ($match->teamB?->name ?? __('Equipo B')) : '');

    // Non-draw qualification is inferred from the score, so pre-check the winner
    // to keep the submitted data consistent. The buttons are only selectable on a draw.
    $checkedId = $isDraw ? $selectedId : ($isNonDraw ? $inferredId : null);

    $state = $hasBoth ? ($isDraw ? 'draw' : 'auto') : 'empty';
@endphp

<div
    class="mt-4 rounded-2xl border border-blue-100 bg-blue-50/70 p-3"
    data-qualified-selector
    data-qualified-state="{{ $state }}"
    data-team-a="{{ $match->team_a_id }}"
    data-team-b="{{ $match->team_b_id }}"
    data-team-a-name="{{ $match->teamA?->name ?? __('Equipo A') }}"
    data-team-b-name="{{ $match->teamB?->name ?? __('Equipo B') }}"
>
    <p class="text-xs font-semibold text-blue-900">
        {{ __('En eliminatorias, si pronosticás empate, elegí quién clasifica.') }}
    </p>

    {{-- No score yet: a qualified team cannot be chosen. --}}
    <p class="mt-2 text-xs font-medium text-blue-600" data-qualified-empty {{ $hasBoth ? 'hidden' : '' }}>
        {{ __('Cargá el resultado para definir quién clasifica.') }}
    </p>

    {{-- Non-draw: the qualified team is inferred from the score winner. --}}
    <p class="mt-2 text-xs font-semibold text-blue-800" data-qualified-auto {{ $isNonDraw ? '' : 'hidden' }}>
        {{ __('Clasifica automáticamente:') }}
        <span class="font-black text-blue-950" data-qualified-auto-name>{{ $inferredName }}</span>
    </p>

    {{-- Draw: the user picks who advances. --}}
    <div data-qualified-draw {{ $isDraw ? '' : 'hidden' }}>
        <p class="mt-2 text-xs font-black text-blue-950">{{ __('¿Quién clasifica?') }}</p>
        <div class="mt-2 grid grid-cols-2 gap-3">
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
                        @checked((string) $checkedId === (string) $teamId)
                    >
                    <span class="flex items-center gap-2 rounded-2xl border-2 border-blue-100 bg-white px-3 py-2.5 text-sm font-bold text-blue-950 shadow-sm transition peer-checked:border-blue-600 peer-checked:bg-blue-600 peer-checked:text-white peer-focus-visible:ring-4 peer-focus-visible:ring-blue-100">
                        <x-team-flag :team="$team" size="sm" />
                        <span class="truncate">{{ $teamName }}</span>
                    </span>
                </label>
            @endforeach
        </div>
        <p class="mt-2 text-[11px] font-medium text-blue-600">
            {{ __('Cuenta el resultado final jugado, incluido alargue.') }}
        </p>
    </div>

    @if ($error)
        <p class="mt-2 text-xs font-semibold text-red-600">{{ $error }}</p>
    @endif
</div>
