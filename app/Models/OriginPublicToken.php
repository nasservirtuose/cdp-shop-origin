<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OriginPublicToken extends Model
{
    protected $fillable = ['pro_id', 'token', 'status', 'revoked_at'];

    protected $casts = [
        'revoked_at' => 'datetime',
    ];
}
