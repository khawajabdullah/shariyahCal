<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreMadhhabRequest;
use App\Http\Requests\Admin\UpdateMadhhabRequest;
use App\Http\Resources\Admin\MadhhabResource;
use App\Models\Madhhab;
use App\Support\DataTable\DataTableQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MadhhabController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Madhhab::query()->withCount('scholars');

        $payload = (new DataTableQuery(
            $query,
            ['name', 'slug'],
            ['id' => 'id', 'name' => 'name', 'slug' => 'slug', 'sort_order' => 'sort_order', 'is_active' => 'is_active', 'updated_at' => 'updated_at'],
            $request,
        ))->toResponse(fn ($rows) => MadhhabResource::collection($rows)->resolve());

        return response()->json($payload);
    }

    public function options(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => Madhhab::query()->ordered()->get(['id', 'name', 'slug', 'is_active']),
        ]);
    }

    public function store(StoreMadhhabRequest $request): JsonResponse
    {
        $madhhab = Madhhab::query()->create([
            'name' => $request->validated('name'),
            'slug' => $request->validated('slug'),
            'sort_order' => $request->integer('sort_order'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return response()->json([
            'status' => 'success',
            'data' => new MadhhabResource($madhhab->loadCount('scholars')),
        ], 201);
    }

    public function update(UpdateMadhhabRequest $request, Madhhab $madhhab): JsonResponse
    {
        $madhhab->fill($request->validated());

        if ($request->exists('is_active')) {
            $madhhab->is_active = $request->boolean('is_active');
        }

        $madhhab->save();

        return response()->json([
            'status' => 'success',
            'data' => new MadhhabResource($madhhab->loadCount('scholars')),
        ]);
    }

    public function destroy(Madhhab $madhhab): JsonResponse
    {
        $madhhab->delete();

        return response()->json(['status' => 'success']);
    }
}
