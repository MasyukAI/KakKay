<?php

use Illuminate\Support\Facades\App;
use MasyukAI\Cart\Cart;
use MasyukAI\Cart\Conditions\CartCondition;
use MasyukAI\Cart\Storage\SessionStorage;

// This is just a placeholder, the actual cart will be initialized in beforeEach
global $cart;

beforeEach(function () {
    global $cart;
    
    // Ensure events dispatcher is available
    $events = new \Illuminate\Events\Dispatcher(app());
    
    // Initialize session storage with array store for testing
    $sessionStore = new \Illuminate\Session\Store('testing', new \Illuminate\Session\ArraySessionHandler(120));
    $sessionStorage = new SessionStorage($sessionStore);
    
    // Initialize cart with session storage
    $cart = new Cart(
        storage: $sessionStorage,
        events: $events,
        instanceName: 'shipping_test',
        eventsEnabled: true
    );
    
    // Clear the cart before each test
    $cart->clear();
});

test('can add shipping condition to cart', function () {
    global $cart;
    
    // Clear the cart first
    $cart->clear();
    
    // Add a shipping condition with shipping method
    $cart->addShipping('Express Shipping', 15, 'express');
    
    // Check that the condition was added
    $conditions = $cart->getConditions();
    $shippingCondition = $conditions->first(fn ($c) => $c->getType() === 'shipping');
    
    expect($shippingCondition)->not->toBeNull();
    expect($shippingCondition->getName())->toBe('Express Shipping');
    expect($shippingCondition->getType())->toBe('shipping');
    expect($shippingCondition->getValue())->toBe(15.0);
    
    // Check getShippingValue method
    expect($cart->getShippingValue())->toBe(15.0);
    
    // Check shipping method was stored
    expect($cart->getShippingMethod())->toBe('express');
    
    // Check getShipping method
    $shipping = $cart->getShipping();
    expect($shipping)->not->toBeNull();
    expect($shipping->getAttributes()['method'])->toBe('express');
    expect($shipping->getAttributes()['description'])->toBe('Express Shipping');
});

test('can add percentage shipping condition to cart', function () {
    global $cart;
    
    // Clear the cart first
    $cart->clear();
    
    // Add a product to the cart
    $cart->add('product-1', 'Test Product', 100, 1);
    
    // Add a percentage-based shipping condition
    $cart->addShipping('International Shipping', '10%', 'international');
    
    // Check that the condition was added
    $conditions = $cart->getConditions();
    $shippingCondition = $conditions->first(fn ($c) => $c->getType() === 'shipping');
    
    expect($shippingCondition)->not->toBeNull();
    expect($shippingCondition->getName())->toBe('International Shipping');
    expect($shippingCondition->getType())->toBe('shipping');
    expect($shippingCondition->getValue())->toBe('+10%');
    
    // For a percentage, the value depends on the subtotal
    // But getShippingValue should return the raw value as a float
    expect($cart->getShippingValue())->toBe(10.0);
    
    // Check shipping method is set correctly
    expect($cart->getShippingMethod())->toBe('international');
    
    // Check that the shipping is applied to the subtotal
    $subtotal = $cart->subtotal();
    $total = $cart->total();
    expect($total - $subtotal)->toBe(10.0);
});

test('shipping condition affects the cart total', function () {
    global $cart;
    
    // Clear the cart first
    $cart->clear();
    
    // Add products to the cart
    $cart->add('product-1', 'Test Product', 100, 2);
    
    // Verify subtotal before conditions
    expect($cart->subtotal())->toBe(200.0);
    
    // Add a shipping condition
    $cart->addShipping('Express Shipping', 15);
    
    // Verify the total includes the shipping
    expect($cart->total())->toBe(215.0);
});

test('can retrieve shipping value and use it during checkout', function () {
    global $cart;
    
    // Clear the cart first
    $cart->clear();
    
    // Add a shipping condition
    $cart->addShipping('Express Shipping', 15);
    
    // Check getShippingValue
    expect($cart->getShippingValue())->toBe(15.0);
    
    // Convert to cents for checkout as our example does
    $shippingInCents = (int) ($cart->getShippingValue() * 100);
    expect($shippingInCents)->toBe(1500);
});

test('can handle negative shipping values (discounts)', function () {
    global $cart;
    
    // Clear the cart first
    $cart->clear();
    
    // Add a shipping condition with negative value (discount)
    $cart->addShipping('Free Shipping Promotion', '-15');
    
    // Check getShippingValue returns negative value
    expect($cart->getShippingValue())->toBe(-15.0);
});

test('can remove shipping condition', function () {
    global $cart;
    
    // Clear the cart first
    $cart->clear();
    
    // Add a shipping condition
    $cart->addShipping('Express Shipping', 15);
    
    // Verify shipping exists
    expect($cart->getShipping())->not->toBeNull();
    expect($cart->getShippingValue())->toBe(15.0);
    
    // Remove shipping condition
    $cart->removeShipping();
    
    // Verify shipping condition was removed
    expect($cart->getShipping())->toBeNull();
    expect($cart->getShippingValue())->toBeNull();
});

test('adding new shipping replaces existing shipping', function () {
    global $cart;
    
    // Clear the cart first
    $cart->clear();
    
    // Add first shipping condition
    $cart->addShipping('Standard Shipping', 10, 'standard');
    
    // Verify first shipping exists
    expect($cart->getShipping())->not->toBeNull();
    expect($cart->getShippingValue())->toBe(10.0);
    expect($cart->getShippingMethod())->toBe('standard');
    
    // Add second shipping condition (should replace first)
    $cart->addShipping('Express Shipping', 20, 'express');
    
    // Verify first shipping was replaced with second
    expect($cart->getShippingValue())->toBe(20.0);
    expect($cart->getShippingMethod())->toBe('express');
    
    // Verify there's only one shipping condition
    $conditions = $cart->getConditions()->filter(fn ($c) => $c->getType() === 'shipping');
    expect($conditions->count())->toBe(1);
});
