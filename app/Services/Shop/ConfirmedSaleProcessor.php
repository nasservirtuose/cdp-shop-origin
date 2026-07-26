<?php

namespace App\Services\Shop;

use App\Models\ShopOrder;
use App\Models\ShopProduct;
use App\Services\Rex\RexEconomicEngine;

class ConfirmedSaleProcessor
{
    public function __construct(private RexEconomicEngine $engine)
    {
    }

    /**
     * Traite un paiement confirme (idempotent). Tire la recompense et grave la commande.
     *
     * $payment attend : provider, provider_transaction_id, product_id, paid_amount_ttc,
     * currency?, origin_pro_id?, origin_token?, origin_status?, paid_at?
     */
    public function process(array $payment): ShopOrder
    {
        $existing = ShopOrder::where('provider', $payment['provider'])
            ->where('provider_transaction_id', $payment['provider_transaction_id'])
            ->first();

        if ($existing) {
            return $existing;
        }

        $product = ShopProduct::findOrFail($payment['product_id']);
        $paid = (float) $payment['paid_amount_ttc'];
        $proId = $payment['origin_pro_id'] ?? null;

        $attributes = [
            'provider' => $payment['provider'],
            'provider_transaction_id' => $payment['provider_transaction_id'],
            'product_id' => $product->id,
            'origin_pro_id' => $proId,
            'origin_token' => $payment['origin_token'] ?? null,
            'origin_status' => $payment['origin_status'] ?? 'UNMATCHED',
            'amount_cents' => (int) round($paid * 100),
            'currency' => $payment['currency'] ?? 'EUR',
            'payment_status' => 'PAID',
            'paid_at' => $payment['paid_at'] ?? now(),
            'economic_snapshot' => null,
            'reward_amount' => null,
            'reward_tier' => null,
            'reward_draw_context' => null,
            'reward_status' => 'NO_PRO',
        ];

        if ($proId !== null) {
            $result = $this->engine->compute($product, $paid);
            $attributes['economic_snapshot'] = $result->economicSnapshot;

            if ($result->valid) {
                $attributes['reward_status'] = 'DRAWN';
                $attributes['reward_amount'] = $result->amount;
                $attributes['reward_tier'] = $result->tierNumber;
                $attributes['reward_draw_context'] = $result->drawContext();
            } else {
                $attributes['reward_status'] = $result->invalidReason;
            }
        }

        return ShopOrder::create($attributes);
    }
}
