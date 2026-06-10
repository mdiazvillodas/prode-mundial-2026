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
                        <div class="flex max-w-full items-center gap-2 self-start sm:self-auto">
                            <div class="inline-flex min-w-0 items-center gap-2 rounded-full bg-indigo-50 px-3 py-1.5 text-xs font-bold text-indigo-900 ring-1 ring-indigo-100">
                                <span class="shrink-0 text-indigo-700">{{ __('Código:') }}</span>
                                <span class="truncate font-black tracking-wide">{{ $privateLeague->code }}</span>
                                <button
                                    type="button"
                                    data-copy-value="{{ $privateLeague->code }}"
                                    class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-indigo-700 transition hover:bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                    aria-label="{{ __('Copiar código de liga') }}"
                                >
                                    <span class="sr-only">{{ __('Copiar código de liga') }}</span>
                                    <svg data-copy-icon class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 8h10v10H8z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 16H5a1 1 0 01-1-1V5a1 1 0 011-1h10a1 1 0 011 1v1" />
                                    </svg>
                                    <svg data-copy-check class="hidden h-4 w-4 text-emerald-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M5 13l4 4L19 7" />
                                    </svg>
                                </button>
                            </div>

                            <button
                                type="button"
                                x-data
                                x-on:click="$dispatch('open-modal', 'league-owner-info')"
                                class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-blue-100 bg-white text-blue-950 shadow-sm shadow-blue-950/5 transition hover:bg-blue-50 hover:text-blue-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                aria-label="{{ __('Ver información de la liga') }}"
                            >
                                <span class="sr-only">{{ __('Ver información de la liga') }}</span>
                                <span class="inline-flex h-5 w-5 items-center justify-center rounded-full border-2 border-current text-xs font-black leading-none">i</span>
                            </button>
                        </div>
                    @endif
                </div>
            </section>

            <x-ranking-table
                :entries="$leaderboard"
                :context-label="__('Miembro activo')"
            />

            @if ($privateLeague->owner_id === auth()->id())
                <x-modal name="league-owner-info" maxWidth="lg" focusable>
                    <div class="space-y-5 p-5 sm:p-6">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <p class="text-xs font-black uppercase tracking-wide text-indigo-700">
                                    {{ __('Información de la liga') }}
                                </p>
                                <h3 class="mt-1 text-xl font-black leading-tight text-blue-950">
                                    {{ $privateLeague->name }}
                                </h3>
                            </div>

                            <button
                                type="button"
                                x-data
                                x-on:click="$dispatch('close-modal', 'league-owner-info')"
                                class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-slate-100 text-slate-500 transition hover:bg-slate-200 hover:text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                aria-label="{{ __('Cerrar') }}"
                            >
                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>

                        <section class="min-w-0 max-w-full space-y-3 overflow-hidden rounded-xl bg-indigo-50/70 p-4 ring-1 ring-indigo-100">
                            <div class="min-w-0">
                                <p class="text-xs font-black uppercase tracking-wide text-indigo-700">
                                    {{ __('Invitación') }}
                                </p>
                                <p class="mt-1 text-sm font-semibold text-slate-600">
                                    {{ __('Tus amigos pueden pedir acceso con el código o el enlace. El ingreso requiere tu aprobación.') }}
                                </p>
                            </div>

                            <div class="grid min-w-0 max-w-full gap-2">
                                <div class="flex min-w-0 max-w-full items-center gap-2 overflow-hidden rounded-lg bg-white px-3 py-2 ring-1 ring-indigo-100">
                                    <span class="shrink-0 text-xs font-black uppercase tracking-wide text-indigo-700">{{ __('Código') }}</span>
                                    <span class="block min-w-0 flex-1 truncate text-sm font-black tracking-wide text-blue-950">{{ $privateLeague->code }}</span>
                                    <button
                                        type="button"
                                        data-copy-value="{{ $privateLeague->code }}"
                                        class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-indigo-700 transition hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                        aria-label="{{ __('Copiar código de liga') }}"
                                    >
                                        <span class="sr-only">{{ __('Copiar código de liga') }}</span>
                                        <svg data-copy-icon class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 8h10v10H8z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 16H5a1 1 0 01-1-1V5a1 1 0 011-1h10a1 1 0 011 1v1" />
                                        </svg>
                                        <svg data-copy-check class="hidden h-4 w-4 text-emerald-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </button>
                                </div>

                                <div class="flex min-w-0 max-w-full items-center gap-2 overflow-hidden rounded-lg bg-white px-3 py-2 ring-1 ring-indigo-100">
                                    <span class="block min-w-0 flex-1 overflow-hidden truncate whitespace-nowrap text-sm font-semibold text-slate-600">{{ $invitationUrl }}</span>
                                    <button
                                        type="button"
                                        data-copy-value="{{ $invitationUrl }}"
                                        class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-indigo-700 transition hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                        aria-label="{{ __('Copiar enlace de invitación') }}"
                                    >
                                        <span class="sr-only">{{ __('Copiar enlace de invitación') }}</span>
                                        <svg data-copy-icon class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 8h10v10H8z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 16H5a1 1 0 01-1-1V5a1 1 0 011-1h10a1 1 0 011 1v1" />
                                        </svg>
                                        <svg data-copy-check class="hidden h-4 w-4 text-emerald-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </section>

                        <section>
                            <h4 class="text-base font-black text-blue-950">
                                {{ __('Miembros') }}
                            </h4>

                            <div class="mt-3 space-y-2">
                                @foreach ($privateLeague->memberships as $membership)
                                    <div class="flex flex-col gap-3 rounded-xl border border-slate-100 bg-slate-50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-black text-gray-950">
                                                {{ $membership->user->displayName() }}
                                            </p>
                                            @if ($membership->user->usernameHandle() && $membership->user->displayName() !== $membership->user->usernameHandle())
                                                <p class="truncate text-xs font-medium text-gray-500">
                                                    {{ $membership->user->usernameHandle() }}
                                                </p>
                                            @endif
                                        </div>

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
                                @endforeach
                            </div>
                        </section>

                        <section>
                            <h4 class="text-base font-black text-blue-950">
                                {{ __('Actividad reciente') }}
                            </h4>

                            @if ($privateLeague->auditLogs->isEmpty())
                                <p class="mt-2 text-sm text-gray-600">
                                    {{ __('Todavía no hay actividad de miembros para esta liga.') }}
                                </p>
                            @else
                                <div class="mt-3 space-y-2">
                                    @foreach ($privateLeague->auditLogs as $auditLog)
                                        <div class="rounded-xl border border-slate-100 bg-slate-50 p-3">
                                            <p class="text-sm font-black text-gray-950">
                                                {{ __('Miembro removido') }}
                                            </p>
                                            <p class="mt-1 text-sm text-gray-600">
                                                {{ $auditLog->actor->displayName() }} {{ __('removió a') }} {{ $auditLog->target->displayName() }}
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
                </x-modal>
            @endif
        </div>
    </div>

    @if ($privateLeague->owner_id === auth()->id())
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const copyText = async (text) => {
                    try {
                        await navigator.clipboard.writeText(text);
                    } catch (error) {
                        const target = document.createElement('textarea');

                        target.value = text;
                        target.setAttribute('readonly', '');
                        target.classList.add('sr-only');
                        document.body.appendChild(target);
                        target.select();
                        document.execCommand('copy');
                        target.remove();
                    }
                };

                document.querySelectorAll('[data-copy-value]').forEach((button) => {
                    button.addEventListener('click', async () => {
                        await copyText(button.dataset.copyValue);

                        const icon = button.querySelector('[data-copy-icon]');
                        const check = button.querySelector('[data-copy-check]');

                        if (! icon || ! check) {
                            return;
                        }

                        icon.classList.add('hidden');
                        check.classList.remove('hidden');
                        window.setTimeout(() => {
                            check.classList.add('hidden');
                            icon.classList.remove('hidden');
                        }, 1500);
                    });
                });
            });
        </script>
    @endif
</x-app-layout>
