<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Scholar extends Model
{
    protected $fillable = [
        'cal_membership_id',
        'cal_username',
        'cal_user_id',
        'email',
        'name',
        'initials',
        'avatar_url',
        'bio',
        'credentials',
        'country',
        'flag',
        'madhhab_id',
        'tier',
        'specialties',
        'schedule',
        'is_active',
        'last_synced_at',
        'raw_payload',
    ];

    protected function casts(): array
    {
        return [
            'credentials' => 'array',
            'specialties' => 'array',
            'schedule' => 'array',
            'raw_payload' => 'array',
            'is_active' => 'boolean',
            'last_synced_at' => 'datetime',
            'cal_user_id' => 'integer',
            'cal_membership_id' => 'integer',
        ];
    }

    public function madhhab(): BelongsTo
    {
        return $this->belongsTo(Madhhab::class);
    }

    public function languages(): BelongsToMany
    {
        return $this->belongsToMany(Language::class, 'language_scholar');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function toPublicArray(): array
    {
        return [
            'id' => $this->cal_username,
            'userId' => $this->cal_user_id,
            'username' => $this->cal_username,
            'name' => $this->name,
            'initials' => $this->initials,
            'avatarUrl' => $this->avatar_url,
            'bio' => $this->bio,
            'credentials' => $this->credentials ?? [],
            'country' => $this->country,
            'flag' => $this->flag,
            'madhhab' => $this->madhhab?->name,
            'languages' => $this->languages->pluck('name')->values()->all(),
            'specialties' => $this->specialties ?? [],
            'tier' => $this->tier,
            'schedule' => $this->schedule,
        ];
    }
}
