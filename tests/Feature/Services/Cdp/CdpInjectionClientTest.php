<?php

namespace Tests\Feature\Services\Cdp;

use App\Models\ShopCategory;
use App\Models\ShopOrder;
use App\Models\ShopProduct;
use App\Services\Cdp\CdpInjectionClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Tests\TestCase;

class CdpInjectionClientTest extends TestCase
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

    public function test_created_response_returns_created_result_and_sends_payload(): void
    {
        $order = $this->makeOrder();

        Http::fake([
            '*' => Http::response([
                'status' => 'created',
                'reward_id' => 9001,
                'origin_pro_id' => 777,
            ], 201),
        ]);

        $result = app(CdpInjectionClient::class)->inject($order);

        Http::assertSent(function ($request) use ($order) {
            return $request->url() === rtrim(config('services.cdp.url'), '/') . '/api/internal/shop/reward'
                && $request->hasHeader('Authorization')
                && $request['planipets_client_id'] === $order->planipets_client_id
                && $request['candidate_origin_pro_id'] === $order->origin_pro_id
                && $request['amount'] === (float) $order->reward_amount
                && $request['currency'] === 'EUR'
                && $request['source_type'] === 'SHOP'
                && $request['source_channel'] === 'DIRECT'
                && $request['source_id'] === $order->id
                && $request['reward_sender'] === 'REX'
                && $request['product_reference'] === $order->product->external_reference
                && $request['kooneo_transaction_id'] === $order->provider_transaction_id;
        });

        $this->assertSame('created', $result->status);
        $this->assertSame(9001, $result->rewardId);
        $this->assertSame(777, $result->originProId);
    }

    public function test_orphan_response_returns_orphan_result(): void
    {
        $order = $this->makeOrder();

        Http::fake([
            '*' => Http::response(['status' => 'orphan'], 200),
        ]);

        $result = app(CdpInjectionClient::class)->inject($order);

        $this->assertSame('orphan', $result->status);
        $this->assertNull($result->rewardId);
    }

    public function test_duplicate_response_returns_duplicate_result(): void
    {
        $order = $this->makeOrder();

        Http::fake([
            '*' => Http::response([
                'status' => 'duplicate',
                'reward_id' => 1234,
                'origin_pro_id' => 777,
            ], 200),
        ]);

        $result = app(CdpInjectionClient::class)->inject($order);

        $this->assertSame('duplicate', $result->status);
        $this->assertSame(1234, $result->rewardId);
        $this->assertSame(777, $result->originProId);
    }

    public function test_unauthorized_response_returns_error_result(): void
    {
        $order = $this->makeOrder();

        Http::fake([
            '*' => Http::response(['message' => 'Unauthorized'], 401),
        ]);

        $result = app(CdpInjectionClient::class)->inject($order);

        $this->assertSame('error', $result->status);
        $this->assertSame(401, $result->httpStatus);
        $this->assertSame('unauthorized (check SHOP_INJECTION_SECRET)', $result->errorMessage);
    }

    public function test_validation_error_returns_error_result_with_raw_body(): void
    {
        $order = $this->makeOrder();

        Http::fake([
            '*' => Http::response(['message' => 'validation failed'], 422),
        ]);

        $result = app(CdpInjectionClient::class)->inject($order);

        $this->assertSame('error', $result->status);
        $this->assertSame(422, $result->httpStatus);
        $this->assertSame(['message' => 'validation failed'], $result->rawResponse);
        $this->assertStringContainsString('validation failed', $result->errorMessage);
    }

    public function test_server_error_returns_error_result(): void
    {
        $order = $this->makeOrder();

        Http::fake([
            '*' => Http::response(['message' => 'server error'], 500),
        ]);

        $result = app(CdpInjectionClient::class)->inject($order);

        $this->assertSame('error', $result->status);
        $this->assertSame(500, $result->httpStatus);
        $this->assertSame('cdp unreachable: HTTP 500', $result->errorMessage);
    }

    public function test_missing_planipets_client_id_throws(): void
    {
        $order = $this->makeOrder(['planipets_client_id' => null]);

        $this->expectException(InvalidArgumentException::class);

        app(CdpInjectionClient::class)->inject($order);
    }

    public function test_positive_reward_amount_required_throws(): void
    {
        $order = $this->makeOrder(['reward_amount' => 0]);

        $this->expectException(InvalidArgumentException::class);

        app(CdpInjectionClient::class)->inject($order);
    }
}
