<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;

class SyncOrderTracking extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:sync-tracking';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize tracking status for all orders with tracking numbers';

    /**
     * Execute the console command.
     */
    public function handle(\App\Services\ShippingTrackingService $trackingService)
    {
        $this->info('Starting tracking synchronization...');

        $shippedStatuses = ['r_order_shipped', 'd_order_shipped', 'j_order_shipped'];

        $orders = Order::whereNotNull('tracking_number')
            ->where(function ($q) use ($shippedStatuses) {
                $q->whereIn('diamond_status', $shippedStatuses)
                    ->orWhere('tracking_status', 'In Transit')
                    ->orWhereNull('tracking_status');
            })
            ->where('tracking_status', '!=', 'Delivered')
            ->get();

        $this->info("Found " . $orders->count() . " orders to sync.");

        $bar = $this->output->createProgressBar($orders->count());
        $bar->start();

        foreach ($orders as $order) {
            /** @var \App\Models\Order $order */

            // If tracking URL is missing but we have carrier + number, try to generate it
            if (!$order->tracking_url && $order->shipping_company_name && $order->tracking_number) {
                $order->tracking_url = $trackingService->generateTrackingUrl($order->shipping_company_name, $order->tracking_number);
                $order->save();
            }

            if ($order->tracking_url) {
                $trackingService->syncOrderTracking($order);
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Tracking synchronization completed.');
    }
}
