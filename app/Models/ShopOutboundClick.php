<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopOutboundClick extends Model
{
    protected $fillable = [
        'click_uuid',
        'visitor_uuid',
        'product_id',
        'commerce_mode',
        'origin_token',
        'pro_id',
        'provider',
        'destination_url',
    ];
}
