<?php

namespace App\Console\Commands\Shop;

use App\Models\KooneoWebhookEvent;
use App\Services\Shop\KooneoEventProcessor;
use Illuminate\Console\Command;

class ReprocessKooneoEvents extends Command
{
    protected $signature = 'shop:reprocess-kooneo-events {--status=received,error}';

    protected $description = 'Reprocess Kooneo webhook events by processing status.';

    public function handle(KooneoEventProcessor $processor): int
    {
        $raw = (string) $this->option('status');
        $statuses = array_values(array_filter(array_map('trim', explode(',', $raw))));

        if ($statuses === []) {
            $this->error('No status provided. Example: --status=received,error');

            return self::FAILURE;
        }

        $events = KooneoWebhookEvent::query()
            ->whereIn('processing_status', $statuses)
            ->orderBy('id')
            ->get();

        $rows = [];

        foreach ($events as $event) {
            $before = $event->processing_status;
            $processor->process($event);
            $event->refresh();

            $rows[] = [
                'id' => $event->id,
                'event_type' => $event->event_type,
                'before' => $before,
                'after' => $event->processing_status,
            ];
        }

        $this->table(['id', 'event_type', 'before', 'after'], $rows);
        $this->info('Processed events: ' . count($rows));

        return self::SUCCESS;
    }
}
