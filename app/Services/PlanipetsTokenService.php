<?php

namespace App\Services;

use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PlanipetsTokenService
{
    private string $secret;

    public function __construct()
    {
        $raw = config('services.planipets.jwt_secret') ?? '';

        if (empty($raw)) {
            throw new \RuntimeException('PLANIPETS_JWT_SECRET is not configured.');
        }

        $this->secret = $raw;
    }

    public function validate(string $token): array
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secret, 'HS256'));
        } catch (ExpiredException $e) {
            throw new \InvalidArgumentException('Token has expired.');
        } catch (\Firebase\JWT\SignatureInvalidException $e) {
            Log::error('[SHOP] JWT signature verification failed', ['error' => $e->getMessage()]);
            throw new \InvalidArgumentException('Token signature is invalid.');
        } catch (\Exception $e) {
            Log::error('[SHOP] JWT decode failed', ['error' => $e->getMessage()]);
            throw new \InvalidArgumentException('Invalid token: ' . $e->getMessage());
        }

        $payload = (array) $decoded;

        $userId = $payload['user_id'] ?? $payload['sub'] ?? null;
        if (empty($userId)) {
            throw new \InvalidArgumentException('Token missing required field: user_id or sub');
        }
        $payload['user_id'] = (int) $userId;
        $payload['sub'] = (int) $userId;

        if (empty($payload['email'])) {
            throw new \InvalidArgumentException('Token missing required field: email');
        }

        if (!empty($payload['jti'])) {
            $cacheKey = 'planipets_token_used:' . $payload['jti'];
            if (Cache::has($cacheKey)) {
                throw new \InvalidArgumentException('Token has already been used.');
            }
            Cache::put($cacheKey, true, now()->addMinutes(10));
        }

        Log::info('[SHOP] Planipets JWT validated', ['pro_id' => $payload['user_id']]);

        return $payload;
    }
}