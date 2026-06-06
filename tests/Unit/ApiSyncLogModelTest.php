<?php

namespace Tests\Unit;

use App\Models\ApiSyncLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiSyncLogModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_sync_log_can_store_data(): void
    {
        $log = ApiSyncLog::query()->create([
            'provider' => 'api-football',
            'sync_type' => 'teams',
            'status' => 'success',
            'http_status' => 200,
            'started_at' => now()->subSecond(),
            'finished_at' => now(),
            'duration_ms' => 123,
            'rate_limit_limit' => 100,
            'rate_limit_remaining' => 80,
            'requests_remaining' => 79,
            'items_received' => 48,
            'items_created' => 2,
            'items_updated' => 46,
            'items_skipped' => 0,
            'metadata' => ['season' => 2026],
        ]);

        $this->assertSame('api-football', $log->provider);
        $this->assertSame('teams', $log->sync_type);
        $this->assertSame(2026, $log->metadata['season']);
        $this->assertTrue($log->started_at->lessThanOrEqualTo($log->finished_at));
    }
}
