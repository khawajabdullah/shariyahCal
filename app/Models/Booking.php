<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    protected $fillable = [
        'cal_booking_uid',
        'scholar_id',
        'attendee_name',
        'attendee_email',
        'starts_at',
        'ends_at',
        'duration_minutes',
        'status',
        'amount',
        'currency',
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
            'amount' => 'decimal:2',
            'duration_minutes' => 'integer',
        ];
    }

    public function scholar(): BelongsTo
    {
        return $this->belongsTo(Scholar::class);
    }
}
