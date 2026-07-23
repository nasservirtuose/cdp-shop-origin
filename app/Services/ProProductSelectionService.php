<?php

namespace App\Services;

use App\Models\ShopProduct;
use App\Models\ProProductSelection;
use Illuminate\Database\Eloquent\Collection;

class ProProductSelectionService
{
    /**
     * Ajouter un produit à Ma sélection du pro.
     * Retourne la sélection créée, ou null si le produit n'existe pas.
     */
    public function add(int $proId, int $productId): ?ProProductSelection
    {
        if (!ShopProduct::where('id', $productId)->where('is_active', true)->exists()) {
            return null;
        }

        return ProProductSelection::firstOrCreate(
            ['pro_id' => $proId, 'product_id' => $productId],
            ['sort_order' => 0]
        );
    }

    /**
     * Retirer un produit de Ma sélection.
     */
    public function remove(int $proId, int $productId): bool
    {
        return ProProductSelection::where('pro_id', $proId)
            ->where('product_id', $productId)
            ->delete() > 0;
    }

    /**
     * Lister tous les produits sélectionnés par le pro, avec détails.
     */
    public function list(int $proId): Collection
    {
        return ProProductSelection::where('pro_id', $proId)
            ->orderBy('sort_order')
            ->with('product')
            ->get();
    }

    /**
     * Vérifier si un produit est en sélection (utile pour la règle pack).
     */
    public function isSelected(int $proId, int $productId): bool
    {
        return ProProductSelection::where('pro_id', $proId)
            ->where('product_id', $productId)
            ->exists();
    }
}
