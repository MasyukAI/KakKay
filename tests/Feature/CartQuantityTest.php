<?php

declare(strict_types=1);

use AIArmada\Cart\Facades\Cart as CartFacade;
use App\Livewire\Cart;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    CartFacade::clear();
});

it('can increment and decrement quantities correctly', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'name' => 'Test Product',
        'price' => 2499, // RM24.99 in cents
        'status' => 'active',
    ]);

    $cart = Livewire::actingAs($user)
        ->test(Cart::class);

    // Add product to cart
    $cart->call('addToCart', $product, 1);

    // Initial state: quantity should be 1
    $cart->assertSet('cartItems.0.quantity', 1);

    // Increment quantity
    $cart->call('incrementQuantity', (string) $product->id);
    $cart->assertSet('cartItems.0.quantity', 2);

    // Increment again
    $cart->call('incrementQuantity', (string) $product->id);
    $cart->assertSet('cartItems.0.quantity', 3);

    // Decrement quantity
    $cart->call('decrementQuantity', (string) $product->id);
    $cart->assertSet('cartItems.0.quantity', 2);

    // Decrement again
    $cart->call('decrementQuantity', (string) $product->id);
    $cart->assertSet('cartItems.0.quantity', 1);

    // Decrement to 0 should remove the item
    $cart->call('decrementQuantity', (string) $product->id);
    expect($cart->get('cartItems'))->toBeEmpty();
});

it('dispatches proper events for cart counter updates', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'name' => 'Test Product',
        'price' => 2499,
        'status' => 'active',
    ]);

    $cart = Livewire::actingAs($user)
        ->test(Cart::class);

    // Add product should dispatch event
    $cart->call('addToCart', $product, 1)
        ->assertDispatched('cart-updated');

    // Increment should dispatch event
    $cart->call('incrementQuantity', (string) $product->id)
        ->assertDispatched('cart-updated');

    // Decrement should dispatch event
    $cart->call('decrementQuantity', (string) $product->id)
        ->assertDispatched('cart-updated');

    // Remove should dispatch event
    $cart->call('removeItem', (string) $product->id)
        ->assertDispatched('cart-updated');
});
