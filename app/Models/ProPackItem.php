<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProPackItem extends Model
{
    protected $fillable = ['pack_id', 'product_id', 'quantity', 'sort_order'];

    protected $attributes = ['quantity' => 1, 'sort_order' => 0];

    protected $casts = ['quantity' => 'integer', 'sort_order' => 'integer'];

    public function pack(): BelongsTo
    {
        return $this->belongsTo(ProPack::class, 'pack_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(ShopProduct::class, 'product_id');
    }
}
