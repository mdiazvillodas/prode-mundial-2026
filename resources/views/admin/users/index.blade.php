<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Admin usuarios') }}
            </h2>
            <p class="text-sm text-gray-500">
                {{ __('Revisión operativa de usuarios y verificación manual de email cuando sea necesario.') }}
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <section class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                {{ __('Esta pantalla no aprueba ni rechaza cuentas. Solo permite marcar un email como verificado si el flujo normal de código no llega o falla.') }}
            </section>

            <section class="overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-gray-100">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left text-xs font-bold uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="px-4 py-3">{{ __('Usuario') }}</th>
                                <th class="px-4 py-3">{{ __('Email') }}</th>
                                <th class="px-4 py-3">{{ __('Rol') }}</th>
                                <th class="px-4 py-3">{{ __('Verificación') }}</th>
                                <th class="px-4 py-3">{{ __('Google') }}</th>
                                <th class="px-4 py-3">{{ __('Creado') }}</th>
                                <th class="px-4 py-3 text-right">{{ __('Acciones') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($users as $user)
                                <tr>
                                    <td class="px-4 py-4">
                                        <div class="font-bold text-gray-950">{{ $user->name }}</div>
                                        <div class="text-xs font-semibold text-gray-500">{{ '@'.$user->username }}</div>
                                    </td>
                                    <td class="px-4 py-4 text-gray-700">{{ $user->email }}</td>
                                    <td class="px-4 py-4">
                                        <span @class([
                                            'inline-flex rounded-full px-2.5 py-1 text-xs font-bold ring-1 ring-inset',
                                            'bg-emerald-50 text-emerald-700 ring-emerald-600/20' => $user->isAdmin(),
                                            'bg-gray-100 text-gray-700 ring-gray-500/20' => ! $user->isAdmin(),
                                        ])>
                                            {{ $user->role }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4">
                                        @if ($user->hasVerifiedEmail())
                                            <div class="inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700 ring-1 ring-inset ring-emerald-600/20">
                                                {{ __('Verificado') }}
                                            </div>
                                            <div class="mt-1 text-xs text-gray-500">
                                                {{ $user->email_verified_at?->format('d/m/Y H:i') }}
                                            </div>
                                        @else
                                            <div class="inline-flex rounded-full bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-700 ring-1 ring-inset ring-amber-600/20">
                                                {{ __('Pendiente') }}
                                            </div>
                                            <div class="mt-1 text-xs text-gray-500">{{ __('Sin fecha') }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4">
                                        @if ($user->google_id)
                                            <span class="inline-flex rounded-full bg-sky-50 px-2.5 py-1 text-xs font-bold text-sky-700 ring-1 ring-inset ring-sky-600/20">
                                                {{ __('Vinculado') }}
                                            </span>
                                        @else
                                            <span class="text-xs font-semibold text-gray-500">{{ __('No vinculado') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 text-gray-600">
                                        {{ $user->created_at?->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-4 py-4 text-right">
                                        @if (! $user->hasVerifiedEmail())
                                            <form method="POST" action="{{ route('admin.users.verify-email', $user) }}">
                                                @csrf
                                                @method('PATCH')

                                                <button
                                                    type="submit"
                                                    class="inline-flex items-center justify-center rounded-md bg-emerald-600 px-3 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
                                                >
                                                    {{ __('Verificar email') }}
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-xs font-semibold text-gray-500">{{ __('Sin acción') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            <div>
                {{ $users->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
