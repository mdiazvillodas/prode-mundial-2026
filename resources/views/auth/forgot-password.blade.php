<x-guest-layout>
    <x-slot name="title">
        {{ __('Olvidé mi contraseña') }}
    </x-slot>

    <x-slot name="subtitle">
        {{ __('Indicá tu email y te enviaremos un enlace para elegir una contraseña nueva.') }}
    </x-slot>

    <x-auth-session-status class="mb-4 rounded-2xl bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-800 ring-1 ring-emerald-100" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email')" class="font-bold text-slate-700" />
            <x-text-input id="email" class="mt-2 block w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-base shadow-sm focus:border-blue-600 focus:bg-white focus:ring-blue-600" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="space-y-4">
            <x-primary-button class="flex w-full justify-center rounded-xl bg-blue-700 px-5 py-3 text-sm font-black normal-case tracking-wide text-white shadow-lg shadow-blue-900/20 hover:bg-blue-600 focus:bg-blue-600 active:bg-blue-800 focus:ring-blue-600">
                {{ __('Enviar enlace de recuperación') }}
            </x-primary-button>

            <p class="text-center text-sm text-slate-600">
                <a href="{{ route('login') }}" class="font-black text-blue-700 hover:text-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2">
                    {{ __('Volver a iniciar sesión') }}
                </a>
            </p>
        </div>
    </form>
</x-guest-layout>
