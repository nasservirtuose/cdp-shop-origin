<?php

namespace App\Enums;

enum CommerceMode: string
{
    case DIRECT_SHOP = 'DIRECT_SHOP';
    case EXTERNAL_AFFILIATE = 'EXTERNAL_AFFILIATE';
    case PARTNER = 'PARTNER';
}
