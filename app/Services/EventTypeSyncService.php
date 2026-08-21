<?php

namespace App\Services;

use App\Models\EventType;
use App\Models\Scholar;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class EventTypeSyncService
{
    public function __construct(protected CalComService $cal)
    {
    }

    /**
     * @return array{created: int, updated: int, total: int}
     */
    public function syncScholar(Scholar $scholar): array
    {
        $apiKey = $scholar->cal_api_key;

        if (! is_string($apiKey) || trim($apiKey) === '') {
            throw new RuntimeException('This scholar does not have a Cal.com API key saved.');
        }

        $payload = $this->cal->eventTypes($apiKey);
        $created = 0;
        $updated = 0;
        $seen = [];

        DB::transaction(function () use ($scholar, $payload, &$created, &$updated, &$seen) {
            foreach ($payload as $row) {
                if (! is_array($row) || ! isset($row['id'])) {
                    continue;
                }

                $calId = (int) $row['id'];
                $seen[] = $calId;

                $eventType = EventType::query()->firstOrNew([
                    'scholar_id' => $scholar->id,
                    'cal_event_type_id' => $calId,
                ]);

                $exists = $eventType->exists;
                $mapped = $this->mapEventType($row);

                // Preserve admin-assigned price/currency across syncs.
                if ($exists) {
                    unset($mapped['price'], $mapped['currency']);
                }

                $eventType->fill($mapped);
                $eventType->raw_payload = $row;
                $eventType->last_synced_at = now();
                $eventType->save();

                $exists ? $updated++ : $created++;
            }

            if ($seen !== []) {
                EventType::query()
                    ->where('scholar_id', $scholar->id)
                    ->whereNotIn('cal_event_type_id', $seen)
                    ->update(['is_active' => false]);
            }

            $scholar->forceFill(['event_types_synced_at' => now()])->save();
        });

        return [
            'created' => $created,
            'updated' => $updated,
            'total' => count($payload),
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    protected function mapEventType(array $row): array
    {
        $currency = $row['currency'] ?? 'usd';
        $currency = is_string($currency) ? mb_strtolower(trim($currency)) : 'usd';

        return [
            'title' => (string) ($row['title'] ?? 'Untitled'),
            'slug' => (string) ($row['slug'] ?? ''),
            'length_in_minutes' => (int) ($row['lengthInMinutes'] ?? $row['length'] ?? 0),
            'description' => is_string($row['description'] ?? null) ? $row['description'] : null,
            'booking_url' => is_string($row['bookingUrl'] ?? null) ? $row['bookingUrl'] : null,
            'schedule_id' => isset($row['scheduleId']) ? (int) $row['scheduleId'] : null,
            'owner_id' => isset($row['ownerId']) ? (int) $row['ownerId'] : null,
            'minimum_booking_notice' => isset($row['minimumBookingNotice']) ? (int) $row['minimumBookingNotice'] : null,
            'locations' => is_array($row['locations'] ?? null) ? $row['locations'] : null,
            'is_hidden' => (bool) ($row['hidden'] ?? false),
            'is_active' => true,
            'price' => null,
            'currency' => $currency !== '' ? $currency : 'usd',
        ];
    }
}
