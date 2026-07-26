<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ShopOrder extends Model
{
    protected $fillable = [
        'provider',
        'provider_transaction_id',
        'product_id',
        'origin_pro_id',
        'origin_token',
        'origin_status',
        'amount_cents',
        'currency',
        'payment_status',
        'paid_at',
        'reward_status',
        'economic_snapshot',
        'reward_amount',
        'reward_tier',
        'reward_draw_context',
    ];

    protected $casts = [
        'amount_cents' => 'integer',
        'paid_at' => 'datetime',
        'economic_snapshot' => 'array',
        'reward_amount' => 'decimal:2',
        'reward_tier' => 'integer',
        'reward_draw_context' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (ShopOrder $order) {
            if (empty($order->uuid)) {
                $order->uuid = (string) Str::uuid();
            }
        });
    }
}
