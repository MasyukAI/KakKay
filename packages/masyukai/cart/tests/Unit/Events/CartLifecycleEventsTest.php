<?php

declare(strict_types=1);

use MasyukAI\Cart\CartManager;
use MasyukAI\Cart\Events\CartCleared;
use MasyukAI\Cart\Events\CartCreated;
use MasyukAI\Cart\Events\CartMerged;

describe('CartCleared Event', function () {
    it('creates event with cart', function () {
        $cart = app(CartManager::class)->getCurrentCart();
        $event = new CartCleared($cart);

        expect($event)->toBeInstanceOf(CartCleared::class);
        expect($event->cart)->toBe($cart);
    });

    it('converts to array', function () {
        $cart = app(CartManager::class)->getCurrentCart();
        $cart->add('test-item', 'Test Item', 10.00);

        $event = new CartCleared($cart);
        $array = $event->toArray();

        expect($array)->toBeArray();
        expect($array)->toHaveKey('identifier');
        expect($array)->toHaveKey('instance_name');
        expect($array)->toHaveKey('timestamp');
        expect($array['identifier'])->toBe($cart->getIdentifier());
    });
});

describe('CartCreated Event', function () {
    it('creates event with cart', function () {
        $cart = app(CartManager::class)->getCurrentCart();
        $event = new CartCreated($cart);

        expect($event)->toBeInstanceOf(CartCreated::class);
        expect($event->cart)->toBe($cart);
    });

    it('converts to array', function () {
        $cart = app(CartManager::class)->getCurrentCart();
        $event = new CartCreated($cart);
        $array = $event->toArray();

        expect($array)->toBeArray();
        expect($array)->toHaveKey('identifier');
        expect($array)->toHaveKey('instance_name');
        expect($array)->toHaveKey('timestamp');
    });
});

describe('CartMerged Event', function () {
    it('creates event with source and target carts', function () {
        $manager = app(CartManager::class);
        $sourceCart = $manager->getCartInstance('source', 'source-id');
        $targetCart = $manager->getCartInstance('target', 'target-id');

        $event = new CartMerged($targetCart, $sourceCart, 2, 'add_quantities');

        expect($event)->toBeInstanceOf(CartMerged::class);
        expect($event->sourceCart)->toBe($sourceCart);
        expect($event->targetCart)->toBe($targetCart);
    });

    it('stores merged items count', function () {
        $manager = app(CartManager::class);
        $sourceCart = $manager->getCartInstance('source', 'source-id');
        $targetCart = $manager->getCartInstance('target', 'target-id');

        $sourceCart->add('item-1', 'Item 1', 10.00);
        $sourceCart->add('item-2', 'Item 2', 20.00);

        $event = new CartMerged($targetCart, $sourceCart, 2, 'add_quantities');

        expect($event->totalItemsMerged)->toBe(2);
    });

    it('stores merge strategy', function () {
        $manager = app(CartManager::class);
        $sourceCart = $manager->getCartInstance('source', 'source-id');
        $targetCart = $manager->getCartInstance('target', 'target-id');

        $event = new CartMerged($targetCart, $sourceCart, 3, 'keep_highest', true);

        expect($event->mergeStrategy)->toBe('keep_highest');
        expect($event->hadConflicts)->toBeTrue();
    });

    it('converts to array', function () {
        $manager = app(CartManager::class);
        $sourceCart = $manager->getCartInstance('source', 'source-id');
        $targetCart = $manager->getCartInstance('target', 'target-id');

        $event = new CartMerged($targetCart, $sourceCart, 3, 'add_quantities');
        $array = $event->toArray();

        expect($array)->toBeArray();
        expect($array)->toHaveKey('target_cart');
        expect($array)->toHaveKey('source_cart');
        expect($array)->toHaveKey('merge_details');
        expect($array)->toHaveKey('timestamp');
        expect($array['merge_details']['items_merged'])->toBe(3);
        expect($array['merge_details']['strategy'])->toBe('add_quantities');
    });

    it('handles zero merged items', function () {
        $manager = app(CartManager::class);
        $sourceCart = $manager->getCartInstance('source', 'source-id');
        $targetCart = $manager->getCartInstance('target', 'target-id');

        $event = new CartMerged($targetCart, $sourceCart, 0, 'add_quantities');

        expect($event->totalItemsMerged)->toBe(0);
    });

    it('handles conflict flag', function () {
        $manager = app(CartManager::class);
        $sourceCart = $manager->getCartInstance('source', 'source-id');
        $targetCart = $manager->getCartInstance('target', 'target-id');

        $eventWithConflict = new CartMerged($targetCart, $sourceCart, 5, 'keep_highest', true);
        $eventWithoutConflict = new CartMerged($targetCart, $sourceCart, 3, 'add_quantities', false);

        expect($eventWithConflict->hadConflicts)->toBeTrue();
        expect($eventWithoutConflict->hadConflicts)->toBeFalse();
    });
});
