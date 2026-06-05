<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->string('api_provider', 50)->nullable()->after('flag_path');
            $table->unsignedBigInteger('api_team_id')->nullable()->after('api_provider');
            $table->string('country')->nullable()->after('api_team_id');
            $table->string('logo_url')->nullable()->after('country');
            $table->timestamp('last_synced_at')->nullable()->after('logo_url');

            // Index for api_provider + api_team_id to prevent duplicate external IDs
            $table->unique(['api_provider', 'api_team_id'], 'teams_api_provider_api_team_id_unique');
        });

        Schema::table('matches', function (Blueprint $table) {
            $table->string('api_provider', 50)->nullable()->after('winner_team_id');
            $table->unsignedBigInteger('api_fixture_id')->nullable()->after('api_provider');
            $table->string('api_status', 50)->nullable()->after('api_fixture_id');
            $table->string('round')->nullable()->after('api_status');
            $table->string('venue_name')->nullable()->after('round');
            $table->string('venue_city')->nullable()->after('venue_name');
            $table->timestamp('last_synced_at')->nullable()->after('venue_city');

            // Index for api_provider + api_fixture_id to prevent duplicate external IDs
            $table->unique(['api_provider', 'api_fixture_id'], 'matches_api_provider_api_fixture_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('matches', function (Blueprint $table) {
            $table->dropUnique('matches_api_provider_api_fixture_id_unique');
            $table->dropColumn([
                'api_provider',
                'api_fixture_id',
                'api_status',
                'round',
                'venue_name',
                'venue_city',
                'last_synced_at',
            ]);
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->dropUnique('teams_api_provider_api_team_id_unique');
            $table->dropColumn([
                'api_provider',
                'api_team_id',
                'country',
                'logo_url',
                'last_synced_at',
            ]);
        });
    }
};
