<?php

namespace Tests\Feature\Webhooks;

use App\Models\KooneoWebhookEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KooneoWebhookControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.kooneo.webhook_secret' => 'sekret']);
    }

    private function payload(array $overrides = []): array
    {
        return array_replace_recursive([
            'type' => 'new_payment',
            'customer' => [
                'email' => 'test@example.com',
                'member_id' => 12345,
            ],
            'invoice' => [
                'amount' => 49.00,
                'currency' => 'EUR',
                'transaction_id' => 'ch_test_001',
                'order_id' => 98211,
                'products' => [
                    [
                        'reference' => 'CONSULT-01',
                        'amount' => 49.00,
                    ],
                ],
                'tags' => [
                    'origin' => 'a24f66771',
                ],
            ],
        ], $overrides);
    }

    public function test_rejects_request_without_secret(): void
    {
        $this->postJson('/webhooks/kooneo', $this->payload())->assertStatus(401);
    }

    public function test_rejects_request_with_wrong_secret(): void
    {
        $this->postJson('/webhooks/kooneo?k=wrong', $this->payload())->assertStatus(401);
    }

    public function test_rejects_empty_body(): void
    {
        $this->postJson('/webhooks/kooneo?k=sekret', [])->assertStatus(400);
    }

    public function test_rejects_payload_without_type(): void
    {
        $this->postJson('/webhooks/kooneo?k=sekret', $this->payload(['type' => null]))->assertStatus(400);
    }

    public function test_accepts_valid_new_payment_and_stores(): void
    {
        $response = $this->postJson('/webhooks/kooneo?k=sekret', $this->payload());

        $response->assertOk()
            ->assertJsonStructure(['status', 'event_id'])
            ->assertJson(['status' => 'received']);

        $this->assertDatabaseCount('kooneo_webhook_events', 1);
        $this->assertDatabaseHas('kooneo_webhook_events', [
            'event_type' => 'new_payment',
            'kooneo_transaction_id' => 'ch_test_001',
            'kooneo_order_id' => '98211',
            'processing_status' => 'received',
        ]);
    }

    public function test_is_idempotent_on_same_transaction_id(): void
    {
        $first = $this->postJson('/webhooks/kooneo?k=sekret', $this->payload());
        $second = $this->postJson('/webhooks/kooneo?k=sekret', $this->payload());

        $first->assertOk();
        $second->assertOk();

        $this->assertDatabaseCount('kooneo_webhook_events', 1);
        $this->assertSame(
            $first->json('event_id'),
            $second->json('event_id')
        );
    }

    public function test_stores_raw_payload_verbatim(): void
    {
        $payload = $this->payload();

        $this->postJson('/webhooks/kooneo?k=sekret', $payload)->assertOk();

        $event = KooneoWebhookEvent::query()->firstOrFail();

        $this->assertEquals($payload, $event->raw_payload);
    }
}