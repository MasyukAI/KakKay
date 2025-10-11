<?php

declare(strict_types=1);

use MasyukAI\Cart\Facades\Cart;
use MasyukAI\Cart\Services\CartMigrationService;

describe('CartMigrationService Identifier Management', function () {
    beforeEach(function () {
        Cart::clear();
    });

    it('gets identifier for user', function () {
        $service = app(CartMigrationService::class);
        $identifier = $service->getUserIdentifier(42);

        expect($identifier)->toBe('42');
    });

    it('gets identifier for guest session', function () {
        $service = app(CartMigrationService::class);
        $sessionId = session()->getId();
        $identifier = $service->getGuestIdentifier($sessionId);

        expect($identifier)->toBe($sessionId);
    });

    it('gets current identifier based on auth state', function () {
        $service = app(CartMigrationService::class);
        $identifier = $service->getCurrentIdentifier();

        expect($identifier)->not->toBeEmpty();
    });
});

describe('CartMigrationService Swapping', function () {
    beforeEach(function () {
        Cart::clear();
    });

    it('swaps cart from one identifier to another', function () {
        // Add items to guest cart
        Cart::add('item-1', 'Item 1', 10.00, 1);

        $oldIdentifier = session()->getId();
        $newIdentifier = 'user-42';

        $service = app(CartMigrationService::class);
        $result = $service->swap($oldIdentifier, $newIdentifier, 'default');

        expect($result)->toBeTrue();
    });

    it('swaps guest cart to user on login', function () {
        Cart::add('guest-item', 'Guest Item', 25.00, 2);

        $service = app(CartMigrationService::class);
        $result = $service->swapGuestCartToUser(42, 'default');

        expect($result)->toBeTrue();
    });
});

describe('CartMigrationService Migration', function () {
    beforeEach(function () {
        Cart::clear();
    });

    it('migrates guest cart to user cart', function () {
        $sessionId = session()->getId();

        // Add items as guest
        Cart::add('item', 'Item', 50.00, 1);

        $service = app(CartMigrationService::class);
        $result = $service->migrateGuestCartToUser(42, 'default', $sessionId);

        expect($result)->toBeTrue();
    });

    it('handles empty guest cart migration', function () {
        $sessionId = session()->getId();

        $service = app(CartMigrationService::class);
        $result = $service->migrateGuestCartToUser(42, 'default', $sessionId);

        expect($result)->toBeFalse(); // No items to migrate
    });
});
