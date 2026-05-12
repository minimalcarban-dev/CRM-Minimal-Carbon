<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class FixStaleTrackingStatus extends Command
{
    protected $signature = 'orders:fix-stale-tracking';
    protected $description = 'Fix orders stuck on "In transit" when their tracking events already show "Out for delivery" or "Delivered"';

    public function handle()
    {
        $this->info('Scanning orders for stale tracking statuses...');

        // Find orders with tracking_status = "In transit" that have tracking_history
        $orders = Order::whereNotNull('tracking_history')
            ->where(function ($q) {
                $q->whereRaw("LOWER(tracking_status) = ?", ['in transit'])
                    ->orWhereRaw("LOWER(tracking_status) = ?", ['pick up'])
                    ->orWhereRaw("LOWER(tracking_status) = ?", ['info received']);
            })
            ->get();

        $this->info("Found {$orders->count()} orders to check.");

        $hierarchy = [
            'Not found' => 0,
            'Info received' => 1,
            'Pick up' => 2,
            'In transit' => 3,
            'Out for delivery' => 4,
            'Delivered' => 5,
        ];

        $promotionKeywords = [
            'Out for delivery' => [
                'out for delivery',
                'out_for_delivery',
                'outfordelivery',
                'out for del',
                'delivering',
                'with delivery courier',
                'out for del.',
                'on vehicle for delivery',
            ],
            'Delivered' => [
                'delivered',
                'shipment delivered',
                'successfully delivered',
                'delivery confirmed',
                'received by',
            ],
        ];

        $fixedCount = 0;

        foreach ($orders as $order) {
            $history = $order->tracking_history;
            if (!is_array($history) || empty($history)) {
                continue;
            }

            $currentStatus = $order->tracking_status;
            $currentRank = $hierarchy[$currentStatus] ?? -1;

            $promotedStatus = $currentStatus;
            $promotedRank = $currentRank;

            // Scan the latest 5 events (history is newest-first)
            $recentEvents = array_slice($history, 0, 5);

            foreach ($recentEvents as $event) {
                $combined = strtolower(
                    ($event['status'] ?? '') . ' ' . ($event['description'] ?? '')
                );

                foreach ($promotionKeywords as $targetStatus => $keywords) {
                    $targetRank = $hierarchy[$targetStatus] ?? -1;
                    if ($targetRank <= $promotedRank) {
                        continue;
                    }

                    foreach ($keywords as $kw) {
                        if (str_contains($combined, $kw)) {
                            $promotedStatus = $targetStatus;
                            $promotedRank = $targetRank;
                            break 2;
                        }
                    }
                }
            }

            if ($promotedStatus !== $currentStatus) {
                $order->update(['tracking_status' => $promotedStatus]);
                $fixedCount++;
                $this->line(
                    "  ✅ Order #{$order->id} (#{$order->tracking_number}): " .
                    "\"{$currentStatus}\" → \"{$promotedStatus}\""
                );
            }
        }

        $this->newLine();
        $this->info("Done! Fixed {$fixedCount} out of {$orders->count()} orders.");

        if ($fixedCount > 0) {
            Log::info("FixStaleTrackingStatus: Promoted {$fixedCount} orders to correct tracking status.");
        }
    }
}
