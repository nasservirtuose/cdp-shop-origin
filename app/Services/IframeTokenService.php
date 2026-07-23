<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class IframeTokenService
{
    private const TTL = 7200;

    public function generate(int $proId): string
    {
        $payload = json_encode(['pid' => $proId, 'exp' => time() + self::TTL]);

        return Crypt::encryptString($payload);
    }

    public function validate(string $token): ?int
    {
        try {
            $json = Crypt::decryptString($token);
            $data = json_decode($json, true);

            if (!$data || empty($data['pid']) || empty($data['exp'])) {
                return null;
            }

            if ($data['exp'] < time()) {
                Log::debug('[SHOP] Iframe token expired', ['pro_id' => $data['pid']]);
                return null;
            }

            return (int) $data['pid'];
        } catch (\Exception $e) {
            Log::warning('[SHOP] Iframe token decryption failed', ['error' => $e->getMessage()]);

            return null;
        }
    }
}