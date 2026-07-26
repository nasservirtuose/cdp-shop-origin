<?php

namespace Tests\Feature;

use App\Models\ShopCategory;
use App\Models\ShopOrder;
use App\Models\ShopOutboundClick;
use App\Models\ShopProduct;
use App\Services\Shop\CommerceProviderRouter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CommerceProviderRouterTest extends TestCase
{
    use RefreshDatabase;

    private function makeProduct(array $config): ShopProduct
    {
        $cat = ShopCategory::create(['name' => 'C', 'slug' => Str::random(10)]);

        return ShopProduct::create(array_merge([
            'name' => 'P',
            'slug' => Str::random(10),
            'category_id' => $cat->id,
            'is_active' => true,
        ], $config));
    }

    public function test_direct_shop_routes_to_kooneo_with_origin_tag(): void
    {
        $product = $this->makeProduct([
            'commerce_mode' => 'DIRECT_SHOP',
            'external_checkout_url' => 'https://checkout.kooneo.com/p/abc',
        ]);

        $res = app(CommerceProviderRouter::class)->route($product, 'a24f0123456789', 4242, 'visitor-1');

        $this->assertSame('KOONEO', $res['provider']);
        $this->assertStringStartsWith('https://checkout.kooneo.com/p/abc', $res['destination_url']);
        $this->assertStringContainsString('tag_origin=a24f0123456789', $res['destination_url']);
        $this->assertSame(1, ShopOutboundClick::count());
        $this->assertSame(0, ShopOrder::count());
    }

    public function test_no_origin_token_uses_direct_tag(): void
    {
        $product = $this->makeProduct([
            'commerce_mode' => 'DIRECT_SHOP',
            'external_checkout_url' => 'https://checkout.kooneo.com/p/abc?ref=x',
        ]);

        $res = app(CommerceProviderRouter::class)->route($product);

        $this->assertStringContainsString('tag_origin=direct', $res['destination_url']);
        $this->assertStringContainsString('&tag_origin=', $res['destination_url']);
    }

    public function test_external_affiliate_uses_affiliate_url_and_provider(): void
    {
        $product = $this->makeProduct([
            'commerce_mode' => 'EXTERNAL_AFFILIATE',
            'seller_provider' => 'awin',
            'affiliate_product_url' => 'https://www.awin1.com/cread.php?x=1',
        ]);

        $res = app(CommerceProviderRouter::class)->route($product, 'a24f0123456789', 4242);

        $this->assertSame('AWIN', $res['provider']);
        $this->assertStringStartsWith('https://www.awin1.com/cread.php', $res['destination_url']);
    }

    public function test_throws_when_no_checkout_url(): void
    {
        $product = $this->makeProduct([
            'commerce_mode' => 'DIRECT_SHOP',
            'external_checkout_url' => null,
        ]);

        $this->expectException(\InvalidArgumentException::class);
        app(CommerceProviderRouter::class)->route($product);
    }
}
