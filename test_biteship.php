<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $service = app(App\Services\BiteshipService::class);
    $rates = $service->getRates([
        'origin_postal_code' => 10110, 
        'destination_postal_code' => 41183, 
        'couriers' => 'jne,sicepat,jnt', 
        'items' => [
            ['name'=>'x','value'=>10000,'weight'=>1000,'quantity'=>1]
        ]
    ]);
    print_r($rates);
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
