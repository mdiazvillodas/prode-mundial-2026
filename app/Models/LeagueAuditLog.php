<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeagueAuditLog extends Model
{
    /** @use HasFactory<\Database\Factories\LeagueAuditLogFactory> */
    use HasFactory;

    public const ACTION_MEMBER_REMOVED = 'member_removed';

    protected $fillable = [
        'private_league_id',
        'actor_user_id',
        'target_user_id',
        'action',
        'details',
    ];

    protected function casts(): array
    {
        return [
            'details' => 'array',
        ];
    }

    public function privateLeague(): BelongsTo
    {
        return $this->belongsTo(PrivateLeague::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    public function target(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }
}
