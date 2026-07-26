<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KooneoWebhookEvent extends Model
{
    protected $fillable = [
        'transaction_id',
        'type',
        'order_id',
        'origin_tag',
        'customer_email',
        'product_reference',
        'amount_cents',
        'currency',
        'is_test',
        'payload',
        'received_at',
        'processed_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'is_test' => 'boolean',
        'amount_cents' => 'integer',
        'order_id' => 'integer',
        'received_at' => 'datetime',
        'processed_at' => 'datetime',
    ];
}