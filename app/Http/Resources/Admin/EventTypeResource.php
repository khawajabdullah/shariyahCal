<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventTypeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'cal_event_type_id' => $this->cal_event_type_id,
            'title' => $this->title,
            'slug' => $this->slug,
            'length_in_minutes' => $this->length_in_minutes,
            'description' => $this->description,
            'booking_url' => $this->booking_url,
            'is_hidden' => $this->is_hidden,
            'is_active' => $this->is_active,
            'price' => $this->price !== null ? (float) $this->price : null,
            'currency' => $this->currency,
            'last_synced_at' => $this->last_synced_at?->toIso8601String(),
        ];
    }
}
