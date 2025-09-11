<?php

declare(strict_types=1);

use Illuminate\Auth\Events\Login;
use MasyukAI\Cart\Cart;
use MasyukAI\Cart\Collections\CartCollection;
use MasyukAI\Cart\Collections\CartConditionCollection;
use MasyukAI\Cart\Conditions\CartCondition;
use MasyukAI\Cart\Listeners\HandleUserLogin;
use MasyukAI\Cart\Models\CartItem;
use MasyukAI\Cart\Services\CartMigrationService;
use MasyukAI\Cart\Storage\SessionStorage;

describe('100% Coverage Tests', function () {
    beforeEach(function () {
        $this->storage = new SessionStorage(app('session.store'), 'cart');
        $this->events = app('events');
        $this->config = config('cart');
        $this->cart = new Cart($this->storage, $this->events, 'default', true, $this->config);
        $this->cart->clear();
    });

    describe('Cart Class Coverage', function () {
        it('can handle associate method when last added item not found in cart', function () {
            // Add an item to get a last added item ID
            $this->cart->add('item-1', 'Test Item', 10.00, 1);

            // Manually clear the cart but keep the last added item ID
            $reflection = new ReflectionClass($this->cart);
            $clearMethod = $reflection->getMethod('save');
            $clearMethod->setAccessible(true);
            $clearMethod->invoke($this->cart, new CartCollection);

            // Now try to associate - should throw exception (line 58)
            expect(fn () => $this->cart->associate('App\\Models\\Product'))
                ->toThrow(\InvalidArgumentException::class, 'Last added item not found in cart.');
        });
    });

    describe('CartConditionCollection Coverage', function () {
        it('can handle withAttribute method with null value check', function () {
            $condition1 = new CartCondition('test1', 'discount', 'subtotal', '-10%', ['category' => 'electronics']);
            $condition2 = new CartCondition('test2', 'discount', 'subtotal', '-5%', ['category' => 'books']);
            $condition3 = new CartCondition('test3', 'tax', 'subtotal', '+8%');

            $collection = new CartConditionCollection([$condition1, $condition2, $condition3]);

            // Test withAttribute with null value (line 208) - should check for attribute existence
            $result = $collection->withAttribute('category');
            expect($result)->toHaveCount(2);

            // Test withAttribute with specific value
            $result = $collection->withAttribute('category', 'electronics');
            expect($result)->toHaveCount(1);
        });
    });

    describe('CartCollection Coverage', function () {
        it('can handle totalWithoutConditions method', function () {
            $this->cart->add('item-1', 'Item 1', 10.00, 2);
            $this->cart->add('item-2', 'Item 2', 15.00, 1);

            $collection = $this->cart->getItems();

            // This should test lines 87-103 in CartCollection (totalWithoutConditions)
            $total = $collection->totalWithoutConditions();
            expect($total)->toBeFloat();
        });

        it('can handle sum method with closure', function () {
            $this->cart->add('item-1', 'Item 1', 10.00, 2);
            $this->cart->add('item-2', 'Item 2', 15.00, 1);

            $collection = $this->cart->getItems();

            // Test sum method (line 127)
            $totalPrice = $collection->sum(function ($item) {
                return $item->price * $item->quantity;
            });

            expect($totalPrice)->toBe(35.0); // (10*2) + (15*1)
        });
    });

    describe('CartCondition Coverage', function () {
        it('can handle getAttributes method when no attributes exist', function () {
            $condition = new CartCondition('test', 'discount', 'subtotal', '-10%');

            // This should test line 190 - empty attributes case
            $attributes = $condition->getAttributes();
            expect($attributes)->toBeArray();
            expect($attributes)->toBeEmpty();
        });

        it('can handle hasAttribute method for non-existent attribute', function () {
            $condition = new CartCondition('test', 'discount', 'subtotal', '-10%');

            // This should test line 199 - attribute doesn't exist
            $hasAttribute = $condition->hasAttribute('nonexistent');
            expect($hasAttribute)->toBeFalse();
        });

        it('can handle toString method', function () {
            $condition = new CartCondition('test', 'discount', 'subtotal', '-10%');

            // This should test line 351 - __toString method
            $string = (string) $condition;
            expect($string)->toBeString();
            expect($string)->toContain('test');
        });
    });

    describe('CartItem Coverage', function () {
        it('can use total method as alias for subtotal', function () {
            $item = new CartItem('item-1', 'Test Item', 10.00, 2);

            // This should test line 346 - total() alias method
            $total = $item->total();
            $subtotal = $item->subtotal();

            expect($total->getAmount())->toBe($subtotal->getAmount());
        });
    });

    describe('HandleUserLogin Coverage', function () {
        it('can handle login when migration service is not available', function () {
            // Create a mock migration service that will be used in constructor
            $migrationService = $this->createMock(CartMigrationService::class);

            // Create a mock login event
            $user = new class
            {
                public $id = 123;
            };

            $event = new Login('web', $user, false);

            // Create listener with migration service
            $listener = new HandleUserLogin($migrationService);

            // Mock the migration service to simulate a scenario where we test line 28
            $migrationService->expects($this->once())
                ->method('migrateGuestCartForUser')
                ->with($user, 'default', null)
                ->willReturn((object) ['success' => false, 'itemsMerged' => 0]);

            // This should test the logic flow in handle method
            $listener->handle($event);

            // The test passes if no exception is thrown
            expect(true)->toBeTrue();
        });
    });

    describe('SessionStorage Coverage', function () {
        it('can handle put method with empty items array', function () {
            $storage = new SessionStorage(app('session.store'), 'cart');

            // This should test lines 23-26 - handling empty items
            $storage->putItems('test-id', 'default', []);

            $items = $storage->getItems('test-id', 'default');
            expect($items)->toBeArray();
            expect($items)->toBeEmpty();
        });
    });

    describe('CartMoney Coverage', function () {
        it('can handle config retrieval scenarios', function () {
            // Since getConfig method was removed, we test that config values are properly accessed
            // This ensures the configuration system is working as expected
            $defaultCurrency = config('cart.price_formatting.currency', 'USD');
            $defaultLocale = config('cart.price_formatting.locale', 'en_US');
            $defaultPrecision = config('cart.price_formatting.precision', 2);

            expect($defaultCurrency)->toBeString();
            expect($defaultLocale)->toBeString();
            expect($defaultPrecision)->toBeInt();
        });
    });

    describe('CartMigrationService Coverage', function () {
        it('can handle migration for user with valid parameters', function () {
            $service = new CartMigrationService;

            // Create a user object instead of just an integer
            $user = new class
            {
                public $id = 123;
            };

            // This should test lines 137-145 - testing the migration logic
            $result = $service->migrateGuestCartForUser($user, 'default', 'old-session-id');

            expect($result)->toBeObject();
            expect($result)->toHaveProperty('success');
        });
    });
});
