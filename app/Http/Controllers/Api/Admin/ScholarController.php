<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateScholarRequest;
use App\Http\Resources\Admin\ScholarResource;
use App\Models\Scholar;
use App\Services\ScholarSyncService;
use App\Support\DataTable\DataTableQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class ScholarController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Scholar::query()->with(['madhhab', 'languages']);

        $payload = (new DataTableQuery(
            $query,
            ['name', 'cal_username', 'email', 'country', 'madhhab.name'],
            [
                'id' => 'id',
                'name' => 'name',
                'cal_username' => 'cal_username',
                'country' => 'country',
                'tier' => 'tier',
                'is_active' => 'is_active',
                'last_synced_at' => 'last_synced_at',
            ],
            $request,
        ))->toResponse(fn ($rows) => ScholarResource::collection($rows)->resolve());

        return response()->json($payload);
    }

    public function show(Scholar $scholar): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => new ScholarResource($scholar->load([
                'madhhab',
                'languages',
                'eventTypes' => fn ($query) => $query->orderBy('length_in_minutes')->orderBy('title'),
            ])),
        ]);
    }

    public function update(UpdateScholarRequest $request, Scholar $scholar): JsonResponse
    {
        $data = $request->safe()->except(['language_ids', 'cal_api_key']);
        $scholar->fill($data);

        if ($request->exists('is_active')) {
            $scholar->is_active = $request->boolean('is_active');
        }

        if ($request->filled('cal_api_key')) {
            $scholar->cal_api_key = trim((string) $request->input('cal_api_key'));
        }

        $scholar->save();

        if ($request->exists('language_ids')) {
            $scholar->languages()->sync($request->input('language_ids', []));
        }

        return response()->json([
            'status' => 'success',
            'data' => new ScholarResource($scholar->load([
                'madhhab',
                'languages',
                'eventTypes' => fn ($query) => $query->orderBy('length_in_minutes')->orderBy('title'),
            ])),
        ]);
    }

    public function destroy(Scholar $scholar): JsonResponse
    {
        $scholar->delete();

        return response()->json(['status' => 'success']);
    }

    public function sync(ScholarSyncService $sync): JsonResponse
    {
        try {
            $result = $sync->sync();
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'status' => 'error',
                'message' => 'Unable to sync scholars from Cal.com.',
            ], 502);
        }

        return response()->json([
            'status' => 'success',
            'data' => $result,
        ]);
    }
}
