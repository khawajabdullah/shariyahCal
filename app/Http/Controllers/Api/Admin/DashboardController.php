<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Language;
use App\Models\Madhhab;
use App\Models\Scholar;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function show(): JsonResponse
    {
        $activeScholars = Scholar::query()->active()->with('languages')->get();
        $countries = $activeScholars
            ->pluck('country')
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $madhahib = Madhhab::query()->ordered()->get(['id', 'name', 'slug', 'is_active', 'sort_order']);
        $languages = Language::query()->ordered()->get(['id', 'name', 'code', 'is_active', 'sort_order']);

        return response()->json([
            'status' => 'success',
            'data' => [
                'counts' => [
                    'scholars' => Scholar::query()->count(),
                    'active_scholars' => $activeScholars->count(),
                    'countries' => $countries->count(),
                    'madhahib' => Madhhab::query()->count(),
                    'active_madhahib' => Madhhab::query()->active()->count(),
                    'languages' => Language::query()->count(),
                    'active_languages' => Language::query()->active()->count(),
                    'bookings' => Booking::query()->count(),
                ],
                'public_directory' => [
                    'scholars' => $activeScholars->count(),
                    'countries' => $countries->count(),
                    'madhahib' => Madhhab::query()->active()->count(),
                    'languages' => Language::query()->active()->count(),
                ],
                'filters' => [
                    'madhahib' => $madhahib,
                    'languages' => $languages,
                    'countries' => $countries,
                ],
                'recent_bookings' => Booking::query()
                    ->with('scholar:id,name,cal_username')
                    ->latest('starts_at')
                    ->limit(6)
                    ->get()
                    ->map(fn (Booking $booking) => [
                        'id' => $booking->id,
                        'attendee_name' => $booking->attendee_name,
                        'attendee_email' => $booking->attendee_email,
                        'status' => $booking->status,
                        'starts_at' => $booking->starts_at?->toIso8601String(),
                        'scholar' => $booking->scholar?->name,
                    ]),
            ],
        ]);
    }
}
