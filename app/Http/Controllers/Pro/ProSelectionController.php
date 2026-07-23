<?php

namespace App\Http\Controllers\Pro;

use App\Http\Controllers\Controller;
use App\Models\ShopProduct;
use App\Services\ProProductSelectionService;
use App\Services\ProProductFavoriteService;
use App\Services\ProPackService;
use App\Support\CurrentPro;
use Illuminate\Http\Request;

class ProSelectionController extends Controller
{
    public function __construct(
        private ProProductSelectionService $selectionService,
        private ProProductFavoriteService $favoriteService,
        private ProPackService $packService,
    ) {}

    /**
     * Affiche Ma sélection du pro avec 3 onglets : Sélection / Favoris / Packs.
     */
    public function index()
    {
        $proId = CurrentPro::id();

        return view('pro.selection.index', [
            'selections'  => $this->selectionService->list($proId),
            'favorites'   => $this->favoriteService->list($proId),
            'packs'       => $this->packService->listPacks($proId),
        ]);
    }

    /**
     * Ajouter un produit à Ma sélection (AJAX ou formulaire).
     */
    public function addSelection(Request $request)
    {
        $productId = $request->input('product_id');
        $proId = CurrentPro::id();

        $selection = $this->selectionService->add($proId, $productId);

        if (!$selection) {
            return response()->json(['error' => 'Produit invalide ou inactif'], 400);
        }

        return response()->json(['success' => true, 'selection' => $selection]);
    }

    /**
     * Retirer de Ma sélection.
     */
    public function removeSelection(Request $request)
    {
        $productId = $request->input('product_id');
        $proId = CurrentPro::id();

        $removed = $this->selectionService->remove($proId, $productId);

        return response()->json(['success' => $removed]);
    }

    /**
     * Ajouter aux favoris.
     */
    public function addFavorite(Request $request)
    {
        $productId = $request->input('product_id');
        $proId = CurrentPro::id();

        $favorite = $this->favoriteService->add($proId, $productId);

        if (!$favorite) {
            return response()->json(['error' => 'Produit invalide'], 400);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Retirer des favoris.
     */
    public function removeFavorite(Request $request)
    {
        $productId = $request->input('product_id');
        $proId = CurrentPro::id();

        $removed = $this->favoriteService->remove($proId, $productId);

        return response()->json(['success' => $removed]);
    }
}
