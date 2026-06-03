<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Crear liga privada') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-gray-100">
                <div class="border-b border-gray-100 bg-indigo-50 px-6 py-5">
                    <p class="text-sm font-medium text-indigo-800">
                        {{ __('Crea una liga para comparar puntos y tabla de posiciones con tu grupo.') }}
                    </p>
                    <p class="mt-1 text-sm text-indigo-700">
                        {{ __('Vas a recibir un codigo visible para compartir cuando el flujo de solicitudes este disponible.') }}
                    </p>
                </div>

                <form method="POST" action="{{ route('private-leagues.store') }}" class="space-y-6 p-6">
                    @csrf

                    <div>
                        <x-input-label for="name" :value="__('Nombre de la liga')" />
                        <x-text-input
                            id="name"
                            name="name"
                            type="text"
                            class="mt-1 block w-full"
                            :value="old('name')"
                            required
                            autofocus
                            maxlength="255"
                            placeholder="{{ __('Ej: Amigos del Mundial') }}"
                        />
                        <x-input-error class="mt-2" :messages="$errors->get('name')" />
                        <p class="mt-2 text-sm text-gray-500">
                            {{ __('Los nombres pueden repetirse. El codigo unico identifica tu liga.') }}
                        </p>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-sm text-gray-500">
                            {{ __('Cada usuario puede crear una sola liga privada.') }}
                        </p>

                        <x-primary-button>
                            {{ __('Crear liga') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
