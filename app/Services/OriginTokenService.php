<?php

namespace App\Services;

use App\Models\OriginPublicToken;

class OriginTokenService
{
    private const PREFIX = 'a24f';
    private const ALPHABET = '0123456789abcdefghijklmnopqrstuvwxyz';
    private const RANDOM_LENGTH = 10;

    /** Token ACTIF du pro, créé si besoin. Un seul actif par pro. */
    public function getActiveToken(int $proId): OriginPublicToken
    {
        $token = OriginPublicToken::where('pro_id', $proId)->where('status', 'ACTIVE')->first();
        if ($token) {
            return $token;
        }

        return OriginPublicToken::create([
            'pro_id' => $proId,
            'token'  => $this->generateUniqueToken(),
            'status' => 'ACTIVE',
        ]);
    }

    /** Révoque le token actif du pro et en génère un nouveau. */
    public function regenerate(int $proId): OriginPublicToken
    {
        OriginPublicToken::where('pro_id', $proId)->where('status', 'ACTIVE')
            ->update(['status' => 'REVOKED', 'revoked_at' => now()]);

        return OriginPublicToken::create([
            'pro_id' => $proId,
            'token'  => $this->generateUniqueToken(),
            'status' => 'ACTIVE',
        ]);
    }

    /**
     * Retrouve le pro derrière un token (actif OU révoqué : le mapping token→pro est permanent).
     * Le token N'AUTHENTIFIE JAMAIS. Retourne null si inconnu.
     */
    public function resolveProId(string $token): ?int
    {
        return OriginPublicToken::where('token', $token)->value('pro_id');
    }

    /** Vérifie le format a24f + 10 caractères [a-z0-9]. */
    public function isValidTokenFormat(string $token): bool
    {
        return (bool) preg_match('/^' . self::PREFIX . '[a-z0-9]{' . self::RANDOM_LENGTH . '}$/', $token);
    }

    /**
     * Sépare un slug suffixé par un token.
     * "longea24f7k92x81m4z" → ['slug' => 'longe', 'token' => 'a24f7k92x81m4z'].
     * Si pas de token valide en fin : token = null, slug = chaîne entière.
     */
    public function extractToken(string $slugWithToken): array
    {
        if (preg_match('/^(.*?)(' . self::PREFIX . '[a-z0-9]{' . self::RANDOM_LENGTH . '})$/', $slugWithToken, $m)) {
            return ['slug' => $m[1], 'token' => $m[2]];
        }

        return ['slug' => $slugWithToken, 'token' => null];
    }

    private function generateToken(): string
    {
        $random = '';
        $max = strlen(self::ALPHABET) - 1;
        for ($i = 0; $i < self::RANDOM_LENGTH; $i++) {
            $random .= self::ALPHABET[random_int(0, $max)]; // random_int = générateur sûr
        }

        return self::PREFIX . $random;
    }

    private function generateUniqueToken(): string
    {
        do {
            $token = $this->generateToken();
        } while (OriginPublicToken::where('token', $token)->exists());

        return $token;
    }
}
