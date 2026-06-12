<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-blue-600">
                {{ __('Prode Mundial 2026') }}
            </p>
            <h2 class="text-2xl font-black leading-tight text-blue-950">
                {{ __('Predicciones') }}
            </h2>
            <p class="text-sm text-slate-500">
                {{ __('Completá tus pronósticos antes de que empiece cada partido.') }}
            </p>
        </div>
    </x-slot>

    <div class="min-h-screen bg-gradient-to-b from-sky-50 via-white to-blue-50/70 py-6">
        <div class="mx-auto max-w-5xl space-y-6 px-4 pb-32 sm:px-6 lg:px-8">
            @if ($dateOptions->isNotEmpty())
                <section class="rounded-[1.25rem] border border-white bg-white/75 p-3 shadow-md shadow-blue-900/5 ring-1 ring-blue-100/70 backdrop-blur">
                    <p class="px-1 pb-2 text-xs font-bold uppercase tracking-[0.16em] text-slate-500">
                        {{ __('Horarios en tu hora local') }}
                    </p>
                    <div class="flex gap-2 overflow-x-auto pb-1" data-date-nav>
                        @foreach ($dateOptions as $dateOption)
                            @php
                                $isActiveDate = $dateOption['date'] === $selectedDate;
                            @endphp

                            <a
                                href="{{ route('predictions.index', ['date' => $dateOption['date'], 'tz' => $timezone]) }}"
                                aria-current="{{ $isActiveDate ? 'date' : 'false' }}"
                                data-date-chip
                                @if ($isActiveDate) data-active-date-chip @endif
                                @class([
                                    'shrink-0 rounded-full px-4 py-2 text-sm font-bold transition focus:outline-none focus:ring-4 focus:ring-blue-100',
                                    'bg-blue-700 text-white shadow-md shadow-blue-700/20' => $isActiveDate,
                                    'bg-blue-50 text-blue-800 hover:bg-blue-100' => ! $isActiveDate,
                                ])
                            >
                                <span class="hidden sm:inline">
                                    {{ $dateOption['date_label'] }} · {{ trans_choice(':count partido|:count partidos', $dateOption['count'], ['count' => $dateOption['count']]) }}
                                </span>
                                <span class="sm:hidden">
                                    {{ $dateOption['mobile_label'] }} · {{ $dateOption['count'] }}
                                </span>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($dateOptions->isEmpty())
                <div class="rounded-[1.5rem] border border-dashed border-blue-200 bg-white p-8 text-center shadow-lg shadow-blue-900/5">
                    <p class="text-lg font-black text-blue-950">
                        {{ __('Todavía no hay partidos cargados') }}
                    </p>
                    <p class="mt-2 text-sm text-slate-600">
                        {{ __('Cuando el calendario tenga partidos, vas a poder completar tus predicciones desde acá.') }}
                    </p>
                </div>
            @else
                <form method="POST" action="{{ route('predictions.bulk-store', ['date' => $selectedDate, 'tz' => $timezone]) }}" id="predictions-form" class="space-y-8">
                    @csrf

                    <section class="space-y-4">
                        <div class="sticky top-0 z-10 -mx-4 bg-sky-50/95 px-4 py-3 backdrop-blur sm:static sm:mx-0 sm:rounded-[1.25rem] sm:bg-transparent sm:px-0">
                            <div class="flex items-end justify-between gap-4 border-b-2 border-blue-200 pb-3">
                                <div>
                                    <p class="text-xs font-black uppercase tracking-[0.18em] text-blue-600">
                                        {{ __('Jornada') }}
                                    </p>
                                    <h3 class="mt-1 text-xl font-black text-blue-950">
                                        {{ \Illuminate\Support\Carbon::parse($selectedDate, $timezone)->translatedFormat('l d/m/Y') }}
                                    </h3>
                                </div>

                                <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-black text-blue-800">
                                    {{ trans_choice(':count partido|:count partidos', $matches->count(), ['count' => $matches->count()]) }}
                                </span>
                            </div>
                        </div>

                        <div class="space-y-5">
                            @foreach ($matches as $match)
                                @php
                                    $prediction = $match->predictions->first();
                                    $displayTimes = $matchDisplayTimes[$match->id] ?? [];
                                    $canPredict = $match->isPredictable();
                                    $liveApiStatuses = ['1H', '2H', 'HT', 'ET', 'BT', 'P', 'SUSP', 'INT', 'LIVE'];
                                    $isLive = in_array($match->api_status, $liveApiStatuses, true);
                                    $isPlaceholder = $match->status === 'placeholder' || ! $match->teamA || ! $match->teamB;
                                    $teamAName = $match->teamA?->name ?? __('Equipo por definir');
                                    $teamBName = $match->teamB?->name ?? __('Equipo por definir');
                                    $closesAt = $match->predictionClosesAt();
                                    $minutesUntilClose = $closesAt ? now()->diffInMinutes($closesAt, false) : null;
                                    $isClosingSoon = $canPredict && $minutesUntilClose !== null && $minutesUntilClose >= 0 && $minutesUntilClose <= 60;
                                    $hasResult = $match->status === 'finished' && $match->team_a_score !== null && $match->team_b_score !== null;

                                    $statusLabels = [
                                        'scheduled' => __('Programado'),
                                        'open' => __('Abierto'),
                                        'locked' => __('Cerrado'),
                                        'finished' => __('Finalizado'),
                                        'placeholder' => __('Equipos por definir'),
                                    ];
                                    if ($isLive) {
                                        $statusLabel = $match->api_status ? __('En vivo · :status', ['status' => $match->api_status]) : __('En vivo');
                                    } else {
                                        $statusLabel = $isClosingSoon ? __('Cierra pronto') : ($statusLabels[$match->status] ?? ucfirst($match->status));
                                    }

                                    if ($isPlaceholder) {
                                        $statusClass = 'bg-sky-100 text-sky-800 ring-sky-200';
                                        $dotClass = 'bg-sky-500';
                                    } elseif ($isLive) {
                                        $statusClass = 'bg-red-100 text-red-800 ring-red-200';
                                        $dotClass = 'bg-red-500';
                                    } elseif ($hasResult || $match->status === 'finished') {
                                        $statusClass = 'bg-slate-100 text-slate-700 ring-slate-200';
                                        $dotClass = 'bg-slate-400';
                                    } elseif ($isClosingSoon) {
                                        $statusClass = 'bg-orange-100 text-orange-800 ring-orange-200';
                                        $dotClass = 'bg-orange-500';
                                    } elseif ($canPredict) {
                                        $statusClass = 'bg-emerald-100 text-emerald-800 ring-emerald-200';
                                        $dotClass = 'bg-emerald-500';
                                    } else {
                                        $statusClass = 'bg-slate-100 text-slate-700 ring-slate-200';
                                        $dotClass = 'bg-slate-400';
                                    }

                                    $teamAError = $errors->first("predictions.{$match->id}.team_a_score");
                                    $teamBError = $errors->first("predictions.{$match->id}.team_b_score");
                                    $qualifiedTeamError = $errors->first("predictions.{$match->id}.predicted_qualified_team_id");
                                @endphp

                                <article
                                    @if ($match->requiresQualifiedTeamPrediction()) data-knockout-card @endif
                                    @class([
                                    'overflow-hidden rounded-[1.75rem] border bg-white shadow-xl shadow-blue-900/5 ring-1 transition',
                                    'border-blue-100 ring-blue-100/80' => $canPredict,
                                    'border-slate-200 opacity-90 ring-slate-100' => ! $canPredict,
                                ])>
                                    <div class="p-4 sm:p-6">
                                        <div class="flex items-start justify-between gap-3">
                                            <span class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-xs font-black ring-1 {{ $statusClass }}">
                                                <span class="h-2 w-2 rounded-full {{ $dotClass }}"></span>
                                                {{ $statusLabel }}
                                            </span>

                                            @if ($prediction)
                                                <span class="inline-flex items-center rounded-full bg-blue-50 px-3 py-1.5 text-xs font-bold text-blue-700">
                                                    {{ $canPredict ? __('Guardada') : __('Tu pronóstico') }}
                                                </span>
                                            @endif
                                        </div>

                                        <div class="mt-4 text-center">
                                            <p class="text-sm font-black text-blue-950">
                                                @if (! empty($displayTimes['kickoff_time']))
                                                    <span>{{ $displayTimes['kickoff_time'] }}</span>
                                                @else
                                                    {{ __('Hora por definir') }}
                                                @endif
                                            </p>
                                            <div class="mt-1 flex flex-wrap items-center justify-center gap-2 text-xs font-semibold text-slate-500">
                                                @if ($match->stage)
                                                    <span>{{ __('Fase') }}: {{ str_replace('_', ' ', $match->stage) }}</span>
                                                @endif

                                                @if ($match->group)
                                                    <span class="h-1 w-1 rounded-full bg-slate-300"></span>
                                                    <span>{{ __('Grupo') }} {{ $match->group }}</span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="mt-6 grid grid-cols-[1fr_auto_1fr] items-start gap-3">
                                            <div class="min-w-0 text-center">
                                                <x-team-flag :team="$match->teamA" size="lg" class="mx-auto ring-4 ring-blue-50" />
                                                <p class="mt-3 truncate text-sm font-black text-blue-950 sm:text-base">{{ $teamAName }}</p>
                                                @if ($match->teamA?->short_name)
                                                    <p class="mt-1 text-[11px] font-bold uppercase tracking-wide text-slate-500">{{ $match->teamA->short_name }}</p>
                                                @endif
                                            </div>

                                            <div class="min-w-[7rem] text-center">
                                                @if ($canPredict)
                                                    <input type="hidden" name="predictions[{{ $match->id }}][changed]" value="{{ old("predictions.{$match->id}.changed", '0') }}" data-changed-input>

                                                    <div class="flex items-center justify-center gap-2">
                                                        <div>
                                                            <label for="prediction-{{ $match->id }}-team-a" class="sr-only">
                                                                {{ __('Goles de :team', ['team' => $teamAName]) }}
                                                            </label>
                                                            <input
                                                                id="prediction-{{ $match->id }}-team-a"
                                                                name="predictions[{{ $match->id }}][team_a_score]"
                                                                type="number"
                                                                min="0"
                                                                max="99"
                                                                inputmode="numeric"
                                                                value="{{ old("predictions.{$match->id}.team_a_score", $prediction?->team_a_score) }}"
                                                                class="h-16 w-14 rounded-2xl border-2 border-blue-100 bg-blue-50/70 text-center text-2xl font-black text-blue-950 shadow-inner transition focus:border-blue-600 focus:bg-white focus:outline-none focus:ring-4 focus:ring-blue-100"
                                                                data-prediction-input
                                                                data-score-a
                                                            >
                                                        </div>

                                                        <span class="text-xl font-black text-slate-300">-</span>

                                                        <div>
                                                            <label for="prediction-{{ $match->id }}-team-b" class="sr-only">
                                                                {{ __('Goles de :team', ['team' => $teamBName]) }}
                                                            </label>
                                                            <input
                                                                id="prediction-{{ $match->id }}-team-b"
                                                                name="predictions[{{ $match->id }}][team_b_score]"
                                                                type="number"
                                                                min="0"
                                                                max="99"
                                                                inputmode="numeric"
                                                                value="{{ old("predictions.{$match->id}.team_b_score", $prediction?->team_b_score) }}"
                                                                class="h-16 w-14 rounded-2xl border-2 border-blue-100 bg-blue-50/70 text-center text-2xl font-black text-blue-950 shadow-inner transition focus:border-blue-600 focus:bg-white focus:outline-none focus:ring-4 focus:ring-blue-100"
                                                                data-prediction-input
                                                                data-score-b
                                                            >
                                                        </div>
                                                    </div>

                                                    @if ($teamAError || $teamBError)
                                                        <div class="mt-2 space-y-1 text-xs font-semibold text-red-600">
                                                            @if ($teamAError)
                                                                <p>{{ $teamAError }}</p>
                                                            @endif
                                                            @if ($teamBError)
                                                                <p>{{ $teamBError }}</p>
                                                            @endif
                                                        </div>
                                                    @endif
                                                @elseif ($prediction)
                                                    <div class="rounded-2xl bg-blue-50 px-4 py-3 text-blue-950 ring-1 ring-blue-100">
                                                        <p class="text-xs font-bold uppercase tracking-wide text-blue-500">
                                                            {{ __('Tu pronóstico') }}
                                                        </p>
                                                        <p class="mt-1 text-3xl font-black">
                                                            {{ $prediction->team_a_score }} - {{ $prediction->team_b_score }}
                                                        </p>
                                                        @php
                                                            $predictedQualifiedName = $prediction->predicted_qualified_team_id == $match->team_a_id
                                                                ? $teamAName
                                                                : ($prediction->predicted_qualified_team_id == $match->team_b_id ? $teamBName : null);
                                                        @endphp
                                                        @if ($predictedQualifiedName)
                                                            <p class="mt-1 text-[11px] font-bold text-blue-600">
                                                                {{ __('Pasa') }}: {{ $predictedQualifiedName }}
                                                            </p>
                                                        @endif
                                                        <p class="mt-1 text-[11px] font-bold text-blue-600">
                                                            {{ __('Ya no se puede editar') }}
                                                        </p>

                                                        @if ($hasResult)
                                                            <p class="mt-2 text-xs font-bold text-slate-500">
                                                                {{ __('Resultado') }}: {{ $match->team_a_score }} - {{ $match->team_b_score }}
                                                            </p>
                                                        @endif
                                                    </div>
                                                @elseif ($hasResult)
                                                    <div class="rounded-2xl bg-slate-950 px-4 py-3 text-white shadow-lg shadow-slate-950/20">
                                                        <p class="text-xs font-bold uppercase tracking-wide text-slate-300">
                                                            {{ __('Resultado') }}
                                                        </p>
                                                        <p class="mt-1 text-3xl font-black">
                                                            {{ $match->team_a_score }} - {{ $match->team_b_score }}
                                                        </p>
                                                    </div>
                                                @else
                                                    <div class="rounded-2xl bg-slate-100 px-4 py-4 text-sm font-black text-slate-500">
                                                        {{ __('vs') }}
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="min-w-0 text-center">
                                                <x-team-flag :team="$match->teamB" size="lg" class="mx-auto ring-4 ring-blue-50" />
                                                <p class="mt-3 truncate text-sm font-black text-blue-950 sm:text-base">{{ $teamBName }}</p>
                                                @if ($match->teamB?->short_name)
                                                    <p class="mt-1 text-[11px] font-bold uppercase tracking-wide text-slate-500">{{ $match->teamB->short_name }}</p>
                                                @endif
                                            </div>
                                        </div>

                                        @if ($canPredict && $match->requiresQualifiedTeamPrediction())
                                            <x-knockout-qualified-selector
                                                :match="$match"
                                                name="predictions[{{ $match->id }}][predicted_qualified_team_id]"
                                                id-prefix="prediction-{{ $match->id }}"
                                                :selected-id="old('predictions.'.$match->id.'.predicted_qualified_team_id', $prediction?->predicted_qualified_team_id)"
                                                :error="$qualifiedTeamError"
                                            />
                                        @endif

                                        <div class="mt-5 rounded-2xl bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-600">
                                            @if ($canPredict)
                                                @if ($prediction)
                                                    <span class="text-blue-700">{{ __('Tu predicción guardada.') }}</span>
                                                @else
                                                    <span class="text-emerald-700">{{ __('Abierto para completar.') }}</span>
                                                @endif

                                                @if ($closesAt)
                                                    <span>
                                                        {{ __('Editar hasta') }}
                                                        <span>{{ $displayTimes['prediction_closes_time'] ?? '' }}</span>.
                                                    </span>
                                                @endif
                                            @elseif ($prediction)
                                                <span class="text-blue-700">
                                                    {{ __('Tu pronóstico') }}: {{ $prediction->team_a_score }} - {{ $prediction->team_b_score }}.
                                                </span>
                                                <span>{{ __('Predicciones cerradas. Ya no se puede editar.') }}</span>
                                            @elseif ($isPlaceholder)
                                                <span class="text-sky-700">{{ __('Equipos por definir. Este partido se habilita cuando estén confirmados.') }}</span>
                                            @elseif ($match->status === 'finished')
                                                <span>{{ __('Finalizado. La predicción ya no se puede editar.') }}</span>
                                            @elseif ($match->status === 'locked')
                                                <span>{{ __('Predicciones cerradas para este partido.') }}</span>
                                            @else
                                                <span>{{ __('Este partido no está disponible para predicciones.') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </section>

                    <div id="floating-save" class="fixed inset-x-0 bottom-0 z-30 hidden border-t border-blue-100 bg-white/95 p-4 shadow-2xl shadow-blue-950/20 backdrop-blur">
                        <div class="mx-auto flex max-w-5xl items-center justify-between gap-3">
                            <div>
                                <p class="text-sm font-black text-blue-950">
                                    {{ __('Tenés cambios sin guardar') }}
                                </p>
                                <p class="text-xs font-medium text-slate-500">
                                    {{ __('Guardá tus predicciones antes de salir.') }}
                                </p>
                            </div>

                            <button
                                type="submit"
                                id="floating-save-button"
                                class="inline-flex items-center justify-center rounded-2xl bg-blue-700 px-5 py-3 text-sm font-black text-white shadow-lg shadow-blue-700/25 transition hover:bg-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-200"
                            >
                                {{ __('Guardar cambios') }}
                            </button>
                        </div>
                    </div>
                </form>
            @endif
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const browserTimezone = (() => {
                try {
                    return Intl.DateTimeFormat().resolvedOptions().timeZone;
                } catch (error) {
                    return null;
                }
            })();

            if (browserTimezone) {
                const url = new URL(window.location.href);

                if (url.searchParams.get('tz') !== browserTimezone) {
                    url.searchParams.set('tz', browserTimezone);
                    window.location.replace(url.toString());

                    return;
                }
            }

            const dateNav = document.querySelector('[data-date-nav]');
            const activeDateChip = dateNav?.querySelector('[data-active-date-chip]');

            if (dateNav && activeDateChip) {
                const left = activeDateChip.offsetLeft - ((dateNav.clientWidth - activeDateChip.clientWidth) / 2);

                dateNav.scrollTo({
                    left: Math.max(0, left),
                    behavior: 'auto',
                });
            }

            const form = document.getElementById('predictions-form');
            const floatingSave = document.getElementById('floating-save');
            const floatingSaveButton = document.getElementById('floating-save-button');

            if (! form || ! floatingSave) {
                return;
            }

            form.querySelectorAll('[data-prediction-input]').forEach((input) => {
                input.dataset.originalValue = input.value;

                input.addEventListener('input', () => {
                    const article = input.closest('article');
                    const changedInput = article?.querySelector('[data-changed-input]');

                    if (changedInput) {
                        changedInput.value = '1';
                    }

                    floatingSave.classList.remove('hidden');
                });
            });

            form.addEventListener('submit', () => {
                if (floatingSaveButton) {
                    floatingSaveButton.disabled = true;
                    floatingSaveButton.textContent = @json(__('Guardando...'));
                }
            });
        });
    </script>

    @include('predictions.partials.knockout-inference')
</x-app-layout>
