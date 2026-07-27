<?php

namespace App\Console\Commands\Shop;

use App\Models\ShopOrder;
use App\Services\Cdp\CdpInjectionClient;
use Illuminate\Console\Command;

class InjectPendingOrders extends Command
{
    protected $signature = 'shop:inject-pending {--limit=100} {--dry-run}';

    protected $description = 'Inject pending shop orders into CDP.';

    public function handle(CdpInjectionClient $client): int
    {
        $orders = ShopOrder::query()
            ->where('injection_status', 'pending_cdp')
            ->whereNotNull('planipets_client_id')
            ->where('reward_amount', '>', 0)
            ->orderBy('id')
            ->limit((int) $this->option('limit'))
            ->get();

        $rows = [];
        $dryRun = (bool) $this->option('dry-run');

        foreach ($orders as $order) {
            $before = $order->injection_status;

            if ($dryRun) {
                $rows[] = [
                    'id' => $order->id,
                    'client' => $order->planipets_client_id,
                    'pro' => $order->origin_pro_id,
                    'amount' => number_format((float) $order->reward_amount, 2, '.', ''),
                    'before' => $before,
                    'after' => 'dry-run',
                ];

                continue;
            }

            $result = $client->inject($order);

            $rows[] = [
                'id' => $order->id,
                'client' => $order->planipets_client_id,
                'pro' => $order->origin_pro_id,
                'amount' => number_format((float) $order->reward_amount, 2, '.', ''),
                'before' => $before,
                'after' => $result->status,
            ];

            $update = [
                'injection_response' => [
                    'status' => $result->status,
                    'reward_id' => $result->rewardId,
                    'origin_pro_id' => $result->originProId,
                    'error_message' => $result->errorMessage,
                    'http_status' => $result->httpStatus,
                    'raw_response' => $result->rawResponse,
                ],
            ];

            if (in_array($result->status, ['created', 'duplicate'], true)) {
                $update['injection_status'] = 'injected';
                $update['cdp_reward_id'] = $result->rewardId;
                $update['injected_at'] = now();
            } elseif ($result->status === 'orphan') {
                $update['injection_status'] = 'orphan';
                $update['injected_at'] = now();
            } else {
                $update['injection_status'] = 'error';
            }

            $order->update($update);
        }

        $this->table(['id', 'client', 'pro', 'amount', 'before', 'after'], $rows);
        $this->info('Processed orders: ' . count($rows) . ($dryRun ? ' (dry-run)' : ''));

        return self::SUCCESS;
    }
}
