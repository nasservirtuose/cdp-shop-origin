<?php

namespace Tests\Feature;

use App\Models\ShopCategory;
use App\Models\ShopOrder;
use App\Models\ShopProduct;
use App\Services\Shop\ConfirmedSaleProcessor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ConfirmedSaleProcessorTest extends TestCase
{
    use RefreshDatabase;

    private array $goodConfig = [
        'vat_percent' => 20,
        'purchase_cost_ht' => 5,
        'variable_costs_ht' => 1,
        'rex_share_percent' => 50,
        'low_bound' => 0.50,
        'high_bound' => 8.00,
    ];

    private function makeProduct(array $config): ShopProduct
    {
        $cat = ShopCategory::create(['name' => 'C', 'slug' => Str::random(10)]);

        $product = ShopProduct::create(array_merge([
            'name' => 'P',
            'slug' => Str::random(10),
            'category_id' => $cat->id,
            'commerce_mode' => 'DIRECT_SHOP',
            'is_active' => true,
        ], $config));

        foreach ([
            ['tier_number' => 1, 'range_start_percentage' => 0, 'range_end_percentage' => 40, 'probability_percentage' => 50],
            ['tier_number' => 2, 'range_start_percentage' => 40, 'range_end_percentage' => 70, 'probability_percentage' => 35],
            ['tier_number' => 3, 'range_start_percentage' => 70, 'range_end_percentage' => 100, 'probability_percentage' => 15],
        ] as $t) {
            $product->rewardTiers()->create(array_merge(['is_active' => true], $t));
        }

        return $product->fresh('rewardTiers');
    }

    private function payment(ShopProduct $product, array $override = []): array
    {
        return array_merge([
            'provider' => 'KOONEO',
            'provider_transaction_id' => 'tx_' . Str::random(8),
            'product_id' => $product->id,
            'paid_amount_ttc' => 24.90,
            'origin_pro_id' => 4242,
            'origin_status' => 'MATCHED',
        ], $override);
    }

    public function test_valid_sale_draws_reward_and_graves_order(): void
    {
        $product = $this->makeProduct($this->goodConfig);
        $order = app(ConfirmedSaleProcessor::class)->process($this->payment($product));

        $this->assertSame('DRAWN', $order->reward_status);
        $this->assertGreaterThan(0.50, (float) $order->reward_amount);
        $this->assertLessThan(7.375, (float) $order->reward_amount);
        $this->assertContains($order->reward_tier, [1, 2, 3]);
        $this->assertSame(2490, $order->amount_cents);
        $this->assertEqualsWithDelta(20.75, $order->economic_snapshot['price_ht'], 0.01);
        $this->assertArrayHasKey('tier_min', $order->reward_draw_context);
    }

    public function test_processing_is_idempotent(): void
    {
        $product = $this->makeProduct($this->goodConfig);
        $processor = app(ConfirmedSaleProcessor::class);
        $payment = $this->payment($product, ['provider_transaction_id' => 'tx_fixed']);

        $first = $processor->process($payment);
        $second = $processor->process($payment);

        $this->assertSame($first->id, $second->id);
        $this->assertSame((string) $first->reward_amount, (string) $second->reward_amount);
        $this->assertSame(1, ShopOrder::where('provider_transaction_id', 'tx_fixed')->count());
    }

    public function test_no_pro_means_no_reward(): void
    {
        $product = $this->makeProduct($this->goodConfig);
        $order = app(ConfirmedSaleProcessor::class)->process($this->payment($product, [
            'origin_pro_id' => null,
            'origin_status' => 'UNMATCHED',
        ]));

        $this->assertSame('NO_PRO', $order->reward_status);
        $this->assertNull($order->reward_amount);
    }

    public function test_invalid_economics_records_no_reward(): void
    {
        $product = $this->makeProduct(array_merge($this->goodConfig, ['purchase_cost_ht' => 50]));
        $order = app(ConfirmedSaleProcessor::class)->process($this->payment($product));

        $this->assertSame('REWARD_ECONOMICS_INVALID', $order->reward_status);
        $this->assertNull($order->reward_amount);
    }

    public function test_snapshot_is_frozen_even_if_product_changes_later(): void
    {
        $product = $this->makeProduct($this->goodConfig);
        $order = app(ConfirmedSaleProcessor::class)->process($this->payment($product));
        $before = $order->economic_snapshot['price_ht'];

        $product->update(['purchase_cost_ht' => 999, 'high_bound' => 1.00]);

        $order->refresh();
        $this->assertSame($before, $order->economic_snapshot['price_ht']);
    }
}
