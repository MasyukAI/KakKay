<?php

declare(strict_types=1);

use MasyukAI\Cart\CartManager;

it('debugs subtotal calculation step by step', function () {
    // Setup integer transformer
    config(['cart.price_formatting.enabled' => true]);
    config(['cart.price_formatting.default_transformer' => 'integer']);

    $cartManager = app(CartManager::class);
    $cartManager->setInstance('session1');
    $cartInstance = $cartManager->getCurrentCart();
    $cartInstance->add('1', 'Test Product', 19.99, 1);

    $cartItem = $cartInstance->getItems()->first();

    // Debug the exact values and transformations
    expect($cartItem->price)->toBeFloat();
    expect($cartItem->quantity)->toBe(1);

    // Get the raw sum before formatting
    $rawSum = $cartItem->getRawPriceSum();
    expect($rawSum)->toBeFloat();
    expect(gettype($rawSum))->toBe('double');

    // Debug formatter behavior
    $formatter = \MasyukAI\Cart\Support\PriceFormatManager::getFormatter();
    expect(get_class($formatter))->toContain('PriceFormatterService');

    // Test the exact formatter flow
    expect($formatter->format($rawSum))->toBeString();

    // Now test the cart subtotal
    expect($cartManager->subtotal())->toBeString();

    // Enable formatting and test again
    $cartManager->formatted();
    expect($cartManager->subtotal())->toBeString();

    expect(true)->toBeTrue();
});
