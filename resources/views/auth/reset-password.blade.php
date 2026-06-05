<x-guest-layout>
    <x-slot name="title">
        {{ __('Restablecer contraseña') }}
    </x-slot>

    <x-slot name="subtitle">
        {{ __('Elegí una contraseña nueva para volver a cargar tus predicciones.') }}
    </x-slot>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <x-input-label for="email" :value="__('Email')" class="font-bold text-slate-700" />
            <x-text-input id="email" class="mt-2 block w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-base shadow-sm focus:border-blue-600 focus:bg-white focus:ring-blue-600" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" :value="__('Contraseña')" class="font-bold text-slate-700" />
            <x-text-input id="password" class="mt-2 block w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-base shadow-sm focus:border-blue-600 focus:bg-white focus:ring-blue-600" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password_confirmation" :value="__('Confirmar contraseña')" class="font-bold text-slate-700" />

            <x-text-input id="password_confirmation" class="mt-2 block w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-base shadow-sm focus:border-blue-600 focus:bg-white focus:ring-blue-600"
                                type="password"
                                name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div>
            <x-primary-button class="flex w-full justify-center rounded-xl bg-blue-700 px-5 py-3 text-sm font-black normal-case tracking-wide text-white shadow-lg shadow-blue-900/20 hover:bg-blue-600 focus:bg-blue-600 active:bg-blue-800 focus:ring-blue-600">
                {{ __('Restablecer contraseña') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
