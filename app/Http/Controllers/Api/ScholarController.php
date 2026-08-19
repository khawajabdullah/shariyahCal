<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CalComService;
use Illuminate\Http\JsonResponse;
use Throwable;

class ScholarController extends Controller
{
    public function __construct(protected CalComService $cal)
    {
    }

    public function index(): JsonResponse
    {
        try {
            return response()->json([
                'status' => 'success',
                'data' => $this->cal->scholars(),
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'status' => 'error',
                'message' => 'Unable to load scholars right now.',
                'data' => [],
            ], 502);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $scholar = $this->cal->scholar($id);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'status' => 'error',
                'message' => 'Unable to load this scholar right now.',
            ], 502);
        }

        if (! $scholar) {
            return response()->json([
                'status' => 'error',
                'message' => 'Scholar not found.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $scholar,
        ]);
    }
}
