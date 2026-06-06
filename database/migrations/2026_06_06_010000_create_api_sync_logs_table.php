<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->string('provider');
            $table->string('sync_type');
            $table->string('status');
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->unsignedInteger('rate_limit_limit')->nullable();
            $table->unsignedInteger('rate_limit_remaining')->nullable();
            $table->unsignedInteger('requests_remaining')->nullable();
            $table->unsignedInteger('items_received')->nullable();
            $table->unsignedInteger('items_created')->nullable();
            $table->unsignedInteger('items_updated')->nullable();
            $table->unsignedInteger('items_skipped')->nullable();
            $table->text('error_message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('provider');
            $table->index('sync_type');
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_sync_logs');
    }
};
