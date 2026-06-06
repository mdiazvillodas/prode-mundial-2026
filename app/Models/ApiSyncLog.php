<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiSyncLog extends Model
{
    protected $fillable = [
        'provider',
        'sync_type',
        'status',
        'http_status',
        'started_at',
        'finished_at',
        'duration_ms',
        'rate_limit_limit',
        'rate_limit_remaining',
        'requests_remaining',
        'items_received',
        'items_created',
        'items_updated',
        'items_skipped',
        'error_message',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'metadata' => 'array',
        ];
    }
}
