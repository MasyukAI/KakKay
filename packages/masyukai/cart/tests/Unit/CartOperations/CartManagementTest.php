<?php

declare(strict_types=1);

use MasyukAI\Cart\Cart;
use MasyukAI\Cart\Models\CartItem;
use MasyukAI\Cart\Storage\SessionStorage;

beforeEach(function () {
    // Initialize session storage for testing
    $sessionStore = new \Illuminate\Session\Store('testing', new \Illuminate\Session\ArraySessionHandler(120));
    $this->sessionStorage = new SessionStorage($sessionStore);

    // Initialize cart
    $this->cart = new Cart(
        storage: $this->sessionStorage,
        events: app('events'),
        instanceName: 'management_test',
        eventsEnabled: true
    );

    $this->cart->clear();
    
    // Add some initial items for management operations
    $this->cart->add('product-1', 'Product 1', 10.00, 2);
    $this->cart->add('product-2', 'Product 2', 15.00, 3);
    $this->cart->add('product-3', 'Product 3', 8.50, 1);
});

describe('Cart Item Management Operations', function () {
    it('can update existing item quantities', function () {
        $updatedItem = $this->cart->update('product-1', ['quantity' => ['value' => 5]]);

        expect($updatedItem)->toBeInstanceOf(CartItem::class);
        expect($updatedItem->quantity)->toBe(5);
        expect($this->cart->getTotalQuantity())->toBe(9); // 5 + 3 + 1
        
        // Verify the item was actually updated in the cart
        expect($this->cart->get('product-1')->quantity)->toBe(5);
    });

    it('can update item attributes', function () {
        $this->cart->update('product-1', [
            'attributes' => ['size' => 'XL', 'color' => 'blue'],
        ]);

        $item = $this->cart->get('product-1');
        expect($item->getAttribute('size'))->toBe('XL');
        expect($item->getAttribute('color'))->toBe('blue');
    });

    it('can remove specific items', function () {
        $initialCount = $this->cart->getItems()->count();
        
        $removedItem = $this->cart->remove('product-2');

        expect($removedItem)->toBeInstanceOf(CartItem::class);
        expect($removedItem->id)->toBe('product-2');
        expect($this->cart->getItems())->toHaveCount($initialCount - 1);
        expect($this->cart->get('product-2'))->toBeNull();
        expect($this->cart->getTotalQuantity())->toBe(3); // 2 + 1 (product-2 removed)
    });

    it('can clear entire cart', function () {
        expect($this->cart->isEmpty())->toBeFalse();
        expect($this->cart->getItems())->toHaveCount(3);

        $result = $this->cart->clear();

        expect($result)->toBeTrue();
        expect($this->cart->isEmpty())->toBeTrue();
        expect($this->cart->getItems())->toHaveCount(0);
        expect($this->cart->getTotalQuantity())->toBe(0);
        expect($this->cart->total()->getAmount())->toBe(0.0);
    });

    it('handles non-existent item operations gracefully', function () {
        expect($this->cart->get('nonexistent'))->toBeNull();
        expect($this->cart->update('nonexistent', ['quantity' => 5]))->toBeNull();
        expect($this->cart->remove('nonexistent'))->toBeNull();
    });
});

describe('Cart Content Analysis', function () {
    it('can search and filter cart content', function () {
        // Test search functionality - find items over $10
        $expensiveItems = $this->cart->search(function (CartItem $item) {
            return $item->price > 10.00;
        });

        expect($expensiveItems)->toHaveCount(1);
        expect($expensiveItems->first()->id)->toBe('product-2');
        expect($expensiveItems->first()->price)->toBe(15.00);
    });

    it('can count items correctly', function () {
        expect($this->cart->count())->toBe(6); // Total quantity: 2 + 3 + 1
        expect($this->cart->getTotalQuantity())->toBe(6);
        expect($this->cart->getItems()->count())->toBe(3); // Unique items
    });

    it('can check cart state', function () {
        expect($this->cart->isEmpty())->toBeFalse();
        
        // Clear and test empty state
        $this->cart->clear();
        expect($this->cart->isEmpty())->toBeTrue();
        expect($this->cart->count())->toBe(0);
    });
});