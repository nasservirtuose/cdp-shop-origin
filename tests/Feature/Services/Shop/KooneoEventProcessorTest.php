<?php

namespace Tests\Feature\Services\Shop;

use App\Models\KooneoWebhookEvent;
use App\Models\OriginPublicToken;
use App\Models\ShopOrder;
use App\Models\ShopProduct;
use App\Models\ShopProductRewardTier;
use App\Services\Shop\KooneoEventProcessor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KooneoEventProcessorTest extends TestCase
{
    use RefreshDatabase;

    private function basePayload(array $overrides = []): array
    {
        return array_replace_recursive([
            'type' => 'new_payment',
            'customer' => [
                'email' => 'c@test.local',
            ],
            'invoice' => [
                'amount' => 49,
                'currency' => 'EUR',
                'transaction_id' => 'ch_proc_001',
                'order_id' => 42,
                'products' => [
                    [
                        'reference' => 'CONSULT-01',
                        'amount' => 49,
                    ],
                ],
                'tags' => [
                    'origin' => 'a24f0000000001',
                    'client' => '12345',
                ],
            ],
        ], $overrides);
    }

    private function createEvent(array $payload): KooneoWebhookEvent
    {
        return KooneoWebhookEvent::create([
            'event_type' => $payload['type'] ?? 'new_payment',
            'kooneo_transaction_id' => $payload['invoice']['transaction_id'] ?? null,
            'kooneo_order_id' => isset($payload['invoice']['order_id']) ? (string) $payload['invoice']['order_id'] : null,
            'raw_payload' => $payload,
            'received_at' => now(),
            'processing_status' => 'received',
        ]);
    }

    private function createProduct(string $reference = 'CONSULT-01'): ShopProduct
    {
        $product = ShopProduct::create([
            'name' => 'Consult test',
            'slug' => 'consult-test-' . strtolower($reference),
            'external_reference' => $reference,
            'commerce_mode' => 'DIRECT_SHOP',
            'external_checkout_url' => 'https://example.test/pay',
            'is_active' => true,
            'is_public' => true,
            'price_ttc' => 49,
            'vat_percent' => 20,
            'purchase_cost_ht' => 10,
            'variable_costs_ht' => 5,
            'rex_share_percent' => 30,
            'low_bound' => 1,
            'high_bound' => 5,
        ]);

        ShopProductRewardTier::create([
            'product_id' => $product->id,
            'tier_number' => 1,
            'range_start_percentage' => 0,
            'range_end_percentage' => 100,
            'probability_percentage' => 100,
            'is_active' => true,
        ]);

        return $product;
    }

    public function test_valid_new_payment_with_origin_and_client_creates_order_and_marks_processed(): void
    {
        $this->createProduct();
        OriginPublicToken::create([
            'pro_id' => 777,
            'token' => 'a24f0000000001',
            'status' => 'ACTIVE',
        ]);

        $event = $this->createEvent($this->basePayload());

        app(KooneoEventProcessor::class)->process($event);

        $event->refresh();
        $order = ShopOrder::query()->firstOrFail();

        $this->assertSame('processed', $event->processing_status);
        $this->assertNotNull($order->reward_amount);
        $this->assertSame(777, $order->origin_pro_id);
        $this->assertSame(12345, $order->planipets_client_id);
        $this->assertSame('pending_cdp', $order->injection_status);
    }

    public function test_valid_new_payment_without_client_keeps_pending_cdp(): void
    {
        $this->createProduct();
        OriginPublicToken::create([
            'pro_id' => 777,
            'token' => 'a24f0000000001',
            'status' => 'ACTIVE',
        ]);

        $payload = $this->basePayload([
            'invoice' => ['tags' => ['origin' => 'a24f0000000001', 'client' => null]],
        ]);
        $event = $this->createEvent($payload);

        app(KooneoEventProcessor::class)->process($event);

        $order = ShopOrder::query()->firstOrFail();

        $this->assertNull($order->planipets_client_id);
        $this->assertSame('pending_cdp', $order->injection_status);
    }

    public function test_valid_new_payment_without_origin_creates_order_with_null_origin_pro_and_pending_cdp(): void
    {
        $this->createProduct();
        $payload = $this->basePayload([
            'invoice' => ['tags' => ['origin' => null, 'client' => '12345']],
        ]);
        $event = $this->createEvent($payload);

        app(KooneoEventProcessor::class)->process($event);

        $order = ShopOrder::query()->firstOrFail();

        $this->assertNull($order->origin_pro_id);
        $this->assertSame('pending_cdp', $order->injection_status);
    }

    public function test_unknown_product_marks_event_error_and_creates_no_order(): void
    {
        $event = $this->createEvent($this->basePayload([
            'invoice' => ['products' => [['reference' => 'UNKNOWN-REF']]],
        ]));

        app(KooneoEventProcessor::class)->process($event);

        $event->refresh();

        $this->assertSame('error', $event->processing_status);
        $this->assertSame(0, ShopOrder::count());
    }

    public function test_refund_event_is_ignored_and_creates_no_order(): void
    {
        $payload = $this->basePayload(['type' => 'refund']);
        $event = $this->createEvent($payload);

        app(KooneoEventProcessor::class)->process($event);

        $event->refresh();

        $this->assertSame('ignored', $event->processing_status);
        $this->assertSame(0, ShopOrder::count());
    }

    public function test_replaying_same_webhook_keeps_one_order_and_processed_event(): void
    {
        $this->createProduct();
        OriginPublicToken::create([
            'pro_id' => 777,
            'token' => 'a24f0000000001',
            'status' => 'ACTIVE',
        ]);

        $payload = $this->basePayload();
        $event = $this->createEvent($payload);
        $eventReplay = KooneoWebhookEvent::create([
            'event_type' => 'new_payment',
            'kooneo_transaction_id' => null,
            'kooneo_order_id' => '42',
            'raw_payload' => $payload,
            'received_at' => now(),
            'processing_status' => 'received',
        ]);

        $processor = app(KooneoEventProcessor::class);
        $processor->process($event);
        $processor->process($eventReplay);

        $eventReplay->refresh();

        $this->assertSame(1, ShopOrder::count());
        $this->assertSame('processed', $eventReplay->processing_status);
    }
}
