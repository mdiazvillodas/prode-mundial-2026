<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class PrivateLeague extends Model
{
    /** @use HasFactory<\Database\Factories\PrivateLeagueFactory> */
    use HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const MAX_ACTIVE_MEMBERSHIPS_PER_USER = 5;

    protected $fillable = [
        'owner_id',
        'name',
        'code',
        'status',
    ];

    protected static function booted(): void
    {
        static::creating(function (PrivateLeague $privateLeague): void {
            $privateLeague->status ??= self::STATUS_ACTIVE;
            $privateLeague->code ??= self::generateUniqueCode();
        });

        static::created(function (PrivateLeague $privateLeague): void {
            $privateLeague->memberships()->create([
                'user_id' => $privateLeague->owner_id,
                'status' => LeagueMembership::STATUS_ACTIVE,
                'joined_at' => now(),
            ]);
        });
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(LeagueMembership::class);
    }

    public function joinRequests(): HasMany
    {
        return $this->hasMany(LeagueJoinRequest::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(LeagueAuditLog::class);
    }

    private static function generateUniqueCode(): string
    {
        do {
            $code = Str::upper(Str::random(8));
        } while (self::query()->where('code', $code)->exists());

        return $code;
    }
}
