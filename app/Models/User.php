<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_USER = 'user';

    public const ROLE_ADMIN = 'admin';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function predictions(): HasMany
    {
        return $this->hasMany(Prediction::class);
    }

    public function ownedPrivateLeague(): HasOne
    {
        return $this->hasOne(PrivateLeague::class, 'owner_id');
    }

    public function leagueMemberships(): HasMany
    {
        return $this->hasMany(LeagueMembership::class);
    }

    public function leagueJoinRequests(): HasMany
    {
        return $this->hasMany(LeagueJoinRequest::class);
    }

    public function leagueAuditLogsAsActor(): HasMany
    {
        return $this->hasMany(LeagueAuditLog::class, 'actor_user_id');
    }

    public function leagueAuditLogsAsTarget(): HasMany
    {
        return $this->hasMany(LeagueAuditLog::class, 'target_user_id');
    }
}
