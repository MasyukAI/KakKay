<?php

use MasyukAI\Cart\Facades\Cart;

beforeEach(function() {
    Cart::clear();
});

it('can add shipping to cart', function() {
    // Add a product to the cart
    Cart::add('123', 'Test Product', 100.00, 1);
    
    // Add shipping
    Cart::addShipping('Express Shipping', 15.00, 'express');
    
    // Verify shipping was added as a condition
    $shipping = Cart::getShipping();
    expect($shipping)->not->toBeNull();
    expect($shipping->getName())->toBe('Express Shipping');
    expect($shipping->getType())->toBe('shipping');
    
    // Check shipping method
    $method = Cart::getShippingMethod();
    expect($method)->toBe('express');
    
    // Check shipping value
    $value = Cart::getShippingValue();
    expect($value)->toBe(15.00);
    
    // Check total includes shipping
    expect(Cart::getTotal())->toBe(115.00);
});

it('can remove shipping from cart', function() {
    // Add a product and shipping
    Cart::add('123', 'Test Product', 100.00, 1);
    Cart::addShipping('Express Shipping', 15.00, 'express');
    
    // Verify shipping exists
    expect(Cart::getShipping())->not->toBeNull();
    expect(Cart::getTotal())->toBe(115.00);
    
    // Remove shipping
    Cart::removeShipping();
    
    // Verify shipping was removed
    expect(Cart::getShipping())->toBeNull();
    expect(Cart::getTotal())->toBe(100.00);
});

it('replaces existing shipping when adding a new one', function() {
    // Add a product and shipping
    Cart::add('123', 'Test Product', 100.00, 1);
    Cart::addShipping('Standard Shipping', 5.00, 'standard');
    
    // Check first shipping
    $shipping = Cart::getShipping();
    expect($shipping->getName())->toBe('Standard Shipping');
    expect(Cart::getShippingValue())->toBe(5.00);
    expect(Cart::getTotal())->toBe(105.00);
    
    // Add a different shipping
    Cart::addShipping('Express Shipping', 15.00, 'express');
    
    // Check new shipping replaced the old one
    $shipping = Cart::getShipping();
    expect($shipping->getName())->toBe('Express Shipping');
    expect(Cart::getShippingValue())->toBe(15.00);
    expect(Cart::getTotal())->toBe(115.00);
});

it('handles string values with plus sign', function() {
    // Add a product to the cart
    Cart::add('123', 'Test Product', 100.00, 1);
    
    // Add shipping with plus sign
    Cart::addShipping('Express Shipping', '+15.00', 'express');
    
    // Verify shipping value
    expect(Cart::getShippingValue())->toBe(15.00);
    expect(Cart::getTotal())->toBe(115.00);
});

it('returns null when no shipping is set', function() {
    // Add a product without shipping
    Cart::add('123', 'Test Product', 100.00, 1);
    
    // Verify null values
    expect(Cart::getShipping())->toBeNull();
    expect(Cart::getShippingMethod())->toBeNull();
    expect(Cart::getShippingValue())->toBeNull();
});
