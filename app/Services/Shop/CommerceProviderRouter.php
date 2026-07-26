<?php

namespace App\Services\Shop;

use App\Models\ShopOutboundClick;
use App\Models\ShopProduct;
use Illuminate\Support\Str;

class CommerceProviderRouter
{
    // Nom du parametre d'origine ajoute a l'URL marchande. A confirmer avec Kooneo.
    private const ORIGIN_TAG_PARAM = 'tag_origin';

    /**
     * Decide le marchand et construit l'URL de sortie (cote serveur), puis
     * enregistre un clic sortant. UN CLIC NE CREE JAMAIS DE RECOMPENSE.
     *
     * @return array{provider:string, destination_url:string, click:ShopOutboundClick}
     */
    public function route(ShopProduct $product, ?string $originToken = null, ?int $proId = null, ?string $visitorUuid = null): array
    {
        $mode = $product->commerce_mode?->value ?? 'DIRECT_SHOP';

        [$provider, $base] = match ($mode) {
            'EXTERNAL_AFFILIATE' => [strtoupper($product->seller_provider ?? 'AFFILIATE'), $product->affiliate_product_url ?: $product->external_checkout_url],
            'PARTNER' => ['PARTNER', $product->external_checkout_url],
            default => ['KOONEO', $product->external_checkout_url],
        };

        if (empty($base)) {
            throw new \InvalidArgumentException("Aucun lien d'achat configure pour le produit #{$product->id} (mode {$mode}).");
        }

        $destination = $this->appendParam($base, self::ORIGIN_TAG_PARAM, $originToken ?: 'direct');

        $click = ShopOutboundClick::create([
            'click_uuid' => (string) Str::uuid(),
            'visitor_uuid' => $visitorUuid,
            'product_id' => $product->id,
            'commerce_mode' => $mode,
            'origin_token' => $originToken,
            'pro_id' => $proId,
            'provider' => $provider,
            'destination_url' => $destination,
        ]);

        return ['provider' => $provider, 'destination_url' => $destination, 'click' => $click];
    }

    private function appendParam(string $url, string $key, string $value): string
    {
        $sep = str_contains($url, '?') ? '&' : '?';

        return $url . $sep . $key . '=' . urlencode($value);
    }
}
