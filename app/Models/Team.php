<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    /** @use HasFactory<\Database\Factories\TeamFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'short_name',
        'country_code',
        'flag_path',
        'api_provider',
        'api_team_id',
        'country',
        'logo_url',
        'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'last_synced_at' => 'datetime',
        ];
    }

    public function hasFlag(): bool
    {
        return filled($this->flag_path);
    }

    public function flagUrl(): ?string
    {
        if (! $this->hasFlag()) {
            return null;
        }

        return asset($this->flag_path);
    }

    public function displayCode(): string
    {
        return $this->short_name
            ?: ($this->country_code ?: strtoupper(substr($this->name, 0, 3)));
    }
}
