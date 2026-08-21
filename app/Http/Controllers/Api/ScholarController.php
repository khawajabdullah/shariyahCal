<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\Madhhab;
use App\Models\Scholar;
use Illuminate\Http\JsonResponse;

class ScholarController extends Controller
{
    public function index(): JsonResponse
    {
        $scholars = Scholar::query()
            ->active()
            ->with([
                'madhhab',
                'languages' => fn ($query) => $query->active()->ordered(),
                'eventTypes' => fn ($query) => $query->bookable()->orderBy('length_in_minutes')->orderBy('title'),
            ])
            // ->orderBy('name')
            ->get()
            ->map(fn (Scholar $scholar) => $scholar->toPublicArray())
            ->values();

        return response()->json([
            'status' => 'success',
            'data' => $scholars,
            'filters' => $this->filtersPayload(),
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $scholar = Scholar::query()
            ->active()
            ->with([
                'madhhab',
                'languages' => fn ($query) => $query->active()->ordered(),
                'eventTypes' => fn ($query) => $query->bookable()->orderBy('length_in_minutes')->orderBy('title'),
            ])
            ->where('cal_username', $id)
            ->first();

        if (! $scholar) {
            return response()->json([
                'status' => 'error',
                'message' => 'Scholar not found.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $scholar->toPublicArray(),
        ]);
    }

    public function filters(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $this->filtersPayload(),
        ]);
    }

    /**
     * @return array{madhahib: list<string>, languages: list<string>}
     */
    protected function filtersPayload(): array
    {
        return [
            'madhahib' => Madhhab::query()->active()->ordered()->pluck('name')->values()->all(),
            'languages' => Language::query()->active()->ordered()->pluck('name')->values()->all(),
        ];
    }
}
