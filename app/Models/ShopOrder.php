<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'planipets_client_id',
        'injection_status',
        'injection_response',
        'injected_at',
        'cdp_reward_id',
    ];

    protected $casts = [
        'amount_cents' => 'integer',
        'paid_at' => 'datetime',
        'economic_snapshot' => 'array',
        'reward_amount' => 'decimal:2',
        'reward_tier' => 'integer',
        'reward_draw_context' => 'array',
        'planipets_client_id' => 'integer',
        'injection_response' => 'array',
        'injected_at' => 'datetime',
        'cdp_reward_id' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (ShopOrder $order) {
            if (empty($order->uuid)) {
                $order->uuid = (string) Str::uuid();
            }
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(ShopProduct::class, 'product_id');
    }
}
