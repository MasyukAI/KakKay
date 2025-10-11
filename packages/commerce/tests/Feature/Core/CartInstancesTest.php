<?php

declare(strict_types=1);

use MasyukAI\Cart\Facades\Cart;

describe('Cart Instance Management', function () {
    beforeEach(function () {
        Cart::setInstance('default')->clear();
        Cart::setInstance('wishlist')->clear();
        Cart::setInstance('comparison')->clear();
        Cart::setInstance('default'); // Reset to default
    });

    it('can switch between cart instances', function () {
        // Add items to default instance
        Cart::setInstance('default')->add('item-1', 'Item 1', 10.00, 1);

        // Switch to wishlist and add items
        Cart::setInstance('wishlist')->add('item-2', 'Item 2', 20.00, 1);

        // Verify isolation
        expect(Cart::setInstance('default')->count())->toBe(1);
        expect(Cart::setInstance('wishlist')->count())->toBe(1);
        expect(Cart::setInstance('default')->get('item-2'))->toBeNull();
        expect(Cart::setInstance('wishlist')->get('item-1'))->toBeNull();
    });

    it('maintains separate totals for each instance', function () {
        Cart::setInstance('default')->add('item-1', 'Item 1', 10.00, 2);
        Cart::setInstance('wishlist')->add('item-2', 'Item 2', 50.00, 1);

        expect(Cart::setInstance('default')->total()->getAmount())->toBe(20.00);
        expect(Cart::setInstance('wishlist')->total()->getAmount())->toBe(50.00);
    });

    it('maintains separate conditions for each instance', function () {
        Cart::setInstance('default')->add('item', 'Item', 100.00, 1);
        Cart::setInstance('wishlist')->add('item', 'Item', 100.00, 1);

        Cart::setInstance('default')->addTax('VAT', '10%');

        expect(Cart::setInstance('default')->total()->getAmount())->toBe(110.00);
        expect(Cart::setInstance('wishlist')->total()->getAmount())->toBe(100.00);
    });

    it('returns current instance name', function () {
        Cart::setInstance('custom-instance');

        expect(Cart::instance())->toBe('custom-instance');
    });

    it('can clear specific instance without affecting others', function () {
        Cart::setInstance('default')->add('item-1', 'Item 1', 10.00, 1);
        Cart::setInstance('wishlist')->add('item-2', 'Item 2', 20.00, 1);

        Cart::setInstance('default')->clear();

        expect(Cart::setInstance('default')->isEmpty())->toBeTrue();
        expect(Cart::setInstance('wishlist')->isEmpty())->toBeFalse();
    });

    it('handles chaining with instance switching', function () {
        Cart::setInstance('default')->add('item-1', 'Item 1', 10.00, 1);
        Cart::setInstance('default')->addTax('VAT', '10%');

        expect(Cart::setInstance('default')->total()->getAmount())->toBe(11.00);
    });
});

describe('Instance Isolation', function () {
    beforeEach(function () {
        Cart::setInstance('default')->clear();
        Cart::setInstance('other')->clear();
    });

    it('isolates items between instances', function () {
        Cart::setInstance('default')->add('shared-id', 'Default Item', 10.00, 5);
        Cart::setInstance('other')->add('shared-id', 'Other Item', 20.00, 3);

        $defaultItem = Cart::setInstance('default')->get('shared-id');
        $otherItem = Cart::setInstance('other')->get('shared-id');

        expect($defaultItem->quantity)->toBe(5);
        expect($defaultItem->price)->toBe(10.00);
        expect($otherItem->quantity)->toBe(3);
        expect($otherItem->price)->toBe(20.00);
    });

    it('isolates metadata between instances', function () {
        Cart::setInstance('default')->setMetadata('customer_note', 'Default note');
        Cart::setInstance('other')->setMetadata('customer_note', 'Other note');

        expect(Cart::setInstance('default')->getMetadata('customer_note'))->toBe('Default note');
        expect(Cart::setInstance('other')->getMetadata('customer_note'))->toBe('Other note');
    });

    it('handles multiple instance operations in sequence', function () {
        $instances = ['cart1', 'cart2', 'cart3'];

        foreach ($instances as $index => $instance) {
            Cart::setInstance($instance)->add("item-{$index}", "Item {$index}", 10.00 * ($index + 1), $index + 1);
        }

        expect(Cart::setInstance('cart1')->getTotalQuantity())->toBe(1);
        expect(Cart::setInstance('cart2')->getTotalQuantity())->toBe(2);
        expect(Cart::setInstance('cart3')->getTotalQuantity())->toBe(3);
    });
});
