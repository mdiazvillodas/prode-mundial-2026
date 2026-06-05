<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Prode Mundial 2026') }}</title>
        <link rel="icon" href="{{ asset('brand/favicon.ico') }}" sizes="any">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <main class="flex min-h-screen items-center justify-center bg-blue-950 px-4 py-10 text-white">
            <section class="w-full max-w-md rounded-3xl bg-white p-6 text-center shadow-2xl shadow-slate-950/30 ring-1 ring-white/70">
                <img
                    src="{{ asset('brand/p26-logo.svg') }}"
                    alt="{{ __('Logo de Prode') }}"
                    class="mx-auto h-16 w-auto"
                >

                <h1 class="mt-6 text-2xl font-black text-blue-950">
                    {{ __('Prode Mundial 2026') }}
                </h1>
                <p class="mt-2 text-sm text-slate-600">
                    {{ __('Entrá para cargar predicciones, sumar puntos y competir en ligas.') }}
                </p>

                <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-center">
                    <a
                        href="{{ route('login') }}"
                        class="inline-flex items-center justify-center rounded-xl bg-blue-700 px-5 py-3 text-sm font-black text-white shadow-lg shadow-blue-900/20 transition hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2"
                    >
                        {{ __('Iniciar sesión') }}
                    </a>

                    <a
                        href="{{ route('register') }}"
                        class="inline-flex items-center justify-center rounded-xl border border-emerald-200 bg-white px-5 py-3 text-sm font-black text-emerald-700 transition hover:bg-emerald-50 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2"
                    >
                        {{ __('Registrarse') }}
                    </a>
                </div>
            </section>
        </main>
    </body>
</html>
