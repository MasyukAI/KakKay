<?php

declare(strict_types=1);

use App\Livewire\Cart;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use MasyukAI\Cart\Facades\Cart as CartFacade;

uses(RefreshDatabase::class);

beforeEach(function () {
    CartFacade::clear();
});

it('can add product to cart using product ID (Tambah button functionality)', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'name' => 'Test Product',
        'slug' => 'test-product',
        'price' => 2499, // RM24.99 in cents
        'is_active' => true,
    ]);

    $cart = Livewire::actingAs($user)
        ->test(Cart::class);

    // Test adding product using ID (like the Tambah button does)
    $cart->call('addToCart', $product->id);

    // Check that the product was added to the cart
    $cartItems = $cart->get('cartItems');
    expect($cartItems)->toHaveCount(1);
    expect($cartItems[0]['name'])->toBe('Test Product');
    expect($cartItems[0]['quantity'])->toBe(1);

    // Verify the event was dispatched
    $cart->assertDispatched('cart-updated');
});

it('can add product to cart using Product object (backward compatibility)', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'name' => 'Test Product',
        'slug' => 'test-product',
        'price' => 2499,
        'is_active' => true,
    ]);

    $cart = Livewire::actingAs($user)
        ->test(Cart::class);

    // Test adding product using Product object (for backward compatibility)
    $cart->call('addToCart', $product);

    // Check that the product was added to the cart
    $cartItems = $cart->get('cartItems');
    expect($cartItems)->toHaveCount(1);
    expect($cartItems[0]['name'])->toBe('Test Product');
    expect($cartItems[0]['quantity'])->toBe(1);

    // Verify the event was dispatched
    $cart->assertDispatched('cart-updated');
});
