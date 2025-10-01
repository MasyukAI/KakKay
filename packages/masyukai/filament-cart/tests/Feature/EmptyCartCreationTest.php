<?php

declare(strict_types=1);

namespace MasyukAI\FilamentCart\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use MasyukAI\Cart\Events\CartCreated;
use MasyukAI\Cart\Events\CartUpdated;
use MasyukAI\Cart\Facades\Cart as CartFacade;
use MasyukAI\FilamentCart\Models\Cart;
use MasyukAI\FilamentCart\Models\CartItem;
use MasyukAI\FilamentCart\Tests\TestCase;

class EmptyCartCreationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Clear any existing carts
        Cart::query()->delete();
        CartItem::query()->delete();
    }

    public function test_homepage_visit_does_not_create_empty_cart_record(): void
    {
        // Ensure no carts exist initially
        $this->assertEquals(0, Cart::count());

        // Simulate homepage visit by checking cart quantity (what brand-header does)
        $quantity = CartFacade::getTotalQuantity();

        // Cart quantity should be 0
        $this->assertEquals(0, $quantity);

        // No cart record should be created in database
        $this->assertEquals(0, Cart::count());
    }

    public function test_cart_created_event_with_empty_cart_does_not_create_record(): void
    {
        // Ensure no carts exist initially
        $this->assertEquals(0, Cart::count());

        // Manually fire CartCreated event with empty cart
        $cartInstance = CartFacade::getCurrentCart();
        Event::dispatch(new CartCreated($cartInstance));

        // No cart record should be created in database
        $this->assertEquals(0, Cart::count());
    }

    public function test_adding_first_item_creates_cart_record(): void
    {
        // Ensure no carts exist initially
        $this->assertEquals(0, Cart::count());

        // Add an item to the cart
        CartFacade::add('test-product', 'Test Product', 1099, 1);

        // Now cart record should exist
        $this->assertEquals(1, Cart::count());
        $this->assertEquals(1, CartItem::count());

        $cart = Cart::first();
        $this->assertNotNull($cart);
        $this->assertEquals(1, $cart->items_count);
    }

    public function test_removing_all_items_cleans_up_cart_record(): void
    {
        // Add an item to create cart record
        CartFacade::add('test-product', 'Test Product', 1099, 1);
        $this->assertEquals(1, Cart::count());
        $this->assertEquals(1, CartItem::count());

        // Remove the item
        CartFacade::remove('test-product');

        // Cart should be empty but let's trigger an update event to clean up
        Event::dispatch(new CartUpdated(CartFacade::getCurrentCart()));

        // Cart record should be cleaned up
        $this->assertEquals(0, Cart::count());
        $this->assertEquals(0, CartItem::count());
    }

    public function test_multiple_items_add_remove_workflow(): void
    {
        // Start with no carts
        $this->assertEquals(0, Cart::count());

        // Add first item - creates cart
        CartFacade::add('product-1', 'Product 1', 1099, 1);
        $this->assertEquals(1, Cart::count());
        $this->assertEquals(1, CartItem::count());

        // Add second item - updates cart
        CartFacade::add('product-2', 'Product 2', 1599, 2);
        $this->assertEquals(1, Cart::count());
        $this->assertEquals(2, CartItem::count());

        // Remove first item - cart still exists
        CartFacade::remove('product-1');
        $this->assertEquals(1, Cart::count());
        $this->assertEquals(1, CartItem::count());

        // Remove last item and trigger update - cart should be cleaned up
        CartFacade::remove('product-2');
        Event::dispatch(new CartUpdated(CartFacade::getCurrentCart()));

        $this->assertEquals(0, Cart::count());
        $this->assertEquals(0, CartItem::count());
    }

    public function test_cart_with_conditions_only_creates_record(): void
    {
        // Start with no carts
        $this->assertEquals(0, Cart::count());

        // Add a cart-level condition (like tax or shipping)
        CartFacade::addCondition(new \MasyukAI\Cart\Conditions\CartCondition(
            'tax',
            'tax',
            'subtotal',
            '10%'
        ));

        // Cart should be created because it has conditions
        Event::dispatch(new CartUpdated(CartFacade::getCurrentCart()));
        $this->assertEquals(1, Cart::count());

        // Remove condition and clean up
        CartFacade::removeCondition('tax');
        Event::dispatch(new CartUpdated(CartFacade::getCurrentCart()));
        $this->assertEquals(0, Cart::count());
    }
}
