<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Liga general') }}
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    {{ __('Tabla de posiciones de la liga general, ordenada por puntos de predicciones puntuadas.') }}
                </p>
            </div>

            <a
                href="{{ route('predictions.index') }}"
                class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
            >
                {{ __('Cargar predicciones') }}
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-6xl space-y-5 px-4 sm:px-6 lg:px-8">
            <x-ranking-table
                :entries="$leaderboard"
                :context-label="__('Liga general')"
            >
                <x-slot name="emptyAction">
                    <a
                        href="{{ route('predictions.index') }}"
                        class="mt-5 inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        {{ __('Ir a predicciones') }}
                    </a>
                </x-slot>
            </x-ranking-table>
        </div>
    </div>
</x-app-layout>
