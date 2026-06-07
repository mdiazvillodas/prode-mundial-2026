@props([
    'user' => null,
    'size' => 'md',
])

@php
    $avatar = $user?->profileAvatar() ?? \App\Support\ProfileAvatarCatalog::default();
    $sizes = [
        'sm' => 'h-8 w-8',
        'md' => 'h-10 w-10',
        'lg' => 'h-14 w-14',
    ];
    $sizeClass = $sizes[$size] ?? $sizes['md'];
    $alt = $user
        ? trim('Avatar de '.$user->name)
        : 'Avatar de usuario';
@endphp

<span {{ $attributes->merge(['class' => $sizeClass.' inline-flex shrink-0 overflow-hidden rounded-full bg-gray-100 ring-1 ring-gray-200']) }}>
    <img
        src="{{ $avatar['url'] }}"
        alt="{{ $alt }}"
        class="h-full w-full object-cover"
        loading="lazy"
    >
</span>
