<?php

use App\Models\Order;
use App\Services\ShippingTrackingService;
use Illuminate\Support\Facades\Log;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "Starting bulk tracking sync...\n";

$trackingService = app(ShippingTrackingService::class);

// Fetch orders that have a tracking number or a tracking URL
$orders = Order::whereNotNull('tracking_number')
    ->orWhereNotNull('tracking_url')
    ->get();

$total = $orders->count();
echo "Found {$total} orders with tracking info.\n";

$count = 0;
$successCount = 0;
$failCount = 0;

foreach ($orders as $order) {
    $count++;
    echo "[{$count}/{$total}] Syncing Order #{$order->id} (Tracking: {$order->tracking_number})... ";

    try {
        $result = $trackingService->syncOrderTracking($order);

        if ($result['success']) {
            echo "SUCCESS: " . $result['message'] . "\n";
            $successCount++;
        } else {
            echo "FAILED: " . $result['message'] . "\n";
            $failCount++;
        }
    } catch (\Exception $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
        $failCount++;
    }

    // Add a small delay to avoid hitting API rate limits too hard
    usleep(500000); // 0.5 seconds
}

echo "\n------------------------------------------------\n";
echo "Sync Complete.\n";
echo "Total: {$total}\n";
echo "Success: {$successCount}\n";
echo "Failed: {$failCount}\n";
echo "------------------------------------------------\n";
