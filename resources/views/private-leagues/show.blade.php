<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Mi liga privada') }}
            </h2>

            <a
                href="{{ route('private-leagues.search') }}"
                class="inline-flex items-center justify-center rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
            >
                {{ __('Buscar ligas') }}
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-4xl space-y-5 px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="rounded-md bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 ring-1 ring-emerald-200">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded-md bg-red-50 px-4 py-3 text-sm font-medium text-red-800 ring-1 ring-red-200">
                    {{ $errors->first() }}
                </div>
            @endif

            <article class="overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-gray-100">
                <div class="border-b border-gray-100 bg-gray-950 px-6 py-6 text-white">
                    <p class="text-sm font-medium uppercase tracking-wide text-indigo-200">
                        {{ __('Liga privada') }}
                    </p>
                    <h3 class="mt-2 text-2xl font-bold">
                        {{ $privateLeague->name }}
                    </h3>
                </div>

                <div class="grid gap-4 p-6 sm:grid-cols-3">
                    <div class="rounded-md bg-indigo-50 p-4">
                        <p class="text-xs font-medium uppercase tracking-wide text-indigo-700">
                            {{ __('Codigo') }}
                        </p>
                        <p class="mt-2 text-2xl font-bold tracking-wide text-indigo-950">
                            {{ $privateLeague->code }}
                        </p>
                    </div>

                    <div class="rounded-md bg-gray-50 p-4">
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-600">
                            {{ __('Owner') }}
                        </p>
                        <p class="mt-2 text-xl font-bold text-gray-950">
                            {{ '@'.$privateLeague->owner->username }}
                        </p>
                    </div>

                    <div class="rounded-md bg-emerald-50 p-4">
                        <p class="text-xs font-medium uppercase tracking-wide text-emerald-700">
                            {{ __('Estado') }}
                        </p>
                        <p class="mt-2 text-xl font-bold capitalize text-emerald-950">
                            {{ $privateLeague->status }}
                        </p>
                    </div>
                </div>
            </article>

            <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-100">
                <h3 class="text-lg font-semibold text-gray-900">
                    {{ __('Miembros') }}
                </h3>

                <div class="mt-4 space-y-3">
                    @foreach ($privateLeague->memberships as $membership)
                        <div class="flex items-center justify-between rounded-md border border-gray-100 bg-gray-50 px-4 py-3">
                            <div>
                                <p class="text-sm font-semibold text-gray-950">
                                    {{ '@'.$membership->user->username }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{ $membership->user_id === $privateLeague->owner_id ? __('Owner') : __('Miembro') }}
                                </p>
                            </div>

                            <p class="text-xs font-medium text-gray-500">
                                {{ optional($membership->joined_at)->format('d/m/Y') }}
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>

            @if ($privateLeague->owner_id === auth()->id())
                <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-100">
                    <h3 class="text-lg font-semibold text-gray-900">
                        {{ __('Solicitudes pendientes') }}
                    </h3>

                    @if ($privateLeague->joinRequests->isEmpty())
                        <p class="mt-2 text-sm text-gray-600">
                            {{ __('Todavia no hay solicitudes para esta liga.') }}
                        </p>
                    @else
                        <div class="mt-4 space-y-3">
                            @foreach ($privateLeague->joinRequests as $joinRequest)
                                <div class="rounded-md border border-gray-100 bg-gray-50 p-4">
                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                        <div>
                                            <p class="text-sm font-semibold text-gray-950">
                                                {{ '@'.$joinRequest->user->username }}
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                {{ __('Solicito acceso el') }} {{ $joinRequest->created_at->format('d/m/Y H:i') }}
                                            </p>
                                        </div>

                                        <div class="flex flex-col gap-2 sm:flex-row">
                                            <form method="POST" action="{{ route('private-leagues.join-requests.accept', [$privateLeague, $joinRequest]) }}">
                                                @csrf
                                                <button
                                                    type="submit"
                                                    class="inline-flex w-full items-center justify-center rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
                                                >
                                                    {{ __('Aceptar') }}
                                                </button>
                                            </form>

                                            <form method="POST" action="{{ route('private-leagues.join-requests.reject', [$privateLeague, $joinRequest]) }}">
                                                @csrf
                                                <button
                                                    type="submit"
                                                    class="inline-flex w-full items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-800 transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                                >
                                                    {{ __('Rechazar') }}
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif

            <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-100">
                <h3 class="text-lg font-semibold text-gray-900">
                    {{ __('Proximos pasos') }}
                </h3>
                <p class="mt-2 text-sm text-gray-600">
                    {{ __('El ranking privado por puntos y la gestion avanzada de miembros se van a sumar en tickets futuros.') }}
                </p>
                <div class="mt-5 grid gap-3 sm:grid-cols-2">
                    <div class="rounded-md border border-dashed border-gray-300 p-4 text-sm text-gray-600">
                        {{ __('Ranking privado por puntos') }}
                    </div>
                    <div class="rounded-md border border-dashed border-gray-300 p-4 text-sm text-gray-600">
                        {{ __('Gestion de miembros') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
