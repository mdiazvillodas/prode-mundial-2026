<?php

namespace App\Support;

use DateTimeZone;

class ViewerTimezone
{
    public const DEFAULT = 'Europe/Madrid';

    public static function resolve(?string $timezone): string
    {
        if ($timezone) {
            try {
                new DateTimeZone($timezone);

                return $timezone;
            } catch (\Throwable) {
                //
            }
        }

        return self::DEFAULT;
    }
}
