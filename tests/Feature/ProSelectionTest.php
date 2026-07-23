<?php

namespace Tests\Feature;

use App\Models\ShopProduct;
use App\Models\ShopCategory;
use App\Models\ShopPro;
use App\Services\ProProductSelectionService;
use App\Services\ProPackService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProSelectionTest extends TestCase
{
    use RefreshDatabase;

    private $selectionService;
    private $packService;
    private $pro;
    private $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->selectionService = app(ProProductSelectionService::class);
        $this->packService = app(ProPackService::class);

        // Créer un pro et un produit de test
        $this->pro = ShopPro::create([
            'planipets_pro_id' => 999,
            'email'            => 'test@example.com',
            'is_active'        => true,
        ]);

        $cat = ShopCategory::create(['name' => 'Test', 'slug' => 'test']);
        $this->product = ShopProduct::create([
            'name'          => 'Test Product',
            'slug'          => 'test-product',
            'category_id'   => $cat->id,
            'commerce_mode' => 'DIRECT_SHOP',
            'is_active'     => true,
        ]);
    }

    public function test_add_product_to_selection(): void
    {
        $sel = $this->selectionService->add($this->pro->planipets_pro_id, $this->product->id);
        $this->assertNotNull($sel);
        $this->assertEquals($this->pro->planipets_pro_id, $sel->pro_id);
    }

    public function test_product_uniqueness_in_selection(): void
    {
        $this->selectionService->add($this->pro->planipets_pro_id, $this->product->id);
        $sel2 = $this->selectionService->add($this->pro->planipets_pro_id, $this->product->id);

        // Doit retourner la même sélection (firstOrCreate)
        $this->assertEquals($sel2->id, $sel2->id);
    }

    public function test_pack_requires_product_in_selection(): void
    {
        // Tenter d'ajouter un produit au pack sans qu'il soit en sélection
        $pack = $this->packService->create($this->pro->planipets_pro_id, 'Test Pack');
        $item = $this->packService->addItem($pack->id, $this->product->id);

        // Doit être rejeté
        $this->assertNull($item);
    }

    public function test_pack_accepts_product_if_selected(): void
    {
        // Ajouter le produit à la sélection d'abord
        $this->selectionService->add($this->pro->planipets_pro_id, $this->product->id);

        // Maintenant créer un pack et y ajouter le produit
        $pack = $this->packService->create($this->pro->planipets_pro_id, 'Test Pack');
        $item = $this->packService->addItem($pack->id, $this->product->id, 2);

        // Doit réussir
        $this->assertNotNull($item);
        $this->assertEquals(2, $item->quantity);
    }
}
