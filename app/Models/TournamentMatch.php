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

    public const STAGE_GROUP = 'group';

    public const STAGE_ROUND_OF_32 = 'round_of_32';

    public const STAGE_ROUND_OF_16 = 'round_of_16';

    public const STAGE_QUARTER_FINAL = 'quarter_final';

    public const STAGE_SEMI_FINAL = 'semi_final';

    public const STAGE_THIRD_PLACE = 'third_place';

    public const STAGE_FINAL = 'final';

    /**
     * Stages where users must predict which team advances to the next round.
     *
     * @var array<int, string>
     */
    public const KNOCKOUT_STAGES = [
        self::STAGE_ROUND_OF_32,
        self::STAGE_ROUND_OF_16,
        self::STAGE_QUARTER_FINAL,
        self::STAGE_SEMI_FINAL,
        self::STAGE_THIRD_PLACE,
        self::STAGE_FINAL,
    ];

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

        return in_array($this->stage, self::KNOCKOUT_STAGES, true);
    }

    public function requiresQualifiedTeamPrediction(): bool
    {
        return $this->isKnockout();
    }

    /**
     * Map an API-Football `league.round` label to a local match stage.
     *
     * Returns null when the round label is empty or cannot be confidently
     * mapped. Callers should preserve the raw round value and surface unknown
     * labels rather than silently treating them as group-stage matches.
     */
    public static function stageFromApiRound(?string $round): ?string
    {
        if ($round === null) {
            return null;
        }

        $normalized = strtolower(trim($round));

        if ($normalized === '') {
            return null;
        }

        return match (true) {
            str_contains($normalized, 'group') => self::STAGE_GROUP,
            str_contains($normalized, 'round of 32'),
            str_contains($normalized, '1/16') => self::STAGE_ROUND_OF_32,
            str_contains($normalized, 'round of 16'),
            str_contains($normalized, '1/8') => self::STAGE_ROUND_OF_16,
            str_contains($normalized, 'quarter'),
            str_contains($normalized, '1/4') => self::STAGE_QUARTER_FINAL,
            str_contains($normalized, 'semi'),
            str_contains($normalized, '1/2') => self::STAGE_SEMI_FINAL,
            // "3rd Place Final" / "Third place" must be matched before the
            // generic "final" check below, since both contain "final".
            str_contains($normalized, 'third'),
            str_contains($normalized, '3rd') => self::STAGE_THIRD_PLACE,
            str_contains($normalized, 'final') => self::STAGE_FINAL,
            default => null,
        };
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
