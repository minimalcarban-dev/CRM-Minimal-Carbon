<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Expense;

$lastExpenses = Expense::latest()->take(10)->get();
foreach ($lastExpenses as $e) {
    echo "ID: " . $e->id . "\n";
    echo "Title: " . $e->title . "\n";
    echo "Image Attribute: " . $e->getAttributes()['invoice_image'] . "\n";
    echo "Image Cast: " . json_encode($e->invoice_image) . "\n";
    echo "Image URL: " . $e->invoice_image_url . "\n";
    echo "--------------------------\n";
}
