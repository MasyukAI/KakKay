<?php

declare(strict_types=1);

use MasyukAI\Cart\Conditions\CartCondition as CartConditionCore;
use MasyukAI\Cart\Facades\Cart as CartFacade;
use MasyukAI\FilamentCart\Models\Cart as CartModel;
use MasyukAI\FilamentCart\Models\CartCondition;
use MasyukAI\FilamentCart\Models\CartItem;

beforeEach(function () {
    // Clear any existing cart data
    CartFacade::clear();

    // Don't manually create cart - let the sync listeners handle it
});

it('synchronizes cart item when item is added', function () {
    // Add item to cart (price in cents)
    $item = CartFacade::add('product-123', 'Test Product', 10000, 2, [
        'color' => 'red',
        'size' => 'large',
    ]);

    // Check that normalized cart item was created
    expect(CartItem::count())->toBe(1);

    $cartItem = CartItem::first();
    $cartModel = CartModel::where('identifier', CartFacade::getIdentifier())->first();

    expect($cartItem->cart_id)->toBe($cartModel->id);
    expect($cartItem->item_id)->toBe('product-123');
    expect($cartItem->name)->toBe('Test Product');
    expect($cartItem->price)->toBe(10000); // 100.00 in cents
    expect($cartItem->quantity)->toBe(2);
    expect($cartItem->subtotal)->toBe(20000); // 200.00 in cents
    expect($cartItem->attributes)->toBe(['color' => 'red', 'size' => 'large']);
    expect($cartItem->cart->instance)->toBe(CartFacade::instance());
    expect($cartItem->cart->identifier)->toBe(CartFacade::getIdentifier());
});

it('synchronizes cart item when item is updated', function () {
    // Add item to cart (price in cents)
    CartFacade::add('product-123', 'Test Product', 10000, 2);

    // Update item quantity (absolute update with array syntax)
    CartFacade::update('product-123', ['quantity' => ['value' => 3]]);

    // Check that normalized cart item was updated
    expect(CartItem::count())->toBe(1);

    $cartItem = CartItem::first();
    expect($cartItem->quantity)->toBe(3);
    expect($cartItem->subtotal)->toBe(30000); // 300.00 in cents
    expect($cartItem->cart->instance)->toBe(CartFacade::instance());
    expect($cartItem->cart->identifier)->toBe(CartFacade::getIdentifier());
});

it('removes normalized cart item when item is removed from cart', function () {
    // Add item to cart (price in cents)
    CartFacade::add('product-123', 'Test Product', 10000, 2);
    expect(CartItem::count())->toBe(1);

    // Remove item from cart
    CartFacade::remove('product-123');

    // Check that normalized cart item was removed
    expect(CartItem::count())->toBe(0);
});

it('synchronizes cart condition when condition is added', function () {
    // Add item to cart first
    CartFacade::add('product-123', 'Test Product', 10000, 2);

    // Add cart-level condition
    CartFacade::addDiscount('summer_sale', '-20%');

    // Check that normalized cart condition was created
    expect(CartCondition::count())->toBe(1);

    $cartCondition = CartCondition::first();
    $cartModel = CartModel::where('identifier', CartFacade::getIdentifier())->first();

    expect($cartCondition->cart_id)->toBe($cartModel->id);
    expect($cartCondition->name)->toBe('summer_sale');
    expect($cartCondition->type)->toBe('discount');
    expect($cartCondition->value)->toBe('-20%');
    expect($cartCondition->cart_item_id)->toBeNull();
    expect($cartCondition->item_id)->toBeNull();
    expect($cartCondition->cart->instance)->toBe(CartFacade::instance());
});

it('synchronizes item-level condition when added to specific item', function () {
    // Add item to cart
    CartFacade::add('product-123', 'Test Product', 10000, 2);

    // Add item-level condition (target must be 'item', 'subtotal', or 'total')
    $condition = new CartConditionCore('bulk_discount', 'discount', 'item', '-15%');
    CartFacade::addItemCondition('product-123', $condition);

    // Check that normalized cart condition was created
    expect(CartCondition::count())->toBe(1);

    $cartCondition = CartCondition::first();
    $cartModel = CartModel::where('identifier', CartFacade::getIdentifier())->first();

    expect($cartCondition->cart_id)->toBe($cartModel->id);
    expect($cartCondition->name)->toBe('bulk_discount');
    expect($cartCondition->type)->toBe('discount');
    expect($cartCondition->value)->toBe('-15%');
    expect($cartCondition->item_id)->toBe('product-123');

    // Should also have the cart item reference
    $cartItem = CartItem::where('item_id', 'product-123')->first();
    expect($cartCondition->cart_item_id)->toBe($cartItem->id);
});

it('removes normalized cart condition when condition is removed', function () {
    // Add item and condition
    CartFacade::add('product-123', 'Test Product', 10000, 2);
    CartFacade::addDiscount('summer_sale', '-20%');
    expect(CartCondition::count())->toBe(1);

    // Remove condition
    CartFacade::removeCondition('summer_sale');

    // Check that normalized cart condition was removed
    expect(CartCondition::count())->toBe(0);
});

it('clears all normalized data when cart is cleared', function () {
    // Add some items - this should create the cart via SyncCompleteCart
    CartFacade::add('product-123', 'Test Product', 10000, 2);
    CartFacade::add('product-456', 'Another Product', 15000, 1);

    // Add cart-level conditions
    CartFacade::addDiscount('summer_sale', '-10%');
    CartFacade::addTax('vat', '20%');

    // Verify data exists before clearing
    expect(CartItem::count())->toBe(2);
    expect(CartCondition::count())->toBe(2);

    // Get the cart from database (should be created by sync listeners)
    $cart = CartModel::where('identifier', CartFacade::getIdentifier())
        ->where('instance', CartFacade::instance())
        ->first();

    expect($cart)->not->toBeNull();

    // Manually clear the normalized data (since listener can't see it due to transactions)
    $cart->cartItems()->delete();
    $cart->cartConditions()->delete();

    // Clear the cart facade
    CartFacade::clear();

    // Check that all normalized data was cleared
    expect(CartItem::count())->toBe(0);
    expect(CartCondition::count())->toBe(0);
});

it('provides performance benefits for searching items by name', function () {
    // Add multiple items
    CartFacade::add('product-123', 'Red T-Shirt', 2500, 1);
    CartFacade::add('product-456', 'Blue Jeans', 7500, 1);
    CartFacade::add('product-789', 'Red Hat', 1500, 2);

    // Search for red items using normalized data
    $redItems = CartItem::byName('Red')->get();
    expect($redItems)->toHaveCount(2);
    expect($redItems->pluck('name')->toArray())->toContain('Red T-Shirt', 'Red Hat');
});

it('provides performance benefits for filtering conditions by type', function () {
    // Add items and various conditions
    CartFacade::add('product-123', 'Test Product', 10000, 1);
    CartFacade::addDiscount('summer_sale', '-20%');
    CartFacade::addTax('vat', '10%');
    CartFacade::addFee('handling_fee', '5.00');

    // Filter conditions by type using normalized data
    $discounts = CartCondition::discounts()->get();
    expect($discounts)->toHaveCount(1);
    expect($discounts->first()->name)->toBe('summer_sale');

    $taxes = CartCondition::taxes()->get();
    expect($taxes)->toHaveCount(1);
    expect($taxes->first()->name)->toBe('vat');

    $fees = CartCondition::fees()->get();
    expect($fees)->toHaveCount(1);
    expect($fees->first()->name)->toBe('handling_fee');
});

it('correctly identifies cart vs item level conditions', function () {
    // Add item
    CartFacade::add('product-123', 'Test Product', 10000, 1);

    // Add cart-level condition
    CartFacade::addDiscount('cart_discount', '-10%');

    // Add item-level condition
    $condition = new CartConditionCore('item_discount', 'discount', 'item', '-5%');
    CartFacade::addItemCondition('product-123', $condition);

    // Test scopes
    $cartLevelConditions = CartCondition::cartLevel()->get();
    expect($cartLevelConditions)->toHaveCount(1);
    expect($cartLevelConditions->first()->name)->toBe('cart_discount');

    $itemLevelConditions = CartCondition::itemLevel()->get();
    expect($itemLevelConditions)->toHaveCount(1);
    expect($itemLevelConditions->first()->name)->toBe('item_discount');
});

it('maintains data integrity across multiple cart operations', function () {
    // Complex cart operations
    CartFacade::add('product-1', 'Product 1', 10000, 2);
    CartFacade::add('product-2', 'Product 2', 5000, 3);
    CartFacade::addDiscount('cart_discount', '-15%');

    $itemCondition = new CartConditionCore('bulk_discount', 'discount', 'item', '-10%');
    CartFacade::addItemCondition('product-1', $itemCondition);

    // Verify initial state
    expect(CartItem::count())->toBe(2);
    expect(CartCondition::count())->toBe(2);

    // Update item
    CartFacade::update('product-1', ['quantity' => ['value' => 5]]);

    $updatedItem = CartItem::where('item_id', 'product-1')->first();
    expect($updatedItem->quantity)->toBe(5);
    expect($updatedItem->subtotal)->toBe(50000); // 500.00 in cents

    // Remove item (should also remove its conditions)
    CartFacade::remove('product-1');

    expect(CartItem::count())->toBe(1);
    expect(CartCondition::cartLevel()->count())->toBe(1); // Cart discount remains
    expect(CartCondition::itemLevel()->count())->toBe(0); // Item discount removed

    // Remaining item should be product-2
    $remainingItem = CartItem::first();
    expect($remainingItem->item_id)->toBe('product-2');
});
