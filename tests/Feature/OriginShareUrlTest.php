<?php

namespace Tests\Feature;

use App\Services\OriginTokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OriginShareUrlTest extends TestCase
{
    use RefreshDatabase;

    public function test_build_product_url_format(): void
    {
        config(['services.planipets.public_url' => 'https://planipets.com']);
        $service = app(OriginTokenService::class);

        $url = $service->buildProductUrl(1, 'longe-cuir');
        $token = $service->getActiveToken(1)->token;

        $this->assertSame('https://planipets.com/produit/longe-cuir' . $token, $url);
        $this->assertStringEndsWith($token, $url);
        $this->assertTrue($service->isValidTokenFormat($token));
    }

    public function test_trailing_slash_in_base_is_handled(): void
    {
        config(['services.planipets.public_url' => 'https://planipets.com/']);
        $service = app(OriginTokenService::class);

        $url = $service->buildProductUrl(2, 'panier');
        $this->assertStringStartsWith('https://planipets.com/produit/panier', $url);
        $this->assertStringNotContainsString('com//produit', $url);
    }
}
