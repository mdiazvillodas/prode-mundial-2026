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
        Schema::create('matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained()->cascadeOnDelete();
            $table->foreignId('team_a_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->foreignId('team_b_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('prediction_closes_at')->nullable();
            $table->string('stage')->nullable();
            $table->string('group')->nullable();
            $table->string('status')->default('scheduled');
            $table->unsignedTinyInteger('team_a_score')->nullable();
            $table->unsignedTinyInteger('team_b_score')->nullable();
            $table->foreignId('winner_team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matches');
    }
};
