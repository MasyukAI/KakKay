<?php

declare(strict_types=1);

use MasyukAI\Cart\Facades\Cart;

uses()->group('cart', 'conditions');

beforeEach(function () {
    Cart::clear();
    Cart::clearConditions();
});

it('clears conditions when cart becomes empty after removing all items', function () {
    // Add item to cart
    Cart::add('product-1', 'Test Product', 5000, 1);

    // Add shipping condition
    Cart::addShipping('shipping', 990, 'standard');

    // Verify cart has items and conditions
    expect(Cart::isEmpty())->toBeFalse()
        ->and(Cart::getItems())->toHaveCount(1)
        ->and(Cart::getConditions())->toHaveCount(1)
        ->and(Cart::getCondition('shipping'))->not->toBeNull();

    // Remove the item
    Cart::remove('product-1');

    // If cart is empty, clear it completely (simulating what Livewire component should do)
    if (Cart::isEmpty()) {
        Cart::clear(); // This deletes the cart and all related data (items, conditions, etc.)
    }

    // Verify cart is empty and conditions are cleared
    expect(Cart::isEmpty())->toBeTrue()
        ->and(Cart::getItems())->toHaveCount(0)
        ->and(Cart::getConditions())->toHaveCount(0)
        ->and(Cart::getCondition('shipping'))->toBeNull();
});

it('clears multiple conditions when cart becomes empty', function () {
    // Add item to cart
    Cart::add('product-1', 'Test Product', 5000, 1);

    // Add multiple conditions
    Cart::addShipping('shipping', 990, 'standard');
    Cart::addTax('tax', '6%');
    Cart::addDiscount('promo', '-10%');

    // Verify cart has items and conditions
    expect(Cart::isEmpty())->toBeFalse()
        ->and(Cart::getItems())->toHaveCount(1)
        ->and(Cart::getConditions())->toHaveCount(3);

    // Remove the item
    Cart::remove('product-1');

    // If cart is empty, clear it completely
    if (Cart::isEmpty()) {
        Cart::clear(); // This deletes the cart and all related data
    }

    // Verify cart is empty and ALL conditions are cleared
    expect(Cart::isEmpty())->toBeTrue()
        ->and(Cart::getItems())->toHaveCount(0)
        ->and(Cart::getConditions())->toHaveCount(0)
        ->and(Cart::getCondition('shipping'))->toBeNull()
        ->and(Cart::getCondition('tax'))->toBeNull()
        ->and(Cart::getCondition('promo'))->toBeNull();
});

it('keeps conditions when items remain in cart after removal', function () {
    // Add multiple items to cart
    Cart::add('product-1', 'Test Product 1', 5000, 1);
    Cart::add('product-2', 'Test Product 2', 3000, 1);

    // Add shipping condition
    Cart::addShipping('shipping', 990, 'standard');

    // Verify cart has items and conditions
    expect(Cart::isEmpty())->toBeFalse()
        ->and(Cart::getItems())->toHaveCount(2)
        ->and(Cart::getConditions())->toHaveCount(1);

    // Remove one item
    Cart::remove('product-1');

    // Don't clear cart since it's not empty
    if (Cart::isEmpty()) {
        Cart::clear();
    }

    // Verify cart still has items and conditions remain
    expect(Cart::isEmpty())->toBeFalse()
        ->and(Cart::getItems())->toHaveCount(1)
        ->and(Cart::getConditions())->toHaveCount(1)
        ->and(Cart::getCondition('shipping'))->not->toBeNull();
});

it('does not clear conditions if cart is not empty', function () {
    // Add item with quantity 2
    Cart::add('product-1', 'Test Product', 5000, 2);

    // Add shipping condition
    Cart::addShipping('shipping', 990, 'standard');

    // Verify cart has items and conditions
    expect(Cart::isEmpty())->toBeFalse()
        ->and(Cart::getItems())->toHaveCount(1)
        ->and(Cart::getConditions())->toHaveCount(1);

    // Update quantity to 1 (still has items)
    Cart::update('product-1', ['quantity' => ['value' => 1]]);

    // Don't clear cart since it's not empty
    if (Cart::isEmpty()) {
        Cart::clear();
    }

    // Verify cart still has items and conditions remain
    expect(Cart::isEmpty())->toBeFalse()
        ->and(Cart::getItems())->toHaveCount(1)
        ->and(Cart::get('product-1')->quantity)->toBe(1)
        ->and(Cart::getConditions())->toHaveCount(1)
        ->and(Cart::getCondition('shipping'))->not->toBeNull();
});
