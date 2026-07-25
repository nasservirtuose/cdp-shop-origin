<?php

namespace App\Models;

use App\Enums\CommerceMode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ShopProduct extends Model
{
    protected $attributes = [
        'commerce_mode'            => 'DIRECT_SHOP',
        'affiliate_reward_enabled' => false,
        'is_active'                => true,
        'is_public'                => false,
    ];

    protected $fillable = [
        'name',
        'slug',
        'short_description',
        'description',
        'main_image',
        'category_id',
        'commerce_mode',
        'seller_provider',
        'external_reference',
        'external_checkout_url',
        'affiliate_provider_id',
        'affiliate_program_id',
        'affiliate_product_url',
        'affiliate_reward_enabled',
        'is_active',
        'is_public',
        'price_ttc',
        'vat_percent',
        'purchase_cost_ht',
        'variable_costs_ht',
        'rex_share_percent',
        'low_bound',
        'high_bound',
    ];

    protected $casts = [
        'commerce_mode' => CommerceMode::class,
        'affiliate_reward_enabled' => 'boolean',
        'is_active' => 'boolean',
        'is_public' => 'boolean',
        'price_ttc' => 'decimal:2',
        'vat_percent' => 'decimal:2',
        'purchase_cost_ht' => 'decimal:2',
        'variable_costs_ht' => 'decimal:2',
        'rex_share_percent' => 'decimal:2',
        'low_bound' => 'decimal:2',
        'high_bound' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (ShopProduct $product) {
            if (empty($product->uuid)) {
                $product->uuid = (string) Str::uuid();
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ShopCategory::class, 'category_id');
    }

    public function rewardTiers(): HasMany
    {
        return $this->hasMany(ShopProductRewardTier::class, 'product_id');
    }
}
