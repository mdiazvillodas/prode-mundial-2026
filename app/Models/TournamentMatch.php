<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TournamentMatch extends Model
{
    /** @use HasFactory<\Database\Factories\TournamentMatchFactory> */
    use HasFactory;

    protected $table = 'matches';

    protected $fillable = [
        'tournament_id',
        'team_a_id',
        'team_b_id',
        'starts_at',
        'prediction_closes_at',
        'stage',
        'group',
        'status',
        'team_a_score',
        'team_b_score',
        'winner_team_id',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'prediction_closes_at' => 'datetime',
            'team_a_score' => 'integer',
            'team_b_score' => 'integer',
        ];
    }

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    public function teamA(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_a_id');
    }

    public function teamB(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_b_id');
    }

    public function winnerTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'winner_team_id');
    }

    public function predictions(): HasMany
    {
        return $this->hasMany(Prediction::class, 'match_id');
    }
}
