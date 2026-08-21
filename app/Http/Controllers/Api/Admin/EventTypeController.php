<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateEventTypeRequest;
use App\Http\Resources\Admin\EventTypeResource;
use App\Models\EventType;
use App\Models\Scholar;
use App\Services\EventTypeSyncService;
use Illuminate\Http\JsonResponse;
use Throwable;

class EventTypeController extends Controller
{
    public function index(Scholar $scholar): JsonResponse
    {
        $eventTypes = $scholar->eventTypes()
            ->orderBy('length_in_minutes')
            ->orderBy('title')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => EventTypeResource::collection($eventTypes)->resolve(),
        ]);
    }

    public function update(UpdateEventTypeRequest $request, Scholar $scholar, EventType $eventType): JsonResponse
    {
        if ($eventType->scholar_id !== $scholar->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Event type does not belong to this scholar.',
            ], 404);
        }

        $eventType->fill($request->validated());
        $eventType->save();

        return response()->json([
            'status' => 'success',
            'data' => new EventTypeResource($eventType),
        ]);
    }

    public function sync(Scholar $scholar, EventTypeSyncService $sync): JsonResponse
    {
        try {
            $result = $sync->syncScholar($scholar);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage() ?: 'Unable to sync event types from Cal.com.',
            ], 502);
        }

        $eventTypes = $scholar->eventTypes()
            ->orderBy('length_in_minutes')
            ->orderBy('title')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'sync' => $result,
                'event_types' => EventTypeResource::collection($eventTypes)->resolve(),
            ],
        ]);
    }
}
