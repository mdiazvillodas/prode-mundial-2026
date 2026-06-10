<?php

namespace App\Support;

use Carbon\CarbonInterface;

class MatchDisplayTime
{
    public static function localDate(?CarbonInterface $date, string $timezone): ?string
    {
        return $date?->copy()->timezone($timezone)->toDateString();
    }

    public static function localTime(?CarbonInterface $date, string $timezone): ?string
    {
        return $date?->copy()->timezone($timezone)->format('H:i');
    }

    public static function localDateTime(?CarbonInterface $date, string $timezone): ?string
    {
        return $date?->copy()->timezone($timezone)->format('d/m/Y H:i');
    }
}
