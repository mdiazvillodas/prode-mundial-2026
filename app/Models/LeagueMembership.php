<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeagueMembership extends Model
{
    /** @use HasFactory<\Database\Factories\LeagueMembershipFactory> */
    use HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_REMOVED = 'removed';

    protected $fillable = [
        'private_league_id',
        'user_id',
        'status',
        'joined_at',
    ];

    protected function casts(): array
    {
        return [
            'joined_at' => 'datetime',
        ];
    }

    public function privateLeague(): BelongsTo
    {
        return $this->belongsTo(PrivateLeague::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
