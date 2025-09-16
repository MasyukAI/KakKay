<?php

declare(strict_types=1);

use MasyukAI\Cart\Cart;
use MasyukAI\Cart\Conditions\CartCondition;
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

describe('Cart edge cases and stress tests', function () {
    it('handles extremely large quantities and prices', function () {
        $largePrice = 999999.99;
        $largeQuantity = 10000;

        $item = $this->cart->add('bulk-item', 'Bulk Product', $largePrice, $largeQuantity);

        expect($item->price)->toBe($largePrice);
        expect($item->quantity)->toBe($largeQuantity);
        expect($this->cart->total()->getAmount())->toBe($largePrice * $largeQuantity);
    });

    it('handles many unique items', function () {
        // Add 100 unique items
        for ($i = 1; $i <= 100; $i++) {
            $this->cart->add("product-{$i}", "Product {$i}", 10.00 + $i, 1);
        }

        expect($this->cart->getItems())->toHaveCount(100);
        expect($this->cart->getTotalQuantity())->toBe(100);

        // Verify we can access any item
        expect($this->cart->get('product-50'))->toBeInstanceOf(CartItem::class);
        expect($this->cart->get('product-50')->name)->toBe('Product 50');
    })->skip(fn () => ! env('RUN_STRESS_TESTS', false), 'Stress test skipped by default - set RUN_STRESS_TESTS=true to include');

    it('handles complex condition chains', function () {
        $this->cart->add('product-1', 'Product', 100.00, 1);

        // Add multiple overlapping conditions
        $conditions = [
            new CartCondition('discount1', 'discount', 'subtotal', '-10%'),
            new CartCondition('discount2', 'discount', 'subtotal', '-5%'),
            new CartCondition('tax1', 'tax', 'subtotal', '+8%'),
            new CartCondition('tax2', 'tax', 'subtotal', '+2%'),
            new CartCondition('fee', 'charge', 'subtotal', '+15.00'),
        ];

        foreach ($conditions as $condition) {
            $this->cart->addCondition($condition);
        }

        expect($this->cart->getConditions())->toHaveCount(5);
        expect($this->cart->total()->getAmount())->toBeFloat();
        expect($this->cart->total()->getAmount())->toBeGreaterThan(0);
    });

    it('handles rapid operations sequence', function () {
        // Rapidly add, update, remove items
        for ($i = 1; $i <= 50; $i++) {
            $this->cart->add("temp-{$i}", "Temp {$i}", 5.00, $i);
        }

        expect($this->cart->getItems())->toHaveCount(50);

        // Update every other item
        for ($i = 2; $i <= 50; $i += 2) {
            $this->cart->update("temp-{$i}", ['quantity' => $i * 2]);
        }

        // Remove every third item
        for ($i = 3; $i <= 50; $i += 3) {
            $this->cart->remove("temp-{$i}");
        }

        expect($this->cart->getItems()->count())->toBeLessThan(50);
        expect($this->cart->isEmpty())->toBeFalse();
    })->skip(fn () => ! env('RUN_STRESS_TESTS', false), 'Stress test skipped by default - set RUN_STRESS_TESTS=true to include');

    it('maintains data integrity during concurrent-like operations', function () {
        $originalItem = $this->cart->add('integrity-test', 'Test Product', 25.99, 3);
        $originalTotal = $this->cart->total()->getAmount();

        // Simulate concurrent modifications
        $this->cart->update('integrity-test', ['name' => 'Updated Product']);
        $updatedItem = $this->cart->get('integrity-test');

        expect($updatedItem->id)->toBe($originalItem->id);
        expect($updatedItem->price)->toBe($originalItem->price);
        expect($updatedItem->quantity)->toBe($originalItem->quantity);
        expect($updatedItem->name)->toBe('Updated Product');
        expect($this->cart->total()->getAmount())->toBe($originalTotal);
    });

    it('handles special characters in item data', function () {
        $specialName = 'Product with émojis 🚀 & special chars: àáâãäåæçèéêë';
        $specialId = 'product-with-special-chars-åæø';
        $specialAttributes = [
            'description' => 'Description with 中文 and русский текст',
            'emoji' => '🎉🎊✨',
            'unicode' => "\u{1F600}\u{1F601}\u{1F602}",
        ];

        $item = $this->cart->add($specialId, $specialName, 15.99, 1, $specialAttributes);

        expect($item->id)->toBe($specialId);
        expect($item->name)->toBe($specialName);
        expect($item->getAttribute('description'))->toBe($specialAttributes['description']);
        expect($item->getAttribute('emoji'))->toBe($specialAttributes['emoji']);
        expect($item->getAttribute('unicode'))->toBe($specialAttributes['unicode']);
    });
});
