<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Liga privada') }}
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    {{ __('Tabla de posiciones de miembros activos y gestion de la liga.') }}
                </p>
            </div>

            <a
                href="{{ route('leagues.index') }}"
                class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
            >
                {{ __('Ver ligas') }}
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-5xl space-y-5 px-4 sm:px-6 lg:px-8">
            <article class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-100">
                <div class="border-b border-gray-100 bg-gray-950 px-6 py-6 text-white">
                    <p class="text-sm font-bold uppercase tracking-wide text-indigo-200">
                        {{ __('Liga privada') }}
                    </p>
                    <h3 class="mt-2 text-3xl font-black">
                        {{ $privateLeague->name }}
                    </h3>
                    <p class="mt-2 text-sm text-gray-300">
                        {{ __('Dueño') }} {{ '@'.$privateLeague->owner->username }}
                    </p>
                </div>

                <div class="grid gap-4 p-5 sm:grid-cols-3">
                    <div class="rounded-xl bg-emerald-50 p-4">
                        <p class="text-xs font-bold uppercase tracking-wide text-emerald-700">
                            {{ __('Estado') }}
                        </p>
                        <p class="mt-2 text-xl font-black capitalize text-emerald-950">
                            {{ $privateLeague->status }}
                        </p>
                    </div>

                    <div class="rounded-xl bg-indigo-50 p-4">
                        <p class="text-xs font-bold uppercase tracking-wide text-indigo-700">
                            {{ __('Miembros activos') }}
                        </p>
                        <p class="mt-2 text-xl font-black text-indigo-950">
                            {{ $privateLeague->memberships->count() }}
                        </p>
                    </div>

                    <div class="rounded-xl bg-gray-50 p-4">
                        <p class="text-xs font-bold uppercase tracking-wide text-gray-600">
                            {{ __('Codigo') }}
                        </p>
                        <p class="mt-2 text-xl font-black tracking-wide text-gray-950">
                            {{ $privateLeague->code }}
                        </p>
                    </div>
                </div>
            </article>

            <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-bold uppercase tracking-wide text-indigo-700">
                            {{ __('Ranking de la liga') }}
                        </p>
                        <h3 class="mt-1 text-2xl font-black text-gray-950">
                            {{ __('Puntos de miembros activos') }}
                        </h3>
                    </div>

                    <p class="text-sm text-gray-500">
                        {{ __('Solo predicciones puntuadas') }}
                    </p>
                </div>

                <div class="mt-5 space-y-3">
                    @foreach ($leaderboard as $entry)
                        <article class="{{ $loop->first ? 'border-amber-200 bg-amber-50' : 'border-gray-100 bg-gray-50' }} rounded-2xl border p-4">
                            <div class="flex items-start gap-3">
                                <div class="{{ $loop->first ? 'bg-amber-500 text-white' : 'bg-gray-950 text-white' }} flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-sm font-black">
                                    {{ $loop->iteration }}
                                </div>

                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-col gap-1 sm:flex-row sm:items-start sm:justify-between">
                                        <div class="min-w-0">
                                            <h4 class="truncate font-black text-gray-950">
                                                {{ '@'.$entry->username }}
                                            </h4>
                                            @if ($loop->first)
                                                <p class="text-xs font-bold uppercase tracking-wide text-amber-700">
                                                    {{ __('Primer puesto') }}
                                                </p>
                                            @else
                                                <p class="text-xs font-bold uppercase tracking-wide text-gray-500">
                                                    {{ __('Miembro activo') }}
                                                </p>
                                            @endif
                                        </div>

                                        <div class="text-left sm:text-right">
                                            <p class="text-3xl font-black text-gray-950">
                                                {{ (int) $entry->total_points }}
                                            </p>
                                            <p class="text-xs font-bold uppercase tracking-wide text-gray-500">
                                            {{ __('Puntos') }}
                                            </p>
                                        </div>
                                    </div>

                                    <dl class="mt-4 grid grid-cols-3 gap-2 text-center">
                                        <div class="rounded-xl bg-white px-2 py-3 ring-1 ring-gray-100">
                                            <dt class="text-[11px] font-bold uppercase tracking-wide text-gray-500">
                                                {{ __('Resultados exactos') }}
                                            </dt>
                                            <dd class="mt-1 text-lg font-black text-gray-950">
                                                {{ (int) $entry->exact_results_count }}
                                            </dd>
                                        </div>

                                        <div class="rounded-xl bg-white px-2 py-3 ring-1 ring-gray-100">
                                            <dt class="text-[11px] font-bold uppercase tracking-wide text-gray-500">
                                                {{ __('Tendencias') }}
                                            </dt>
                                            <dd class="mt-1 text-lg font-black text-gray-950">
                                                {{ (int) $entry->trend_count }}
                                            </dd>
                                        </div>

                                        <div class="rounded-xl bg-white px-2 py-3 ring-1 ring-gray-100">
                                            <dt class="text-[11px] font-bold uppercase tracking-wide text-gray-500">
                                                {{ __('Predicciones puntuadas') }}
                                            </dt>
                                            <dd class="mt-1 text-lg font-black text-gray-950">
                                                {{ (int) $entry->scored_predictions_count }}
                                            </dd>
                                        </div>
                                    </dl>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>

            @if ($privateLeague->owner_id === auth()->id())
                <details class="group rounded-2xl bg-white shadow-sm ring-1 ring-gray-100">
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-4 px-6 py-5">
                        <div>
                            <p class="text-sm font-bold uppercase tracking-wide text-indigo-700">
                                {{ __('Gestionar liga') }}
                            </p>
                            <h3 class="mt-1 text-lg font-black text-gray-950">
                                {{ __('Invitaciones, solicitudes de ingreso y miembros') }}
                            </h3>
                        </div>
                        <span class="rounded-full bg-gray-100 px-3 py-1 text-sm font-bold text-gray-700 transition group-open:bg-indigo-600 group-open:text-white">
                            {{ __('Abrir') }}
                        </span>
                    </summary>

                    <div class="space-y-6 border-t border-gray-100 p-6">
                        <section>
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-sm font-bold uppercase tracking-wide text-emerald-700">
                                        {{ __('Invitacion') }}
                                    </p>
                                    <h4 class="mt-1 text-base font-black text-gray-950">
                                        {{ __('Compartir liga') }}
                                    </h4>
                                </div>
                                <div class="rounded-full bg-indigo-50 px-3 py-1 text-sm font-bold tracking-wide text-indigo-800">
                                    {{ $privateLeague->code }}
                                </div>
                            </div>

                            <p class="mt-2 text-sm text-gray-600">
                                {{ __('Compartir este link permite que otros usuarios soliciten acceso. El ingreso sigue requiriendo tu aprobacion.') }}
                            </p>

                            <div class="mt-4 flex flex-col gap-3 sm:flex-row">
                                <input
                                    id="league-invitation-url"
                                    type="text"
                                    readonly
                                    value="{{ $invitationUrl }}"
                                    class="block w-full rounded-md border-gray-300 bg-gray-50 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                                >

                                <button
                                    type="button"
                                    data-copy-invite
                                    class="inline-flex items-center justify-center rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
                                >
                                    {{ __('Copiar link') }}
                                </button>
                            </div>

                            <p data-copy-invite-status class="mt-2 hidden text-sm font-medium text-emerald-700">
                                {{ __('Link copiado') }}
                            </p>
                        </section>

                        <section>
                            <h4 class="text-base font-black text-gray-950">
                                {{ __('Solicitudes de ingreso pendientes') }}
                            </h4>

                            @if ($privateLeague->joinRequests->isEmpty())
                                <p class="mt-2 text-sm text-gray-600">
                                    {{ __('Todavia no hay solicitudes de ingreso para esta liga.') }}
                                </p>
                            @else
                                <div class="mt-4 space-y-3">
                                    @foreach ($privateLeague->joinRequests as $joinRequest)
                                        <div class="rounded-xl border border-gray-100 bg-gray-50 p-4">
                                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                                <div>
                                                    <p class="text-sm font-bold text-gray-950">
                                                        {{ '@'.$joinRequest->user->username }}
                                                    </p>
                                                    <p class="text-xs text-gray-500">
                                                        {{ __('Solicitud de ingreso enviada el') }} {{ $joinRequest->created_at->format('d/m/Y H:i') }}
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
                        </section>

                        <section>
                            <h4 class="text-base font-black text-gray-950">
                                {{ __('Miembros') }}
                            </h4>

                            <div class="mt-4 space-y-3">
                                @foreach ($privateLeague->memberships as $membership)
                                    <div class="flex flex-col gap-3 rounded-xl border border-gray-100 bg-gray-50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                                        <div>
                                            <p class="text-sm font-bold text-gray-950">
                                                {{ '@'.$membership->user->username }}
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                {{ $membership->user_id === $privateLeague->owner_id ? __('Dueño') : __('Miembro') }}
                                            </p>
                                        </div>

                                        <div class="flex flex-col gap-2 sm:items-end">
                                            <p class="text-xs font-medium text-gray-500">
                                                {{ optional($membership->joined_at)->format('d/m/Y') }}
                                            </p>

                                            @if ($membership->user_id !== $privateLeague->owner_id)
                                                <form method="POST" action="{{ route('private-leagues.members.remove', [$privateLeague, $membership->user]) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button
                                                        type="submit"
                                                        class="inline-flex items-center justify-center rounded-md border border-red-200 bg-white px-3 py-1.5 text-xs font-semibold text-red-700 transition hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                                                    >
                                                        {{ __('Remover miembro') }}
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </section>

                        <section>
                            <h4 class="text-base font-black text-gray-950">
                                {{ __('Actividad reciente') }}
                            </h4>

                            @if ($privateLeague->auditLogs->isEmpty())
                                <p class="mt-2 text-sm text-gray-600">
                                    {{ __('Todavia no hay actividad de miembros para esta liga.') }}
                                </p>
                            @else
                                <div class="mt-4 space-y-3">
                                    @foreach ($privateLeague->auditLogs as $auditLog)
                                        <div class="rounded-xl border border-gray-100 bg-gray-50 p-4">
                                            <p class="text-sm font-bold text-gray-950">
                                                {{ __('Miembro removido') }}
                                            </p>
                                            <p class="mt-1 text-sm text-gray-600">
                                                {{ '@'.$auditLog->actor->username }} {{ __('removio a') }} {{ '@'.$auditLog->target->username }}
                                            </p>
                                            <p class="mt-2 text-xs text-gray-500">
                                                {{ $auditLog->created_at->format('d/m/Y H:i') }}
                                            </p>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </section>
                    </div>
                </details>
            @endif
        </div>
    </div>

    @if ($privateLeague->owner_id === auth()->id())
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const button = document.querySelector('[data-copy-invite]');
                const input = document.getElementById('league-invitation-url');
                const status = document.querySelector('[data-copy-invite-status]');

                if (! button || ! input || ! status) {
                    return;
                }

                button.addEventListener('click', async () => {
                    try {
                        await navigator.clipboard.writeText(input.value);
                    } catch (error) {
                        input.select();
                        document.execCommand('copy');
                    }

                    status.classList.remove('hidden');
                    window.setTimeout(() => status.classList.add('hidden'), 2500);
                });
            });
        </script>
    @endif
</x-app-layout>
