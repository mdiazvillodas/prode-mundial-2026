<x-guest-layout>
    <x-slot name="title">
        {{ __('Verificá tu correo') }}
    </x-slot>

    <x-slot name="subtitle">
        {{ __('Revisá :email e ingresá el código de 6 dígitos. Vence en 15 minutos; si no llega, podés pedir uno nuevo.', ['email' => auth()->user()->email]) }}
    </x-slot>

    <form method="POST" action="{{ route('verification.code.verify') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="code" :value="__('Código de verificación')" class="font-bold text-slate-700" />
            <x-text-input
                id="code"
                class="mt-2 block w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-center text-2xl font-black tracking-[0.3em] text-blue-950 shadow-sm focus:border-blue-600 focus:bg-white focus:ring-blue-600"
                type="text"
                name="code"
                :value="old('code')"
                inputmode="numeric"
                maxlength="6"
                pattern="[0-9]{6}"
                required
                autofocus
                autocomplete="one-time-code"
            />
            <x-input-error :messages="$errors->get('code')" class="mt-2" />
        </div>

        <x-primary-button class="flex w-full justify-center rounded-xl bg-blue-700 px-5 py-3 text-sm font-black normal-case tracking-wide text-white shadow-lg shadow-blue-900/20 hover:bg-blue-600 focus:bg-blue-600 active:bg-blue-800 focus:ring-blue-600">
            {{ __('Verificar correo') }}
        </x-primary-button>
    </form>

    <div class="mt-6 space-y-4 border-t border-slate-100 pt-5">
        <form method="POST" action="{{ route('verification.code.resend') }}">
            @csrf

            <button type="submit" class="w-full rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-blue-700 shadow-sm transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2">
                {{ __('Reenviar código') }}
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="w-full text-center text-sm font-bold text-slate-500 transition hover:text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2">
                {{ __('Cerrar sesión') }}
            </button>
        </form>
    </div>
</x-guest-layout>
