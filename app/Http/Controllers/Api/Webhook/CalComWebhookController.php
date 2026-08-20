<?php

namespace App\Http\Controllers\Api\Webhook;

use App\Http\Controllers\Controller;
use App\Services\BookingSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class CalComWebhookController extends Controller
{
    public function store(Request $request, BookingSyncService $sync): JsonResponse
    {
        $this->verifySignature($request);

        try {
            $sync->upsertFromWebhook($request->all());
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'status' => 'error',
                'message' => 'Unable to persist booking payload.',
            ], 500);
        }

        return response()->json(['status' => 'success']);
    }

    protected function verifySignature(Request $request): void
    {
        $secret = (string) config('services.cal.webhook_secret');

        if ($secret === '') {
            if (app()->isProduction()) {
                abort(401, 'Webhook secret is not configured.');
            }

            Log::warning('Cal.com webhook accepted without signature verification.');

            return;
        }

        $provided = (string) ($request->header('X-Cal-Signature-256') ?? $request->header('Cal-Signature-256') ?? '');
        $provided = str_starts_with($provided, 'sha256=') ? substr($provided, 7) : $provided;
        $expected = hash_hmac('sha256', $request->getContent(), $secret);

        if ($provided === '' || ! hash_equals($expected, $provided)) {
            abort(401, 'Invalid webhook signature.');
        }
    }
}
