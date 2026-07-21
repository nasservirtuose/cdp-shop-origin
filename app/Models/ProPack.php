<?php

namespace App\Models;

use App\Enums\PackStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ProPack extends Model
{
    protected $fillable = ['pro_id', 'name', 'slug', 'description', 'status'];

    protected $attributes = ['status' => 'DRAFT'];

    protected $casts = ['status' => PackStatus::class];

    protected static function booted(): void
    {
        static::creating(function (ProPack $pack) {
            if (empty($pack->uuid)) {
                $pack->uuid = (string) Str::uuid();
            }
        });
    }

    public function items(): HasMany
    {
        return $this->hasMany(ProPackItem::class, 'pack_id');
    }
}
