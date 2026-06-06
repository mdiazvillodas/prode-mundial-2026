@props([
    'team' => null,
    'size' => 'md',
])

@php
    $sizes = [
        'sm' => 'h-8 w-8 text-[10px]',
        'md' => 'h-10 w-10 text-xs',
        'lg' => 'h-14 w-14 text-sm',
    ];

    $sizeClass = $sizes[$size] ?? $sizes['md'];
    $hasTeam = filled($team);
    $teamName = $hasTeam ? (string) $team->name : __('Equipo por definir');
    $code = $hasTeam ? ($team->short_name ?: ($team->country_code ?: '')) : '';

    if ($code === '' && $hasTeam) {
        $code = collect(preg_split('/\s+/', trim($teamName)))
            ->filter()
            ->map(fn (string $word): string => mb_substr($word, 0, 1))
            ->take(3)
            ->implode('');
    }

    $label = $hasTeam ? __('Bandera de :team', ['team' => $teamName]) : __('Equipo por definir');
    $fallback = $hasTeam ? strtoupper(mb_substr($code !== '' ? $code : $teamName, 0, 3)) : 'TBD';
@endphp

<span
    {{ $attributes->merge([
        'class' => "{$sizeClass} inline-flex shrink-0 items-center justify-center overflow-hidden rounded-full bg-slate-100 font-black uppercase text-slate-600 ring-2 ring-white shadow-sm",
    ]) }}
    aria-label="{{ $label }}"
>
    @if ($hasTeam && filled($team->flag_path))
        <img
            src="{{ asset($team->flag_path) }}"
            alt="{{ $label }}"
            class="h-full w-full object-cover"
            loading="lazy"
        >
    @else
        <span aria-hidden="true">{{ $fallback }}</span>
    @endif
</span>
