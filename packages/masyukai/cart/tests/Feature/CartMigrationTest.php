<?php

declare(strict_types=1);

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Session;
use MasyukAI\Cart\Events\CartMerged;
use MasyukAI\Cart\Facades\Cart;
use MasyukAI\Cart\Listeners\HandleUserLogin;
use MasyukAI\Cart\Listeners\HandleUserLogout;
use MasyukAI\Cart\Services\CartMigrationService;
use Tests\TestCase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->cartMigration = new CartMigrationService();
    
    // Create a test user
    $this->user = new class {
        public $id = 1;
        public function getAuthIdentifier() { return $this->id; }
    };
    
    Cart::clear();
    Cart::setInstance('guest_123')->clear();
    Cart::setInstance('user_1')->clear();
    Cart::setInstance('default'); // Reset to default
});

it('can migrate guest cart to user cart', function () {
    // Add items to guest cart
    Cart::setInstance('guest_123')->add(
        'product-1',
        'Test Product 1',
        10.00,
        2
    );

    Cart::setInstance('guest_123')->add(
        'product-2', 
        'Test Product 2',
        15.00,
        1
    );

    expect(Cart::setInstance('guest_123')->count())->toBe(3);
    expect(Cart::setInstance('user_1')->count())->toBe(0);

    // Migrate guest cart to user cart
    $result = $this->cartMigration->migrateGuestCartToUser('guest_123', 1);

    expect($result)->toBeTrue();
    expect(Cart::setInstance('user_1')->count())->toBe(3);
    expect(Cart::setInstance('guest_123')->count())->toBe(0);
    
    $userItems = Cart::setInstance('user_1')->content();
    expect($userItems)->toHaveCount(2);
    expect($userItems->first()->name)->toBe('Test Product 1');
    expect($userItems->first()->quantity)->toBe(2);
});

it('can handle merge conflicts with add quantities strategy', function () {
    // Add items to both carts with same product
    Cart::setInstance('guest_123')->add(
        'product-1',
        'Test Product',
        10.00,
        2
    );

    Cart::setInstance('user_1')->add(
        'product-1',
        'Test Product',
        10.00,
        3
    );

    // Set merge strategy to add quantities
    config(['cart.migration.merge_strategy' => 'add_quantities']);

    $this->cartMigration->migrateGuestCartToUser('guest_123', 1);

    $userItems = Cart::setInstance('user_1')->content();
    expect($userItems)->toHaveCount(1);
    expect($userItems->first()->quantity)->toBe(5); // 2 + 3
});

it('can handle merge conflicts with keep highest quantity strategy', function () {
    // Add items to both carts with same product
    Cart::setInstance('guest_123')->add(
        'product-1',
        'Test Product',
        10.00,
        5
    );

    Cart::setInstance('user_1')->add(
        'product-1',
        'Test Product',
        10.00,
        3
    );

    // Set merge strategy to keep highest quantity
    config(['cart.migration.merge_strategy' => 'keep_highest_quantity']);

    $this->cartMigration->migrateGuestCartToUser('guest_123', 1);

    $userItems = Cart::setInstance('user_1')->content();
    expect($userItems)->toHaveCount(1);
    expect($userItems->first()->quantity)->toBe(5); // Keep highest (guest cart)
});

it('can handle merge conflicts with keep user cart strategy', function () {
    // Add items to both carts with same product
    Cart::setInstance('guest_123')->add(
        'product-1',
        'Test Product',
        10.00,
        5
    );

    Cart::setInstance('user_1')->add(
        'product-1',
        'Test Product',
        10.00,
        3
    );

    // Set merge strategy to keep user cart
    config(['cart.migration.merge_strategy' => 'keep_user_cart']);

    $this->cartMigration->migrateGuestCartToUser('guest_123', 1);

    $userItems = Cart::setInstance('user_1')->content();
    expect($userItems)->toHaveCount(1);
    expect($userItems->first()->quantity)->toBe(3); // Keep user cart quantity
});

it('can handle merge conflicts with replace with guest strategy', function () {
    // Add items to both carts with same product
    Cart::setInstance('guest_123')->add(
        'product-1',
        'Test Product',
        10.00,
        5
    );

    Cart::setInstance('user_1')->add(
        'product-1',
        'Test Product',
        10.00,
        3
    );

    // Set merge strategy to replace with guest
    config(['cart.migration.merge_strategy' => 'replace_with_guest']);

    $this->cartMigration->migrateGuestCartToUser('guest_123', 1);

    $userItems = Cart::setInstance('user_1')->content();
    expect($userItems)->toHaveCount(1);
    expect($userItems->first()->quantity)->toBe(5); // Replace with guest cart quantity
});

it('dispatches cart merged event on successful migration', function () {
    Event::fake();

    // Add items to guest cart
    Cart::setInstance('guest_123')->add(
        'product-1',
        'Test Product',
        10.00,
        2
    );

    $this->cartMigration->migrateGuestCartToUser('guest_123', 1);

    Event::assertDispatched(CartMerged::class, function ($event) {
        return $event->targetInstance === 'user_1' &&
               $event->sourceInstance === 'guest_123' &&
               $event->totalItemsMerged === 2;
    });
});

it('handles user login event automatically when configured', function () {
    config(['cart.migration.auto_migrate_on_login' => true]);
    
    // Mock Auth facade
    Auth::shouldReceive('id')->andReturn(1);
    Auth::shouldReceive('user')->andReturn($this->user);
    Auth::shouldReceive('check')->andReturn(true); // After login
    
    // Mock session to return guest cart ID
    Session::shouldReceive('getId')->andReturn('123');
    Session::shouldReceive('flash')->withAnyArgs()->andReturn(true);
    
    // Add items to guest cart
    Cart::setInstance('guest_123')->add(
        'product-1',
        'Test Product',
        10.00,
        2
    );

    $listener = new HandleUserLogin($this->cartMigration);
    $event = new Login('web', $this->user, false);
    
    expect(Cart::setInstance('guest_123')->count())->toBe(2);
    expect(Cart::setInstance('user_1')->count())->toBe(0);

    $listener->handle($event);

    expect(Cart::setInstance('user_1')->count())->toBe(2);
    expect(Cart::setInstance('guest_123')->count())->toBe(0);
});

it('handles user logout event when configured', function () {
    config(['cart.migration.backup_guest_cart_on_logout' => true]);
    
    // Mock Auth facade
    Auth::shouldReceive('id')->andReturn(1);
    Auth::shouldReceive('user')->andReturn($this->user);
    Auth::shouldReceive('check')->andReturn(false); // After logout
    
    // Mock session to return guest cart ID
    Session::shouldReceive('getId')->andReturn('123');
    
    // Add items to user cart
    Cart::setInstance('user_1')->add(
        'product-1',
        'Test Product',
        10.00,
        2
    );

    $listener = new HandleUserLogout($this->cartMigration);
    $event = new Logout('web', $this->user);
    
    expect(Cart::setInstance('user_1')->count())->toBe(2);
    expect(Cart::setInstance('guest_123')->count())->toBe(0);

    $listener->handle($event);

    // User cart should be copied to guest cart for backup
    expect(Cart::setInstance('guest_123')->count())->toBe(2);
    expect(Cart::setInstance('user_1')->count())->toBe(2); // Original remains
});

it('returns false when guest cart is empty', function () {
    expect(Cart::setInstance('guest_123')->count())->toBe(0);
    
    $result = $this->cartMigration->migrateGuestCartToUser('guest_123', 1);
    
    expect($result)->toBeFalse();
});

it('can get instance name for authenticated user', function () {
    $instanceName = $this->cartMigration->getInstanceName(1);
    expect($instanceName)->toBe('user_1');
});

it('can get instance name for guest session', function () {
    $instanceName = $this->cartMigration->getInstanceName(null, 'session_123');
    expect($instanceName)->toBe('guest_session_123');
});

it('validates merge strategy configuration', function () {
    // Test with invalid merge strategy
    config(['cart.migration.merge_strategy' => 'invalid_strategy']);
    
    Cart::setInstance('guest_123')->add(
        'product-1',
        'Test Product',
        10.00,
        2
    );

    Cart::setInstance('user_1')->add(
        'product-1',
        'Test Product',
        10.00,
        3
    );

    // Should fall back to default strategy (add_quantities)
    $this->cartMigration->migrateGuestCartToUser('guest_123', 1);

    $userItems = Cart::setInstance('user_1')->content();
    expect($userItems->first()->quantity)->toBe(5); // Should add quantities as fallback
});

it('preserves cart item attributes during migration', function () {
    // Add item with custom attributes to guest cart
    Cart::setInstance('guest_123')->add(
        'product-1',
        'Test Product',
        10.00,
        1,
        [
            'color' => 'red',
            'size' => 'large',
            'gift_wrap' => true,
        ]
    );

    $this->cartMigration->migrateGuestCartToUser('guest_123', 1);

    $userItems = Cart::setInstance('user_1')->content();
    $item = $userItems->first();
    
    expect($item->attributes->get('color'))->toBe('red');
    expect($item->attributes->get('size'))->toBe('large');
    expect($item->attributes->get('gift_wrap'))->toBe(true);
});
