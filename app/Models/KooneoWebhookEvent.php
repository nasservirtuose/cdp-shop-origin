<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KooneoWebhookEvent extends Model
{
    protected $fillable = [
        'event_type',
        'kooneo_transaction_id',
        'kooneo_order_id',
        'raw_payload',
        'received_at',
        'processed_at',
        'processing_status',
        'processing_error',
    ];

    protected $casts = [
        'raw_payload' => 'array',
        'received_at' => 'datetime',
        'processed_at' => 'datetime',
    ];
}