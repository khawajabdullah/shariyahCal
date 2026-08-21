<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'cal_booking_uid' => $this->cal_booking_uid,
            'scholar_id' => $this->scholar_id,
            'scholar' => $this->whenLoaded('scholar', fn () => [
                'id' => $this->scholar?->id,
                'name' => $this->scholar?->name,
                'cal_username' => $this->scholar?->cal_username,
            ]),
            'attendee_name' => $this->attendee_name,
            'attendee_email' => $this->attendee_email,
            'attendee_phone' => $this->attendee_phone,
            'starts_at' => $this->starts_at?->toIso8601String(),
            'ends_at' => $this->ends_at?->toIso8601String(),
            'duration_minutes' => $this->duration_minutes,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'title' => $this->title,
            'meeting_url' => $this->meeting_url,
            'event_type' => $this->whenLoaded('eventType', fn () => [
                'id' => $this->eventType?->id,
                'title' => $this->eventType?->title,
                'length_in_minutes' => $this->eventType?->length_in_minutes,
            ]),
            'last_synced_at' => $this->last_synced_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
