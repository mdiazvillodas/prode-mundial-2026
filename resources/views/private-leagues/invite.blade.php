<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Invitacion a liga privada') }}
            </h2>

            <a
                href="{{ route('private-leagues.search') }}"
                class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-800 transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
            >
                {{ __('Buscar ligas') }}
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-3xl space-y-5 px-4 sm:px-6 lg:px-8">
            <article class="overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-gray-100">
                <div class="border-b border-gray-100 bg-gray-950 px-6 py-6 text-white">
                    <p class="text-sm font-medium uppercase tracking-wide text-indigo-200">
                        {{ __('Invitacion de liga') }}
                    </p>
                    <h3 class="mt-2 text-2xl font-bold">
                        {{ $privateLeague->name }}
                    </h3>
                    <div class="mt-2 text-sm text-gray-300">
                        <p>{{ __('Dueño') }}: {{ $privateLeague->owner->displayName() }}</p>
                        @if ($privateLeague->owner->usernameHandle() && $privateLeague->owner->displayName() !== $privateLeague->owner->usernameHandle())
                            <p class="text-xs text-gray-400">{{ $privateLeague->owner->usernameHandle() }}</p>
                        @endif
                    </div>
                </div>

                <div class="space-y-5 p-6">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="rounded-md bg-indigo-50 p-4">
                            <p class="text-xs font-medium uppercase tracking-wide text-indigo-700">
                                {{ __('Codigo') }}
                            </p>
                            <p class="mt-2 text-2xl font-bold tracking-wide text-indigo-950">
                                {{ $privateLeague->code }}
                            </p>
                        </div>

                        <div class="rounded-md bg-emerald-50 p-4">
                            <p class="text-xs font-medium uppercase tracking-wide text-emerald-700">
                                {{ __('Estado') }}
                            </p>
                            <p class="mt-2 text-xl font-bold text-emerald-950">
                                {{ __('Aprobacion requerida') }}
                            </p>
                        </div>
                    </div>

                    @if ($isOwner)
                        <div class="rounded-md bg-amber-50 p-4 text-sm text-amber-900 ring-1 ring-amber-200">
                            {{ __('Esta es tu liga. No necesitas solicitar ingreso.') }}
                        </div>

                        <a
                            href="{{ route('private-leagues.show', $privateLeague) }}"
                            class="inline-flex w-full items-center justify-center rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 sm:w-auto"
                        >
                            {{ __('Ver mi liga') }}
                        </a>
                    @elseif ($isActiveMember)
                        <div class="rounded-md bg-emerald-50 p-4 text-sm text-emerald-900 ring-1 ring-emerald-200">
                            {{ __('Ya sos miembro activo de esta liga.') }}
                        </div>

                        <a
                            href="{{ route('private-leagues.show', $privateLeague) }}"
                            class="inline-flex w-full items-center justify-center rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 sm:w-auto"
                        >
                            {{ __('Ver liga') }}
                        </a>
                    @elseif ($pendingJoinRequest)
                        <div class="rounded-md bg-amber-50 p-4 text-sm text-amber-900 ring-1 ring-amber-200">
                            {{ __('Ya tenes una solicitud de ingreso pendiente para esta liga. El dueño debe aprobarla.') }}
                        </div>
                    @elseif ($activeMembershipsCount >= 3)
                        <div class="rounded-md bg-gray-100 p-4 text-sm text-gray-700">
                            {{ __('Ya alcanzaste el limite de 3 ligas privadas activas.') }}
                        </div>
                    @else
                        @if ($wasRemoved)
                            <div class="rounded-md bg-gray-100 p-4 text-sm text-gray-700">
                                {{ __('Habias sido removido de esta liga. Podes enviar una nueva solicitud de ingreso, pero el dueño debe aprobarla nuevamente.') }}
                            </div>
                        @endif

                        <p class="text-sm text-gray-600">
                            {{ __('Enviar una solicitud de ingreso no te agrega automaticamente. El dueño de la liga tiene que aprobar tu acceso.') }}
                        </p>

                        <form method="POST" action="{{ route('private-leagues.join-requests.store', $privateLeague) }}">
                            @csrf
                            <button
                                type="submit"
                                class="inline-flex w-full items-center justify-center rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 sm:w-auto"
                            >
                                {{ __('Solicitar ingreso') }}
                            </button>
                        </form>
                    @endif
                </div>
            </article>
        </div>
    </div>
</x-app-layout>
