<?php

declare(strict_types=1);

use MasyukAI\Cart\Cart;
use MasyukAI\Cart\Models\CartItem;
use MasyukAI\Cart\Storage\DatabaseStorage;
use MasyukAI\Cart\Storage\SessionStorage;

beforeEach(function () {
    // Ensure events dispatcher is available
    if (! app()->bound('events')) {
        app()->singleton('events', function ($app) {
            return new \Illuminate\Events\Dispatcher($app);
        });
    }

    // Initialize session storage with array session store for testing
    $sessionStore = new \Illuminate\Session\Store('testing', new \Illuminate\Session\ArraySessionHandler(120));
    $this->sessionStorage = new SessionStorage($sessionStore);

    // Only initialize database storage if db is available (some tests don't need it)
    if (app()->bound('db')) {
        try {
            $this->databaseStorage = new DatabaseStorage(
                database: app('db')->connection(),
                table: 'carts'
            );
        } catch (\Exception $e) {
            $this->databaseStorage = null; // Skip database tests if connection fails
        }
    } else {
        $this->databaseStorage = null; // Skip database tests if db not bound
    }

    // Initialize cart with session storage for most tests
    $this->cart = new Cart(
        storage: $this->sessionStorage,
        identifier: 'test-user',
        events: new \Illuminate\Events\Dispatcher,
        instanceName: 'bulletproof_test',
        eventsEnabled: true
    );

    // Clear any existing cart data
    $this->cart->clear();
});

describe('Cart calculations and information', function () {
    beforeEach(function () {
        $this->cart->add('product-1', 'Product 1', 10.99, 2);
        $this->cart->add('product-2', 'Product 2', 15.50, 3);
        $this->cart->add('product-3', 'Product 3', 8.25, 1);
    });

    it('returns accurate item counts', function () {
        expect($this->cart->getTotalQuantity())->toBe(6);
        expect($this->cart->count())->toBe(6);
        expect($this->cart->getItems()->count())->toBe(3); // Unique items
    });

    it('calculates correct subtotals', function () {
        // (10.99 * 2) + (15.50 * 3) + (8.25 * 1) = 21.98 + 46.50 + 8.25 = 76.73
        expect($this->cart->subtotal()->getAmount())->toBe(76.73);
    });

    it('can get specific items with all properties', function () {
        $item = $this->cart->get('product-1');

        expect($item)->toBeInstanceOf(CartItem::class);
        expect($item->id)->toBe('product-1');
        expect($item->name)->toBe('Product 1');
        expect($item->price)->toBe(10.99);
        expect($item->quantity)->toBe(2);
        expect($item->getRawSubtotal())->toBe(21.98);

        expect($this->cart->get('nonexistent'))->toBeNull();
    });

    it('accurately determines empty state', function () {
        expect($this->cart->isEmpty())->toBeFalse();

        $this->cart->clear();

        expect($this->cart->isEmpty())->toBeTrue();
        expect($this->cart->getTotalQuantity())->toBe(0);
        expect($this->cart->subtotal()->getAmount())->toBe(0);
        expect($this->cart->total()->getAmount())->toBe(0.0);
    });

    it('provides correct cart state after operations', function () {
        // Initial state
        expect($this->cart->getTotalQuantity())->toBe(6);
        expect($this->cart->subtotal()->getAmount())->toBe(76.73);

        // After adding item
        $this->cart->add('product-4', 'Product 4', 20.00, 1);
        expect($this->cart->getTotalQuantity())->toBe(7);
        expect($this->cart->subtotal()->getAmount())->toBe(96.73);

        // After removing item
        $this->cart->remove('product-2');
        expect($this->cart->getTotalQuantity())->toBe(4);
        expect(round($this->cart->subtotal()->getAmount(), 2))->toBe(50.23);

        // After updating quantity
        $this->cart->update('product-1', ['quantity' => ['value' => 5]]);
        expect($this->cart->getTotalQuantity())->toBe(7); // 5 (product-1) + 1 (product-3) + 1 (product-4)
        expect($this->cart->subtotal()->getAmount())->toBe(83.20);
    });

    it('can convert cart to array format', function () {
        $cartArray = $this->cart->toArray();

        expect($cartArray)->toBeArray();
        expect($cartArray)->toHaveKeys(['items', 'quantity', 'subtotal', 'total', 'conditions']);
        expect($cartArray['items'])->toHaveCount(3);
        expect($cartArray['quantity'])->toBe(6);
        expect(round($cartArray['subtotal'], 2))->toBe(76.73);
        expect(round($cartArray['total'], 2))->toBe(76.73);
        expect($cartArray['conditions'])->toHaveCount(0);
    });

    it('handles precision and rounding correctly', function () {
        // Clear cart for clean test
        $this->cart->clear();

        // Test with prices that might cause rounding issues
        $prices = [0.01, 0.10, 0.33, 1.333333, 9.999999, 10.006];

        foreach ($prices as $index => $price) {
            $this->cart->add("precision-{$index}", "Product {$index}", $price, 1);
        }

        $total = $this->cart->total();
        expect($total->getAmount())->toBeFloat();
        expect(round($total->getAmount(), 2))->toBe($total->getAmount()); // Should already be rounded
    });
});
