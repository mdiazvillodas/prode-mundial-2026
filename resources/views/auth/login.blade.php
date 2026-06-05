<x-guest-layout>
    <x-slot name="title">
        {{ __('Iniciar sesión') }}
    </x-slot>

    <x-slot name="subtitle">
        {{ __('Entrá para cargar tus predicciones, ver tus puntos y seguir tus ligas.') }}
    </x-slot>

    <x-auth-session-status class="mb-4 rounded-2xl bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-800 ring-1 ring-emerald-100" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email')" class="font-bold text-slate-700" />
            <x-text-input id="email" class="mt-2 block w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-base shadow-sm focus:border-blue-600 focus:bg-white focus:ring-blue-600" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" :value="__('Contraseña')" class="font-bold text-slate-700" />

            <x-text-input id="password" class="mt-2 block w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-base shadow-sm focus:border-blue-600 focus:bg-white focus:ring-blue-600"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-slate-300 text-blue-700 shadow-sm focus:ring-blue-600" name="remember">
                <span class="ms-2 text-sm font-medium text-slate-600">{{ __('Recordarme') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm font-bold text-blue-700 hover:text-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2" href="{{ route('password.request') }}">
                    {{ __('Olvidé mi contraseña') }}
                </a>
            @endif
        </div>

        <div class="space-y-4">
            <x-primary-button class="flex w-full justify-center rounded-xl bg-blue-700 px-5 py-3 text-sm font-black normal-case tracking-wide text-white shadow-lg shadow-blue-900/20 hover:bg-blue-600 focus:bg-blue-600 active:bg-blue-800 focus:ring-blue-600">
                {{ __('Iniciar sesión') }}
            </x-primary-button>

            <p class="text-center text-sm text-slate-600">
                {{ __('¿Todavía no tenés cuenta?') }}
                <a href="{{ route('register') }}" class="font-black text-emerald-700 hover:text-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">
                    {{ __('Crear cuenta') }}
                </a>
            </p>
        </div>
    </form>
</x-guest-layout>
