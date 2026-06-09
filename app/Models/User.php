<?php

namespace App\Models;

use App\Support\ProfileAvatarCatalog;
use Database\Factories\UserFactory;
use InvalidArgumentException;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
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
        'email_verified_at',
        'password',
        'role',
        'google_id',
        'avatar_url',
        'profile_avatar_key',
        'auth_provider',
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

    public function displayName(): string
    {
        return trim((string) $this->name) !== ''
            ? $this->name
            : '@'.$this->username;
    }

    public function usernameHandle(): ?string
    {
        return $this->username ? '@'.$this->username : null;
    }

    public function hasChosenProfileAvatar(): bool
    {
        return ProfileAvatarCatalog::isValid($this->profile_avatar_key);
    }

    /**
     * @return array{key: string, label: string, path: string, url: string}
     */
    public function profileAvatar(): array
    {
        return ProfileAvatarCatalog::get($this->profile_avatar_key)
            ?? ProfileAvatarCatalog::default();
    }

    public function profileAvatarUrl(): string
    {
        return $this->profileAvatar()['url'];
    }

    public function profileAvatarLabel(): string
    {
        return $this->profileAvatar()['label'];
    }

    public function setProfileAvatarKey(?string $key): self
    {
        if ($key !== null && ! ProfileAvatarCatalog::isValid($key)) {
            throw new InvalidArgumentException('Invalid profile avatar key.');
        }

        $this->profile_avatar_key = $key;

        return $this;
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

    public function emailVerificationCodes(): HasMany
    {
        return $this->hasMany(EmailVerificationCode::class);
    }
}
