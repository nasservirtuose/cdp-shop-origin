<?php

namespace Tests\Feature;

use Firebase\JWT\JWT;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanipetsSsoTest extends TestCase
{
    use RefreshDatabase;

    private string $secret = 'test-secret-shop-sso';

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.planipets.jwt_secret' => $this->secret]);
    }

    private function mint(array $claims, ?string $secret = null): string
    {
        return JWT::encode($claims, $secret ?? $this->secret, 'HS256');
    }

    public function test_valid_token_logs_in_and_creates_pro(): void
    {
        $token = $this->mint(['user_id' => 4242, 'email' => 'pro@example.com', 'name' => 'Dr Test', 'jti' => 'jti-1', 'exp' => time() + 300]);

        $this->get('/auth/planipets?token=' . $token)->assertRedirect(route('pro.dashboard'));
        $this->assertDatabaseHas('shop_pros', ['planipets_pro_id' => 4242, 'email' => 'pro@example.com']);
        $this->assertTrue(auth('pro')->check());
        $this->assertSame(4242, auth('pro')->user()->proId());
    }

    public function test_bad_signature_is_rejected(): void
    {
        $token = $this->mint(['user_id' => 1, 'email' => 'x@y.z', 'exp' => time() + 300], 'WRONG-secret');

        $this->get('/auth/planipets?token=' . $token)->assertRedirect(route('pro.login.page'));
        $this->assertFalse(auth('pro')->check());
        $this->assertDatabaseCount('shop_pros', 0);
    }

    public function test_replayed_jti_is_rejected(): void
    {
        $token = $this->mint(['user_id' => 7, 'email' => 'a@b.c', 'jti' => 'once', 'exp' => time() + 300]);

        $this->get('/auth/planipets?token=' . $token)->assertRedirect(route('pro.dashboard'));
        $this->get('/auth/planipets?token=' . $token)->assertRedirect(route('pro.login.page'));
    }

    public function test_pro_pages_require_auth(): void
    {
        $this->get('/pro/dashboard')->assertRedirect(route('pro.login.page'));
    }
}