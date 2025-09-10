<?php

declare(strict_types=1);

use MasyukAI\Cart\Facades\Cart;

it('debugs exact values in integer transformer flow', function () {
    // Setup integer transformer like the working test
    config([
        'cart.price_formatting.auto_format' => true,
        'cart.price_formatting.transformer' => \MasyukAI\Cart\PriceTransformers\IntegerPriceTransformer::class,
    ]);

    // Reset the formatter to pick up new config
    \MasyukAI\Cart\Support\PriceFormatManager::resetFormatting();

    // Add item and track each step
    Cart::add('item-1', 'Test Item', 19.99, 1);

    $item = Cart::getItems()->first();
    expect($item->price)->toBeFloat();
    expect($item->getPrice())->toBeString();
    expect(gettype($item->price))->toBe('double');
    expect(gettype($item->getPrice()))->toBe('string');

    // Test getPriceSum directly
    $priceSum = $item->getPriceSum();
    expect($priceSum)->toBeString();
    expect(gettype($priceSum))->toBe('string');

    // Test the cart subtotal calculation
    $cartSubtotal = Cart::subtotal();
    expect($cartSubtotal)->toBeString();
    expect(gettype($cartSubtotal))->toBe('string');

    // Now enable formatting and test again
    Cart::formatted();
    $formattedSubtotal = Cart::subtotal();
    expect($formattedSubtotal)->toBeString();
    expect(gettype($formattedSubtotal))->toBe('string');

    expect(true)->toBeTrue();
});
