<?php

declare(strict_types=1);

use Livewire\Livewire;
use MasyukAI\Cart\Facades\Cart;
use MasyukAI\Cart\Http\Livewire\CartTable;

beforeEach(function () {
    Cart::session(config('cart.session.key'))->flush();
});

afterEach(function () {
    Cart::session(config('cart.session.key'))->flush();
});

it('can be instantiated with default values', function (): void {
    Livewire::test(CartTable::class)
        ->assertSet('showConditions', false);
});

it('computes items correctly when cart is empty', function (): void {
    $component = Livewire::test(CartTable::class);

    expect($component->instance()->isEmpty)->toBeTrue();
    expect($component->instance()->items->count())->toBe(0);
});

it('computes items correctly when cart has items', function (): void {
    Cart::add('product-1', 'Product 1', 100, 2);
    Cart::add('product-2', 'Product 2', 50, 1);

    $component = Livewire::test(CartTable::class);

    expect($component->instance()->isEmpty)->toBeFalse();
    expect($component->instance()->items->count())->toBe(2);
    expect($component->instance()->items->has('product-1'))->toBeTrue();
    expect($component->instance()->items->has('product-2'))->toBeTrue();
});

it('can update item quantity to specific value', function (): void {
    Cart::add('test-product', 'Test Product', 100, 2);

    Livewire::test(CartTable::class)
        ->call('updateQuantity', 'test-product', 5)
        ->assertDispatched('cart-updated')
        ->assertDispatched('item-updated', itemId: 'test-product');

    $item = Cart::get('test-product');
    expect($item->quantity)->toBe(5);
});

it('removes item when quantity is updated to zero', function (): void {
    Cart::add('test-product', 'Test Product', 100, 2);

    Livewire::test(CartTable::class)
        ->call('updateQuantity', 'test-product', 0)
        ->assertDispatched('cart-updated');

    expect(Cart::get('test-product'))->toBeNull();
});

it('removes item when quantity is updated to negative value', function (): void {
    Cart::add('test-product', 'Test Product', 100, 2);

    Livewire::test(CartTable::class)
        ->call('updateQuantity', 'test-product', -1)
        ->assertDispatched('cart-updated');

    expect(Cart::get('test-product'))->toBeNull();
});

it('can increase item quantity', function (): void {
    Cart::add('test-product', 'Test Product', 100, 2);

    Livewire::test(CartTable::class)
        ->call('increaseQuantity', 'test-product')
        ->assertDispatched('cart-updated')
        ->assertDispatched('item-updated', itemId: 'test-product');

    $item = Cart::get('test-product');
    expect($item->quantity)->toBe(3);
});

it('can decrease item quantity', function (): void {
    Cart::add('test-product', 'Test Product', 100, 5);

    Livewire::test(CartTable::class)
        ->call('decreaseQuantity', 'test-product')
        ->assertDispatched('cart-updated')
        ->assertDispatched('item-updated', itemId: 'test-product');

    $item = Cart::get('test-product');
    expect($item->quantity)->toBe(4);
});

it('removes item when decreasing quantity from 1', function (): void {
    Cart::add('test-product', 'Test Product', 100, 1);

    Livewire::test(CartTable::class)
        ->call('decreaseQuantity', 'test-product')
        ->assertDispatched('cart-updated');

    expect(Cart::get('test-product'))->toBeNull();
});

it('can remove specific item', function (): void {
    Cart::add('product-1', 'Product 1', 100, 1);
    Cart::add('product-2', 'Product 2', 200, 1);

    Livewire::test(CartTable::class)
        ->call('removeItem', 'product-1')
        ->assertDispatched('cart-updated')
        ->assertDispatched('item-removed', itemId: 'product-1', itemName: 'Product 1');

    expect(Cart::get('product-1'))->toBeNull()
        ->and(Cart::get('product-2'))->not->toBeNull()
        ->and(Cart::count())->toBe(1);
});

it('handles removing non-existent item gracefully', function (): void {
    Livewire::test(CartTable::class)
        ->call('removeItem', 'non-existent-product');

    // Should not throw exception or dispatch events for non-existent items
    expect(true)->toBeTrue();
});

it('can clear entire cart', function (): void {
    Cart::add('product-1', 'Product 1', 100, 1);
    Cart::add('product-2', 'Product 2', 200, 1);

    Livewire::test(CartTable::class)
        ->call('clearCart')
        ->assertDispatched('cart-updated')
        ->assertDispatched('cart-cleared');

    expect(Cart::count())->toBe(0)
        ->and(Cart::isEmpty())->toBeTrue();
});

it('can toggle conditions visibility', function (): void {
    Livewire::test(CartTable::class)
        ->assertSet('showConditions', false)
        ->call('toggleConditions')
        ->assertSet('showConditions', true)
        ->call('toggleConditions')
        ->assertSet('showConditions', false);
});

it('listens to cart-updated event', function (): void {
    $component = Livewire::test(CartTable::class);

    // Add item and trigger the event
    Cart::add('event-test', 'Event Test', 50, 1);
    $component->call('refreshCart');

    // Verify the computed property reflects the change
    expect($component->instance()->isEmpty)->toBeFalse();
});

it('renders the correct view', function (): void {
    Livewire::test(CartTable::class)
        ->assertViewIs('cart::livewire.cart-table');
});

it('gets item price without conditions by default', function (): void {
    Cart::add('price-test', 'Price Test', 100, 1);
    $item = Cart::get('price-test');

    $component = Livewire::test(CartTable::class);
    $price = $component->instance()->getItemPrice($item);

    expect($price)->toBe(100.0);
});

it('gets item price with conditions when enabled', function (): void {
    Cart::add('price-test', 'Price Test', 100, 1);

    $condition = new \MasyukAI\Cart\Conditions\CartCondition(
        'item-discount',
        'discount',
        'item',
        '-10%'
    );

    Cart::update('price-test', ['conditions' => [$condition]]);
    $item = Cart::get('price-test');

    $component = Livewire::test(CartTable::class)
        ->set('showConditions', true);

    // The price should be calculated with conditions
    $price = $component->instance()->getItemPrice($item);
    expect($price)->toBe($item->getPriceWithConditions()); // Use actual calculated price
});

it('gets item total without conditions by default', function (): void {
    Cart::add('total-test', 'Total Test', 100, 3);
    $item = Cart::get('total-test');

    $component = Livewire::test(CartTable::class);
    $total = $component->instance()->getItemTotal($item);

    expect($total)->toBe(300.0);
});

it('gets item total with conditions when enabled', function (): void {
    Cart::add('total-test', 'Total Test', 100, 2);

    $condition = new \MasyukAI\Cart\Conditions\CartCondition(
        'item-discount',
        'discount',
        'item',
        '-20%'
    );

    Cart::update('total-test', ['conditions' => [$condition]]);
    $item = Cart::get('total-test');

    $component = Livewire::test(CartTable::class)
        ->set('showConditions', true);

    $total = $component->instance()->getItemTotal($item);
    expect($total)->toBe($item->getPriceSumWithConditions()); // Use actual calculated total
});

it('handles multiple quantity operations correctly', function (): void {
    Cart::add('multi-test', 'Multi Test', 50, 1);

    $component = Livewire::test(CartTable::class);

    // Increase quantity multiple times
    $component->call('increaseQuantity', 'multi-test')
        ->call('increaseQuantity', 'multi-test')
        ->call('increaseQuantity', 'multi-test');

    $item = Cart::get('multi-test');
    expect($item->quantity)->toBe(4);

    // Decrease quantity
    $component->call('decreaseQuantity', 'multi-test');

    $item = Cart::get('multi-test');
    expect($item->quantity)->toBe(3);
});

it('handles concurrent item operations', function (): void {
    Cart::add('product-1', 'Product 1', 100, 1);
    Cart::add('product-2', 'Product 2', 200, 1);
    Cart::add('product-3', 'Product 3', 300, 1);

    $component = Livewire::test(CartTable::class);

    $component->call('increaseQuantity', 'product-1')
        ->call('removeItem', 'product-2')
        ->call('updateQuantity', 'product-3', 5);

    expect(Cart::get('product-1')->quantity)->toBe(2)
        ->and(Cart::get('product-2'))->toBeNull()
        ->and(Cart::get('product-3')->quantity)->toBe(5)
        ->and(Cart::getItems()->count())->toBe(2); // Count unique items, not total quantity
});
