<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <img
                            src="{{ asset('brand/p26-logo.svg') }}"
                            alt="{{ __('Prode') }}"
                            class="block h-10 w-auto"
                        >
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Inicio') }}
                    </x-nav-link>
                    @if (Auth::user()->isAdmin())
                        <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.*')">
                            {{ __('Administracion') }}
                        </x-nav-link>
                    @endif
                    <x-nav-link :href="route('predictions.index')" :active="request()->routeIs('predictions.*')">
                        {{ __('Predicciones') }}
                    </x-nav-link>
                    <x-nav-link :href="route('leagues.index')" :active="request()->routeIs('leagues.index') || request()->routeIs('leaderboard.index') || request()->routeIs('private-leagues.*')">
                        {{ __('Ligas') }}
                    </x-nav-link>
                    <x-nav-link :href="route('calendar.index')" :active="request()->routeIs('calendar.index')">
                        {{ __('Calendario') }}
                    </x-nav-link>
                </div>
            </div>

            <div class="flex items-center gap-1.5 sm:gap-3">
                @if ($headerPendingJoinRequestsLeague)
                    @php
                        $pendingJoinRequests = $headerPendingJoinRequestsLeague->joinRequests;
                        $pendingJoinRequestsCount = $pendingJoinRequests->count();
                    @endphp

                    <button
                        type="button"
                        x-data
                        x-on:click="$dispatch('open-modal', 'pending-join-requests')"
                        class="relative inline-flex h-9 w-9 items-center justify-center rounded-full border border-blue-100 bg-white text-blue-950 shadow-sm shadow-blue-950/5 transition hover:bg-blue-50 hover:text-blue-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:h-10 sm:w-10"
                        aria-label="{{ __('Ver solicitudes pendientes') }}: {{ $pendingJoinRequestsCount }}"
                    >
                        <span class="sr-only">{{ __('Solicitudes pendientes') }}</span>
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 00-12 0v3.2a2 2 0 01-.6 1.4L4 17h5m6 0a3 3 0 01-6 0m6 0H9" />
                        </svg>
                        <span class="absolute -right-1 -top-1 inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-amber-400 px-1 text-[10px] font-black leading-none text-blue-950 ring-2 ring-white">
                            {{ $pendingJoinRequestsCount }}
                        </span>
                    </button>
                @endif

                <!-- Settings Dropdown -->
                <div class="hidden sm:flex sm:items-center">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                <div>{{ Auth::user()->name }}</div>

                                <div class="ms-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">
                                {{ __('Perfil') }}
                            </x-dropdown-link>

                            <!-- Authentication -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf

                                <x-dropdown-link :href="route('logout')"
                                        onclick="event.preventDefault();
                                                    this.closest('form').submit();">
                                    {{ __('Cerrar sesion') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>

                <!-- Hamburger -->
                <div class="-me-2 flex items-center sm:hidden">
                    <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    @if ($headerPendingJoinRequestsLeague)
        <x-modal name="pending-join-requests" maxWidth="lg" focusable>
            <div class="space-y-5 p-5 sm:p-6">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <p class="text-xs font-black uppercase tracking-wide text-indigo-700">
                            {{ __('Solicitudes pendientes') }}
                        </p>
                        <h2 class="mt-1 text-xl font-black leading-tight text-blue-950">
                            {{ $headerPendingJoinRequestsLeague->name }}
                        </h2>
                    </div>

                    <button
                        type="button"
                        x-data
                        x-on:click="$dispatch('close-modal', 'pending-join-requests')"
                        class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-slate-100 text-slate-500 transition hover:bg-slate-200 hover:text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                        aria-label="{{ __('Cerrar') }}"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>

                <div class="space-y-3">
                    @foreach ($pendingJoinRequests as $joinRequest)
                        <div class="rounded-xl border border-blue-100 bg-blue-50/60 p-3 sm:p-4">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div class="flex min-w-0 items-center gap-3">
                                    <x-profile-avatar :user="$joinRequest->user" size="sm" />
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-black text-blue-950">
                                            {{ $joinRequest->user->displayName() }}
                                        </p>
                                        @if ($joinRequest->user->usernameHandle() && $joinRequest->user->displayName() !== $joinRequest->user->usernameHandle())
                                            <p class="truncate text-xs font-bold text-slate-500">
                                                {{ $joinRequest->user->usernameHandle() }}
                                            </p>
                                        @endif
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-2 sm:flex sm:shrink-0">
                                    <form method="POST" action="{{ route('private-leagues.join-requests.accept', [$headerPendingJoinRequestsLeague, $joinRequest]) }}">
                                        @csrf
                                        <button
                                            type="submit"
                                            class="inline-flex w-full items-center justify-center rounded-md bg-emerald-600 px-3 py-2 text-xs font-black text-white transition hover:bg-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
                                        >
                                            {{ __('Aceptar') }}
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('private-leagues.join-requests.reject', [$headerPendingJoinRequestsLeague, $joinRequest]) }}">
                                        @csrf
                                        <button
                                            type="submit"
                                            class="inline-flex w-full items-center justify-center rounded-md border border-slate-200 bg-white px-3 py-2 text-xs font-black text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                        >
                                            {{ __('Rechazar') }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </x-modal>
    @endif

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Inicio') }}
            </x-responsive-nav-link>
            @if (Auth::user()->isAdmin())
                <x-responsive-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.*')">
                    {{ __('Administracion') }}
                </x-responsive-nav-link>
            @endif
            <x-responsive-nav-link :href="route('predictions.index')" :active="request()->routeIs('predictions.*')">
                {{ __('Predicciones') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('leagues.index')" :active="request()->routeIs('leagues.index') || request()->routeIs('leaderboard.index') || request()->routeIs('private-leagues.*')">
                {{ __('Ligas') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('calendar.index')" :active="request()->routeIs('calendar.index')">
                {{ __('Calendario') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Perfil') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Cerrar sesion') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
