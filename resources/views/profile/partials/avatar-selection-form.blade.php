@props([
    'user',
    'submitLabel' => __('Guardar avatar'),
])

@php
    $avatars = \App\Support\ProfileAvatarCatalog::all();
    $selectedAvatarKey = old('profile_avatar_key', $user?->profile_avatar_key);
@endphp

<form method="post" action="{{ route('profile.avatar.update') }}" class="space-y-5">
    @csrf
    @method('patch')

    <div class="grid grid-cols-2 gap-3 sm:grid-cols-5">
        @foreach ($avatars as $avatar)
            @php
                $inputId = 'profile-avatar-'.$avatar['key'].'-'.substr(md5($submitLabel), 0, 8);
                $isSelected = $selectedAvatarKey === $avatar['key'];
            @endphp

            <label
                for="{{ $inputId }}"
                class="group flex cursor-pointer flex-col items-center gap-2 rounded-lg border bg-white p-3 text-center text-xs font-bold text-gray-700 shadow-sm transition hover:border-emerald-400 hover:bg-emerald-50 has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50 has-[:checked]:ring-2 has-[:checked]:ring-emerald-200"
            >
                <input
                    id="{{ $inputId }}"
                    type="radio"
                    name="profile_avatar_key"
                    value="{{ $avatar['key'] }}"
                    class="sr-only"
                    @checked($isSelected)
                >
                <span class="inline-flex h-14 w-14 overflow-hidden rounded-full bg-gray-100 ring-1 ring-gray-200">
                    <img
                        src="{{ $avatar['url'] }}"
                        alt="{{ $avatar['label'] }}"
                        class="h-full w-full object-cover"
                        loading="lazy"
                    >
                </span>
                <span>{{ $avatar['label'] }}</span>
            </label>
        @endforeach
    </div>

    <x-input-error class="mt-2" :messages="$errors->get('profile_avatar_key')" />

    <div class="flex items-center justify-end">
        <x-primary-button>{{ $submitLabel }}</x-primary-button>
    </div>
</form>
