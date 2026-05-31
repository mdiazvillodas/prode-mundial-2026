<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Predicciones') }}
            </h2>
            <p class="text-sm text-gray-500">
                {{ __('Carga o edita varios marcadores desde la lista de partidos.') }}
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 rounded-md bg-emerald-50 p-4 text-sm font-medium text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            @if ($matchesByDate->isEmpty())
                <div class="bg-white border border-gray-200 shadow-sm sm:rounded-lg">
                    <div class="p-6 text-sm text-gray-700">
                        {{ __('Todavia no hay partidos cargados.') }}
                    </div>
                </div>
            @else
                <form method="POST" action="{{ route('predictions.bulk-store') }}" id="predictions-form" class="space-y-6">
                    @csrf

                    @foreach ($matchesByDate as $date => $matches)
                        <section class="space-y-3">
                            <div class="sticky top-0 z-10 border-y border-gray-200 bg-gray-50 px-3 py-2 sm:rounded-md sm:border">
                                <h3 class="text-sm font-semibold uppercase text-gray-700">
                                    {{ $date === 'unscheduled' ? __('Fecha por definir') : \Illuminate\Support\Carbon::parse($date)->translatedFormat('l d/m/Y') }}
                                </h3>
                            </div>

                            <div class="space-y-4">
                                @foreach ($matches as $match)
                                    @php
                                        $prediction = $match->predictions->first();
                                        $canPredict = $match->isPredictable();
                                        $isPlaceholder = $match->status === 'placeholder' || ! $match->teamA || ! $match->teamB;
                                        $teamAName = $match->teamA?->name ?? 'Equipo por definir';
                                        $teamBName = $match->teamB?->name ?? 'Equipo por definir';
                                        $statusLabels = [
                                            'scheduled' => 'Programado',
                                            'open' => 'Abierto',
                                            'locked' => 'Cerrado',
                                            'finished' => 'Terminado',
                                            'placeholder' => 'Por definir',
                                        ];
                                        $statusClasses = [
                                            'scheduled' => 'bg-sky-50 text-sky-700 ring-sky-600/20',
                                            'open' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
                                            'locked' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
                                            'finished' => 'bg-gray-100 text-gray-700 ring-gray-500/20',
                                            'placeholder' => 'bg-violet-50 text-violet-700 ring-violet-600/20',
                                        ];
                                        $statusLabel = $statusLabels[$match->status] ?? ucfirst($match->status);
                                        $statusClass = $statusClasses[$match->status] ?? 'bg-gray-100 text-gray-700 ring-gray-500/20';
                                        $teamAError = $errors->first("predictions.{$match->id}.team_a_score");
                                        $teamBError = $errors->first("predictions.{$match->id}.team_b_score");
                                    @endphp

                                    <article class="bg-white border border-gray-200 shadow-sm sm:rounded-lg">
                                        <div class="p-4 sm:p-5">
                                            <div class="flex flex-col gap-4">
                                                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                                    <div class="min-w-0">
                                                        <div class="flex flex-wrap items-center gap-2 text-xs text-gray-500">
                                                            <span>{{ $match->starts_at ? $match->starts_at->format('H:i') : __('Hora por definir') }}</span>

                                                            @if ($match->stage)
                                                                <span aria-hidden="true">-</span>
                                                                <span>{{ __('Fase') }}: {{ str_replace('_', ' ', $match->stage) }}</span>
                                                            @endif

                                                            @if ($match->group)
                                                                <span aria-hidden="true">-</span>
                                                                <span>{{ __('Grupo') }} {{ $match->group }}</span>
                                                            @endif
                                                        </div>

                                                        <div class="mt-3 grid grid-cols-[1fr_auto_1fr] items-center gap-3">
                                                            <div class="min-w-0">
                                                                <p class="truncate text-base font-semibold text-gray-900">{{ $teamAName }}</p>
                                                                @if ($match->teamA?->short_name)
                                                                    <p class="text-xs uppercase tracking-wide text-gray-500">{{ $match->teamA->short_name }}</p>
                                                                @endif
                                                            </div>

                                                            <div class="text-center text-sm font-semibold text-gray-500">
                                                                @if ($match->status === 'finished')
                                                                    <span class="inline-flex min-w-14 justify-center rounded-md bg-gray-900 px-2 py-1 text-white">
                                                                        {{ $match->team_a_score }} - {{ $match->team_b_score }}
                                                                    </span>
                                                                @else
                                                                    <span>{{ __('vs') }}</span>
                                                                @endif
                                                            </div>

                                                            <div class="min-w-0 text-right">
                                                                <p class="truncate text-base font-semibold text-gray-900">{{ $teamBName }}</p>
                                                                @if ($match->teamB?->short_name)
                                                                    <p class="text-xs uppercase tracking-wide text-gray-500">{{ $match->teamB->short_name }}</p>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <span class="inline-flex w-fit items-center rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset {{ $statusClass }}">
                                                        {{ $statusLabel }}
                                                    </span>
                                                </div>

                                                @if ($canPredict)
                                                    <input type="hidden" name="predictions[{{ $match->id }}][changed]" value="{{ old("predictions.{$match->id}.changed", '0') }}" data-changed-input>

                                                    <div class="grid grid-cols-2 gap-3 rounded-md bg-gray-50 p-3">
                                                        <div>
                                                            <label for="prediction-{{ $match->id }}-team-a" class="block text-xs font-medium text-gray-600">
                                                                {{ $match->teamA?->short_name ?: $teamAName }}
                                                            </label>
                                                            <input
                                                                id="prediction-{{ $match->id }}-team-a"
                                                                name="predictions[{{ $match->id }}][team_a_score]"
                                                                type="number"
                                                                min="0"
                                                                max="99"
                                                                inputmode="numeric"
                                                                value="{{ old("predictions.{$match->id}.team_a_score", $prediction?->team_a_score) }}"
                                                                class="mt-1 block w-full rounded-md border-gray-300 text-center text-base font-semibold shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                                data-prediction-input
                                                            >
                                                            @if ($teamAError)
                                                                <p class="mt-1 text-xs text-red-600">{{ $teamAError }}</p>
                                                            @endif
                                                        </div>

                                                        <div>
                                                            <label for="prediction-{{ $match->id }}-team-b" class="block text-right text-xs font-medium text-gray-600">
                                                                {{ $match->teamB?->short_name ?: $teamBName }}
                                                            </label>
                                                            <input
                                                                id="prediction-{{ $match->id }}-team-b"
                                                                name="predictions[{{ $match->id }}][team_b_score]"
                                                                type="number"
                                                                min="0"
                                                                max="99"
                                                                inputmode="numeric"
                                                                value="{{ old("predictions.{$match->id}.team_b_score", $prediction?->team_b_score) }}"
                                                                class="mt-1 block w-full rounded-md border-gray-300 text-center text-base font-semibold shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                                data-prediction-input
                                                            >
                                                            @if ($teamBError)
                                                                <p class="mt-1 text-xs text-right text-red-600">{{ $teamBError }}</p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="rounded-md bg-gray-50 p-3 text-sm text-gray-600">
                                                        @if ($isPlaceholder)
                                                            {{ __('Este partido se completara cuando los equipos esten definidos.') }}
                                                        @elseif ($match->status === 'finished')
                                                            {{ __('Partido terminado. La prediccion ya no se puede editar.') }}
                                                        @elseif ($match->status === 'locked')
                                                            {{ __('Predicciones cerradas para este partido.') }}
                                                        @else
                                                            {{ __('Este partido no esta disponible para predicciones.') }}
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        </section>
                    @endforeach

                    <div id="floating-save" class="fixed inset-x-0 bottom-0 z-30 hidden border-t border-gray-200 bg-white/95 p-4 shadow-lg backdrop-blur">
                        <div class="mx-auto flex max-w-5xl items-center justify-between gap-3">
                            <p class="text-sm font-medium text-gray-700">
                                {{ __('Tenes cambios sin guardar.') }}
                            </p>

                            <x-primary-button>
                                {{ __('Guardar cambios') }}
                            </x-primary-button>
                        </div>
                    </div>
                </form>
            @endif
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('predictions-form');
            const floatingSave = document.getElementById('floating-save');

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
        });
    </script>
</x-app-layout>
