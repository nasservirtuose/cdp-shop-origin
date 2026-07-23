<?php

namespace App\Services;

use App\Models\ProPack;
use App\Models\ProPackItem;
use App\Models\ShopProduct;
use Illuminate\Support\Str;

class ProPackService
{
    private ProProductSelectionService $selectionService;

    public function __construct(ProProductSelectionService $selectionService)
    {
        $this->selectionService = $selectionService;
    }

    /**
     * Créer un nouveau pack pour le pro.
     */
    public function create(int $proId, string $name, ?string $description = null): ProPack
    {
        return ProPack::create([
            'pro_id'     => $proId,
            'uuid'       => Str::uuid(),
            'name'       => $name,
            'description' => $description,
            'status'     => 'DRAFT',
        ]);
    }

    /**
     * Ajouter un produit au pack.
     * **RÈGLE M1** : le produit doit être en Ma sélection du pro (beforehand).
     */
    public function addItem(int $packId, int $productId, int $quantity = 1): ?ProPackItem
    {
        $pack = ProPack::find($packId);
        if (!$pack) {
            return null;
        }

        // Vérifier que le produit existe et est actif
        if (!ShopProduct::where('id', $productId)->where('is_active', true)->exists()) {
            return null;
        }

        // **RÈGLE : le produit doit être dans Ma sélection du pro**
        if (!$this->selectionService->isSelected($pack->pro_id, $productId)) {
            return null; // Rejeter silencieusement (ou logger)
        }

        return ProPackItem::firstOrCreate(
            ['pack_id' => $packId, 'product_id' => $productId],
            ['quantity' => $quantity, 'sort_order' => 0]
        );
    }

    /**
     * Retirer un produit du pack.
     */
    public function removeItem(int $packId, int $productId): bool
    {
        return ProPackItem::where('pack_id', $packId)
            ->where('product_id', $productId)
            ->delete() > 0;
    }

    /**
     * Lister les packs du pro avec leurs items.
     */
    public function listPacks(int $proId)
    {
        return ProPack::where('pro_id', $proId)
            ->with('items.product')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Récupérer un pack avec ses items (pour affichage/édition).
     */
    public function getPack(int $packId, int $proId): ?ProPack
    {
        return ProPack::where('id', $packId)
            ->where('pro_id', $proId)
            ->with('items.product')
            ->first();
    }

    /**
     * Calculer le total du pack (somme des prix des produits × quantités).
     * Les totaux ne sont JAMAIS stockés — recalculés à chaque affichage.
     */
    public function getPackTotal(ProPack $pack): array
    {
        $items = $pack->items;

        $totalPrice = 0;
        foreach ($items as $item) {
            if ($item->product) {
                // À ce stade M1, on n'a pas de prix sur le produit.
                // Placeholder : utilise un prix fictif ou laisse vide.
                // En M3, ce sera le vrai prix depuis la config économique.
                $totalPrice += 0; // TODO: récupérer le prix réel en M3
            }
        }

        return [
            'total_price' => $totalPrice,
            'item_count'  => $items->count(),
        ];
    }
}
