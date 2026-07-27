<?php

namespace App\Services\Cdp;

use App\Models\ShopOrder;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class CdpInjectionClient
{
    public function inject(ShopOrder $order): CdpInjectionResult
    {
        if ($order->planipets_client_id === null) {
            throw new \InvalidArgumentException('shop order must have planipets_client_id before CDP injection');
        }

        if ((float) $order->reward_amount <= 0) {
            throw new \InvalidArgumentException('shop order must have positive reward_amount before CDP injection');
        }

        $payload = [
            'planipets_client_id' => $order->planipets_client_id,
            'candidate_origin_pro_id' => $order->origin_pro_id,
            'amount' => (float) $order->reward_amount,
            'currency' => $order->currency ?? 'EUR',
            'source_type' => 'SHOP',
            'source_channel' => 'DIRECT',
            'source_id' => $order->id,
            'reward_sender' => 'REX',
            'product_reference' => $order->product?->external_reference,
            'kooneo_transaction_id' => $order->provider_transaction_id,
        ];

        $url = rtrim((string) config('services.cdp.url'), '/') . '/api/internal/shop/reward';

        try {
            $response = Http::withToken((string) config('services.cdp.injection_secret'))
                ->timeout((int) config('services.cdp.timeout_seconds'))
                ->retry(2, 500, fn ($exception) => $exception instanceof ConnectionException, false)
                ->post($url, $payload);
        } catch (ConnectionException $exception) {
            return CdpInjectionResult::error('cdp unreachable: ' . $exception->getMessage());
        }

        $body = $response->json();
        $status = $response->status();

        if ($status === 201 && ($body['status'] ?? null) === 'created') {
            return CdpInjectionResult::created((int) $body['reward_id'], isset($body['origin_pro_id']) ? (int) $body['origin_pro_id'] : null);
        }

        if ($status === 200 && ($body['status'] ?? null) === 'orphan') {
            return CdpInjectionResult::orphan();
        }

        if ($status === 200 && ($body['status'] ?? null) === 'duplicate') {
            return CdpInjectionResult::duplicate((int) $body['reward_id'], isset($body['origin_pro_id']) ? (int) $body['origin_pro_id'] : null);
        }

        if ($status === 401) {
            return CdpInjectionResult::error('unauthorized (check SHOP_INJECTION_SECRET)', 401, is_array($body) ? $body : null);
        }

        if ($status === 422) {
            return CdpInjectionResult::error('validation failed: ' . json_encode($body), 422, is_array($body) ? $body : null);
        }

        if ($response->serverError()) {
            return CdpInjectionResult::error('cdp unreachable: HTTP ' . $status, $status, is_array($body) ? $body : null);
        }

        return CdpInjectionResult::error('cdp unreachable: unexpected response', $status, is_array($body) ? $body : null);
    }
}