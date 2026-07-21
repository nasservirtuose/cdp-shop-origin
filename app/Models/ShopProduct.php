<?php

namespace App\Models;

use App\Enums\CommerceMode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
    ];

    protected $casts = [
        'commerce_mode' => CommerceMode::class,
        'affiliate_reward_enabled' => 'boolean',
        'is_active' => 'boolean',
        'is_public' => 'boolean',
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
}
