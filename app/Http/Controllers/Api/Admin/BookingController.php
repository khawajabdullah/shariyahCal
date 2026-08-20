<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\BookingResource;
use App\Models\Booking;
use App\Services\BookingSyncService;
use App\Support\DataTable\DataTableQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class BookingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Booking::query()->with('scholar:id,name,cal_username');

        $payload = (new DataTableQuery(
            $query,
            ['attendee_name', 'attendee_email', 'cal_booking_uid', 'status', 'scholar.name'],
            [
                'id' => 'id',
                'attendee_name' => 'attendee_name',
                'attendee_email' => 'attendee_email',
                'status' => 'status',
                'starts_at' => 'starts_at',
                'duration_minutes' => 'duration_minutes',
                'amount' => 'amount',
            ],
            $request,
        ))->toResponse(fn ($rows) => BookingResource::collection($rows)->resolve());

        return response()->json($payload);
    }

    public function show(Booking $booking): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => new BookingResource($booking->load('scholar:id,name,cal_username')),
        ]);
    }

    public function sync(BookingSyncService $sync): JsonResponse
    {
        try {
            $result = $sync->syncFromCal();
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'status' => 'error',
                'message' => 'Unable to sync bookings from Cal.com.',
            ], 502);
        }

        return response()->json([
            'status' => 'success',
            'data' => $result,
        ]);
    }
}
