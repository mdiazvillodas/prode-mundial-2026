<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class PrivateLeague extends Model
{
    /** @use HasFactory<\Database\Factories\PrivateLeagueFactory> */
    use HasFactory;

    public const STATUS_ACTIVE = 'active';

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
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    private static function generateUniqueCode(): string
    {
        do {
            $code = Str::upper(Str::random(8));
        } while (self::query()->where('code', $code)->exists());

        return $code;
    }
}
