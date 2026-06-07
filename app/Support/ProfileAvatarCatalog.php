<?php

namespace App\Support;

use Illuminate\Support\Collection;

class ProfileAvatarCatalog
{
    /**
     * @return Collection<string, array{key: string, label: string, path: string, url: string}>
     */
    public static function all(): Collection
    {
        return collect(config('profile-avatars', []))
            ->mapWithKeys(fn (array $avatar, string $key): array => [
                $key => self::normalize($key, $avatar),
            ]);
    }

    /**
     * @return array{key: string, label: string, path: string, url: string}|null
     */
    public static function get(?string $key): ?array
    {
        if ($key === null || $key === '') {
            return null;
        }

        return self::all()->get($key);
    }

    public static function isValid(?string $key): bool
    {
        return self::get($key) !== null;
    }

    /**
     * @return array{key: string, label: string, path: string, url: string}
     */
    public static function default(): array
    {
        return self::all()->get('default');
    }

    public static function path(?string $key): ?string
    {
        return self::get($key)['path'] ?? null;
    }

    public static function url(?string $key): ?string
    {
        return self::get($key)['url'] ?? null;
    }

    /**
     * @param  array{label?: string, path?: string}  $avatar
     * @return array{key: string, label: string, path: string, url: string}
     */
    private static function normalize(string $key, array $avatar): array
    {
        $path = (string) ($avatar['path'] ?? '');

        return [
            'key' => $key,
            'label' => (string) ($avatar['label'] ?? $key),
            'path' => $path,
            'url' => asset($path),
        ];
    }
}
