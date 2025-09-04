<?php

use App\Services\PaymentMethodService;

it('can get available payment methods', function () {
    $service = new PaymentMethodService;
    $methods = $service->getAvailablePaymentMethods();

    expect($methods)->toBeArray()
        ->and($methods)->toHaveCount(7); // Should get default methods
});

it('can get default payment methods as fallback', function () {
    $service = new PaymentMethodService;
    $methods = $service->getDefaultPaymentMethods();

    expect($methods)->toBeArray()
        ->and($methods)->not->toBeEmpty()
        ->and($methods)->toHaveCount(7);

    // Check that all required fields are present
    foreach ($methods as $method) {
        expect($method)->toHaveKeys(['id', 'name', 'description', 'icon', 'group']);
    }
});

it('can get payment method display name', function () {
    $service = new PaymentMethodService;

    expect($service->getPaymentMethodDisplayName('fpx_b2c'))
        ->toBe('FPX Online Banking');

    expect($service->getPaymentMethodDisplayName('tng_ewallet'))
        ->toBe('Touch \'n Go eWallet');

    // Test fallback for unknown method
    expect($service->getPaymentMethodDisplayName('unknown_method'))
        ->toBe('Unknown method');
});

it('can get payment method description', function () {
    $service = new PaymentMethodService;

    expect($service->getPaymentMethodDescription('fpx_b2c'))
        ->toBe('Bayar dengan Internet Banking Malaysia');

    expect($service->getPaymentMethodDescription('visa'))
        ->toBe('Bayar dengan kad kredit atau debit Visa');
});

it('can get payment method icon', function () {
    $service = new PaymentMethodService;

    expect($service->getPaymentMethodIcon('fpx_b2c'))
        ->toBe('building-office');

    expect($service->getPaymentMethodIcon('visa'))
        ->toBe('credit-card');

    expect($service->getPaymentMethodIcon('tng_ewallet'))
        ->toBe('wallet');
});

it('can get payment method group', function () {
    $service = new PaymentMethodService;

    expect($service->getPaymentMethodGroup('fpx_b2c'))
        ->toBe('banking');

    expect($service->getPaymentMethodGroup('visa'))
        ->toBe('card');

    expect($service->getPaymentMethodGroup('tng_ewallet'))
        ->toBe('ewallet');

    expect($service->getPaymentMethodGroup('duitnow_qr'))
        ->toBe('qr');
});

it('can get grouped payment methods', function () {
    $service = new PaymentMethodService;
    $grouped = $service->getGroupedPaymentMethods();

    expect($grouped)->toBeArray();

    // Should have banking, card, ewallet, and qr groups
    expect($grouped)->toHaveKeys(['banking', 'card', 'ewallet', 'qr']);
});

it('can format payment methods', function () {
    $service = new PaymentMethodService;

    $methods = [
        ['name' => 'fpx_b2c'],
        ['name' => 'visa'],
    ];

    $formatted = $service->formatPaymentMethods($methods);

    expect($formatted)->toBeArray()
        ->and($formatted)->toHaveCount(2);

    foreach ($formatted as $method) {
        expect($method)->toHaveKeys(['id', 'name', 'description', 'icon', 'group']);
    }
});
