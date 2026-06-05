@props([
    'entries',
    'contextLabel' => __('Liga general'),
    'emptyTitle' => __('Todavia no hay tabla de posiciones'),
    'emptyText' => __('La tabla de posiciones se va a completar cuando haya predicciones puntuadas.'),
])

@php
    $currentUserId = auth()->id();
@endphp

@if ($entries->isEmpty())
    <div class="rounded-2xl bg-white p-8 text-center shadow-sm shadow-blue-900/5 ring-1 ring-blue-100">
        <h3 class="text-lg font-black text-blue-950">
            {{ $emptyTitle }}
        </h3>
        <p class="mt-2 text-sm text-slate-600">
            {{ $emptyText }}
        </p>

        {{ $emptyAction ?? '' }}
    </div>
@else
    <div class="overflow-hidden rounded-2xl bg-white shadow-lg shadow-blue-900/5 ring-1 ring-blue-100">
        <div class="grid grid-cols-[3rem_minmax(0,1fr)_4.5rem_3rem_3rem_3.25rem] items-center gap-2 border-b border-blue-100 bg-blue-950 px-3 py-2 text-[10px] font-black uppercase tracking-wide text-blue-100 sm:grid-cols-[4rem_minmax(0,1fr)_6rem_7rem_6rem_8rem] sm:px-4 sm:text-xs">
            <span class="text-center sm:hidden">{{ __('#') }}</span>
            <span class="hidden text-center sm:block">{{ __('Posicion') }}</span>
            <span>{{ __('Usuario') }}</span>
            <span class="text-right sm:hidden">{{ __('Pts') }}</span>
            <span class="hidden text-right sm:block">{{ __('Puntos') }}</span>
            <span class="text-right sm:hidden">{{ __('Ex') }}</span>
            <span class="hidden text-right sm:block">{{ __('Resultados exactos') }}</span>
            <span class="text-right sm:hidden">{{ __('Ten') }}</span>
            <span class="hidden text-right sm:block">{{ __('Tendencias') }}</span>
            <span class="text-right sm:hidden">{{ __('Pred') }}</span>
            <span class="hidden text-right sm:block">{{ __('Predicciones puntuadas') }}</span>
        </div>

        <div class="divide-y divide-slate-100">
            @foreach ($entries as $entry)
                @php
                    $isFirstPlace = $loop->first;
                    $isCurrentUser = $currentUserId && (int) $entry->id === (int) $currentUserId;
                @endphp

                <div @class([
                    'grid grid-cols-[3rem_minmax(0,1fr)_4.5rem_3rem_3rem_3.25rem] items-center gap-2 px-3 py-3 text-sm transition sm:grid-cols-[4rem_minmax(0,1fr)_6rem_7rem_6rem_8rem] sm:px-4',
                    'bg-amber-50/80' => $isFirstPlace,
                    'bg-emerald-50/70' => ! $isFirstPlace && $isCurrentUser,
                    'bg-white hover:bg-blue-50/50' => ! $isFirstPlace && ! $isCurrentUser,
                ])>
                    <div class="flex justify-center">
                        <span @class([
                            'inline-flex h-8 min-w-8 items-center justify-center rounded-full px-2 text-xs font-black sm:h-9 sm:min-w-9 sm:text-sm',
                            'bg-amber-500 text-white shadow-sm shadow-amber-900/20' => $isFirstPlace,
                            'bg-emerald-600 text-white' => ! $isFirstPlace && $isCurrentUser,
                            'bg-slate-100 text-slate-700' => ! $isFirstPlace && ! $isCurrentUser,
                        ])>
                            {{ $loop->iteration }}
                        </span>
                    </div>

                    <div class="min-w-0">
                        <div class="flex min-w-0 items-center gap-2">
                            <p class="truncate font-black text-blue-950">
                                {{ '@'.$entry->username }}
                            </p>
                            @if ($isCurrentUser)
                                <span class="hidden rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-black uppercase tracking-wide text-emerald-800 sm:inline-flex">
                                    {{ __('Vos') }}
                                </span>
                            @endif
                        </div>
                        <p class="mt-0.5 truncate text-[11px] font-bold uppercase tracking-wide text-slate-500">
                            {{ $isFirstPlace ? __('Primer puesto') : $contextLabel }}
                        </p>
                    </div>

                    <div class="text-right">
                        <p class="text-xl font-black leading-none text-blue-950 sm:text-2xl">
                            {{ (int) $entry->total_points }}
                        </p>
                        <p class="mt-0.5 text-[10px] font-black uppercase tracking-wide text-slate-500 sm:hidden">
                            {{ __('Pts') }}
                        </p>
                    </div>

                    <p class="text-right text-sm font-black text-slate-700 sm:text-base">
                        {{ (int) $entry->exact_results_count }}
                    </p>

                    <p class="text-right text-sm font-black text-slate-700 sm:text-base">
                        {{ (int) $entry->trend_count }}
                    </p>

                    <p class="text-right text-sm font-black text-slate-700 sm:text-base">
                        {{ (int) $entry->scored_predictions_count }}
                    </p>
                </div>
            @endforeach
        </div>
    </div>
@endif
