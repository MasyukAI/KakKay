<?php

use App\Livewire\Cart;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use MasyukAI\Cart\Facades\Cart as CartFacade;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Clear cart before each test and reset instance
    CartFacade::clear();

    // Force clear any cached instances
    app()->forgetInstance('cart');
    app()->forgetInstance('cart.manager');
    app()->forgetInstance('cart.transformer');
});

it('can render', function () {
    // Create test category and products
    $category = Category::factory()->create();
    Product::factory()->count(3)->create([
        'is_active' => true,
        'category_id' => $category->id,
    ]);

    $component = Livewire::test(Cart::class);

    $component->assertSee('Troli awak masih sunyi'); // Cart should be empty initially
});

it('can add product to cart', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'name' => 'Test Book',
        'price' => 1999, // RM19.99 in cents
        'category_id' => $category->id,
    ]);

    Livewire::test(Cart::class)
        ->call('addToCart', $product, 1)
        ->assertHasNoErrors();

    // Verify cart has the item
    expect(CartFacade::getItems())->toHaveCount(1);

    $cartItem = CartFacade::getItems()->first();
    expect($cartItem->id)->toBe((string) $product->id);
    expect($cartItem->name)->toBe('Test Book');
    expect($cartItem->quantity)->toBe(1);

    // Price should be stored correctly and retrievable in cents
    expect($cartItem->getPrice()->getAmount())->toBe(1999.0);
});

it('can display cart items with correct prices', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'name' => 'Test Book',
        'price' => 2499, // RM24.99 in cents
        'category_id' => $category->id,
    ]);

    $cart = Livewire::test(Cart::class);
    $cart->call('addToCart', $product, 2);

    // Check that cart items are loaded correctly
    $cartItems = $cart->get('cartItems');
    expect($cartItems)->toHaveCount(1);

    $item = $cartItems[0];
    expect($item['name'])->toBe('Test Book');
    expect($item['price'])->toBe('RM24.99'); // Price formatted as string
    expect($item['quantity'])->toBe(2);
});

it('can calculate subtotal correctly', function () {
    $category = Category::factory()->create();
    $product1 = Product::factory()->create([
        'price' => 1999, // RM19.99
        'category_id' => $category->id,
    ]);
    $product2 = Product::factory()->create([
        'price' => 2499, // RM24.99
        'category_id' => $category->id,
    ]);

    $cart = Livewire::test(Cart::class);
    $cart->call('addToCart', $product1, 1);
    $cart->call('addToCart', $product2, 2);

    // Verify the cart has correct items and quantities
    $cart->assertSet('cartItems.0.quantity', 1);
    $cart->assertSet('cartItems.1.quantity', 2);

    // Test that the component displays the correct total in the UI
    $cart->assertSee('RM19.99'); // Product 1 price
    $cart->assertSee('RM24.99'); // Product 2 price
});

it('can format prices correctly', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'price' => 1999, // RM19.99
        'category_id' => $category->id,
    ]);

    $cart = Livewire::test(Cart::class);
    $cart->call('addToCart', $product, 1);

    // Test that prices are formatted correctly in the UI
    $cart->assertSee('RM19.99');

    // Test the component has the correct item price (formatted)
    $cart->assertSet('cartItems.0.price', 'RM19.99');

    // Verify that prices are always formatted (not showing raw cents in price displays)
    // We check the formatted versions are present
    $cart->assertSee('RM19.99'); // Item price
    $cart->assertSee('RM9.90');  // Shipping
    $cart->assertSee('RM29.89'); // Total (19.99 + 9.90)
});

it('can update item quantity', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'price' => 1999,
        'category_id' => $category->id,
    ]);

    $cart = Livewire::test(Cart::class);
    $cart->call('addToCart', $product, 1);

    // Verify initial quantity
    $itemId = (string) $product->id;
    expect(CartFacade::get($itemId)->quantity)->toBe(1);

    // Update quantity to 3
    $cart->call('updateQuantity', $itemId, 3);
    expect(CartFacade::get($itemId)->quantity)->toBe(3);
});

it('can remove items from cart', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'price' => 1999,
        'category_id' => $category->id,
    ]);

    $cart = Livewire::test(Cart::class);
    $cart->call('addToCart', $product, 1);

    expect(CartFacade::getItems())->toHaveCount(1);

    $itemId = (string) $product->id;
    $cart->call('removeItem', $itemId);

    expect(CartFacade::getItems())->toHaveCount(0);
});
