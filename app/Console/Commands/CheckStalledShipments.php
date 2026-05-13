<?php

namespace App\Console\Commands;

use App\Models\Admin;
use App\Models\Order;
use App\Models\OrderInvestigation;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CheckStalledShipments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-stalled-shipments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically detect stalled shipments based on tracking history and status.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for stalled shipments...');

        $shippedStatuses = ['r_order_shipped', 'd_order_shipped', 'j_order_shipped'];

        $orders = Order::whereIn('diamond_status', $shippedStatuses)
            ->whereNotNull('tracking_number')
            ->where(function ($q) {
                $q->whereDoesntHave('investigation')
                    ->orWhereHas('investigation', function ($iq) {
                        $iq->whereNotIn('investigation_status', ['Resolved', 'Delivered']);
                    });
            })
            ->get();

        $stalledCount = 0;

        $systemAdmin = Admin::where('is_super', true)->first();

        if (!$systemAdmin) {
            $this->error('No super admin found to create investigations.');
            return 1;
        }

        $adminId = $systemAdmin->id;

        foreach ($orders as $order) {

            if ($order->investigation) {
                continue;
            }

            $isStalled = false;
            $reason = '';

            $status = strtolower($order->tracking_status ?? '');

            $keywords = [
                'exception',
                'delayed',
                'returned',
                'address',
                'held',
                'stalled',
                'problem'
            ];

            foreach ($keywords as $keyword) {

                if (str_contains($status, $keyword)) {

                    if ($order->tracking_history && count($order->tracking_history) > 0) {

                        $trackingHistory = $order->tracking_history;

                        $lastEvent = end($trackingHistory);

                        $lastDate = Carbon::parse($lastEvent['date'] ?? now());

                        if ($lastDate->diffInDays(now()) >= 3) {

                            $isStalled = true;

                            $reason = "No tracking updates for more than 3 days (Last update: "
                                . $lastDate->format('d M Y') . ")";
                        }
                    }

                    break;
                }
            }

            if ($isStalled) {

                OrderInvestigation::create([
                    'order_id' => $order->id,
                    'created_by' => $adminId,
                    'customer_name' => $order->client_name,
                    'courier_name' => $order->shipping_company_name,
                    'tracking_number' => $order->tracking_number,
                    'shipment_status' => $order->tracking_status,
                    'investigation_status' => 'Pending',
                    'last_tracking_update' => now(),
                    'investigation_notes' => [
                        [
                            'time' => now()->toDateTimeString(),
                            'admin' => 'System Autodetect',
                            'text' => "Auto-detected stalled shipment. Reason: {$reason}"
                        ]
                    ]
                ]);

                $stalledCount++;

                $this->line("Created investigation for Order #{$order->id}");
            }
        }

        $this->info("Completed. Detected {$stalledCount} new stalled shipments.");

        return 0;
    }
}