<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Liga privada') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-5xl space-y-4 px-4 sm:px-6 lg:px-8">
            <a href="{{ route('leagues.index') }}" class="inline-flex items-center text-sm font-black text-blue-700 hover:text-blue-600">
                {{ __('← Volver a ligas') }}
            </a>

            <section class="rounded-2xl bg-white p-4 shadow-sm shadow-blue-900/5 ring-1 ring-blue-100 sm:p-5">
                <p class="text-xs font-black uppercase tracking-wide text-indigo-700">
                    {{ __('Liga privada') }}
                </p>
                <div class="mt-1 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <h3 class="min-w-0 text-2xl font-black leading-tight text-blue-950 sm:text-3xl">
                        {{ $privateLeague->name }}
                    </h3>

                    @if ($privateLeague->owner_id === auth()->id())
                        <div class="inline-flex max-w-full items-center gap-2 self-start rounded-full bg-indigo-50 px-3 py-1.5 text-xs font-bold text-indigo-900 ring-1 ring-indigo-100 sm:self-auto">
                            <span class="shrink-0 text-indigo-700">{{ __('Código:') }}</span>
                            <span class="truncate font-black tracking-wide">{{ $privateLeague->code }}</span>
                            <button
                                type="button"
                                data-copy-league-code="{{ $privateLeague->code }}"
                                class="shrink-0 rounded-full px-2 py-0.5 font-black text-indigo-700 transition hover:bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                            >
                                {{ __('Copiar') }}
                            </button>
                        </div>
                    @endif
                </div>
                @if ($privateLeague->owner_id === auth()->id())
                    <p data-copy-code-status class="mt-2 hidden text-xs font-bold text-emerald-700">
                        {{ __('Código copiado') }}
                    </p>
                @endif
            </section>

            <x-ranking-table
                :entries="$leaderboard"
                :context-label="__('Miembro activo')"
            />

            @if ($privateLeague->owner_id === auth()->id())
                <details class="group rounded-2xl bg-white shadow-sm shadow-blue-900/5 ring-1 ring-blue-100">
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
                        <section class="grid gap-3 sm:grid-cols-3">
                            <div class="rounded-xl bg-emerald-50 px-4 py-3">
                                <p class="text-xs font-bold uppercase tracking-wide text-emerald-700">
                                    {{ __('Estado') }}
                                </p>
                                <p class="mt-1 text-sm font-black capitalize text-emerald-950">
                                    {{ $privateLeague->status }}
                                </p>
                            </div>

                            <div class="rounded-xl bg-indigo-50 px-4 py-3">
                                <p class="text-xs font-bold uppercase tracking-wide text-indigo-700">
                                    {{ __('Miembros activos') }}
                                </p>
                                <p class="mt-1 text-sm font-black text-indigo-950">
                                    {{ $privateLeague->memberships->count() }}
                                </p>
                            </div>

                            <div class="rounded-xl bg-slate-50 px-4 py-3">
                                <p class="text-xs font-bold uppercase tracking-wide text-slate-600">
                                    {{ __('Codigo') }}
                                </p>
                                <p class="mt-1 text-sm font-black tracking-wide text-slate-950">
                                    {{ $privateLeague->code }}
                                </p>
                            </div>
                        </section>

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
                                                        {{ $joinRequest->user->displayName() }}
                                                    </p>
                                                    @if ($joinRequest->user->usernameHandle() && $joinRequest->user->displayName() !== $joinRequest->user->usernameHandle())
                                                        <p class="text-xs font-medium text-gray-500">
                                                            {{ $joinRequest->user->usernameHandle() }}
                                                        </p>
                                                    @endif
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
                                                {{ $membership->user->displayName() }}
                                            </p>
                                            @if ($membership->user->usernameHandle() && $membership->user->displayName() !== $membership->user->usernameHandle())
                                                <p class="text-xs font-medium text-gray-500">
                                                    {{ $membership->user->usernameHandle() }}
                                                </p>
                                            @endif
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
                                                {{ $auditLog->actor->displayName() }} {{ __('removio a') }} {{ $auditLog->target->displayName() }}
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
                const codeButton = document.querySelector('[data-copy-league-code]');
                const codeStatus = document.querySelector('[data-copy-code-status]');

                const copyText = async (text, fallbackInput = null) => {
                    try {
                        await navigator.clipboard.writeText(text);
                    } catch (error) {
                        const target = fallbackInput ?? document.createElement('textarea');
                        const shouldRemoveTarget = ! fallbackInput;

                        if (shouldRemoveTarget) {
                            target.value = text;
                            target.setAttribute('readonly', '');
                            target.classList.add('sr-only');
                            document.body.appendChild(target);
                        }

                        target.select();
                        document.execCommand('copy');

                        if (shouldRemoveTarget) {
                            target.remove();
                        }
                    }
                };

                if (codeButton && codeStatus) {
                    codeButton.addEventListener('click', async () => {
                        await copyText(codeButton.dataset.copyLeagueCode);

                        codeStatus.classList.remove('hidden');
                        window.setTimeout(() => codeStatus.classList.add('hidden'), 2500);
                    });
                }

                if (! button || ! input || ! status) {
                    return;
                }

                button.addEventListener('click', async () => {
                    await copyText(input.value, input);

                    status.classList.remove('hidden');
                    window.setTimeout(() => status.classList.add('hidden'), 2500);
                });
            });
        </script>
    @endif
</x-app-layout>
