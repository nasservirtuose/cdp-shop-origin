<?php

namespace Tests\Feature;

use App\Models\KooneoWebhookEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KooneoWebhookReceiverTest extends TestCase
{
    use RefreshDatabase;

    private function payload(): array
    {
        return [
            'type' => 'new_payment',
            'version' => '1.0',
            'customer' => [
                'email' => 'client@exemple.fr',
                'firstname' => 'Marie',
                'lastname' => 'Dupont',
                'member_id' => 12345,
            ],
            'invoice' => [
                'amount' => 49.00,
                'currency' => 'EUR',
                'transaction_id' => 'ch_3Qx8fZ',
                'number' => 'F2026-00841',
                'order_id' => 98211,
                'pay_mode' => 'Stripe',
                'is_test' => 0,
                'products' => [[
                    'id' => 77,
                    'reference' => 'CONSULT-01',
                    'name' => 'Consultation',
                    'amount' => 49.00,
                    'qty' => 1,
                ]],
                'tags' => ['origin' => 'a24f66771'],
            ],
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.kooneo.webhook_secret' => 'sekret']);
    }

    public function test_valid_webhook_is_stored(): void
    {
        $this->postJson('/webhooks/kooneo?k=sekret', $this->payload())->assertOk();

        $this->assertDatabaseHas('kooneo_webhook_events', [
            'transaction_id' => 'ch_3Qx8fZ',
            'type' => 'new_payment',
            'order_id' => 98211,
            'origin_tag' => 'a24f66771',
            'customer_email' => 'client@exemple.fr',
            'product_reference' => 'CONSULT-01',
            'amount_cents' => 4900,
            'currency' => 'EUR',
        ]);
    }

    public function test_wrong_secret_is_rejected(): void
    {
        $this->postJson('/webhooks/kooneo?k=WRONG', $this->payload())->assertNotFound();
        $this->assertSame(0, KooneoWebhookEvent::count());
    }

    public function test_missing_secret_is_rejected(): void
    {
        $this->postJson('/webhooks/kooneo', $this->payload())->assertNotFound();
    }

    public function test_duplicate_event_is_stored_once(): void
    {
        $this->postJson('/webhooks/kooneo?k=sekret', $this->payload())->assertOk();
        $this->postJson('/webhooks/kooneo?k=sekret', $this->payload())->assertOk();
        $this->assertSame(1, KooneoWebhookEvent::count());
    }
}