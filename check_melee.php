<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

$order = \App\Models\Order::find(217);
echo "Order 217 melee_entries:\n";
echo json_encode($order->melee_entries, JSON_PRETTY_PRINT) . "\n";
echo "\nRaw DB value:\n";
echo \Illuminate\Support\Facades\DB::table('orders')->where('id', 217)->value('melee_entries') . "\n";
