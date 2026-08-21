<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    protected $fillable = [
        'cal_booking_uid',
        'cal_booking_id',
        'cal_event_type_id',
        'scholar_id',
        'event_type_id',
        'attendee_name',
        'attendee_email',
        'attendee_phone',
        'attendee_timezone',
        'attendee_language',
        'notes',
        'guests',
        'starts_at',
        'ends_at',
        'duration_minutes',
        'status',
        'amount',
        'currency',
        'payment_status',
        'title',
        'meeting_url',
        'raw_payload',
        'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'last_synced_at' => 'datetime',
            'raw_payload' => 'array',
            'guests' => 'array',
            'amount' => 'decimal:2',
            'duration_minutes' => 'integer',
            'cal_booking_id' => 'integer',
            'cal_event_type_id' => 'integer',
        ];
    }

    public function scholar(): BelongsTo
    {
        return $this->belongsTo(Scholar::class);
    }

    public function eventType(): BelongsTo
    {
        return $this->belongsTo(EventType::class);
    }
}
