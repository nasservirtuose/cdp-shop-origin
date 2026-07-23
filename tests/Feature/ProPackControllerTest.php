<?php

namespace Tests\Feature;

use App\Models\ShopCategory;
use App\Models\ShopProduct;
use App\Models\ShopPro;
use App\Models\ProProductSelection;
use App\Models\ProPack;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProPackControllerTest extends TestCase
{
    use RefreshDatabase;

    private ShopPro $pro;
    private ShopProduct $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pro = ShopPro::create(['planipets_pro_id' => 555, 'email' => 'p@x.z', 'is_active' => true]);
        $cat = ShopCategory::create(['name' => 'C', 'slug' => 'c']);
        $this->product = ShopProduct::create([
            'name' => 'P', 'slug' => 'p', 'category_id' => $cat->id,
            'commerce_mode' => 'DIRECT_SHOP', 'is_active' => true,
        ]);
    }

    public function test_pro_can_create_pack(): void
    {
        $this->actingAs($this->pro, 'pro')
            ->post(route('pro.packs.store'), ['name' => 'Mon pack'])
            ->assertRedirect();

        $this->assertDatabaseHas('pro_packs', ['pro_id' => 555, 'name' => 'Mon pack']);
    }

    public function test_cannot_add_item_not_in_selection(): void
    {
        $pack = ProPack::create(['pro_id' => 555, 'name' => 'X']);

        $this->actingAs($this->pro, 'pro')
            ->post(route('pro.packs.items.add', $pack), ['product_id' => $this->product->id])
            ->assertRedirect();

        $this->assertDatabaseCount('pro_pack_items', 0);
    }

    public function test_can_add_item_when_in_selection(): void
    {
        ProProductSelection::create(['pro_id' => 555, 'product_id' => $this->product->id]);
        $pack = ProPack::create(['pro_id' => 555, 'name' => 'X']);

        $this->actingAs($this->pro, 'pro')
            ->post(route('pro.packs.items.add', $pack), ['product_id' => $this->product->id, 'quantity' => 3])
            ->assertRedirect();

        $this->assertDatabaseHas('pro_pack_items', ['pack_id' => $pack->id, 'product_id' => $this->product->id, 'quantity' => 3]);
    }

    public function test_cannot_touch_another_pros_pack(): void
    {
        ShopPro::create(['planipets_pro_id' => 777, 'email' => 'o@x.z', 'is_active' => true]);
        $otherPack = ProPack::create(['pro_id' => 777, 'name' => 'Other']);

        $this->actingAs($this->pro, 'pro')
            ->get(route('pro.packs.show', $otherPack))
            ->assertForbidden();
    }
}
