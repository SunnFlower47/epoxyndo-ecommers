<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$order = App\Models\Order::latest()->first();
if ($order) {
    echo "Order ID: " . $order->id . "\n";
    echo "Order Num: " . $order->order_number . "\n";
    echo "Snap Token: " . $order->snap_token . "\n";
    echo "Created At: " . $order->created_at . "\n";
} else {
    echo "No orders found.\n";
}
