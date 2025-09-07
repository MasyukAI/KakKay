<?php

declare(strict_types=1);

use MasyukAI\Cart\Cart;
use MasyukAI\Cart\Storage\SessionStorage;

describe('Traits Coverage Tests', function () {
    beforeEach(function () {
        $this->storage = new SessionStorage(app('session.store'), 'cart');
        $this->events = app('events');
        $this->config = config('cart');
        $this->cart = new Cart($this->storage, $this->events, 'default', true, $this->config);
        $this->cart->clear();
    });

    describe('ManagesConditions Coverage', function () {
        it('can handle removing condition from item that does not exist', function () {
            // This tests line 178 in ManagesConditions - when item doesn't exist
            $result = $this->cart->removeItemCondition('non-existent-item', 'some-condition');
            expect($result)->toBeFalse();
        });

        it('can handle removing non-existent condition from existing item', function () {
            // Add an item first
            $this->cart->add('item-1', 'Test Item', 10.00, 1);

            // Try to remove a condition that doesn't exist on the item
            // This tests the condition exists check in removeItemCondition
            $result = $this->cart->removeItemCondition('item-1', 'non-existent-condition');
            expect($result)->toBeFalse();
        });
    });

    describe('ManagesItems Coverage', function () {
        it('can handle update that results in item removal due to zero quantity', function () {
            // Add an item first
            $this->cart->add('item-1', 'Test Item', 10.00, 2);
            expect($this->cart->count())->toBe(2); // Total quantity
            expect($this->cart->getItems()->count())->toBe(1); // Number of unique items

            // Test absolute quantity update to 0 - should trigger removal
            $result = $this->cart->update('item-1', ['quantity' => ['value' => 0]]);
            expect($result)->toBeInstanceOf(\MasyukAI\Cart\Models\CartItem::class);
            expect($this->cart->count())->toBe(0);
            expect($this->cart->getItems()->count())->toBe(0);
        });

        it('can handle update that results in item removal due to negative quantity', function () {
            // Add an item with quantity 2
            $this->cart->add('item-1', 'Test Item', 10.00, 2);

            // Test relative quantity update that results in negative - should trigger removal
            $result = $this->cart->update('item-1', ['quantity' => -5]); // 2 + (-5) = -3
            expect($result)->toBeInstanceOf(\MasyukAI\Cart\Models\CartItem::class);
            expect($this->cart->count())->toBe(0);
        });

        it('can handle price normalization with local decimals config', function () {
            // Create cart with local decimals config
            $configWithDecimals = array_merge($this->config, ['decimals' => 2]);
            $cart = new Cart($this->storage, $this->events, 'test', true, $configWithDecimals);

            // Add item with string price that has comma (will be removed, not converted to decimal)
            // '10,99' becomes '1099' after comma removal, then 1099.00 after rounding
            $cart->add('item-1', 'Test Item', '10,99', 1);
            $item = $cart->get('item-1');

            // The price should be normalized - comma removed, then rounded
            expect($item->price)->toBe(1099.00);
        });
    });

    describe('ManagesStorage Coverage', function () {
        it('can use getContent alias method', function () {
            // Add some items to have content
            $this->cart->add('item-1', 'Test Item', 10.00, 1);

            // Test the getContent alias method (line 79)
            $content = $this->cart->getContent();

            expect($content)->toBeArray();
            expect($content)->toHaveKey('items');
            expect($content['items'])->toHaveCount(1);
        });

        it('can handle restoreAssociatedModel with invalid associated data', function () {
            // This tests line 138 in ManagesStorage where class doesn't exist
            $reflection = new ReflectionClass($this->cart);
            $method = $reflection->getMethod('restoreAssociatedModel');
            $method->setAccessible(true);

            // Test with class that doesn't exist
            $result = $method->invoke($this->cart, ['class' => 'NonExistentClass']);
            expect($result)->toBeNull();

            // Test with array that doesn't have class key
            $result = $method->invoke($this->cart, ['other_key' => 'value']);
            expect($result)->toBeNull();

            // Test with null
            $result = $method->invoke($this->cart, null);
            expect($result)->toBeNull();
        });
    });
});
