<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Order;

$o = Order::whereNotNull('tracking_status')->orderBy('updated_at', 'desc')->first();
if ($o) {
    echo "ID: " . $o->id . "\n";
    echo "Status: " . $o->tracking_status . "\n";
    echo "History: " . json_encode($o->tracking_history, JSON_PRETTY_PRINT) . "\n";
} else {
    echo "No tracked orders found.\n";
}
