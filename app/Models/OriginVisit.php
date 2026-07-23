<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OriginVisit extends Model
{
    protected $fillable = [
        'visitor_uuid', 'origin_token', 'pro_id',
        'landing_url', 'referrer', 'first_touch_at', 'last_touch_at', 'expires_at',
    ];

    protected $casts = [
        'first_touch_at' => 'datetime',
        'last_touch_at'  => 'datetime',
        'expires_at'     => 'datetime',
    ];
}
