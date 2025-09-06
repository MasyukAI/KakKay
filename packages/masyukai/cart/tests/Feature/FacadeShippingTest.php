<?php

declare(strict_types=1);

use MasyukAI\Cart\Facades\Cart;

it('can use shipping methods through the Cart facade', function () {
    session()->flush();

    // Set the cart instance
    Cart::instance('facade-shipping-test');

    // Add items to cart
    Cart::add('product-1', 'Product 1', 100.00, 1);

    // Test facade addShipping method
    Cart::addShipping('Facade Shipping', 12.99, 'standard', [
        'carrier' => 'FedEx',
        'tracking' => true,
    ]);

    expect(Cart::getShipping())->not->toBeNull()
        ->and(Cart::getShippingMethod())->toBe('standard')
        ->and(Cart::getShippingValue())->toBe(12.99)
        ->and(Cart::getShipping()->getAttribute('carrier'))->toBe('FedEx')
        ->and(Cart::getShipping()->getAttribute('tracking'))->toBe(true)
        ->and(Cart::total())->toBe(112.99); // 100 + 12.99

    // Test facade removeShipping method
    Cart::removeShipping();

    expect(Cart::getShipping())->toBeNull()
        ->and(Cart::getShippingMethod())->toBeNull()
        ->and(Cart::getShippingValue())->toBeNull()
        ->and(Cart::total())->toBe(100.00);
});
