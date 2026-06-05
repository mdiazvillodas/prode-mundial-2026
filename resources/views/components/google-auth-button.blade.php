@php
    $isGoogleConfigured = filled(config('services.google.client_id'))
        && filled(config('services.google.client_secret'))
        && filled(config('services.google.redirect'));
@endphp

@if ($isGoogleConfigured)
    <div class="mb-5 space-y-4">
        <a
            href="{{ route('auth.google.redirect') }}"
            class="inline-flex w-full items-center justify-center gap-3 rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-800 shadow-sm transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2"
        >
            <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-blue-700 text-xs font-black text-white">
                G
            </span>
            {{ __('Continuar con Google') }}
        </a>

        <div class="flex items-center gap-3">
            <div class="h-px flex-1 bg-slate-200"></div>
            <span class="text-xs font-bold uppercase tracking-wide text-slate-400">
                {{ __('O usá email') }}
            </span>
            <div class="h-px flex-1 bg-slate-200"></div>
        </div>
    </div>
@endif
