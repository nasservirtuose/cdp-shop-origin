<?php

namespace Tests\Feature;

use App\Models\ShopCategory;
use App\Models\ShopProduct;
use App\Services\Rex\RexEconomicEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class RexEconomicEngineTest extends TestCase
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

    private function makeProduct(array $config, ?array $tiers = null): ShopProduct
    {
        $cat = ShopCategory::create(['name' => 'Cat', 'slug' => Str::random(10)]);

        $product = ShopProduct::create(array_merge([
            'name' => 'P',
            'slug' => Str::random(10),
            'category_id' => $cat->id,
            'commerce_mode' => 'DIRECT_SHOP',
            'is_active' => true,
        ], $config));

        $tiers = $tiers ?? [
            ['tier_number' => 1, 'range_start_percentage' => 0, 'range_end_percentage' => 40, 'probability_percentage' => 50],
            ['tier_number' => 2, 'range_start_percentage' => 40, 'range_end_percentage' => 70, 'probability_percentage' => 35],
            ['tier_number' => 3, 'range_start_percentage' => 70, 'range_end_percentage' => 100, 'probability_percentage' => 15],
        ];

        foreach ($tiers as $t) {
            $product->rewardTiers()->create(array_merge(['is_active' => true], $t));
        }

        return $product->fresh('rewardTiers');
    }

    public function test_valid_reward_is_always_strictly_within_bounds(): void
    {
        $product = $this->makeProduct($this->goodConfig);
        $engine = app(RexEconomicEngine::class);

        for ($i = 0; $i < 300; $i++) {
            $r = $engine->compute($product, 24.90);
            $this->assertTrue($r->valid);
            $this->assertGreaterThan(0.50, $r->amount);
            $this->assertLessThan(7.375, $r->amount);
            $this->assertContains($r->tierNumber, [1, 2, 3]);
        }
    }

    public function test_invalid_when_margin_not_positive(): void
    {
        $product = $this->makeProduct(array_merge($this->goodConfig, ['purchase_cost_ht' => 50]));
        $r = app(RexEconomicEngine::class)->compute($product, 24.90);

        $this->assertFalse($r->valid);
        $this->assertSame('REWARD_ECONOMICS_INVALID', $r->invalidReason);
    }

    public function test_invalid_when_effective_high_below_low(): void
    {
        $product = $this->makeProduct(array_merge($this->goodConfig, ['rex_share_percent' => 1, 'low_bound' => 5.00]));
        $r = app(RexEconomicEngine::class)->compute($product, 24.90);

        $this->assertFalse($r->valid);
        $this->assertSame('REWARD_ECONOMICS_INVALID', $r->invalidReason);
    }

    public function test_invalid_when_config_incomplete(): void
    {
        $product = $this->makeProduct(array_merge($this->goodConfig, ['high_bound' => null]));
        $r = app(RexEconomicEngine::class)->compute($product, 24.90);

        $this->assertFalse($r->valid);
    }

    public function test_throws_when_tier_probabilities_not_100(): void
    {
        $product = $this->makeProduct($this->goodConfig, [
            ['tier_number' => 1, 'range_start_percentage' => 0, 'range_end_percentage' => 100, 'probability_percentage' => 90],
        ]);

        $this->expectException(\InvalidArgumentException::class);
        app(RexEconomicEngine::class)->compute($product, 24.90);
    }

    public function test_economic_snapshot_is_correct(): void
    {
        $product = $this->makeProduct($this->goodConfig);
        $r = app(RexEconomicEngine::class)->compute($product, 24.90);
        $s = $r->economicSnapshot;

        $this->assertEqualsWithDelta(20.75, $s['price_ht'], 0.01);
        $this->assertEqualsWithDelta(14.75, $s['margin'], 0.01);
        $this->assertEqualsWithDelta(7.375, $s['rex_budget'], 0.02);
        $this->assertEqualsWithDelta(7.375, $s['high_bound_effective'], 0.02);
    }
}
