<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Team;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Prediction extends Model
{
    /** @use HasFactory<\Database\Factories\PredictionFactory> */
    use HasFactory;

    public const STATUS_SUBMITTED = 'submitted';

    public const STATUS_LOCKED = 'locked';

    public const STATUS_SCORED = 'scored';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'user_id',
        'match_id',
        'team_a_score',
        'team_b_score',
        'predicted_qualified_team_id',
        'status',
        'points_awarded',
    ];

    protected function casts(): array
    {
        return [
            'team_a_score' => 'integer',
            'team_b_score' => 'integer',
            'predicted_qualified_team_id' => 'integer',
            'points_awarded' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function match(): BelongsTo
    {
        return $this->belongsTo(TournamentMatch::class, 'match_id');
    }

    public function predictedQualifiedTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'predicted_qualified_team_id');
    }
}
