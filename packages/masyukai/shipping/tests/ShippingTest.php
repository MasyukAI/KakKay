<?php

use MasyukAI\Shipping\Facades\Shipping;
use MasyukAI\Shipping\Models\Shipment;

it('can get available shipping methods', function () {
    $methods = Shipping::getShippingMethods();

    expect($methods)->toBeArray()
        ->and($methods)->toHaveCount(4)
        ->and($methods['standard'])->toHaveKeys(['name', 'description', 'price', 'estimated_days'])
        ->and($methods['standard']['name'])->toBe('Standard Shipping');
});

it('can calculate shipping cost', function () {
    $items = [
        ['id' => 1, 'quantity' => 1, 'weight' => 500],
        ['id' => 2, 'quantity' => 2, 'weight' => 300],
    ];

    $cost = Shipping::calculateCost($items, 'standard');

    expect($cost)->toBe(500); // Base standard shipping cost
});

it('can calculate weight-based shipping cost', function () {
    $items = [
        ['id' => 1, 'quantity' => 1, 'weight' => 2500], // 2.5kg - over threshold
    ];

    $cost = Shipping::calculateCost($items, 'standard');

    expect($cost)->toBe(1000); // 500 base + 500 surcharge for 0.5kg extra
});

it('can get shipping quotes', function () {
    $items = [
        ['id' => 1, 'quantity' => 1, 'weight' => 500],
    ];

    $quotes = Shipping::getQuotes($items);

    expect($quotes)->toBeArray()
        ->and($quotes)->toHaveCount(4);

    foreach ($quotes as $quote) {
        expect($quote)->toHaveKeys(['method_id', 'method_name', 'description', 'cost', 'estimated_days', 'provider']);
    }
});

it('can create a shipment', function () {
    $shipment = Shipment::create([
        'shippable_type' => 'App\Models\Order',
        'shippable_id' => 1,
        'provider' => 'local',
        'method' => 'standard',
        'destination_address' => [
            'name' => 'John Doe',
            'line1' => '123 Main St',
            'city' => 'Kuala Lumpur',
            'state' => 'WP',
            'postal_code' => '50000',
            'country' => 'MY',
        ],
        'weight' => 1500,
        'cost' => 500,
    ]);

    $result = Shipping::createShipment($shipment);

    expect($result)->toHaveKeys(['success', 'tracking_number'])
        ->and($result['success'])->toBeTrue()
        ->and($result['tracking_number'])->toStartWith('LOCAL-');

    $shipment->refresh();
    expect($shipment->tracking_number)->not->toBeNull()
        ->and($shipment->status)->toBe('created');
});

it('can get tracking information', function () {
    $trackingNumber = 'LOCAL-123456';

    $trackingInfo = Shipping::getTrackingInfo($trackingNumber);

    expect($trackingInfo)->toHaveKeys(['tracking_number', 'status', 'estimated_delivery', 'events'])
        ->and($trackingInfo['tracking_number'])->toBe($trackingNumber)
        ->and($trackingInfo['events'])->toBeArray();
});