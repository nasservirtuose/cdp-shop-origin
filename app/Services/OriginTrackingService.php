<?php

namespace App\Services;

use App\Models\OriginVisit;

class OriginTrackingService
{
    private const COOKIE_DAYS = 30;

    public function __construct(private OriginTokenService $tokenService) {}

    /**
     * Enregistre un "touch" Origin (règle Last Click : le dernier pro cliqué gagne).
     * Retourne le OriginVisit, ou null si le token est inconnu (aucune attribution).
     */
    public function recordTouch(string $visitorUuid, string $token, ?string $landingUrl = null, ?string $referrer = null): ?OriginVisit
    {
        $proId = $this->tokenService->resolveProId($token);
        if ($proId === null) {
            return null; // token inconnu → on ne touche à rien
        }

        $now = now();
        $visit = OriginVisit::firstOrNew(['visitor_uuid' => $visitorUuid]);

        if (!$visit->exists) {
            $visit->first_touch_at = $now;
        }

        // Last Click : on écrase l'attribution avec le dernier pro cliqué
        $visit->origin_token  = $token;
        $visit->pro_id        = $proId;
        $visit->landing_url   = $landingUrl;
        $visit->referrer      = $referrer;
        $visit->last_touch_at = $now;
        $visit->expires_at    = $now->copy()->addDays(self::COOKIE_DAYS);
        $visit->save();

        return $visit;
    }

    /**
     * Pro attribué à un visiteur (Last Click, non expiré). null si aucun ou expiré.
     * C'est ce que M3 utilisera pour attribuer une vente au bon pro.
     */
    public function getAttributedProId(string $visitorUuid): ?int
    {
        return OriginVisit::where('visitor_uuid', $visitorUuid)
            ->where('expires_at', '>', now())
            ->value('pro_id');
    }
}
