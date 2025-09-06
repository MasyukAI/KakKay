<?php

declare(strict_types=1);

use MasyukAI\Cart\Http\Livewire\CartSummary;
use MasyukAI\Cart\Facades\Cart;
use Livewire\Livewire;

beforeEach(function (): void {
    // Skip cart clear for now due to facade issue
    // Cart::clear();
});

afterEach(function (): void {
    try {
        // Skip cart clearing due to facade issue 
        // Cart::setInstance('default');
        // Cart::clear();
        // Cart::setInstance('shopping'); 
        // Cart::clear();
        // Cart::setInstance('wishlist');
        // Cart::clear();
        // Cart::setInstance('default');
    } catch (Exception $e) {
        // Ignore errors when instances don't exist
    }
});

it('can be instantiated with default values', function (): void {
    Livewire::test(CartSummary::class)
        ->assertSet('showDetails', false);
});

it('computes cart data correctly when empty', function (): void {
    $component = Livewire::test(CartSummary::class);
    
    expect($component->get('itemCount'))->toBe(0)
        ->and($component->get('totalQuantity'))->toBe(0)
        ->and($component->get('subtotal'))->toBe(0.0)
        ->and($component->get('total'))->toBe(0.0)
        ->and($component->get('isEmpty'))->toBe(true);
});

it('computes cart data correctly with items', function (): void {
    // Ensure we're using the default cart instance  
    Cart::setInstance('default');
    Cart::clear();
    
    // Add items to cart
    Cart::add('product-1', 'Product 1', 100, 2);
    Cart::add('product-2', 'Product 2', 50, 3);
    
    // Create component after adding items so computed properties reflect current state
    $component = Livewire::test(CartSummary::class);
    
    expect($component->get('itemCount'))->toBe(2)  // 2 unique items
        ->and($component->get('totalQuantity'))->toBe(5)  // 2 + 3 total quantity
        ->and($component->get('subtotal'))->toBe(350.0)  // (100*2) + (50*3)
        ->and($component->get('total'))->toBe(350.0)
        ->and($component->get('isEmpty'))->toBe(false);
});

it('computes cart array correctly', function (): void {
    Cart::add('test-product', 'Test Product', 75, 2, ['color' => 'red']);
    
    $component = Livewire::test(CartSummary::class);
    $cartData = $component->get('cart');
    
    expect($cartData)->toBeArray()
        ->and($cartData['items'])->toHaveKey('test-product')
        ->and($cartData['subtotal'])->toBe(150.0)
        ->and($cartData['total'])->toBe(150.0)
        ->and($cartData['quantity'])->toBe(2)
        ->and($cartData['count'])->toBe(1)
        ->and($cartData['is_empty'])->toBeFalse();
});

it('can toggle details visibility', function (): void {
    Livewire::test(CartSummary::class)
        ->assertSet('showDetails', false)
        ->call('toggleDetails')
        ->assertSet('showDetails', true)
        ->call('toggleDetails')
        ->assertSet('showDetails', false);
});

it('refreshes cart when cart-updated event is dispatched', function (): void {
    $component = Livewire::test(CartSummary::class);
    
    // Add item to cart
    Cart::add('refresh-test', 'Refresh Test', 99, 1);
    
    // Dispatch the event that should trigger refresh
    $component->dispatch('cart-updated')
        ->assertDispatched('cart-summary-updated');
    
    // Verify computed properties reflect the changes
    expect($component->get('itemCount'))->toBe(1)
        ->and($component->get('isEmpty'))->toBeFalse();
});

it('listens to cart-updated event', function (): void {
    Livewire::test(CartSummary::class)
        ->call('refreshCart')
        ->assertDispatched('cart-summary-updated');
});

it('renders the correct view', function (): void {
    Livewire::test(CartSummary::class)
        ->assertViewIs('cart::livewire.cart-summary');
});

it('updates computed properties when cart changes', function (): void {
    $component = Livewire::test(CartSummary::class);
    
    // Initially empty
    expect($component->get('itemCount'))->toBe(0);
    
    // Add an item
    Cart::add('dynamic-product', 'Dynamic Product', 200, 1);
    
    // Call refreshCart to update computed properties
    $component->call('refreshCart');
    
    // Check computed property updates
    expect($component->get('itemCount'))->toBe(1)
        ->and($component->get('total'))->toBe(200.0)
        ->and($component->get('isEmpty'))->toBeFalse();
});

it('handles cart with conditions correctly', function (): void {
    Cart::add('product-1', 'Product 1', 100, 1);
    
    $condition = new \MasyukAI\Cart\Conditions\CartCondition(
        'tax',
        'charge',
        'subtotal',
        '+10%'
    );
    Cart::addCondition($condition);
    
    $component = Livewire::test(CartSummary::class);
    
    expect($component->get('subtotal'))->toBe(100.0)
        ->and($component->get('total'))->toBe(110.0);  // 100 + 10% tax
});

it('maintains accuracy with decimal prices', function (): void {
    Cart::add('decimal-product', 'Decimal Product', 19.99, 3);
    
    $component = Livewire::test(CartSummary::class);
    
    expect($component->get('subtotal'))->toBe(59.97)
        ->and($component->get('total'))->toBe(59.97);
});

it('handles large quantities correctly', function (): void {
    Cart::add('bulk-product', 'Bulk Product', 10, 100);
    
    $component = Livewire::test(CartSummary::class);
    
    expect($component->get('totalQuantity'))->toBe(100)
        ->and($component->get('total'))->toBe(1000.0);
});

it('reflects multiple cart instances', function (): void {
    // Clear all instances
    Cart::setInstance('default');
    Cart::clear();
    Cart::setInstance('wishlist');
    Cart::clear();
    
    // Default instance
    Cart::setInstance('default');
    Cart::add('default-product', 'Default Product', 50, 1);
    
    $defaultComponent = Livewire::test(CartSummary::class);
    expect($defaultComponent->get('itemCount'))->toBe(1);
    
    // Switch to different instance
    Cart::setInstance('wishlist');
    Cart::add('wish-product', 'Wish Product', 30, 2);
    
    // Create component after switching instance and adding items
    $wishlistComponent = Livewire::test(CartSummary::class);
    expect($wishlistComponent->get('itemCount'))->toBe(1)
        ->and($wishlistComponent->get('total'))->toBe(60.0);
    
    // Reset to default
    Cart::setInstance('default');
});

it('handles empty cart gracefully', function (): void {
    Cart::add('temp-product', 'Temp Product', 100, 1);
    Cart::clear();
    
    $component = Livewire::test(CartSummary::class);
    
    expect($component->get('itemCount'))->toBe(0)
        ->and($component->get('totalQuantity'))->toBe(0)
        ->and($component->get('subtotal'))->toBe(0.0)
        ->and($component->get('total'))->toBe(0.0)
        ->and($component->get('isEmpty'))->toBe(true);
    
    $cartData = $component->get('cart');
    expect($cartData['items'])->toBeEmpty();
});
