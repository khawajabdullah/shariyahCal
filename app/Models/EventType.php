<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventType extends Model
{
    protected $fillable = [
        'scholar_id',
        'cal_event_type_id',
        'title',
        'slug',
        'length_in_minutes',
        'description',
        'booking_url',
        'schedule_id',
        'owner_id',
        'minimum_booking_notice',
        'locations',
        'is_hidden',
        'is_active',
        'price',
        'currency',
        'raw_payload',
        'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'locations' => 'array',
            'raw_payload' => 'array',
            'is_hidden' => 'boolean',
            'is_active' => 'boolean',
            'price' => 'decimal:2',
            'length_in_minutes' => 'integer',
            'cal_event_type_id' => 'integer',
            'schedule_id' => 'integer',
            'owner_id' => 'integer',
            'minimum_booking_notice' => 'integer',
            'last_synced_at' => 'datetime',
        ];
    }

    public function scholar(): BelongsTo
    {
        return $this->belongsTo(Scholar::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function scopeBookable(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->where('is_hidden', false)
            ->whereNotNull('price');
    }

    public function toPublicArray(): array
    {
        return [
            'id' => $this->id,
            'calEventTypeId' => $this->cal_event_type_id,
            'title' => $this->title,
            'slug' => $this->slug,
            'lengthInMinutes' => $this->length_in_minutes,
            'description' => $this->description,
            'price' => $this->price !== null ? (float) $this->price : null,
            'currency' => $this->currency ?: 'usd',
        ];
    }
}
