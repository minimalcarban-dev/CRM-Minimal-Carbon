<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Expense;
use App\Http\Controllers\ExpenseController;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Facades\Log;

// Mock Auth
$admin = \App\Models\Admin::first();
Auth::guard('admin')->login($admin);

// Create a mock request with a file
$file = UploadedFile::fake()->image('invoice.jpg');
$request = new Request([
    'date' => '2026-03-09',
    'title' => 'Test Logic Fix',
    'amount' => 100,
    'transaction_type' => 'out',
    'payment_method' => 'cash',
    'paid_to_received_from' => 'Test Vendor',
], [], [], [], ['invoice_image' => $file]);

// We can't easily mock facades in a standalone script like this because of how Laravel's container works 
// with statics, but we can verify the controller's code logic manually or check if it throws errors.

echo "Testing store method logic...\n";

try {
    // This will likely fail because Cloudinary is not configured or will actually try to upload if configured.
    // But our goal is to see if it saves an empty object {} or nothing.
    $controller = new ExpenseController();
    $controller->store($request);
    echo "Store called.\n";
} catch (\Exception $e) {
    echo "Caught expected or unexpected exception: " . $e->getMessage() . "\n";
}

$last = Expense::latest()->first();
echo "Last ID: " . $last->id . "\n";
echo "Title: " . $last->title . "\n";
echo "Image data: " . json_encode($last->invoice_image) . "\n";
?>