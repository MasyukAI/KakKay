<?php

declare(strict_types=1);

use MasyukAI\Cart\Facades\Cart;

it('demonstrates that core cart package shipping methods work perfectly', function () {
    session()->flush();

    // Test core Cart package shipping functionality
    Cart::instance('shipping-test');
    Cart::add('product-1', 'Product 1', 100.00, 1);

    // Using core Cart package methods (the only approach now)
    Cart::addShipping('Express Shipping', 15.99, 'express', [
        'carrier' => 'UPS',
        'estimated_days' => 1,
    ]);

    $shipping = Cart::getShipping();
    $method = Cart::getShippingMethod();
    $value = Cart::getShippingValue();
    $total = Cart::total();

    // Verify all shipping functionality works correctly
    expect($shipping)->not->toBeNull()
        ->and($shipping->getName())->toBe('Express Shipping')
        ->and($shipping->getType())->toBe('shipping')
        ->and($shipping->getAttribute('carrier'))->toBe('UPS')
        ->and($shipping->getAttribute('estimated_days'))->toBe(1)
        ->and($method)->toBe('express')
        ->and($value)->toBe(15.99)
        ->and($total)->toBe(115.99); // 100 + 15.99

    // Test replacement functionality
    Cart::addShipping('Standard Shipping', 9.99, 'standard');

    expect(Cart::getShippingMethod())->toBe('standard')
        ->and(Cart::getShippingValue())->toBe(9.99)
        ->and(Cart::total())->toBe(109.99) // 100 + 9.99
        ->and(Cart::getConditions()->count())->toBe(1); // Only one shipping condition

    // Test removal
    Cart::removeShipping();

    expect(Cart::getShipping())->toBeNull()
        ->and(Cart::getShippingMethod())->toBeNull()
        ->and(Cart::getShippingValue())->toBeNull()
        ->and(Cart::total())->toBe(100.00); // Back to original total
});
