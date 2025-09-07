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

    describe('Backup Operations', function () {
        it('can backup user cart to guest session', function () {
            // Setup user cart data via storage
            $storage = Cart::storage();
            $userData = [
                'backup_item' => [
                    'id' => 'backup_item',
                    'name' => 'Backup Product',
                    'price' => 15.0,
                    'quantity' => 1,
                    'attributes' => [],
                    'conditions' => [],
                    'associated_model' => null,
                ],
            ];
            $storage->putItems('111', 'default', $userData);

            $result = $this->service->backupUserCartToGuest(111, 'default', 'backup_session');
            expect($result)->toBeTrue();
        });

        it('returns false when user cart is empty for backup', function () {
            // Ensure user cart is empty
            $storage = Cart::storage();
            $storage->forget('999', 'default');

            $result = $this->service->backupUserCartToGuest(999, 'default', 'backup_empty');
            expect($result)->toBeFalse();
        });
    });

    describe('Auto-switch Methods', function () {
        it('auto switch cart identifier when user is authenticated', function () {
            Auth::shouldReceive('check')->andReturn(true);
            Auth::shouldReceive('id')->andReturn(123);

            $this->service->autoSwitchCartIdentifier();
            expect(true)->toBeTrue(); // Method completes without error
        });

        it('auto switch cart instance when user is not authenticated', function () {
            Auth::shouldReceive('check')->andReturn(false);

            $this->service->autoSwitchCartInstance();
            expect(true)->toBeTrue(); // Method completes without error
        });
    });

    describe('Multiple Instance Migration', function () {
        it('can migrate multiple instances from guest to user', function () {
            // Setup multiple guest cart instances via storage
            $storage = Cart::storage();

            $guestData1 = [
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
            $guestData2 = [
                'item2' => [
                    'id' => 'item2',
                    'name' => 'Product 2',
                    'price' => 20.0,
                    'quantity' => 1,
                    'attributes' => [],
                    'conditions' => [],
                    'associated_model' => null,
                ],
            ];

            $storage->putItems('multi_session', 'instance1', $guestData1);
            $storage->putItems('multi_session', 'instance2', $guestData2);

            $results = $this->service->migrateAllGuestInstances(333, 'multi_session');
            expect($results)->toBeArray();
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

        it('can exercise mergeCartData method for coverage', function () {
            $reflection = new ReflectionClass($this->service);
            $method = $reflection->getMethod('mergeCartData');
            $method->setAccessible(true);

            // Setup test data in storage first
            $storage = Cart::storage();
            $sourceData = ['item1' => ['id' => 'item1', 'name' => 'Product 1', 'price' => 10.0, 'quantity' => 2, 'attributes' => [], 'conditions' => [], 'associated_model' => null]];
            $targetData = ['item2' => ['id' => 'item2', 'name' => 'Product 2', 'price' => 20.0, 'quantity' => 1, 'attributes' => [], 'conditions' => [], 'associated_model' => null]];

            $storage->putItems('source_id', 'default', $sourceData);
            $storage->putItems('target_id', 'default', $targetData);

            $result = $method->invokeArgs($this->service, ['source_id', 'target_id', 'default']);
            expect($result)->toBeInstanceOf(\MasyukAI\Cart\Collections\CartCollection::class);
        });

        it('can exercise getConflictItems method for coverage', function () {
            $reflection = new ReflectionClass($this->service);
            $method = $reflection->getMethod('getConflictItems');
            $method->setAccessible(true);

            // Setup test data in storage first
            $storage = Cart::storage();
            $sourceData = ['item1' => ['id' => 'item1', 'name' => 'Product 1', 'price' => 10.0, 'quantity' => 2, 'attributes' => [], 'conditions' => [], 'associated_model' => null]];
            $targetData = ['item1' => ['id' => 'item1', 'name' => 'Product 1', 'price' => 10.0, 'quantity' => 1, 'attributes' => [], 'conditions' => [], 'associated_model' => null]];

            $storage->putItems('conflict_source', 'default', $sourceData);
            $storage->putItems('conflict_target', 'default', $targetData);

            $result = $method->invokeArgs($this->service, ['conflict_source', 'conflict_target', 'default']);
            expect($result)->toBeInstanceOf(\Illuminate\Support\Collection::class);
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

        it('merges items with all strategies in mergeCartData', function () {
            $reflection = new ReflectionClass($this->service);
            $method = $reflection->getMethod('mergeCartData');
            $method->setAccessible(true);

            $strategies = [
                'add_quantities' => 8,
                'keep_highest_quantity' => 5,
                'keep_user_cart' => 5,
                'replace_with_guest' => 3,
                'unknown_strategy' => 8,
            ];

            foreach ($strategies as $strategy => $expectedQuantity) {
                config(['cart.migration.merge_strategy' => $strategy]);

                // Setup test data in storage
                $storage = Cart::storage();
                $sourceData = [
                    'item1' => [
                        'id' => 'item1',
                        'name' => 'Product 1',
                        'price' => 10.0,
                        'quantity' => 3,
                        'attributes' => [],
                        'conditions' => [],
                        'associated_model' => null,
                    ],
                ];
                $targetData = [
                    'item1' => [
                        'id' => 'item1',
                        'name' => 'Product 1',
                        'price' => 10.0,
                        'quantity' => 5,
                        'attributes' => [],
                        'conditions' => [],
                        'associated_model' => null,
                    ],
                ];
                $storage->putItems('source_id', 'default', $sourceData);
                $storage->putItems('target_id', 'default', $targetData);

                $result = $method->invokeArgs($this->service, ['source_id', 'target_id', 'default']);
                expect($result)->toBeInstanceOf(\MasyukAI\Cart\Collections\CartCollection::class);
                expect($result->get('item1')['quantity'])->toBe($expectedQuantity);
            }
        });
    });
});
