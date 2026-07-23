<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class ShopPro extends Authenticatable
{
    protected $table = 'shop_pros';

    protected $fillable = [
        'planipets_pro_id', 'email', 'name', 'phone', 'clinic_name',
        'is_active', 'last_login_at', 'last_synced_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
        'last_synced_at' => 'datetime',
    ];

    public function proId(): int
    {
        return $this->planipets_pro_id;
    }

    public function displayName(): string
    {
        return $this->name ?? "Pro #{$this->planipets_pro_id}";
    }
}