<?php

use Illuminate\Support\Facades\Route;

Route::post('/midtrans', function () {
    // Handle midtrans webhook
});

Route::post('/biteship', [\App\Http\Controllers\Webhook\BiteshipController::class, 'handle']);
