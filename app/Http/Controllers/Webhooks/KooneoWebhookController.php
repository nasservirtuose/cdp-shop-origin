<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\KooneoWebhookEvent;
use App\Services\Shop\KooneoEventProcessor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class KooneoWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $secret = (string) config('services.kooneo.webhook_secret');
        $providedSecret = (string) $request->query('k');

        if ($secret === '' || ! hash_equals($secret, $providedSecret)) {
            Log::warning('Rejected Kooneo webhook due to invalid secret.', [
                'ip' => $request->ip(),
            ]);

            return response('', 401);
        }

        $payload = $request->json()->all();

        if ($payload === []) {
            return response('', 400);
        }

        $eventType = $payload['type'] ?? null;

        if (! is_string($eventType) || $eventType === '') {
            return response('', 400);
        }

        $invoice = $payload['invoice'] ?? [];
        $transactionId = $invoice['transaction_id'] ?? null;
        $orderId = $invoice['order_id'] ?? null;

        if ($transactionId !== null && $transactionId !== '') {
            $event = KooneoWebhookEvent::firstOrCreate(
                [
                    'event_type' => $eventType,
                    'kooneo_transaction_id' => $transactionId,
                ],
                [
                    'kooneo_order_id' => $orderId,
                    'raw_payload' => $payload,
                    'received_at' => now(),
                    'processing_status' => 'received',
                ]
            );
        } else {
            Log::warning('Kooneo webhook missing transaction_id.', [
                'event_type' => $eventType,
                'ip' => $request->ip(),
            ]);

            $event = KooneoWebhookEvent::create([
                'event_type' => $eventType,
                'kooneo_transaction_id' => null,
                'kooneo_order_id' => $orderId,
                'raw_payload' => $payload,
                'received_at' => now(),
                'processing_status' => 'received',
            ]);
        }

        try {
            app(KooneoEventProcessor::class)->process($event);
        } catch (\Throwable $e) {
            report($e);
        }

        return response()->json([
            'status' => 'received',
            'event_id' => $event->id,
        ]);
    }
}