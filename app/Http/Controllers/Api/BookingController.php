<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Scholar;
use App\Services\BookingCreateService;
use Illuminate\Http\JsonResponse;
use RuntimeException;
use Throwable;

class BookingController extends Controller
{
    public function store(StoreBookingRequest $request, BookingCreateService $bookings): JsonResponse
    {
        $scholar = Scholar::query()
            ->active()
            ->where('cal_username', $request->validated('scholar_id'))
            ->first();

        if (! $scholar) {
            return response()->json([
                'status' => 'error',
                'message' => 'Scholar not found.',
            ], 404);
        }

        try {
            $booking = $bookings->create($scholar, $request->validated());
        } catch (RuntimeException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'status' => 'error',
                'message' => 'Unable to create booking with Cal.com right now.',
            ], 502);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $booking->id,
                'uid' => $booking->cal_booking_uid,
                'status' => $booking->status,
                'payment_status' => $booking->payment_status,
                'starts_at' => $booking->starts_at?->toIso8601String(),
                'ends_at' => $booking->ends_at?->toIso8601String(),
                'duration_minutes' => $booking->duration_minutes,
                'amount' => $booking->amount !== null ? (float) $booking->amount : null,
                'currency' => $booking->currency,
                'meeting_url' => $booking->meeting_url,
                'title' => $booking->title,
            ],
        ], 201);
    }
}
