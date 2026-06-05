<?php

namespace App\Support;

use App\Models\Team;

class TeamFlagMapping
{
    /**
     * @return array{country_code: string, flag_path: string}|null
     */
    public static function forTeam(Team $team): ?array
    {
        return self::forCode($team->short_name)
            ?? self::forCode($team->country_code);
    }

    /**
     * @return array{country_code: string, flag_path: string}|null
     */
    public static function forCode(?string $code): ?array
    {
        if ($code === null) {
            return null;
        }

        $code = strtoupper(trim($code));

        if ($code === '') {
            return null;
        }

        $mapping = config("team-flags.{$code}");

        if (! is_array($mapping)) {
            return null;
        }

        $countryCode = $mapping['country_code'] ?? null;
        $flagPath = $mapping['flag_path'] ?? null;

        if (! is_string($countryCode) || ! is_string($flagPath)) {
            return null;
        }

        return [
            'country_code' => $countryCode,
            'flag_path' => $flagPath,
        ];
    }

    public static function assetExists(string $flagPath): bool
    {
        return is_file(public_path($flagPath));
    }
}
