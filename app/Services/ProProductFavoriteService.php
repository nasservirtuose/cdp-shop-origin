<?php

namespace App\Services;

use App\Models\ShopProduct;
use App\Models\ProProductFavorite;
use Illuminate\Database\Eloquent\Collection;

class ProProductFavoriteService
{
    public function add(int $proId, int $productId): ?ProProductFavorite
    {
        if (!ShopProduct::where('id', $productId)->where('is_active', true)->exists()) {
            return null;
        }

        return ProProductFavorite::firstOrCreate(
            ['pro_id' => $proId, 'product_id' => $productId]
        );
    }

    public function remove(int $proId, int $productId): bool
    {
        return ProProductFavorite::where('pro_id', $proId)
            ->where('product_id', $productId)
            ->delete() > 0;
    }

    public function list(int $proId): Collection
    {
        return ProProductFavorite::where('pro_id', $proId)
            ->with('product')
            ->get();
    }

    public function isFavorite(int $proId, int $productId): bool
    {
        return ProProductFavorite::where('pro_id', $proId)
            ->where('product_id', $productId)
            ->exists();
    }
}
