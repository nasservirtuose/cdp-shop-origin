<?php

namespace App\Http\Controllers\Pro;

use App\Http\Controllers\Controller;
use App\Models\ShopProduct;
use App\Models\ShopCategory;
use App\Services\ProProductSelectionService;
use App\Services\ProProductFavoriteService;
use App\Services\OriginTokenService;
use App\Support\CurrentPro;

class ProCatalogController extends Controller
{
    public function __construct(
        private ProProductSelectionService $selectionService,
        private ProProductFavoriteService $favoriteService,
        private OriginTokenService $originTokenService,
    ) {}

    public function index()
    {
        $proId = CurrentPro::id();
        $categories = ShopCategory::where('is_active', true)
            ->with(['products' => function ($q) {
                $q->where('is_active', true);
            }])
            ->get();

        $allProducts = ShopProduct::where('is_active', true)->get();

        $selectedIds = $this->selectionService->list($proId)->pluck('product_id')->toArray();
        $favoriteIds = $this->favoriteService->list($proId)->pluck('product_id')->toArray();

        return view('pro.catalog.index', [
            'categories'  => $categories,
            'products'    => $allProducts,
            'selectedIds' => $selectedIds,
            'favoriteIds' => $favoriteIds,
        ]);
    }

    public function show(ShopProduct $product)
    {
        $proId = CurrentPro::id();

        return view('pro.catalog.show', [
            'product'    => $product,
            'isSelected' => $this->selectionService->isSelected($proId, $product->id),
            'isFavorite' => $this->favoriteService->isFavorite($proId, $product->id),
            'shareUrl'   => $this->originTokenService->buildProductUrl($proId, $product->slug),
        ]);
    }
}
