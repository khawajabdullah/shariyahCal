<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreLanguageRequest;
use App\Http\Requests\Admin\UpdateLanguageRequest;
use App\Http\Resources\Admin\LanguageResource;
use App\Models\Language;
use App\Support\DataTable\DataTableQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Language::query()->withCount('scholars');

        $payload = (new DataTableQuery(
            $query,
            ['name', 'code'],
            ['id' => 'id', 'name' => 'name', 'code' => 'code', 'sort_order' => 'sort_order', 'is_active' => 'is_active', 'updated_at' => 'updated_at'],
            $request,
        ))->toResponse(fn ($rows) => LanguageResource::collection($rows)->resolve());

        return response()->json($payload);
    }

    public function options(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => Language::query()->ordered()->get(['id', 'name', 'code', 'is_active']),
        ]);
    }

    public function store(StoreLanguageRequest $request): JsonResponse
    {
        $language = Language::query()->create([
            'name' => $request->validated('name'),
            'code' => $request->validated('code'),
            'sort_order' => $request->integer('sort_order'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return response()->json([
            'status' => 'success',
            'data' => new LanguageResource($language->loadCount('scholars')),
        ], 201);
    }

    public function update(UpdateLanguageRequest $request, Language $language): JsonResponse
    {
        $language->fill($request->validated());

        if ($request->exists('is_active')) {
            $language->is_active = $request->boolean('is_active');
        }

        $language->save();

        return response()->json([
            'status' => 'success',
            'data' => new LanguageResource($language->loadCount('scholars')),
        ]);
    }

    public function destroy(Language $language): JsonResponse
    {
        $language->delete();

        return response()->json(['status' => 'success']);
    }
}
