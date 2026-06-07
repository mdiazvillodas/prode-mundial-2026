@php
    $user = auth()->user();
    $shouldPromptForAvatar = $user
        && $user->profile_avatar_key === null
        && ! request()->routeIs('profile.*')
        && ! request()->routeIs('admin.*');
@endphp

@if ($shouldPromptForAvatar)
    <x-modal name="profile-avatar-prompt" :show="true" maxWidth="xl" focusable>
        <div class="space-y-5 p-6">
            <div class="space-y-2">
                <h2 class="text-xl font-black text-gray-950">{{ __('Elegí tu avatar') }}</h2>
                <p class="text-sm font-semibold leading-6 text-gray-600">
                    {{ __('Podés elegir una imagen para aparecer en rankings y ligas. También podés usar la silueta.') }}
                </p>
            </div>

            @include('profile.partials.avatar-selection-form', [
                'user' => $user,
                'submitLabel' => __('Guardar y continuar'),
            ])
        </div>
    </x-modal>
@endif
