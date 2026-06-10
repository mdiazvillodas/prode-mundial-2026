@props([
    'entries',
    'contextLabel' => __('Liga general'),
    'emptyTitle' => __('Todavia no hay tabla de posiciones'),
    'emptyText' => __('La tabla de posiciones se va a completar cuando haya predicciones puntuadas.'),
])

@php
    $currentUserId = auth()->id();
    $hasRecentForm = $entries->contains(fn ($entry): bool => ! empty($entry->recent_form));
    $formStyles = [
        'exact' => [
            'label' => __('Exacto'),
            'class' => 'bg-violet-500 ring-violet-200',
            'symbol' => '★',
        ],
        'trend' => [
            'label' => __('Tendencia'),
            'class' => 'bg-emerald-500 ring-emerald-200',
            'symbol' => '',
        ],
        'incorrect' => [
            'label' => __('Incorrecto'),
            'class' => 'bg-red-500 ring-red-200',
            'symbol' => '',
        ],
        'none' => [
            'label' => __('Sin pronóstico'),
            'class' => 'bg-slate-300 ring-slate-200',
            'symbol' => '',
        ],
    ];
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
    <div class="rounded-2xl bg-white p-3 shadow-lg shadow-blue-900/5 ring-1 ring-blue-100 sm:overflow-hidden sm:p-0">
        <div @class([
            'hidden items-center gap-3 border-b border-blue-100 bg-blue-950 px-4 py-2 text-xs font-black uppercase tracking-wide text-blue-100 sm:grid',
            'sm:grid-cols-[4rem_minmax(0,1.7fr)_6rem_7rem_6rem_6rem_7rem]' => $hasRecentForm,
            'sm:grid-cols-[4rem_minmax(0,1.7fr)_6rem_6rem_6rem_7rem]' => ! $hasRecentForm,
        ])>
            <span class="text-center">{{ __('Posicion') }}</span>
            <span>{{ __('Usuario') }}</span>
            <span class="text-right">{{ __('Puntos') }}</span>
            @if ($hasRecentForm)
                <span class="text-right">{{ __('Racha reciente') }}</span>
            @endif
            <span class="text-right">{{ __('Exactos') }}</span>
            <span class="text-right">{{ __('Tendencias') }}</span>
            <span class="text-right">{{ __('Predicciones') }}</span>
        </div>

        <div class="space-y-3 sm:space-y-0 sm:divide-y sm:divide-slate-100">
            @foreach ($entries as $entry)
                @php
                    $isFirstPlace = $loop->first;
                    $isCurrentUser = $currentUserId && (int) $entry->id === (int) $currentUserId;
                    $displayName = trim((string) ($entry->name ?? '')) !== ''
                        ? $entry->name
                        : '@'.$entry->username;
                    $showUsername = trim((string) ($entry->name ?? '')) !== '' && ! empty($entry->username);
                    $entryUser = new \App\Models\User([
                        'name' => $displayName,
                        'profile_avatar_key' => $entry->profile_avatar_key ?? null,
                    ]);
                    $metricClasses = 'rounded-xl bg-white px-3 py-2 ring-1 ring-slate-100 sm:bg-transparent sm:px-0 sm:py-0 sm:ring-0';
                @endphp

                <div @class([
                    'rounded-2xl border p-3 text-sm transition sm:grid sm:items-center sm:gap-3 sm:rounded-none sm:border-0 sm:px-4 sm:py-3',
                    'sm:grid-cols-[4rem_minmax(0,1.7fr)_6rem_7rem_6rem_6rem_7rem]' => $hasRecentForm,
                    'sm:grid-cols-[4rem_minmax(0,1.7fr)_6rem_6rem_6rem_7rem]' => ! $hasRecentForm,
                    'border-amber-200 bg-amber-50/80 sm:bg-amber-50/80' => $isFirstPlace,
                    'border-emerald-200 bg-emerald-50/70 sm:bg-emerald-50/70' => ! $isFirstPlace && $isCurrentUser,
                    'border-slate-100 bg-white hover:bg-blue-50/50' => ! $isFirstPlace && ! $isCurrentUser,
                ])>
                    <div class="hidden justify-center sm:flex">
                        <span @class([
                            'inline-flex h-8 min-w-8 items-center justify-center rounded-full px-2 text-xs font-black sm:h-9 sm:min-w-9 sm:text-sm',
                            'bg-amber-500 text-white shadow-sm shadow-amber-900/20' => $isFirstPlace,
                            'bg-emerald-600 text-white' => ! $isFirstPlace && $isCurrentUser,
                            'bg-slate-100 text-slate-700' => ! $isFirstPlace && ! $isCurrentUser,
                        ])>
                            {{ $loop->iteration }}
                        </span>
                    </div>

                    <div class="flex min-w-0 items-start justify-between gap-3 sm:items-center">
                        <div class="flex min-w-0 items-center gap-3">
                            <span @class([
                                'inline-flex h-9 min-w-9 items-center justify-center rounded-full px-2 text-xs font-black sm:hidden',
                                'bg-amber-500 text-white shadow-sm shadow-amber-900/20' => $isFirstPlace,
                                'bg-emerald-600 text-white' => ! $isFirstPlace && $isCurrentUser,
                                'bg-slate-100 text-slate-700' => ! $isFirstPlace && ! $isCurrentUser,
                            ])>
                                {{ $loop->iteration }}
                            </span>
                            <x-profile-avatar :user="$entryUser" size="sm" />
                            <div class="min-w-0">
                                <div class="flex min-w-0 items-center gap-2">
                                    <p class="truncate font-black text-blue-950">
                                        {{ $displayName }}
                                    </p>
                                    @if ($isCurrentUser)
                                        <span class="inline-flex rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-black uppercase tracking-wide text-emerald-800">
                                            {{ __('Vos') }}
                                        </span>
                                    @endif
                                </div>
                                @if ($showUsername)
                                    <p class="mt-0.5 truncate text-xs font-bold text-slate-500">
                                        {{ '@'.$entry->username }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        <div class="text-right sm:hidden">
                            <p class="text-2xl font-black leading-none text-blue-950">
                                {{ (int) $entry->total_points }}
                            </p>
                            <p class="mt-0.5 text-[10px] font-black uppercase tracking-wide text-slate-500">
                                {{ __('Pts') }}
                            </p>
                        </div>
                    </div>

                    <div class="{{ $metricClasses }} hidden text-right sm:block">
                        <p class="text-2xl font-black leading-none text-blue-950">
                            {{ (int) $entry->total_points }}
                        </p>
                    </div>

                    @if ($hasRecentForm)
                        <div class="mt-3 flex items-center justify-between gap-3 rounded-xl bg-white px-3 py-2 ring-1 ring-slate-100 sm:mt-0 sm:justify-end sm:bg-transparent sm:px-0 sm:py-0 sm:ring-0">
                            <span class="text-[11px] font-black uppercase tracking-wide text-slate-500 sm:hidden">{{ __('Forma') }}</span>
                            <div class="flex items-center justify-end gap-1.5" aria-label="{{ __('Racha reciente') }}">
                                @foreach (($entry->recent_form ?? []) as $form)
                                    @php
                                        $style = $formStyles[$form['state'] ?? 'none'] ?? $formStyles['none'];
                                    @endphp
                                    <span
                                        class="inline-flex h-4 w-4 items-center justify-center rounded-full text-[8px] font-black leading-none text-white ring-2 sm:h-3.5 sm:w-3.5 {{ $style['class'] }}"
                                        title="{{ $style['label'] }}"
                                        aria-label="{{ $style['label'] }}"
                                    >
                                        {{ $style['symbol'] }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="mt-3 grid grid-cols-3 gap-2 sm:mt-0 sm:contents">
                        <div class="{{ $metricClasses }} text-center sm:text-right">
                            <p class="text-[10px] font-black uppercase tracking-wide text-slate-500 sm:hidden">{{ __('Exactos') }}</p>
                            <p class="mt-0.5 text-base font-black text-slate-700 sm:mt-0">
                                {{ (int) $entry->exact_results_count }}
                            </p>
                        </div>

                        <div class="{{ $metricClasses }} text-center sm:text-right">
                            <p class="text-[10px] font-black uppercase tracking-wide text-slate-500 sm:hidden">{{ __('Tendencias') }}</p>
                            <p class="mt-0.5 text-base font-black text-slate-700 sm:mt-0">
                                {{ (int) $entry->trend_count }}
                            </p>
                        </div>

                        <div class="{{ $metricClasses }} text-center sm:text-right">
                            <p class="text-[10px] font-black uppercase tracking-wide text-slate-500 sm:hidden">{{ __('Pred') }}</p>
                            <p class="mt-0.5 text-base font-black text-slate-700 sm:mt-0">
                                {{ (int) $entry->scored_predictions_count }}
                            </p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
