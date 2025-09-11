<?php

declare(strict_types=1);

use MasyukAI\Cart\Facades\Cart;
use MasyukAI\Cart\Support\CartMoney;

beforeEach(function () {
    Cart::clear();
    CartMoney::resetFormatting();
});

it('can get price amounts when formatting is enabled', function () {
    config(['cart.display.formatting_enabled' => true]);

    Cart::add('item-1', 'Test Item', 19.99, 1);

    expect(Cart::subtotal()->getAmount())->toBe(19.99);
    expect(Cart::total()->getAmount())->toBe(19.99);
    expect(Cart::subtotal()->format())->toBeString();
});

it('returns price amounts when auto_format is disabled', function () {
    config(['cart.display.formatting_enabled' => false]);

    Cart::add('item-1', 'Test Item', 19.99, 1);

    expect(Cart::subtotal()->getAmount())->toBe(19.99);
    expect(Cart::total()->getAmount())->toBe(19.99);
});

it('formats cart item prices correctly', function () {
    config(['cart.display.formatting_enabled' => true]);

    // Reset formatter to pick up new configuration
    \MasyukAI\Cart\Support\CartMoney::resetFormatting();

    Cart::add('item-1', 'Test Item', 19.99, 2);
    $item = Cart::get('item-1');

    expect($item->getPrice()->getAmount())->toBe(19.99);
    expect($item->subtotal()->getAmount())->toBe(39.98);
    expect($item->getPrice()->format())->toBeString();
});

it('can use integer price transformer', function () {
    config([
        'cart.display.formatting_enabled' => true,
        'cart.display.transformer' => \MasyukAI\Cart\PriceTransformers\IntegerPriceTransformer::class,
    ]);

    // Reset the formatter to pick up new config
    CartMoney::resetFormatting();

    // Add item with decimal price (will be stored as cents)
    Cart::add('item-1', 'Test Item', 19.99, 1);

    expect(Cart::subtotal()->getAmount())->toBe(19.99);
    expect(Cart::subtotal()->format())->toBeString();
});
