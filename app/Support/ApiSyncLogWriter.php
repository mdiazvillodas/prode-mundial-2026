<?php

namespace App\Support;

use App\Models\ApiSyncLog;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Log;

class ApiSyncLogWriter
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public static function write(array $attributes): void
    {
        try {
            ApiSyncLog::query()->create($attributes);
        } catch (\Throwable $exception) {
            Log::warning('Could not write API sync log.', [
                'provider' => $attributes['provider'] ?? null,
                'sync_type' => $attributes['sync_type'] ?? null,
                'status' => $attributes['status'] ?? null,
                'exception' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * @return array<string, int|null>
     */
    public static function responseMetrics(Response $response): array
    {
        return [
            'http_status' => $response->status(),
            'rate_limit_limit' => self::nullableInteger($response->header('X-RateLimit-Limit')),
            'rate_limit_remaining' => self::nullableInteger($response->header('X-RateLimit-Remaining')),
            'requests_remaining' => self::nullableInteger($response->header('X-RateLimit-Requests-Remaining')),
        ];
    }

    private static function nullableInteger(mixed $value): ?int
    {
        if (! is_numeric($value)) {
            return null;
        }

        return (int) $value;
    }
}
