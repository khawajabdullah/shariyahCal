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
        'cal_api_key',
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
        'event_types_synced_at',
        'raw_payload',
    ];

    protected $hidden = [
        'cal_api_key',
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
            'event_types_synced_at' => 'datetime',
            'cal_user_id' => 'integer',
            'cal_membership_id' => 'integer',
            'cal_api_key' => 'encrypted',
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

    public function eventTypes(): HasMany
    {
        return $this->hasMany(EventType::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function hasCalApiKey(): bool
    {
        return filled($this->cal_api_key);
    }

    public function toPublicArray(): array
    {
        $eventTypes = $this->relationLoaded('eventTypes')
            ? $this->eventTypes
            : $this->eventTypes()->bookable()->orderBy('length_in_minutes')->get();

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
            'eventTypes' => $eventTypes
                ->map(fn (EventType $eventType) => $eventType->toPublicArray())
                ->values()
                ->all(),
        ];
    }
}
