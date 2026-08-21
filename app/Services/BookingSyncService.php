<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\EventType;
use App\Models\Scholar;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Throwable;

class BookingSyncService
{
    public function __construct(protected CalComService $cal)
    {
    }

    /**
     * @return array{created: int, updated: int, total: int}
     */
    public function syncFromCal(): array
    {
        return $this->upsertMany($this->cal->bookings());
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function upsertFromWebhook(array $payload): Booking
    {
        $booking = $payload['payload'] ?? $payload['data'] ?? $payload;
        $mapped = $this->mapBooking(is_array($booking) ? $booking : $payload);

        return $this->upsert($mapped, is_array($booking) ? $booking : $payload);
    }

    /**
     * @param  list<array<string, mixed>>  $bookings
     * @return array{created: int, updated: int, total: int}
     */
    public function upsertMany(array $bookings): array
    {
        $created = 0;
        $updated = 0;

        DB::transaction(function () use ($bookings, &$created, &$updated) {
            foreach ($bookings as $row) {
                if (! is_array($row)) {
                    continue;
                }

                $mapped = $this->mapBooking($row);

                if ($mapped['cal_booking_uid'] === '') {
                    continue;
                }

                $exists = Booking::query()->where('cal_booking_uid', $mapped['cal_booking_uid'])->exists();
                $this->upsert($mapped, $row);
                $exists ? $updated++ : $created++;
            }
        });

        return [
            'created' => $created,
            'updated' => $updated,
            'total' => count($bookings),
        ];
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<string, mixed>  $raw
     */
    public function upsert(array $attributes, array $raw): Booking
    {
        $uid = $attributes['cal_booking_uid'];

        $booking = Booking::query()->firstOrNew(['cal_booking_uid' => $uid]);
        $booking->fill($attributes);
        $booking->raw_payload = $raw;
        $booking->last_synced_at = now();
        $booking->save();

        return $booking;
    }

    /**
     * @param  array<string, mixed>  $booking
     * @return array<string, mixed>
     */
    protected function mapBooking(array $booking): array
    {
        $uid = (string) ($booking['uid'] ?? $booking['id'] ?? '');
        $attendees = is_array($booking['attendees'] ?? null) ? $booking['attendees'] : [];
        $firstAttendee = is_array($attendees[0] ?? null) ? $attendees[0] : [];
        $hosts = is_array($booking['hosts'] ?? null) ? $booking['hosts'] : [];
        $firstHost = is_array($hosts[0] ?? null) ? $hosts[0] : [];
        $user = is_array($booking['user'] ?? null) ? $booking['user'] : [];

        $username = $firstHost['username'] ?? $user['username'] ?? $booking['username'] ?? null;
        $hostEmail = $firstHost['email'] ?? $user['email'] ?? null;
        $scholarId = null;

        if (is_string($username) && $username !== '') {
            $scholarId = Scholar::query()->where('cal_username', $username)->value('id');
        }

        if (! $scholarId && isset($firstHost['id'])) {
            $scholarId = Scholar::query()->where('cal_user_id', $firstHost['id'])->value('id');
        }

        if (! $scholarId && is_string($hostEmail) && $hostEmail !== '') {
            $scholarId = Scholar::query()->where('email', mb_strtolower(trim($hostEmail)))->value('id');
        }

        $start = $this->toDate($booking['start'] ?? $booking['startTime'] ?? $booking['start_time'] ?? null);
        $end = $this->toDate($booking['end'] ?? $booking['endTime'] ?? $booking['end_time'] ?? null);
        $duration = $booking['duration'] ?? $booking['length'] ?? null;

        if ($duration === null && $start && $end) {
            $duration = $start->diffInMinutes($end);
        }

        $price = $booking['price'] ?? data_get($booking, 'metadata.price');
        $currency = $booking['currency'] ?? data_get($booking, 'metadata.currency');
        $calEventTypeId = $booking['eventTypeId'] ?? data_get($booking, 'eventType.id');
        $eventTypeId = null;

        if ($scholarId && $calEventTypeId) {
            $eventTypeId = EventType::query()
                ->where('scholar_id', $scholarId)
                ->where('cal_event_type_id', (int) $calEventTypeId)
                ->value('id');
        }

        return [
            'cal_booking_uid' => $uid,
            'cal_booking_id' => isset($booking['id']) && is_numeric($booking['id']) ? (int) $booking['id'] : null,
            'cal_event_type_id' => $calEventTypeId ? (int) $calEventTypeId : null,
            'scholar_id' => $scholarId,
            'event_type_id' => $eventTypeId,
            'attendee_name' => $firstAttendee['name'] ?? $booking['responses']['name'] ?? null,
            'attendee_email' => $firstAttendee['email'] ?? $booking['responses']['email'] ?? null,
            'attendee_phone' => $firstAttendee['phoneNumber'] ?? null,
            'attendee_timezone' => $firstAttendee['timeZone'] ?? null,
            'attendee_language' => $firstAttendee['language'] ?? null,
            'starts_at' => $start,
            'ends_at' => $end,
            'duration_minutes' => is_numeric($duration) ? (int) $duration : null,
            'status' => (string) ($booking['status'] ?? $booking['bookingStatus'] ?? 'unknown'),
            'amount' => is_numeric($price) ? $price : null,
            'currency' => is_string($currency) ? $currency : null,
            'title' => is_string($booking['title'] ?? null) ? $booking['title'] : null,
            'meeting_url' => $booking['meetingUrl'] ?? $booking['videoCallUrl'] ?? data_get($booking, 'metadata.videoCallUrl'),
        ];
    }

    protected function toDate(mixed $value): ?Carbon
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (Throwable) {
            return null;
        }
    }
}
