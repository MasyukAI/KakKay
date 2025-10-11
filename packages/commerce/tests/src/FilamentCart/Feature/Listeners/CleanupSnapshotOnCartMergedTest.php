<?php

declare(strict_types=1);

use AIArmada\Cart\Events\CartMerged;
use AIArmada\Cart\Facades\Cart;
use AIArmada\FilamentCart\Listeners\CleanupSnapshotOnCartMerged;
use AIArmada\FilamentCart\Models\Cart as CartSnapshot;
use AIArmada\FilamentCart\Models\CartCondition;
use AIArmada\FilamentCart\Models\CartItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Ensure the cart_snapshots table exists
    if (! Schema::hasTable('cart_snapshots')) {
        Schema::create('cart_snapshots', function ($table) {
            $table->uuid('id')->primary();
            $table->string('identifier')->index();
            $table->string('instance')->default('default')->index();
            $table->json('items')->nullable();
            $table->json('conditions')->nullable();
            $table->json('metadata')->nullable();
            $table->integer('items_count')->default(0);
            $table->integer('quantity')->default(0);
            $table->bigInteger('subtotal')->default(0);
            $table->bigInteger('total')->default(0);
            $table->bigInteger('savings')->default(0);
            $table->string('currency', 3)->default('MYR');
            $table->timestamps();
            $table->unique(['identifier', 'instance']);
        });
    }
});

describe('CartMerged Event Cleanup', function () {
    it('removes guest cart snapshot when cart is merged to user', function () {
        // Create guest cart snapshot
        $guestSnapshot = CartSnapshot::create([
            'identifier' => 'guest_session_123',
            'instance' => 'default',
            'items' => ['product-1' => ['id' => 'product-1', 'quantity' => 2]],
            'items_count' => 1,
            'quantity' => 2,
            'subtotal' => 1000,
            'total' => 1000,
            'currency' => 'MYR',
        ]);

        // Create user cart snapshot
        $userSnapshot = CartSnapshot::create([
            'identifier' => '42',
            'instance' => 'default',
            'items' => ['product-2' => ['id' => 'product-2', 'quantity' => 1]],
            'items_count' => 1,
            'quantity' => 1,
            'subtotal' => 500,
            'total' => 500,
            'currency' => 'MYR',
        ]);

        // Verify both snapshots exist
        expect(CartSnapshot::where('identifier', 'guest_session_123')->exists())->toBeTrue();
        expect(CartSnapshot::where('identifier', '42')->exists())->toBeTrue();

        // Create cart instances for the event
        // After a real swap, both carts would have the user identifier ('42')
        // but we pass the original source identifier in the event
        $guestCart = Cart::getCartInstance('default', '42'); // After swap, uses user ID
        $userCart = Cart::getCartInstance('default', '42');

        // Dispatch CartMerged event with original source identifier
        $event = new CartMerged(
            targetCart: $userCart,
            sourceCart: $guestCart,
            totalItemsMerged: 2,
            mergeStrategy: 'add_quantities',
            hadConflicts: false,
            originalSourceIdentifier: 'guest_session_123', // The guest session before swap
            originalTargetIdentifier: '42'
        );
        $listener = new CleanupSnapshotOnCartMerged;
        $listener->handle($event);

        // Guest snapshot should be deleted
        expect(CartSnapshot::where('identifier', 'guest_session_123')->exists())->toBeFalse();

        // User snapshot should still exist
        expect(CartSnapshot::where('identifier', '42')->exists())->toBeTrue();
    });

    it('handles cleanup for multiple instances separately', function () {
        // Create guest snapshots for different instances
        CartSnapshot::create([
            'identifier' => 'guest_session_456',
            'instance' => 'default',
            'items' => ['product-1' => ['id' => 'product-1', 'quantity' => 1]],
            'items_count' => 1,
            'quantity' => 1,
            'currency' => 'MYR',
        ]);

        CartSnapshot::create([
            'identifier' => 'guest_session_456',
            'instance' => 'wishlist',
            'items' => ['product-2' => ['id' => 'product-2', 'quantity' => 1]],
            'items_count' => 1,
            'quantity' => 1,
            'currency' => 'MYR',
        ]);

        // Verify both exist
        expect(CartSnapshot::where('identifier', 'guest_session_456')->count())->toBe(2);

        // Merge only the default cart
        $guestCart = Cart::getCartInstance('default', 'guest_session_456');
        $userCart = Cart::getCartInstance('default', '99');

        $event = new CartMerged(
            targetCart: $userCart,
            sourceCart: $guestCart,
            totalItemsMerged: 1,
            mergeStrategy: 'add_quantities',
            hadConflicts: false,
            originalSourceIdentifier: 'guest_session_456',
            originalTargetIdentifier: '99'
        );
        $listener = new CleanupSnapshotOnCartMerged;
        $listener->handle($event);

        // Only the default instance should be deleted
        expect(CartSnapshot::where('identifier', 'guest_session_456')
            ->where('instance', 'default')
            ->exists())->toBeFalse();

        // Wishlist should still exist
        expect(CartSnapshot::where('identifier', 'guest_session_456')
            ->where('instance', 'wishlist')
            ->exists())->toBeTrue();
    });

    it('does not fail if snapshot does not exist', function () {
        // No snapshot exists for this identifier
        expect(CartSnapshot::where('identifier', 'non_existent_session')->exists())->toBeFalse();

        // Create cart instances
        $guestCart = Cart::getCartInstance('default', 'non_existent_session');
        $userCart = Cart::getCartInstance('default', '100');

        // Dispatch event should not throw exception
        $event = new CartMerged(
            targetCart: $userCart,
            sourceCart: $guestCart,
            totalItemsMerged: 0,
            mergeStrategy: 'add_quantities',
            hadConflicts: false,
            originalSourceIdentifier: 'non_existent_session',
            originalTargetIdentifier: '100'
        );
        $listener = new CleanupSnapshotOnCartMerged;
        $listener->handle($event);

        // Should complete without error
        expect(true)->toBeTrue();
    });
});

describe('Integration with Cart Migration', function () {
    it('cleans up guest snapshot during actual cart migration', function () {
        // Create guest cart by directly using storage
        $storage = Cart::storage();
        $guestIdentifier = 'guest_789';

        $storage->putItems($guestIdentifier, 'default', [
            'product-1' => [
                'id' => 'product-1',
                'name' => 'Test Product',
                'price' => 99.99,
                'quantity' => 2,
                'attributes' => [],
                'conditions' => [],
            ],
        ]);

        // Create guest snapshot
        CartSnapshot::create([
            'identifier' => $guestIdentifier,
            'instance' => 'default',
            'items' => ['product-1' => ['id' => 'product-1', 'quantity' => 2]],
            'items_count' => 1,
            'quantity' => 2,
            'currency' => 'MYR',
        ]);

        // Verify guest snapshot exists
        $guestSnapshot = CartSnapshot::where('identifier', $guestIdentifier)
            ->where('instance', 'default')
            ->first();
        expect($guestSnapshot)->not->toBeNull();
        expect($guestSnapshot->quantity)->toBe(2);

        // Perform cart migration (swap)
        $migrationService = app(AIArmada\Cart\Services\CartMigrationService::class);

        $result = $migrationService->swap($guestIdentifier, '50', 'default');
        expect($result)->toBeTrue();

        // Manually trigger the listener to simulate real behavior
        $listener = new CleanupSnapshotOnCartMerged;
        $guestCart = Cart::getCartInstance('default', $guestIdentifier);
        $userCart = Cart::getCartInstance('default', '50');
        $event = new CartMerged(
            targetCart: $userCart,
            sourceCart: $guestCart,
            totalItemsMerged: 2,
            mergeStrategy: 'add_quantities',
            hadConflicts: false,
            originalSourceIdentifier: $guestIdentifier,
            originalTargetIdentifier: '50'
        );
        $listener->handle($event);

        // Guest snapshot should be cleaned up
        expect(CartSnapshot::where('identifier', $guestIdentifier)->exists())->toBeFalse();
    });

    it('prevents duplicate snapshots on login', function () {
        // Create guest cart via storage
        $storage = Cart::storage();
        $guestIdentifier = 'session_abc';

        $storage->putItems($guestIdentifier, 'default', [
            'product-1' => [
                'id' => 'product-1',
                'name' => 'Product 1',
                'price' => 50.00,
                'quantity' => 1,
                'attributes' => [],
                'conditions' => [],
            ],
            'product-2' => [
                'id' => 'product-2',
                'name' => 'Product 2',
                'price' => 75.00,
                'quantity' => 2,
                'attributes' => [],
                'conditions' => [],
            ],
        ]);

        // Create guest snapshot
        $guestSnapshot = CartSnapshot::create([
            'identifier' => $guestIdentifier,
            'instance' => 'default',
            'items' => [
                'product-1' => ['id' => 'product-1', 'quantity' => 1],
                'product-2' => ['id' => 'product-2', 'quantity' => 2],
            ],
            'items_count' => 2,
            'quantity' => 3,
            'currency' => 'MYR',
        ]);
        expect($guestSnapshot)->not->toBeNull();
        expect($guestSnapshot->items_count)->toBe(2);

        // User logs in and cart is migrated
        $migrationService = app(AIArmada\Cart\Services\CartMigrationService::class);

        // Do the swap
        $result = $migrationService->swap($guestIdentifier, '75', 'default');
        expect($result)->toBeTrue();

        // Manually trigger cleanup
        $listener = new CleanupSnapshotOnCartMerged;
        $guestCart = Cart::getCartInstance('default', $guestIdentifier);
        $userCart = Cart::getCartInstance('default', '75');
        $event = new CartMerged(
            targetCart: $userCart,
            sourceCart: $guestCart,
            totalItemsMerged: 3,
            mergeStrategy: 'add_quantities',
            hadConflicts: false,
            originalSourceIdentifier: $guestIdentifier,
            originalTargetIdentifier: '75'
        );
        $listener->handle($event);

        // Should only have no snapshots now (guest removed, user not synced in test)
        $guestSnapshots = CartSnapshot::where('identifier', $guestIdentifier)->get();
        expect($guestSnapshots)->toHaveCount(0);
    });
});

describe('Normalized Data Cleanup', function () {
    it('cleans up cart snapshot with items and conditions via cascade delete', function () {
        $guestIdentifier = 'guest_with_normalized_data';

        // Create guest snapshot with items and conditions
        $cartSnapshot = CartSnapshot::create([
            'identifier' => $guestIdentifier,
            'instance' => 'default',
            'items' => ['product-1' => ['id' => 'product-1', 'quantity' => 2]],
            'items_count' => 1,
            'quantity' => 2,
            'currency' => 'MYR',
        ]);

        // Create normalized item records
        $item1 = $cartSnapshot->cartItems()->create([
            'item_id' => 'product-1',
            'name' => 'Test Product',
            'price' => 9999, // 99.99 in cents
            'quantity' => 2,
            'attributes' => ['color' => 'red'],
        ]);

        $item2 = $cartSnapshot->cartItems()->create([
            'item_id' => 'product-2',
            'name' => 'Another Product',
            'price' => 4999, // 49.99 in cents
            'quantity' => 1,
        ]);

        // Create cart-level condition
        $cartSnapshot->cartConditions()->create([
            'name' => 'tax',
            'type' => 'tax',
            'target' => 'subtotal',
            'value' => '10%',
            'is_percentage' => true,
            'order' => 1,
        ]);

        // Create item-level condition
        $cartSnapshot->cartConditions()->create([
            'cart_item_id' => $item1->id,
            'item_id' => 'product-1',
            'name' => 'discount',
            'type' => 'discount',
            'target' => 'price',
            'value' => '5%',
            'is_discount' => true,
            'is_percentage' => true,
            'order' => 1,
        ]);

        // Verify all data exists
        expect(CartSnapshot::where('identifier', $guestIdentifier)->count())->toBe(1);
        expect(CartItem::where('cart_id', $cartSnapshot->id)->count())->toBe(2);
        expect(CartCondition::where('cart_id', $cartSnapshot->id)->count())->toBe(2);

        // Trigger cleanup
        $guestCart = Cart::getCartInstance('default', $guestIdentifier);
        $userCart = Cart::getCartInstance('default', '500');

        $event = new CartMerged(
            targetCart: $userCart,
            sourceCart: $guestCart,
            totalItemsMerged: 2,
            mergeStrategy: 'add_quantities',
            hadConflicts: false,
            originalSourceIdentifier: $guestIdentifier,
            originalTargetIdentifier: '500'
        );
        $listener = new CleanupSnapshotOnCartMerged;
        $listener->handle($event);

        // Verify all normalized data is cleaned up via cascade delete
        expect(CartSnapshot::where('identifier', $guestIdentifier)->exists())->toBeFalse();
        expect(CartItem::where('cart_id', $cartSnapshot->id)->count())->toBe(0);
        expect(CartCondition::where('cart_id', $cartSnapshot->id)->count())->toBe(0);
    });
});

describe('Edge Cases', function () {
    it('handles empty guest cart cleanup', function () {
        // Create an empty guest snapshot
        CartSnapshot::create([
            'identifier' => 'empty_session',
            'instance' => 'default',
            'items' => [],
            'items_count' => 0,
            'quantity' => 0,
            'currency' => 'MYR',
        ]);

        expect(CartSnapshot::where('identifier', 'empty_session')->exists())->toBeTrue();

        $guestCart = Cart::getCartInstance('default', 'empty_session');
        $userCart = Cart::getCartInstance('default', '200');

        $event = new CartMerged(
            targetCart: $userCart,
            sourceCart: $guestCart,
            totalItemsMerged: 0,
            mergeStrategy: 'add_quantities',
            hadConflicts: false,
            originalSourceIdentifier: 'empty_session',
            originalTargetIdentifier: '200'
        );
        $listener = new CleanupSnapshotOnCartMerged;
        $listener->handle($event);

        // Empty snapshot should still be removed
        expect(CartSnapshot::where('identifier', 'empty_session')->exists())->toBeFalse();
    });

    it('preserves other user snapshots during cleanup', function () {
        // Create multiple user snapshots
        CartSnapshot::create(['identifier' => '100', 'instance' => 'default', 'currency' => 'MYR']);
        CartSnapshot::create(['identifier' => '101', 'instance' => 'default', 'currency' => 'MYR']);
        CartSnapshot::create(['identifier' => 'guest_xyz', 'instance' => 'default', 'currency' => 'MYR']);

        expect(CartSnapshot::count())->toBe(3);

        // Clean up only the guest snapshot
        $guestCart = Cart::getCartInstance('default', 'guest_xyz');
        $userCart = Cart::getCartInstance('default', '100');

        $event = new CartMerged(
            targetCart: $userCart,
            sourceCart: $guestCart,
            totalItemsMerged: 0,
            mergeStrategy: 'add_quantities',
            hadConflicts: false,
            originalSourceIdentifier: 'guest_xyz',
            originalTargetIdentifier: '100'
        );
        $listener = new CleanupSnapshotOnCartMerged;
        $listener->handle($event);

        // Should have 2 snapshots remaining (the other users)
        expect(CartSnapshot::count())->toBe(2);
        expect(CartSnapshot::where('identifier', '100')->exists())->toBeTrue();
        expect(CartSnapshot::where('identifier', '101')->exists())->toBeTrue();
        expect(CartSnapshot::where('identifier', 'guest_xyz')->exists())->toBeFalse();
    });
});
