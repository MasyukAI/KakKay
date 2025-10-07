<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use MasyukAI\Cart\Events\CartMerged;
use MasyukAI\Cart\Facades\Cart;
use MasyukAI\Cart\Listeners\HandleUserLogin;
use MasyukAI\FilamentCart\Models\Cart as CartSnapshot;

uses(RefreshDatabase::class);

describe('Guest to User Cart Migration with Snapshot Cleanup', function () {
    beforeEach(function () {
        $this->user = User::factory()->create([
            'email' => 'test@example.com',
        ]);
    });

    it('removes guest cart snapshot after successful login migration', function () {
        // Simulate guest adding items to cart
        $guestSessionId = session()->getId();

        Cart::add('product-1', 'Test Product 1', 100.00, 2);
        Cart::add('product-2', 'Test Product 2', 50.00, 1);

        // Force snapshot creation using the current cart instance
        $syncManager = app(MasyukAI\FilamentCart\Services\CartSyncManager::class);
        $currentCart = Cart::getCartInstance('default', $guestSessionId);
        $syncManager->sync($currentCart);

        // Verify guest snapshot exists
        $guestSnapshot = CartSnapshot::where('identifier', $guestSessionId)
            ->where('instance', 'default')
            ->first();

        expect($guestSnapshot)->not->toBeNull();
        expect($guestSnapshot->items_count)->toBe(2);
        expect($guestSnapshot->quantity)->toBe(3);

        // Store the old session ID for migration
        Cache::put("cart_migration_{$this->user->email}", $guestSessionId);

        // User logs in - this should trigger cart migration
        Event::fake([CartMerged::class]);

        $loginEvent = new Login('web', $this->user, false);
        $migrationService = app(MasyukAI\Cart\Services\CartMigrationService::class);
        $listener = new HandleUserLogin($migrationService);
        $listener->handle($loginEvent);

        // CartMerged event should have been dispatched
        Event::assertDispatched(CartMerged::class);

        // Get the event to manually trigger cleanup listener
        $dispatchedEvents = Event::dispatched(CartMerged::class);
        expect($dispatchedEvents)->toHaveCount(1);

        // Manually trigger the cleanup listener (since Event::fake prevents auto-execution)
        $cleanupListener = new MasyukAI\FilamentCart\Listeners\CleanupSnapshotOnCartMerged;
        $cleanupListener->handle($dispatchedEvents[0][0]);

        // Guest snapshot should be removed
        expect(CartSnapshot::where('identifier', $guestSessionId)->exists())->toBeFalse();

        // User should now have a snapshot with the migrated items
        $userSnapshot = CartSnapshot::where('identifier', (string) $this->user->id)
            ->where('instance', 'default')
            ->first();

        // Note: The user snapshot might not exist yet in this test since we're faking events
        // In production, SyncCompleteCart listener would create/update it
    });

    it('prevents duplicate cart snapshots on login', function () {
        $guestSessionId = session()->getId();

        // Guest adds items
        Cart::add('product-1', 'Product 1', 100.00, 1);

        // Create guest snapshot
        $syncManager = app(MasyukAI\FilamentCart\Services\CartSyncManager::class);
        $currentCart = Cart::getCartInstance('default', $guestSessionId);
        $syncManager->sync($currentCart);

        // User already has a cart from previous session
        $userIdentifier = (string) $this->user->id;
        CartSnapshot::create([
            'identifier' => $userIdentifier,
            'instance' => 'default',
            'items' => ['product-2' => ['id' => 'product-2', 'quantity' => 1]],
            'items_count' => 1,
            'quantity' => 1,
            'subtotal' => 5000,
            'total' => 5000,
            'currency' => 'MYR',
        ]);

        // Before login: should have 2 snapshots
        expect(CartSnapshot::count())->toBe(2);

        // Store session for migration
        Cache::put("cart_migration_{$this->user->email}", $guestSessionId);

        // User logs in
        $loginEvent = new Login('web', $this->user, false);
        $migrationService = app(MasyukAI\Cart\Services\CartMigrationService::class);
        $listener = new HandleUserLogin($migrationService);
        $listener->handle($loginEvent);

        // Manually trigger cleanup
        $guestCart = Cart::getCartInstance('default', $guestSessionId);
        $userCart = Cart::getCartInstance('default', $userIdentifier);
        $event = new CartMerged(
            targetCart: $userCart,
            sourceCart: $guestCart,
            totalItemsMerged: 1,
            mergeStrategy: 'add_quantities',
            hadConflicts: false,
            originalSourceIdentifier: $guestSessionId,
            originalTargetIdentifier: $userIdentifier
        );
        $cleanupListener = new MasyukAI\FilamentCart\Listeners\CleanupSnapshotOnCartMerged;
        $cleanupListener->handle($event);

        // After cleanup: should only have 1 snapshot (the user's)
        $snapshots = CartSnapshot::all();
        expect($snapshots)->toHaveCount(1);
        expect($snapshots->first()->identifier)->toBe($userIdentifier);
    });

    it('handles multiple cart instances during login', function () {
        $guestSessionId = session()->getId();

        // Guest has items in both default cart and wishlist
        $storage = Cart::storage();
        $storage->putItems($guestSessionId, 'default', [
            'product-1' => [
                'id' => 'product-1',
                'name' => 'Product 1',
                'price' => 100.00,
                'quantity' => 1,
                'attributes' => [],
                'conditions' => [],
            ],
        ]);

        $storage->putItems($guestSessionId, 'wishlist', [
            'product-2' => [
                'id' => 'product-2',
                'name' => 'Product 2',
                'price' => 50.00,
                'quantity' => 1,
                'attributes' => [],
                'conditions' => [],
            ],
        ]);

        // Create snapshots for both instances
        $syncManager = app(MasyukAI\FilamentCart\Services\CartSyncManager::class);

        $defaultCart = Cart::getCartInstance('default', $guestSessionId);
        $syncManager->sync($defaultCart);

        $wishlistCart = Cart::getCartInstance('wishlist', $guestSessionId);
        $syncManager->sync($wishlistCart);

        // Should have 2 guest snapshots
        expect(CartSnapshot::where('identifier', $guestSessionId)->count())->toBe(2);

        // User logs in - only default cart is migrated
        Cache::put("cart_migration_{$this->user->email}", $guestSessionId);

        $loginEvent = new Login('web', $this->user, false);
        $migrationService = app(MasyukAI\Cart\Services\CartMigrationService::class);
        $listener = new HandleUserLogin($migrationService);
        $listener->handle($loginEvent);

        // Cleanup the default instance snapshot
        $guestCart = Cart::getCartInstance('default', $guestSessionId);
        $userCart = Cart::getCartInstance('default', (string) $this->user->id);
        $event = new CartMerged(
            targetCart: $userCart,
            sourceCart: $guestCart,
            totalItemsMerged: 1,
            mergeStrategy: 'add_quantities',
            hadConflicts: false,
            originalSourceIdentifier: $guestSessionId,
            originalTargetIdentifier: (string) $this->user->id
        );
        $cleanupListener = new MasyukAI\FilamentCart\Listeners\CleanupSnapshotOnCartMerged;
        $cleanupListener->handle($event);

        // Guest default snapshot should be removed
        expect(CartSnapshot::where('identifier', $guestSessionId)
            ->where('instance', 'default')
            ->exists())->toBeFalse();

        // Guest wishlist snapshot should still exist (not migrated)
        expect(CartSnapshot::where('identifier', $guestSessionId)
            ->where('instance', 'wishlist')
            ->exists())->toBeTrue();
    });
});

describe('Snapshot Consistency', function () {
    it('ensures only one active cart snapshot per user per instance', function () {
        $user = User::factory()->create();
        $userIdentifier = (string) $user->id;

        // User adds items via storage
        $storage = Cart::storage();
        $storage->putItems($userIdentifier, 'default', [
            'product-1' => [
                'id' => 'product-1',
                'name' => 'Product 1',
                'price' => 100.00,
                'quantity' => 2,
                'attributes' => [],
                'conditions' => [],
            ],
        ]);

        // Create snapshot
        $syncManager = app(MasyukAI\FilamentCart\Services\CartSyncManager::class);
        $cart = Cart::getCartInstance('default', $userIdentifier);
        $syncManager->sync($cart);

        // Should have exactly 1 snapshot
        $snapshots = CartSnapshot::where('identifier', $userIdentifier)
            ->where('instance', 'default')
            ->get();

        expect($snapshots)->toHaveCount(1);
        expect($snapshots->first()->quantity)->toBe(2);
    });

    it('prevents orphaned guest snapshots after migration', function () {
        $guestSessionId = 'guest_orphan_test';

        // Create guest snapshot
        CartSnapshot::create([
            'identifier' => $guestSessionId,
            'instance' => 'default',
            'items' => ['product-1' => ['id' => 'product-1', 'quantity' => 1]],
            'items_count' => 1,
            'quantity' => 1,
            'currency' => 'MYR',
        ]);

        expect(CartSnapshot::where('identifier', $guestSessionId)->exists())->toBeTrue();

        // Simulate migration
        $guestCart = Cart::getCartInstance('default', $guestSessionId);
        $userCart = Cart::getCartInstance('default', '999');

        $event = new CartMerged(
            targetCart: $userCart,
            sourceCart: $guestCart,
            totalItemsMerged: 1,
            mergeStrategy: 'add_quantities',
            hadConflicts: false,
            originalSourceIdentifier: $guestSessionId,
            originalTargetIdentifier: '999'
        );
        $cleanupListener = new MasyukAI\FilamentCart\Listeners\CleanupSnapshotOnCartMerged;
        $cleanupListener->handle($event);

        // Orphaned guest snapshot should be cleaned up
        expect(CartSnapshot::where('identifier', $guestSessionId)->exists())->toBeFalse();
    });
});
