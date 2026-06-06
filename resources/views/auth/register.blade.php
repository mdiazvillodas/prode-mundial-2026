<x-guest-layout>
    <x-slot name="title">
        {{ __('Crear cuenta') }}
    </x-slot>

    <x-slot name="subtitle">
        {{ __('Registrate para sumar puntos, entrar a la Liga general y competir en ligas privadas.') }}
    </x-slot>

    <x-google-auth-button />

    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf
        <div class="absolute left-[-9999px] top-auto h-px w-px overflow-hidden" aria-hidden="true">
            <label for="website">Website</label>
            <input id="website" type="text" name="website" value="" tabindex="-1" autocomplete="off">
        </div>

        <div>
            <x-input-label for="name" :value="__('Nombre')" class="font-bold text-slate-700" />
            <x-text-input id="name" class="mt-2 block w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-base shadow-sm focus:border-blue-600 focus:bg-white focus:ring-blue-600" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="username" :value="__('Nombre de usuario')" class="font-bold text-slate-700" />
            <x-text-input id="username" class="mt-2 block w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-base shadow-sm focus:border-blue-600 focus:bg-white focus:ring-blue-600" type="text" name="username" :value="old('username')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('username')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" class="font-bold text-slate-700" />
            <x-text-input id="email" class="mt-2 block w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-base shadow-sm focus:border-blue-600 focus:bg-white focus:ring-blue-600" type="email" name="email" :value="old('email')" required autocomplete="email" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" :value="__('Contraseña')" class="font-bold text-slate-700" />

            <x-text-input id="password" class="mt-2 block w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-base shadow-sm focus:border-blue-600 focus:bg-white focus:ring-blue-600"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password_confirmation" :value="__('Confirmar contraseña')" class="font-bold text-slate-700" />

            <x-text-input id="password_confirmation" class="mt-2 block w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-base shadow-sm focus:border-blue-600 focus:bg-white focus:ring-blue-600"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="space-y-4">
            <x-primary-button class="flex w-full justify-center rounded-xl bg-emerald-600 px-5 py-3 text-sm font-black normal-case tracking-wide text-white shadow-lg shadow-emerald-900/20 hover:bg-emerald-500 focus:bg-emerald-500 active:bg-emerald-700 focus:ring-emerald-600">
                {{ __('Registrarse') }}
            </x-primary-button>

            <p class="text-center text-sm text-slate-600">
                {{ __('¿Ya tenés cuenta?') }}
                <a class="font-black text-blue-700 hover:text-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2" href="{{ route('login') }}">
                    {{ __('Ya tengo cuenta') }}
                </a>
            </p>
        </div>
    </form>
</x-guest-layout>
