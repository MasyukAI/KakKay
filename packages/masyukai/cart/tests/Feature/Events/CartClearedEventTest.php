<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use MasyukAI\Cart\Events\CartCleared;
use MasyukAI\Cart\Facades\Cart;

describe('CartCleared Event Dispatch', function () {
    beforeEach(function () {
        Event::fake(); // Fake events BEFORE any cart operations
        Cart::clear();
    });

    it('dispatches CartCleared event when clearing the cart', function () {
        // Add items first
        Cart::add('item-1', 'Item 1', 100.00, 1);
        Cart::add('item-2', 'Item 2', 50.00, 2);

        // Clear the cart
        Cart::clear();

        // Assert CartCleared event was dispatched
        Event::assertDispatched(CartCleared::class, function (CartCleared $event) {
            return $event->cart instanceof \MasyukAI\Cart\Cart;
        });
    });

    it('dispatches CartCleared event even when cart is already empty', function () {
        // Cart is already empty from beforeEach

        // Try to clear again
        Cart::clear();

        // Should still dispatch event even when cart is already empty
        Event::assertDispatched(CartCleared::class);
    });

    it('includes correct data in CartCleared event', function () {
        Cart::add('item-1', 'Item 1', 100.00, 1);

        Cart::clear();

        Event::assertDispatched(CartCleared::class, function (CartCleared $event) {
            $data = $event->toArray();

            return isset($data['identifier']) &&
                   isset($data['instance_name']) &&
                   isset($data['timestamp']);
        });
    });

    it('dispatches CartCleared event when events are enabled', function () {
        Cart::add('item-1', 'Item 1', 100.00, 1);

        Cart::clear();

        Event::assertDispatched(CartCleared::class);
    });
});
