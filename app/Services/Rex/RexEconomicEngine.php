<?php

namespace App\Services\Rex;

use App\Models\ShopProduct;

class RexEconomicEngine
{
    public function compute(ShopProduct $product, float $paidAmountTtc): RexDrawResult
    {
        foreach (['vat_percent', 'purchase_cost_ht', 'variable_costs_ht', 'rex_share_percent', 'low_bound', 'high_bound'] as $field) {
            if ($product->$field === null) {
                return RexDrawResult::invalid('REWARD_ECONOMICS_INVALID', 'Configuration economique incomplete.');
            }
        }

        $vatRate = (float) $product->vat_percent / 100;
        $priceHt = $paidAmountTtc / (1 + $vatRate);
        $margin = $priceHt - (float) $product->purchase_cost_ht - (float) $product->variable_costs_ht;
        $low = (float) $product->low_bound;
        $high = (float) $product->high_bound;

        $snapshot = [
            'paid_amount_ttc' => round($paidAmountTtc, 2),
            'price_ht' => round($priceHt, 2),
            'vat_percent' => (float) $product->vat_percent,
            'purchase_cost_ht' => (float) $product->purchase_cost_ht,
            'variable_costs_ht' => (float) $product->variable_costs_ht,
            'rex_share_percent' => (float) $product->rex_share_percent,
            'margin' => round($margin, 2),
            'low_bound' => round($low, 2),
            'high_bound_configured' => round($high, 2),
        ];

        if ($margin <= 0) {
            return RexDrawResult::invalid('REWARD_ECONOMICS_INVALID', 'Marge disponible <= 0.', $snapshot);
        }

        $rexBudget = $margin * ((float) $product->rex_share_percent / 100);
        $effectiveHigh = min($high, $rexBudget);

        $snapshot['rex_budget'] = round($rexBudget, 3);
        $snapshot['high_bound_effective'] = round($effectiveHigh, 3);

        if ($effectiveHigh <= $low) {
            return RexDrawResult::invalid('REWARD_ECONOMICS_INVALID', 'Borne haute effective <= borne basse.', $snapshot);
        }

        $globalMinCents = (int) floor($low * 100) + 1;
        $globalMaxCents = (int) ceil($effectiveHigh * 100) - 1;

        if ($globalMinCents > $globalMaxCents) {
            return RexDrawResult::invalid('REWARD_ECONOMICS_INVALID', 'Espace de recompense trop etroit.', $snapshot);
        }

        $tiers = $product->rewardTiers()->where('is_active', true)->orderBy('tier_number')->get();
        $totalProb = (int) $tiers->sum('probability_percentage');

        if ($tiers->isEmpty() || $totalProb !== 100) {
            throw new \InvalidArgumentException("Les probabilites des tranches actives doivent totaliser 100 % (actuel : {$totalProb} %). ");
        }

        $tier = $this->pickTier($tiers, $totalProb);

        $span = $effectiveHigh - $low;
        $tierMin = $low + ((float) $tier->range_start_percentage / 100) * $span;
        $tierMax = $low + ((float) $tier->range_end_percentage / 100) * $span;

        $minCents = max((int) ceil($tierMin * 100), $globalMinCents);
        $maxCents = min((int) floor($tierMax * 100), $globalMaxCents);

        if ($minCents > $maxCents) {
            $minCents = $globalMinCents;
            $maxCents = $globalMaxCents;
        }

        $amount = random_int($minCents, $maxCents) / 100;

        return RexDrawResult::valid(
            amount: $amount,
            tierNumber: (int) $tier->tier_number,
            tierMin: round($tierMin, 2),
            tierMax: round($tierMax, 2),
            tierProbability: (int) $tier->probability_percentage,
            snapshot: $snapshot,
        );
    }

    private function pickTier($tiers, int $totalProb)
    {
        $r = random_int(1, $totalProb);
        $cumulative = 0;

        foreach ($tiers as $tier) {
            $cumulative += (int) $tier->probability_percentage;
            if ($r <= $cumulative) {
                return $tier;
            }
        }

        return $tiers->last();
    }
}
