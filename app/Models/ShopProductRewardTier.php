<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShopProductRewardTier extends Model
{
    protected $fillable = [
        'product_id',
        'tier_number',
        'range_start_percentage',
        'range_end_percentage',
        'probability_percentage',
        'is_active',
    ];

    protected $casts = [
        'range_start_percentage' => 'decimal:2',
        'range_end_percentage' => 'decimal:2',
        'probability_percentage' => 'integer',
        'is_active' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(ShopProduct::class, 'product_id');
    }
}
