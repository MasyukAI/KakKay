<?php

declare(strict_types=1);

namespace MasyukAI\FilamentCart\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use MasyukAI\Cart\Facades\Cart as CartFacade;
use MasyukAI\FilamentCart\Models\Cart;
use MasyukAI\FilamentCart\Models\CartItem;
use MasyukAI\FilamentCart\Tests\TestCase;

class SyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_cart_item_synchronization_works(): void
    {
        // Add item to cart (id, name, price, quantity)
        CartFacade::add('1', 'Test Product', 10000, 1);

        // Verify normalized cart record exists
        $this->assertDatabaseHas('carts', [
            'identifier' => CartFacade::getIdentifier(),
            'instance' => CartFacade::instance(),
        ]);

        // Verify normalized cart item record exists
        $cart = Cart::where('identifier', CartFacade::getIdentifier())->first();
        $this->assertNotNull($cart);

        $this->assertDatabaseHas('cart_items', [
            'cart_id' => $cart->id,
            'item_id' => '1',
            'name' => 'Test Product',
            'price' => 10000, // Price stored in cents
            'quantity' => 1,
        ]);
    }

    public function test_cart_synchronization_without_queue(): void
    {
        // Ensure queue sync is disabled
        config(['filament-cart.synchronization.queue_sync' => false]);

        // Add multiple items (id, name, price, quantity)
        CartFacade::add('1', 'Product 1', 5000, 2);
        CartFacade::add('2', 'Product 2', 7500, 1);

        // Verify both items are synchronized
        $cart = Cart::where('identifier', CartFacade::getIdentifier())->first();
        $this->assertNotNull($cart);

        $this->assertDatabaseCount('cart_items', 2);

        $cartItem1 = CartItem::where('cart_id', $cart->id)->where('item_id', '1')->first();
        $this->assertEquals(5000, $cartItem1->price); // Price in cents
        $this->assertEquals(2, $cartItem1->quantity);

        $cartItem2 = CartItem::where('cart_id', $cart->id)->where('item_id', '2')->first();
        $this->assertEquals(7500, $cartItem2->price); // Price in cents
        $this->assertEquals(1, $cartItem2->quantity);
    }
}
