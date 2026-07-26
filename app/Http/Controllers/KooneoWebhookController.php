<?php

namespace App\Http\Controllers;

use App\Models\KooneoWebhookEvent;
use Illuminate\Http\Request;

class KooneoWebhookController extends Controller
{
    public function receive(Request $request)
    {
        // Kooneo ne signe pas ses webhooks: secret dans l'URL (?k=...).
        $secret = (string) config('services.kooneo.webhook_secret');
        abort_unless($secret !== '' && hash_equals($secret, (string) $request->query('k')), 404);

        $payload = $request->all();
        $type = $payload['type'] ?? null;
        $invoice = $payload['invoice'] ?? [];
        $txId = $invoice['transaction_id'] ?? null;

        // Idempotence: meme evenement re-emis -> on accuse reception en 2xx.
        $existing = KooneoWebhookEvent::where('transaction_id', $txId)
            ->where('type', $type)
            ->first();

        if ($existing) {
            return response()->json(['received' => true, 'duplicate' => true]);
        }

        KooneoWebhookEvent::create([
            'transaction_id' => $txId,
            'type' => $type,
            'order_id' => $invoice['order_id'] ?? null,
            'origin_tag' => $invoice['tags']['origin'] ?? null,
            'customer_email' => $payload['customer']['email'] ?? null,
            'product_reference' => $invoice['products'][0]['reference'] ?? null,
            'amount_cents' => isset($invoice['amount'])
                ? (int) round(((float) $invoice['amount']) * 100)
                : null,
            'currency' => $invoice['currency'] ?? null,
            'is_test' => (bool) ($invoice['is_test'] ?? false),
            'payload' => $payload,
            'received_at' => now(),
        ]);

        return response()->json(['received' => true]);
    }
}