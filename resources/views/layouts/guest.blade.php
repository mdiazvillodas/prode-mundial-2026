<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-slate-900 antialiased">
        <x-toasts />

        <div class="relative min-h-screen overflow-hidden bg-blue-950">
            <div class="absolute inset-0 bg-[linear-gradient(135deg,rgba(15,23,42,0.98),rgba(30,64,175,0.9)_48%,rgba(6,78,59,0.84))]"></div>
            <div class="absolute inset-0 bg-[linear-gradient(160deg,transparent_0%,transparent_42%,rgba(255,255,255,0.08)_42%,rgba(255,255,255,0.08)_46%,transparent_46%,transparent_100%)]"></div>
            <div class="absolute inset-x-0 bottom-0 h-44 bg-gradient-to-t from-slate-950/45 to-transparent"></div>

            <div class="relative mx-auto flex min-h-screen w-full max-w-6xl flex-col px-4 py-6 sm:px-6 lg:grid lg:grid-cols-[minmax(0,1fr)_28rem] lg:items-center lg:gap-12 lg:px-8">
                <section class="flex flex-1 flex-col justify-center py-6 text-white lg:min-h-screen lg:py-10">
                    <a href="/" class="inline-flex w-fit items-center gap-3 rounded-full bg-white/10 px-3 py-2 text-white shadow-sm ring-1 ring-white/15 transition hover:bg-white/15 focus:outline-none focus:ring-2 focus:ring-emerald-300 focus:ring-offset-2 focus:ring-offset-blue-950">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-emerald-400 text-sm font-black text-blue-950 shadow-sm shadow-emerald-950/20">
                            P26
                        </span>
                        <span class="text-sm font-black uppercase tracking-wide">
                            Prode Mundial 2026
                        </span>
                    </a>

                    <div class="mt-10 max-w-xl">
                        <p class="text-sm font-black uppercase tracking-wide text-emerald-200">
                            {{ __('Liga, puntos y orgullo futbolero') }}
                        </p>
                        <h1 class="mt-3 text-4xl font-black leading-tight sm:text-5xl lg:text-6xl">
                            {{ __('Pronosticá el Mundial con tus amigos.') }}
                        </h1>
                        <p class="mt-5 text-base font-medium leading-7 text-blue-50 sm:text-lg">
                            {{ __('Pronosticá partidos, sumá puntos y competí en ligas con tus amigos.') }}
                        </p>
                        <p class="mt-3 text-sm font-medium text-blue-100">
                            {{ __('Entretenimiento entre amigos, sin apuestas ni dinero real.') }}
                        </p>
                    </div>

                    <div class="mt-8 grid max-w-xl grid-cols-3 gap-3 text-blue-50">
                        <div class="rounded-2xl bg-white/10 p-4 ring-1 ring-white/15">
                            <p class="text-2xl font-black text-white">6</p>
                            <p class="mt-1 text-xs font-bold uppercase tracking-wide text-blue-100">{{ __('Puntos exactos') }}</p>
                        </div>
                        <div class="rounded-2xl bg-white/10 p-4 ring-1 ring-white/15">
                            <p class="text-2xl font-black text-white">3</p>
                            <p class="mt-1 text-xs font-bold uppercase tracking-wide text-blue-100">{{ __('Por tendencia') }}</p>
                        </div>
                        <div class="rounded-2xl bg-white/10 p-4 ring-1 ring-white/15">
                            <p class="text-2xl font-black text-white">{{ __('Ligas') }}</p>
                            <p class="mt-1 text-xs font-bold uppercase tracking-wide text-blue-100">{{ __('Con amigos') }}</p>
                        </div>
                    </div>
                </section>

                <main class="flex items-center pb-6 lg:pb-0">
                    <section class="w-full overflow-hidden rounded-3xl bg-white p-6 shadow-2xl shadow-slate-950/30 ring-1 ring-white/70 sm:p-8">
                        <div class="mb-6">
                            <p class="text-xs font-black uppercase tracking-wide text-indigo-700">
                                {{ $eyebrow ?? __('Prode Mundial 2026') }}
                            </p>
                            <h2 class="mt-2 text-3xl font-black text-blue-950">
                                {{ $title ?? __('Entrá a tu cuenta') }}
                            </h2>
                            @isset($subtitle)
                                <p class="mt-2 text-sm leading-6 text-slate-600">
                                    {{ $subtitle }}
                                </p>
                            @endisset
                        </div>

                        {{ $slot }}
                    </section>
                </main>
            </div>
        </div>
    </body>
</html>
