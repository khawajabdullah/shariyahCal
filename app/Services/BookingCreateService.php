<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\EventType;
use App\Models\Scholar;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class BookingCreateService
{
    public function __construct(protected CalComService $cal)
    {
    }

    /**
     * @param  array{
     *     event_type_id: int,
     *     start: string,
     *     attendee_name: string,
     *     attendee_email: string,
     *     attendee_phone?: string|null,
     *     attendee_timezone?: string|null,
     *     attendee_language?: string|null,
     *     notes?: string|null,
     *     guests?: list<string>|null
     * }  $input
     */
    public function create(Scholar $scholar, array $input): Booking
    {
        // dd($scholar, $input);
        /** @var EventType|null $eventType */
        $eventType = EventType::query()
            ->where('scholar_id', $scholar->id)
            ->whereKey($input['event_type_id'])
            ->bookable()
            ->first();

        if (! $eventType) {
            throw new RuntimeException('Selected session length is not available for this scholar.');
        }

        $timeZone = $input['attendee_timezone']
            ?: data_get($scholar->schedule, 'timeZone')
            ?: 'UTC';

        $attendee = [
            'name' => $input['attendee_name'],
            'email' => $input['attendee_email'],
            'timeZone' => $timeZone,
            'language' => $input['attendee_language'] ?? 'en',
        ];

        if (! empty($input['attendee_phone'])) {
            $attendee['phoneNumber'] = $input['attendee_phone'];
        }

        $payload = [
            'start' => Carbon::parse($input['start'], $timeZone)->utc()->toIso8601String(),
            'attendee' => $attendee,
            'eventTypeId' => $eventType->cal_event_type_id,
            'eventTypeSlug' => $eventType->slug,
            'username' => $scholar->cal_username,
            'metadata' => [
                'localEventTypeId' => (string) $eventType->id,
                'localScholarId' => (string) $scholar->id,
                'price' => (string) $eventType->price,
                'currency' => (string) $eventType->currency,
            ],
        ];

        if (! empty($input['notes'])) {
            $payload['bookingFieldsResponses'] = [
                'notes' => $input['notes'],
            ];
        }

        if (! empty($input['guests']) && is_array($input['guests'])) {
            $payload['guests'] = array_values($input['guests']);
        }

        $apiKey = is_string($scholar->cal_api_key) && $scholar->cal_api_key !== ''
            ? $scholar->cal_api_key
            : null;

        $remote = $this->cal->createBooking($payload, $apiKey);

        return DB::transaction(function () use ($scholar, $eventType, $input, $remote, $timeZone) {
            $uid = (string) ($remote['uid'] ?? '');

            if ($uid === '') {
                throw new RuntimeException('Cal.com booking response did not include a UID.');
            }

            $start = $this->toDate($remote['start'] ?? $input['start']);
            $end = $this->toDate($remote['end'] ?? null);
            $duration = $remote['duration'] ?? $eventType->length_in_minutes;

            if ($duration === null && $start && $end) {
                $duration = $start->diffInMinutes($end);
            }

            $booking = Booking::query()->firstOrNew(['cal_booking_uid' => $uid]);
            $booking->fill([
                'cal_booking_id' => isset($remote['id']) ? (int) $remote['id'] : null,
                'cal_event_type_id' => $eventType->cal_event_type_id,
                'scholar_id' => $scholar->id,
                'event_type_id' => $eventType->id,
                'attendee_name' => $input['attendee_name'],
                'attendee_email' => $input['attendee_email'],
                'attendee_phone' => $input['attendee_phone'] ?? null,
                'attendee_timezone' => $timeZone,
                'attendee_language' => $input['attendee_language'] ?? 'en',
                'notes' => $input['notes'] ?? null,
                'guests' => $input['guests'] ?? null,
                'starts_at' => $start,
                'ends_at' => $end,
                'duration_minutes' => is_numeric($duration) ? (int) $duration : null,
                'status' => (string) ($remote['status'] ?? 'accepted'),
                'amount' => $eventType->price,
                'currency' => $eventType->currency,
                'payment_status' => 'pending',
                'title' => $remote['title'] ?? $eventType->title,
                'meeting_url' => $remote['meetingUrl'] ?? $remote['location'] ?? null,
                'raw_payload' => $remote,
                'last_synced_at' => now(),
            ]);
            $booking->save();

            return $booking;
        });
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
