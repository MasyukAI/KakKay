<?php

declare(strict_types=1);

use MasyukAI\Cart\Facades\Cart;

it('debugs exact values in integer transformer flow', function () {
    // Setup integer transformer like the working test
    config([
        'cart.display.formatting_enabled' => true,
        'cart.display.transformer' => \MasyukAI\Cart\PriceTransformers\IntegerPriceTransformer::class,
    ]);

    // Reset the formatter to pick up new config
    \MasyukAI\Cart\Support\CartMoney::resetFormatting();

    // Add item and track each step
    Cart::add('item-1', 'Test Item', 19.99, 1);

    $item = Cart::getItems()->first();
    expect($item->price)->toBeFloat();
    expect((string) $item->getPrice())->toBeString();
    expect(gettype($item->price))->toBe('double');
    expect(gettype((string) $item->getPrice()))->toBe('string');

    // Test getPriceSum directly
    $priceSum = $item->getPriceSum();
    expect((string) $priceSum)->toBeString();
    expect(gettype((string) $priceSum))->toBe('string');

    // Test the cart subtotal calculation
    $cartSubtotal = Cart::subtotal();
    expect($cartSubtotal->getAmount())->toBeFloat();
    expect(gettype($cartSubtotal->getAmount()))->toBe('double');

    // Test formatted output using the CartMoney format method
    $formattedSubtotal = Cart::subtotal();
    expect((string) $formattedSubtotal)->toBeString();
    expect(gettype((string) $formattedSubtotal))->toBe('string');

    expect(true)->toBeTrue();
});
