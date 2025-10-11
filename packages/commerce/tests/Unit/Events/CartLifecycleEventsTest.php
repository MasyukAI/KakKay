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

        $event = new CartMerged(
            targetCart: $targetCart,
            sourceCart: $sourceCart,
            totalItemsMerged: 2,
            mergeStrategy: 'add_quantities',
            hadConflicts: false,
            originalSourceIdentifier: 'source-id',
            originalTargetIdentifier: 'target-id'
        );

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

        $event = new CartMerged(
            targetCart: $targetCart,
            sourceCart: $sourceCart,
            totalItemsMerged: 2,
            mergeStrategy: 'add_quantities',
            hadConflicts: false,
            originalSourceIdentifier: 'source-id',
            originalTargetIdentifier: 'target-id'
        );

        expect($event->totalItemsMerged)->toBe(2);
    });

    it('stores merge strategy', function () {
        $manager = app(CartManager::class);
        $sourceCart = $manager->getCartInstance('source', 'source-id');
        $targetCart = $manager->getCartInstance('target', 'target-id');

        $event = new CartMerged(
            targetCart: $targetCart,
            sourceCart: $sourceCart,
            totalItemsMerged: 3,
            mergeStrategy: 'keep_highest',
            hadConflicts: true,
            originalSourceIdentifier: 'source-id',
            originalTargetIdentifier: 'target-id'
        );

        expect($event->mergeStrategy)->toBe('keep_highest');
        expect($event->hadConflicts)->toBeTrue();
    });

    it('converts to array', function () {
        $manager = app(CartManager::class);
        $sourceCart = $manager->getCartInstance('source', 'source-id');
        $targetCart = $manager->getCartInstance('target', 'target-id');

        $event = new CartMerged(
            targetCart: $targetCart,
            sourceCart: $sourceCart,
            totalItemsMerged: 3,
            mergeStrategy: 'add_quantities',
            hadConflicts: false,
            originalSourceIdentifier: 'source-id',
            originalTargetIdentifier: 'target-id'
        );
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

        $event = new CartMerged(
            targetCart: $targetCart,
            sourceCart: $sourceCart,
            totalItemsMerged: 0,
            mergeStrategy: 'add_quantities',
            hadConflicts: false,
            originalSourceIdentifier: 'source-id',
            originalTargetIdentifier: 'target-id'
        );

        expect($event->totalItemsMerged)->toBe(0);
    });

    it('handles conflict flag', function () {
        $manager = app(CartManager::class);
        $sourceCart = $manager->getCartInstance('source', 'source-id');
        $targetCart = $manager->getCartInstance('target', 'target-id');

        $eventWithConflict = new CartMerged(
            targetCart: $targetCart,
            sourceCart: $sourceCart,
            totalItemsMerged: 5,
            mergeStrategy: 'keep_highest',
            hadConflicts: true,
            originalSourceIdentifier: 'source-id',
            originalTargetIdentifier: 'target-id'
        );
        $eventWithoutConflict = new CartMerged(
            targetCart: $targetCart,
            sourceCart: $sourceCart,
            totalItemsMerged: 3,
            mergeStrategy: 'add_quantities',
            hadConflicts: false,
            originalSourceIdentifier: 'source-id',
            originalTargetIdentifier: 'target-id'
        );

        expect($eventWithConflict->hadConflicts)->toBeTrue();
        expect($eventWithoutConflict->hadConflicts)->toBeFalse();
    });
});
