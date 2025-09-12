<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Auth;
use MasyukAI\Cart\Facades\Cart;
use MasyukAI\Cart\Services\CartMigrationService;

beforeEach(function () {
    $this->service = new CartMigrationService;

    // Mock Auth facade for testing
    Auth::clearResolvedInstances();

    // Ensure clean state
    Cart::setInstance('default')->clear();
});

describe('CartMigrationService Coverage Tests', function () {
    describe('Identifier Management', function () {
        it('can get identifier for user ID', function () {
            $identifier = $this->service->getIdentifier(123);
            expect($identifier)->toBe('123');
        });

        it('can get identifier for session ID', function () {
            $identifier = $this->service->getIdentifier(null, 'abc123');
            expect($identifier)->toBe('abc123');
        });

        it('falls back to current session when no params provided', function () {
            $identifier = $this->service->getIdentifier();
            expect($identifier)->toBeString()->not->toBeEmpty();
        });

        it('can get current identifier when authenticated', function () {
            Auth::shouldReceive('check')->once()->andReturn(true);
            Auth::shouldReceive('id')->once()->andReturn(456);

            $identifier = $this->service->getCurrentIdentifier();
            expect($identifier)->toBe('456');
        });

        it('can get current identifier when not authenticated', function () {
            Auth::shouldReceive('check')->once()->andReturn(false);

            $identifier = $this->service->getCurrentIdentifier();
            expect($identifier)->toBeString()->not->toBeEmpty();
        });

        it('can get guest identifier', function () {
            $identifier = $this->service->getGuestIdentifier('guest_session');
            expect($identifier)->toBe('guest_session');
        });

        it('can get user identifier', function () {
            $identifier = $this->service->getUserIdentifier(789);
            expect($identifier)->toBe('789');
        });
    });

    describe('Cart Migration', function () {
        it('returns false when guest cart is empty', function () {
            // Test migration with empty guest cart
            $result = $this->service->migrateGuestCartToUser(123, 'default', 'empty_session');
            expect($result)->toBeFalse();
        });

        it('can migrate guest cart with items to user cart', function () {
            // Setup guest cart data directly in storage
            $storage = Cart::storage();
            $guestData = [
                'item1' => [
                    'id' => 'item1',
                    'name' => 'Product 1',
                    'price' => 10.0,
                    'quantity' => 2,
                    'attributes' => [],
                    'conditions' => [],
                    'associated_model' => null,
                ],
            ];
            $storage->putItems('test_session', 'default', $guestData);

            // Migrate to user cart
            $result = $this->service->migrateGuestCartToUser(123, 'default', 'test_session');
            expect($result)->toBeTrue();
        });

        it('can migrate guest cart with conditions', function () {
            // Setup guest cart with items and conditions via storage
            $storage = Cart::storage();

            $guestData = [
                'item1' => [
                    'id' => 'item1',
                    'name' => 'Product 1',
                    'price' => 10.0,
                    'quantity' => 1,
                    'attributes' => [],
                    'conditions' => [],
                    'associated_model' => null,
                ],
            ];
            $storage->putItems('cond_session', 'default', $guestData);

            $conditionData = [
                'discount' => [
                    'name' => 'discount',
                    'type' => 'discount',
                    'target' => 'subtotal',
                    'value' => '-10',
                    'attributes' => [],
                    'order' => 0,
                ],
            ];
            $storage->putConditions('cond_session', 'default', $conditionData);

            $result = $this->service->migrateGuestCartToUser(789, 'default', 'cond_session');
            expect($result)->toBeTrue();
        });

        it('can migrate with user object instead of ID', function () {
            $user = (object) ['id' => 456];

            // Setup guest cart data
            $storage = Cart::storage();
            $guestData = [
                'item1' => [
                    'id' => 'item1',
                    'name' => 'Product 1',
                    'price' => 10.0,
                    'quantity' => 1,
                    'attributes' => [],
                    'conditions' => [],
                    'associated_model' => null,
                ],
            ];
            $storage->putItems('user_session', 'default', $guestData);

            $result = $this->service->migrateGuestCartForUser($user, 'default', 'user_session');
            expect($result)->toBeInstanceOf('stdClass');
        });

        it('returns appropriate result type', function () {
            $storage = Cart::storage();
            $guestData = [
                'item1' => [
                    'id' => 'item1',
                    'name' => 'Product 1',
                    'price' => 10.0,
                    'quantity' => 1,
                    'attributes' => [],
                    'conditions' => [],
                    'associated_model' => null,
                ],
            ];
            $storage->putItems('result_session', 'default', $guestData);

            $result = $this->service->migrateGuestCartToUser(555, 'default', 'result_session');
            expect($result)->toBeTrue();
        });
    });

    describe('Auto-switch Methods', function () {
        it('auto switch cart identifier when user is authenticated', function () {
            Auth::shouldReceive('check')->andReturn(true);
            Auth::shouldReceive('id')->andReturn(123);

            $this->service->autoSwitchCartIdentifier();
            expect(true)->toBeTrue(); // Method completes without error
        });

    });

    describe('Merge Strategy Testing', function () {
        it('can handle add quantities merge strategy', function () {
            config(['cart.migration.merge_strategy' => 'add_quantities']);

            $storage = Cart::storage();
            $guestSessionId = 'strategy_session';
            $userId = 444;

            // Setup guest cart
            $guestData = [
                'item1' => [
                    'id' => 'item1',
                    'name' => 'Product 1',
                    'price' => 10.0,
                    'quantity' => 2,
                    'attributes' => [],
                    'conditions' => [],
                    'associated_model' => null,
                ],
            ];
            $storage->putItems($guestSessionId, 'default', $guestData);

            // Setup existing user cart with same item
            $userData = [
                'item1' => [
                    'id' => 'item1',
                    'name' => 'Product 1',
                    'price' => 10.0,
                    'quantity' => 1,
                    'attributes' => [],
                    'conditions' => [],
                    'associated_model' => null,
                ],
            ];
            $storage->putItems((string) $userId, 'default', $userData);

            $result = $this->service->migrateGuestCartToUser($userId, 'default', $guestSessionId);

            expect($result)->toBeTrue();
        });
    });

    describe('Additional Coverage Tests', function () {
        it('can exercise protected methods via reflection for coverage', function () {
            $reflection = new ReflectionClass($this->service);
            $method = $reflection->getMethod('mergeItemsArray');
            $method->setAccessible(true);

            $guestItems = ['item1' => ['id' => 'item1', 'quantity' => 2]];
            $userItems = ['item2' => ['id' => 'item2', 'quantity' => 1]];

            $result = $method->invokeArgs($this->service, [$guestItems, $userItems]);
            expect($result)->toBeArray();
        });

        it('can resolve quantity conflicts for all strategies', function () {
            $reflection = new ReflectionClass($this->service);
            $method = $reflection->getMethod('resolveQuantityConflict');
            $method->setAccessible(true);

            $userQuantity = 5;
            $guestQuantity = 3;

            // add_quantities
            $result = $method->invokeArgs($this->service, [$userQuantity, $guestQuantity, 'add_quantities']);
            expect($result)->toBe(8);

            // keep_highest_quantity
            $result = $method->invokeArgs($this->service, [$userQuantity, $guestQuantity, 'keep_highest_quantity']);
            expect($result)->toBe(5);

            // keep_user_cart
            $result = $method->invokeArgs($this->service, [$userQuantity, $guestQuantity, 'keep_user_cart']);
            expect($result)->toBe(5);

            // replace_with_guest
            $result = $method->invokeArgs($this->service, [$userQuantity, $guestQuantity, 'replace_with_guest']);
            expect($result)->toBe(3);

            // default (unknown strategy)
            $result = $method->invokeArgs($this->service, [$userQuantity, $guestQuantity, 'unknown_strategy']);
            expect($result)->toBe(8);
        });
    });
});
