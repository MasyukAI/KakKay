<?php

declare(strict_types=1);

use MasyukAI\Cart\Cart;
use MasyukAI\Cart\Conditions\CartCondition;
use MasyukAI\Cart\Exceptions\InvalidCartItemException;
use MasyukAI\Cart\Models\CartItem;
use MasyukAI\Cart\Storage\SessionStorage;

beforeEach(function () {
    // Initialize session storage for testing
    $sessionStore = new \Illuminate\Session\Store('testing', new \Illuminate\Session\ArraySessionHandler(120));
    $this->sessionStorage = new SessionStorage($sessionStore);

    // Initialize cart
    $this->cart = new Cart(
        identifier: 'add_items_test',
        storage: $this->sessionStorage,
        events: app('events'),
        instanceName: 'add_items_test',
        eventsEnabled: true
    );

    $this->cart->clear();
});

describe('Cart Item Addition Operations', function () {
    it('can add a simple item with all required properties', function () {
        $item = $this->cart->add(
            id: 'product-1',
            name: 'Test Product',
            price: 10.00,
            quantity: 2
        );

        expect($item)->toBeInstanceOf(CartItem::class);
        expect($item->id)->toBe('product-1');
        expect($item->name)->toBe('Test Product');
        expect($item->price)->toBe(10.00);
        expect($item->quantity)->toBe(2);
        expect($item->getRawSubtotal())->toBe(20.00);

        expect($this->cart->getTotalQuantity())->toBe(2);
        expect($this->cart->total()->getAmount())->toBe(20.00);
        expect($this->cart->count())->toBe(2);
        expect($this->cart->getItems())->toHaveCount(1);
        expect($this->cart->get('product-1'))->toBeInstanceOf(CartItem::class);
    });

    it('can add item with comprehensive attributes', function () {
        $attributes = [
            'size' => 'L',
            'color' => 'blue',
            'material' => 'cotton',
            'brand' => 'TestBrand',
            'sku' => 'TST-001',
            'category' => 'clothing',
            'tags' => ['summer', 'casual'],
            'metadata' => ['created_by' => 'system'],
        ];

        $item = $this->cart->add(
            id: 'product-1',
            name: 'Premium T-Shirt',
            price: 25.99,
            quantity: 1,
            attributes: $attributes
        );

        expect($item->attributes->toArray())->toBe($attributes);
        expect($item->getAttribute('size'))->toBe('L');
        expect($item->getAttribute('color'))->toBe('blue');
        expect($item->getAttribute('tags'))->toBe(['summer', 'casual']);
        expect($item->getAttribute('metadata'))->toBe(['created_by' => 'system']);
        expect($item->getAttribute('nonexistent'))->toBeNull();
    });

    it('can add item with multiple conditions', function () {
        $discount = new CartCondition(
            name: 'summer_discount',
            type: 'discount',
            target: 'subtotal',
            value: '-15%'
        );

        $tax = new CartCondition(
            name: 'vat',
            type: 'tax',
            target: 'subtotal',
            value: '+20%'
        );

        $item = $this->cart->add(
            id: 'product-1',
            name: 'Discounted Product',
            price: 100.00,
            quantity: 1,
            conditions: [$discount, $tax]
        );

        expect($item->getConditions())->toHaveCount(2);
        // 100 - 15% = 85, then +20% = 102
        expect($item->getRawSubtotal())->toBe(102.00);
    });

    it('merges quantities when adding existing items', function () {
        $initialAttributes = ['size' => 'M', 'color' => 'red'];
        $this->cart->add('product-1', 'Product', 10.00, 2, $initialAttributes);

        // When adding the same item, it should merge quantities
        $newAttributes = ['size' => 'L', 'style' => 'casual'];
        $this->cart->add('product-1', 'Product', 10.00, 3, $newAttributes);

        expect($this->cart->getTotalQuantity())->toBe(5);

        $item = $this->cart->get('product-1');
        expect($item->quantity)->toBe(5);
        // Current behavior: new attributes replace old ones
        expect($item->getAttribute('size'))->toBe('L');
        expect($item->getAttribute('color'))->toBeNull(); // Not preserved in current implementation
        expect($item->getAttribute('style'))->toBe('casual');
    });

    it('handles large quantities and prices correctly', function () {
        $item = $this->cart->add('product-1', 'Expensive Product', 9999.99, 1000);

        expect($item->price)->toBe(9999.99);
        expect($item->quantity)->toBe(1000);
        expect($item->getRawSubtotal())->toBe(9999990.0);
        expect($this->cart->total()->getAmount())->toBe(9999990.0);
    });

    it('handles decimal prices with precision', function () {
        $precisionPrices = [0.01, 0.99, 1.234, 99.999, 123.456789];

        foreach ($precisionPrices as $index => $price) {
            $this->cart->add("product-{$index}", 'Product', $price, 1);
        }

        expect($this->cart->getItems())->toHaveCount(5);
        expect($this->cart->get('product-2')->price)->toBe(1.234); // Price stored as provided
    });
});

describe('Cart Item Addition Validation', function () {
    it('validates and rejects invalid prices', function () {
        expect(fn () => $this->cart->add('product-1', 'Product', -10.00, 1))
            ->toThrow(InvalidCartItemException::class, 'Cart item price must be a positive number');

        expect(fn () => $this->cart->add('product-2', 'Product', -0.01, 1))
            ->toThrow(InvalidCartItemException::class, 'Cart item price must be a positive number');
    });

    it('validates and rejects invalid quantities', function () {
        expect(fn () => $this->cart->add('product-1', 'Product', 10.00, 0))
            ->toThrow(InvalidCartItemException::class, 'Cart item quantity must be a positive integer');

        expect(fn () => $this->cart->add('product-2', 'Product', 10.00, -1))
            ->toThrow(InvalidCartItemException::class, 'Cart item quantity must be a positive integer');
    });

    it('validates and rejects invalid item IDs', function () {
        expect(fn () => $this->cart->add('', 'Product', 10.00, 1))
            ->toThrow(InvalidCartItemException::class, 'Cart item ID is required');
    });

    it('validates and rejects invalid item names', function () {
        expect(fn () => $this->cart->add('product-1', '', 10.00, 1))
            ->toThrow(InvalidCartItemException::class, 'Cart item name is required');
    });
});
