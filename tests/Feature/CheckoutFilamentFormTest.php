<?php

use App\Livewire\Checkout;

test('checkout component class exists', function () {
    expect(class_exists(Checkout::class))->toBeTrue();
});

test('checkout component implements required interfaces', function () {
    $reflection = new ReflectionClass(Checkout::class);
    
    expect($reflection->implementsInterface(\Filament\Schemas\Contracts\HasSchemas::class))->toBeTrue();
});

test('checkout component has required methods', function () {
    $checkout = new Checkout();
    
    expect(method_exists($checkout, 'form'))->toBeTrue();
    expect(method_exists($checkout, 'processCheckout'))->toBeTrue();
    expect(method_exists($checkout, 'getSubtotal'))->toBeTrue();
    expect(method_exists($checkout, 'getTotal'))->toBeTrue();
});

test('checkout route works', function () {
    $response = $this->get('/checkout');
    
    // Should either show checkout page or redirect to cart (when no items)
    expect($response->status())->toBeIn([200, 302]);
});
