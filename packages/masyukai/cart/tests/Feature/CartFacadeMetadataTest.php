<?php

declare(strict_types=1);

use MasyukAI\Cart\Facades\Cart;
use MasyukAI\Cart\Storage\SessionStorage;

describe('Cart Facade Metadata Management', function () {
    beforeEach(function () {
        $session = new \Illuminate\Session\Store('test', new \Illuminate\Session\ArraySessionHandler(60));
        $storage = new SessionStorage($session);

        // Reset the facade for testing
        app()->singleton('cart', function () use ($storage) {
            return new \MasyukAI\Cart\CartManager($storage);
        });
    });

    test('facade metadata methods work correctly', function () {
        // Test facade methods
        Cart::setMetadata('facade_test', 'working');
        expect(Cart::getMetadata('facade_test'))->toBe('working');

        expect(Cart::hasMetadata('facade_test'))->toBeTrue();
        expect(Cart::hasMetadata('missing_key'))->toBeFalse();

        Cart::removeMetadata('facade_test');
        expect(Cart::hasMetadata('facade_test'))->toBeFalse();
    });

    test('facade batch metadata operations work', function () {
        Cart::setMetadataBatch([
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => 123,
        ]);

        expect(Cart::getMetadata('key1'))->toBe('value1');
        expect(Cart::getMetadata('key2'))->toBe('value2');
        expect(Cart::getMetadata('key3'))->toBe(123);
    });

    test('facade method chaining works', function () {
        $result = Cart::setMetadata('chain1', 'value1')
            ->setMetadata('chain2', 'value2');

        // The facade should return the Cart instance for chaining
        expect($result)->toBeInstanceOf(\MasyukAI\Cart\Cart::class);

        expect(Cart::getMetadata('chain1'))->toBe('value1');
        expect(Cart::getMetadata('chain2'))->toBe('value2');
    });
});
