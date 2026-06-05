<x-guest-layout>
    <x-slot name="title">
        {{ __('Verificá tu email') }}
    </x-slot>

    <x-slot name="subtitle">
        {{ __('Antes de empezar, verificá tu email con el código de 6 dígitos que te enviamos. Si no lo recibiste, podemos enviarte otro.') }}
    </x-slot>

    @if (session('status') == 'verification-code-sent')
        <div class="mb-4 rounded-2xl bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-800 ring-1 ring-emerald-100">
            {{ __('Enviamos un nuevo código de verificación al email que usaste al registrarte.') }}
        </div>
    @endif

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <div>
                <x-primary-button class="flex w-full justify-center rounded-xl bg-blue-700 px-5 py-3 text-sm font-black normal-case tracking-wide text-white shadow-lg shadow-blue-900/20 hover:bg-blue-600 focus:bg-blue-600 active:bg-blue-800 focus:ring-blue-600 sm:w-auto">
                    {{ __('Reenviar código de verificación') }}
                </x-primary-button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="text-sm font-black text-blue-700 hover:text-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2">
                {{ __('Cerrar sesión') }}
            </button>
        </form>
    </div>
</x-guest-layout>
