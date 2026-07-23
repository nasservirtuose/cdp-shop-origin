<?php

namespace App\Support;

use App\Models\ShopPro;
use Illuminate\Support\Facades\Auth;

class CurrentPro
{
    public static function pro(): ?ShopPro
    {
        return Auth::guard('pro')->user();
    }

    public static function id(): int
    {
        $pro = Auth::guard('pro')->user();
        abort_if($pro === null, 403, 'Aucun pro authentifié.');

        return $pro->proId();
    }
}