<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TournamentMatch extends Model
{
    /** @use HasFactory<\Database\Factories\TournamentMatchFactory> */
    use HasFactory;

    public const STATUS_SCHEDULED = 'scheduled';

    public const STATUS_OPEN = 'open';

    public const STATUS_LOCKED = 'locked';

    public const STATUS_FINISHED = 'finished';

    public const STATUS_PLACEHOLDER = 'placeholder';

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
        'api_provider',
        'api_fixture_id',
        'api_status',
        'round',
        'venue_name',
        'venue_city',
        'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'prediction_closes_at' => 'datetime',
            'team_a_score' => 'integer',
            'team_b_score' => 'integer',
            'last_synced_at' => 'datetime',
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

    public function isKnockout(): bool
    {
        if (! $this->stage) {
            return false;
        }

        return in_array($this->stage, [
            'round_of_32',
            'round_of_16',
            'quarter_final',
            'semi_final',
            'third_place',
            'final',
        ], true);
    }

    public function requiresQualifiedTeamPrediction(): bool
    {
        return $this->isKnockout();
    }

    public function predictionClosesAt(): ?CarbonInterface
    {
        if ($this->prediction_closes_at) {
            return $this->prediction_closes_at;
        }

        return $this->starts_at?->copy()->subMinutes(5);
    }

    public function isPredictable(): bool
    {
        if (! $this->team_a_id || ! $this->team_b_id) {
            return false;
        }

        if (in_array($this->status, [
            self::STATUS_PLACEHOLDER,
            self::STATUS_FINISHED,
            self::STATUS_LOCKED,
        ], true)) {
            return false;
        }

        return ! $this->predictionClosesAt()?->isPast();
    }
}
