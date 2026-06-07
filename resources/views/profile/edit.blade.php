<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Perfil') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-3xl space-y-5">
                    <div class="flex items-center gap-4">
                        <x-profile-avatar :user="$user" size="lg" />
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">
                                {{ __('Avatar') }}
                            </h3>
                            <p class="mt-1 text-sm text-gray-600">
                                {{ __('Elegí una imagen local para tu perfil. No usamos fotos de Google ni subidas externas.') }}
                            </p>
                        </div>
                    </div>

                    @include('profile.partials.avatar-selection-form', [
                        'user' => $user,
                        'submitLabel' => __('Actualizar avatar'),
                    ])
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
