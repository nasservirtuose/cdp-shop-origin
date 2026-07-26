<?php

namespace App\Services\Shop;

use App\Models\KooneoWebhookEvent;
use App\Models\ShopProduct;
use App\Services\OriginTokenService;
use Illuminate\Support\Facades\DB;

class KooneoEventProcessor
{
    public function __construct(
        private ConfirmedSaleProcessor $confirmedSale,
        private OriginTokenService $originTokens,
    ) {
    }

    public function process(KooneoWebhookEvent $event): void
    {
        if ($event->processing_status !== 'received') {
            return;
        }

        if ($event->event_type !== 'new_payment') {
            $event->update([
                'processing_status' => 'ignored',
                'processed_at' => now(),
            ]);

            return;
        }

        try {
            DB::transaction(function () use ($event): void {
                $payload = $event->raw_payload;
                $transactionId = $payload['invoice']['transaction_id'] ?? null;
                $productRef = $payload['invoice']['products'][0]['reference'] ?? null;
                $amountTtc = (float) ($payload['invoice']['amount'] ?? 0);
                $tagOrigin = $payload['invoice']['tags']['origin'] ?? null;
                $tagClient = $payload['invoice']['tags']['client'] ?? null;

                if (! is_string($transactionId) || $transactionId === '') {
                    throw new \RuntimeException('missing required kooneo field: invoice.transaction_id');
                }

                if (! is_string($productRef) || $productRef === '') {
                    throw new \RuntimeException('missing required kooneo field: invoice.products[0].reference');
                }

                $product = ShopProduct::where('external_reference', $productRef)->firstOrFail();
                $candidateProId = is_string($tagOrigin) && $tagOrigin !== ''
                    ? $this->originTokens->resolveProId($tagOrigin)
                    : null;

                $payment = [
                    'provider' => 'kooneo',
                    'provider_transaction_id' => $transactionId,
                    'product_id' => $product->id,
                    'paid_amount_ttc' => $amountTtc,
                    'currency' => $payload['invoice']['currency'] ?? 'EUR',
                    'origin_token' => $tagOrigin,
                    'origin_pro_id' => $candidateProId,
                ];

                $order = $this->confirmedSale->process($payment);

                $planipetsClientId = null;
                if ($tagClient !== null && $tagClient !== '') {
                    $planipetsClientId = (int) $tagClient;
                }

                $injectionStatus = ($order->reward_status === 'REWARD_ECONOMICS_INVALID')
                    ? 'no_reward'
                    : 'pending_cdp';

                $order->update([
                    'planipets_client_id' => $planipetsClientId,
                    'injection_status' => $injectionStatus,
                ]);

                $event->update([
                    'processing_status' => 'processed',
                    'processed_at' => now(),
                    'processing_error' => null,
                ]);
            });
        } catch (\Throwable $e) {
            $event->update([
                'processing_status' => 'error',
                'processing_error' => $e->getMessage(),
            ]);

            report($e);
        }
    }
}