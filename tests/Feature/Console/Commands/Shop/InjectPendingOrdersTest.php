<?php

namespace Tests\Feature\Console\Commands\Shop;

use App\Models\ShopCategory;
use App\Models\ShopOrder;
use App\Models\ShopProduct;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class InjectPendingOrdersTest extends TestCase
{
    use RefreshDatabase;

    private function makeProduct(array $overrides = []): ShopProduct
    {
        $category = ShopCategory::create([
            'name' => 'Category ' . Str::random(6),
            'slug' => Str::slug(Str::random(10)) . '-' . Str::random(4),
        ]);

        return ShopProduct::create(array_merge([
            'name' => 'Product ' . Str::random(6),
            'slug' => Str::slug(Str::random(10)) . '-' . Str::random(4),
            'category_id' => $category->id,
            'commerce_mode' => 'DIRECT_SHOP',
            'external_reference' => 'EXT-' . Str::upper(Str::random(8)),
            'is_active' => true,
            'is_public' => true,
            'price_ttc' => 49,
            'vat_percent' => 20,
            'purchase_cost_ht' => 10,
            'variable_costs_ht' => 5,
            'rex_share_percent' => 30,
            'low_bound' => 1,
            'high_bound' => 5,
        ], $overrides));
    }

    private function makeOrder(array $overrides = []): ShopOrder
    {
        $product = $this->makeProduct();

        return ShopOrder::create(array_merge([
            'provider' => 'kooneo',
            'provider_transaction_id' => 'txn_' . Str::random(8),
            'product_id' => $product->id,
            'origin_pro_id' => 777,
            'amount_cents' => 4990,
            'currency' => 'EUR',
            'payment_status' => 'PAID',
            'reward_status' => 'DRAWN',
            'reward_amount' => 7.50,
            'planipets_client_id' => 12345,
            'injection_status' => 'pending_cdp',
        ], $overrides));
    }

    public function test_successful_injection_marks_order_injected(): void
    {
        $order = $this->makeOrder();

        Http::fake([
            '*' => Http::response([
                'status' => 'created',
                'reward_id' => 8888,
                'origin_pro_id' => 777,
            ], 201),
        ]);

        $this->artisan('shop:inject-pending')
            ->assertExitCode(0);

        $order->refresh();

        $this->assertSame('injected', $order->injection_status);
        $this->assertSame(8888, $order->cdp_reward_id);
        $this->assertNotNull($order->injected_at);
        $this->assertSame('created', $order->injection_response['status']);
    }

    public function test_order_without_client_id_is_not_selected(): void
    {
        $selected = $this->makeOrder();
        $ignored = $this->makeOrder(['planipets_client_id' => null]);

        Http::fake([
            '*' => Http::response(['status' => 'created', 'reward_id' => 8888, 'origin_pro_id' => 777], 201),
        ]);

        $this->artisan('shop:inject-pending')
            ->assertExitCode(0);

        $selected->refresh();
        $ignored->refresh();

        $this->assertSame('injected', $selected->injection_status);
        $this->assertSame('pending_cdp', $ignored->injection_status);
        $this->assertNull($ignored->injected_at);
    }

    public function test_orphan_response_marks_order_orphan(): void
    {
        $order = $this->makeOrder();

        Http::fake([
            '*' => Http::response(['status' => 'orphan'], 200),
        ]);

        $this->artisan('shop:inject-pending')
            ->assertExitCode(0);

        $order->refresh();

        $this->assertSame('orphan', $order->injection_status);
        $this->assertNotNull($order->injected_at);
        $this->assertSame('orphan', $order->injection_response['status']);
    }

    public function test_dry_run_does_not_update_database(): void
    {
        $order = $this->makeOrder();

        Http::fake();

        $this->artisan('shop:inject-pending', ['--dry-run' => true])
            ->assertExitCode(0);

        Http::assertNothingSent();

        $order->refresh();

        $this->assertSame('pending_cdp', $order->injection_status);
        $this->assertNull($order->cdp_reward_id);
        $this->assertNull($order->injected_at);
    }
}
