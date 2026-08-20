<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScholarResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'cal_membership_id' => $this->cal_membership_id,
            'cal_username' => $this->cal_username,
            'cal_user_id' => $this->cal_user_id,
            'email' => $this->email,
            'name' => $this->name,
            'initials' => $this->initials,
            'avatar_url' => $this->avatar_url,
            'bio' => $this->bio,
            'credentials' => $this->credentials ?? [],
            'country' => $this->country,
            'flag' => $this->flag,
            'tier' => $this->tier,
            'specialties' => $this->specialties ?? [],
            'is_active' => $this->is_active,
            'last_synced_at' => $this->last_synced_at?->toIso8601String(),
            'madhhab_id' => $this->madhhab_id,
            'madhhab' => $this->whenLoaded('madhhab', fn () => [
                'id' => $this->madhhab?->id,
                'name' => $this->madhhab?->name,
            ]),
            'languages' => $this->whenLoaded('languages', fn () => $this->languages->map(fn ($language) => [
                'id' => $language->id,
                'name' => $language->name,
                'code' => $language->code,
            ])->values()),
            'language_ids' => $this->whenLoaded('languages', fn () => $this->languages->pluck('id')->values()),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
