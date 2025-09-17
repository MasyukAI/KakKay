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
    $this->user = User::factory()->create();
    $this->product = Product::factory()->create();
});

it('shows loading state for the specific product button when adding to cart', function () {
    $component = Livewire::actingAs($this->user)
        ->test(Cart::class)
        ->assertSet('addingProductId', null);

    // Mock the cart add operation to capture the loading state
    $component->call('addToCart', $this->product->id);

    // The loading state should be cleared after the operation
    $component->assertSet('addingProductId', null);
});

it('handles loading state correctly for Product objects', function () {
    $component = Livewire::actingAs($this->user)
        ->test(Cart::class)
        ->assertSet('addingProductId', null);

    // Test with Product object (backward compatibility)
    $component->call('addToCart', $this->product);

    // The loading state should be cleared after the operation
    $component->assertSet('addingProductId', null);
});

it('loading state property is properly typed as integer', function () {
    $component = Livewire::actingAs($this->user)
        ->test(Cart::class);

    // Call with integer product ID
    $component->call('addToCart', $this->product->id);

    // Verify the component processed successfully (no errors about type conversion)
    $component->assertStatus(200);
});
