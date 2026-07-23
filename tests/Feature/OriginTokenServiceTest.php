<?php

namespace Tests\Feature;

use App\Models\OriginPublicToken;
use App\Services\OriginTokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OriginTokenServiceTest extends TestCase
{
    use RefreshDatabase;

    private OriginTokenService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(OriginTokenService::class);
    }

    public function test_token_has_correct_format(): void
    {
        $token = $this->service->getActiveToken(1);
        $this->assertMatchesRegularExpression('/^a24f[a-z0-9]{10}$/', $token->token);
        $this->assertTrue($this->service->isValidTokenFormat($token->token));
    }

    public function test_one_active_token_per_pro(): void
    {
        $t1 = $this->service->getActiveToken(1);
        $t2 = $this->service->getActiveToken(1);
        $this->assertSame($t1->token, $t2->token);
        $this->assertDatabaseCount('origin_public_tokens', 1);
    }

    public function test_regenerate_revokes_old_and_creates_new(): void
    {
        $old = $this->service->getActiveToken(1);
        $new = $this->service->regenerate(1);

        $this->assertNotSame($old->token, $new->token);
        $this->assertDatabaseHas('origin_public_tokens', ['token' => $old->token, 'status' => 'REVOKED']);
        $this->assertDatabaseHas('origin_public_tokens', ['token' => $new->token, 'status' => 'ACTIVE']);
        $this->assertEquals(1, OriginPublicToken::where('pro_id', 1)->where('status', 'ACTIVE')->count());
    }

    public function test_resolve_pro_id_from_token(): void
    {
        $token = $this->service->getActiveToken(42);
        $this->assertSame(42, $this->service->resolveProId($token->token));
        $this->assertNull($this->service->resolveProId('a24f0000000000'));
    }

    public function test_extract_token_from_slug(): void
    {
        $token = $this->service->getActiveToken(1)->token;
        $result = $this->service->extractToken('longe' . $token);
        $this->assertSame('longe', $result['slug']);
        $this->assertSame($token, $result['token']);

        $none = $this->service->extractToken('longe');
        $this->assertNull($none['token']);
        $this->assertSame('longe', $none['slug']);
    }
}
