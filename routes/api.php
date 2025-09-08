<?php

use App\Http\Controllers\Api\ShippingController;
use Illuminate\Support\Facades\Route;

Route::prefix('shipping')->group(function () {
    // Public shipping endpoints
    Route::get('/methods', [ShippingController::class, 'getMethods']);
    Route::post('/quotes', [ShippingController::class, 'getQuotes']);
    Route::post('/calculate-cost', [ShippingController::class, 'calculateCost']);
    Route::get('/track/{trackingNumber}', [ShippingController::class, 'track']);
    
    // Protected endpoints (require authentication)
    Route::middleware('auth')->group(function () {
        Route::post('/orders/{order}/shipment', [ShippingController::class, 'createShipment']);
    });
});