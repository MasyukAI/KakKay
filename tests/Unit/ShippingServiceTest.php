<?php

declare(strict_types=1);

use App\Services\ShippingService;

it('can calculate shipping cost by method', function () {
    $service = new ShippingService;

    expect($service->calculateShipping('standard'))->toBe(500); // RM5 Standard shipping
    expect($service->calculateShipping('fast'))->toBe(1500);
    expect($service->calculateShipping('express'))->toBe(4900);
    expect($service->calculateShipping('pickup'))->toBe(0);
    expect($service->calculateShipping('unknown'))->toBe(500); // Default to standard
});

it('can get available shipping methods', function () {
    $service = new ShippingService;
    $methods = $service->getAvailableShippingMethods();

    expect($methods)->toBeArray()
        ->and($methods)->toHaveCount(4);

    // Check structure
    foreach ($methods as $method) {
        expect($method)->toHaveKeys(['id', 'name', 'description', 'price', 'estimated_days']);
    }

    // Check specific methods
    $methodIds = array_column($methods, 'id');
    expect($methodIds)->toContain('standard', 'fast', 'express', 'pickup');
});

it('can get shipping method by id', function () {
    $service = new ShippingService;

    $standard = $service->getShippingMethodById('standard');
    expect($standard)->not->toBeNull()
        ->and($standard['id'])->toBe('standard')
        ->and($standard['price'])->toBe(500);

    $express = $service->getShippingMethodById('express');
    expect($express)->not->toBeNull()
        ->and($express['id'])->toBe('express')
        ->and($express['price'])->toBe(4900);

    $unknown = $service->getShippingMethodById('unknown');
    expect($unknown)->toBeNull();
});

it('can calculate weight-based shipping', function () {
    $service = new ShippingService;

    // Under 2kg - base charge only
    expect($service->calculateShippingByWeight(1500, 'standard'))->toBe(500);
    expect($service->calculateShippingByWeight(2000, 'standard'))->toBe(500);

    // Over 2kg - add surcharge
    expect($service->calculateShippingByWeight(2500, 'standard'))->toBe(1000); // 500 + 500 for 0.5kg extra
    expect($service->calculateShippingByWeight(3000, 'standard'))->toBe(1000); // 500 + 500 for 1kg extra
    expect($service->calculateShippingByWeight(3500, 'standard'))->toBe(1500); // 500 + 1000 for 1.5kg extra

    // With express shipping
    expect($service->calculateShippingByWeight(2500, 'express'))->toBe(5400); // 4900 + 500
});

it('can check if shipping is required', function () {
    $service = new ShippingService;

    // Physical items require shipping
    $physicalItems = [
        ['is_digital' => false],
        ['is_digital' => false],
    ];
    expect($service->isShippingRequired($physicalItems))->toBeTrue();

    // Mixed items require shipping
    $mixedItems = [
        ['is_digital' => true],
        ['is_digital' => false],
    ];
    expect($service->isShippingRequired($mixedItems))->toBeTrue();

    // All digital items don't require shipping
    $digitalItems = [
        ['is_digital' => true],
        ['is_digital' => true],
    ];
    expect($service->isShippingRequired($digitalItems))->toBeFalse();

    // Items without is_digital flag require shipping (default behavior)
    $unknownItems = [
        ['name' => 'Item 1'],
        ['name' => 'Item 2'],
    ];
    expect($service->isShippingRequired($unknownItems))->toBeTrue();
});
