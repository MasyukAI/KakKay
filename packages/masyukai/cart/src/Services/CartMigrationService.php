<?php

declare(strict_types=1);

namespace MasyukAI\Cart\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use MasyukAI\Cart\Collections\CartCollection;
use MasyukAI\Cart\Events\CartMerged;
use MasyukAI\Cart\Facades\Cart;

class CartMigrationService
{
    /**
     * Get the appropriate cart identifier for a user or guest session.
     * 
     * NOTE: This returns an IDENTIFIER (user ID or session ID) that identifies WHO owns the cart,
     * NOT an instance name (which identifies WHICH cart - like 'wishlist', 'shopping', etc.)
     * 
     * Examples:
     * - Guest identifier: session ID like "abc123def456" 
     * - User identifier: user ID like "42"
     * - Instance names: "default", "wishlist", "compare", etc.
     */
    public function getIdentifier(?int $userId = null, ?string $sessionId = null): string
    {
        if ($userId) {
            return (string) $userId;
        }

        if ($sessionId) {
            return $sessionId;
        }

        // Fallback to current session if no params provided
        return session()->getId();
    }

    /**
     * Migrate guest cart to user cart when user logs in.
     * 
     * IMPORTANT: This method works with IDENTIFIERS (who owns the cart) and INSTANCES (which cart type).
     * 
     * @param int $userId The user ID that will become the new cart IDENTIFIER 
     * @param string $instance The cart instance name (e.g., 'default', 'wishlist')
     * @param string|null $oldSessionId The guest session ID (cart IDENTIFIER) to migrate from
     * 
     * Example: migrateGuestCartToUser(42, 'default', 'abc123') 
     * - Migrates from identifier 'abc123' to identifier '42' 
     * - For the 'default' cart instance
     */
    public function migrateGuestCartToUser(int $userId, string $instance = 'default', ?string $oldSessionId = null): bool
    {
        $guestIdentifier = $this->getIdentifier(null, $oldSessionId ?? session()->getId());
        $userIdentifier = $this->getIdentifier($userId);

        // Get the storage directly to work with specific identifiers
        $storage = Cart::storage();

        // Get guest cart items for the specified instance
        $guestItems = $storage->getItems($guestIdentifier, $instance);

        // If guest cart is empty, nothing to migrate
        if (empty($guestItems)) {
            return false;
        }

        // Get existing user cart items for the same instance
        $userItems = $storage->getItems($userIdentifier, $instance);

        // Merge the cart data using arrays directly
        $mergedItems = $this->mergeItemsArray($guestItems, $userItems);

        // Save merged items to user cart
        $storage->putItems($userIdentifier, $instance, $mergedItems);

        // Also migrate conditions if any
        $guestConditions = $storage->getConditions($guestIdentifier, $instance);
        if (! empty($guestConditions)) {
            $userConditions = $storage->getConditions($userIdentifier, $instance);
            $mergedConditions = $this->mergeConditionsData($guestConditions, $userConditions);
            $storage->putConditions($userIdentifier, $instance, $mergedConditions);
        }

        // Clear the guest cart after successful migration
        $storage->forget($guestIdentifier, $instance);

        // Get Cart instances for the event
        $cartManager = Cart::getFacadeRoot();
        $targetCartInstance = $cartManager->getCartInstance($instance);

        // Convert merged items array to CartCollection for the event
        $mergedItemsCollection = new CartCollection($mergedItems);

        // Dispatch cart merged event
        event(new CartMerged(
            targetCart: $targetCartInstance,
            sourceCart: $targetCartInstance, // Limited by design
            mergedItems: $mergedItemsCollection,
            targetInstance: $instance,
            sourceInstance: $instance,
            totalItemsMerged: array_sum(array_column($mergedItems, 'quantity')),
            hadConflicts: count($mergedItems) > count($guestItems)
        ));

        return true;
    }

    /**
     * Migrate guest cart to user cart when user logs in (user object version).
     */
    public function migrateGuestCartForUser($user, string $instance = 'default', ?string $oldSessionId = null): object
    {
        $success = $this->migrateGuestCartToUser($user->id, $instance, $oldSessionId);

        return (object) [
            'success' => $success,
            'itemsMerged' => $success ? 1 : 0, // Simplified for now
            'conflicts' => collect(),
            'message' => $success ? 'Cart migration completed successfully' : 'No items to migrate',
        ];
    }

    /**
     * Merge cart data between different identifiers for a specific instance.
     */
    protected function mergeCartData(string $sourceIdentifier, string $targetIdentifier, string $instance): CartCollection
    {
        // Get source cart items using storage directly
        $sourceItems = Cart::storage()->getItems($sourceIdentifier, $instance);
        $targetItems = Cart::storage()->getItems($targetIdentifier, $instance);

        $mergedItems = new CartCollection;
        $mergeStrategy = config('cart.migration.merge_strategy', 'add_quantities');

        foreach ($sourceItems as $itemId => $sourceItemData) {
            $existingItem = $targetItems[$itemId] ?? null;

            if ($existingItem) {
                // Handle conflict based on strategy
                $newQuantity = $this->resolveQuantityConflict(
                    $existingItem['quantity'],
                    $sourceItemData['quantity'],
                    $mergeStrategy
                );

                // Update existing item quantity
                $targetItems[$itemId]['quantity'] = $newQuantity;
                $mergedItems->put($itemId, $targetItems[$itemId]);
            } else {
                // Add new item from source cart to target cart
                $targetItems[$itemId] = $sourceItemData;
                $mergedItems->put($itemId, $sourceItemData);
            }
        }

        // Save merged items and conditions to target identifier
        Cart::storage()->putItems($targetIdentifier, $instance, $targetItems);
        $targetConditions = Cart::storage()->getConditions($targetIdentifier, $instance);
        $sourceConditions = Cart::storage()->getConditions($sourceIdentifier, $instance);

        // Merge conditions too
        $mergedConditions = array_merge($targetConditions, $sourceConditions);
        Cart::storage()->putConditions($targetIdentifier, $instance, $mergedConditions);

        return $mergedItems;
    }

    /**
     * Resolve quantity conflicts based on merge strategy.
     */
    protected function resolveQuantityConflict(int $userQuantity, int $guestQuantity, string $strategy): int
    {
        return match ($strategy) {
            'add_quantities' => $userQuantity + $guestQuantity,
            'keep_highest_quantity' => max($userQuantity, $guestQuantity),
            'keep_user_cart' => $userQuantity,
            'replace_with_guest' => $guestQuantity,
            default => $userQuantity + $guestQuantity, // Fallback to add_quantities
        };
    }

    /**
     * Get items that had conflicts during merge.
     */
    protected function getConflictItems(string $sourceIdentifier, string $targetIdentifier, string $instance): \Illuminate\Support\Collection
    {
        $sourceItems = Cart::storage()->getItems($sourceIdentifier, $instance);
        $targetItems = Cart::storage()->getItems($targetIdentifier, $instance);

        $conflicts = collect();

        foreach ($sourceItems as $itemId => $sourceItemData) {
            $existingItem = $targetItems[$itemId] ?? null;
            if ($existingItem) {
                $conflicts->push([
                    'id' => $itemId,
                    'name' => $sourceItemData['name'] ?? 'Unknown',
                    'user_quantity' => $existingItem['quantity'] ?? 0,
                    'guest_quantity' => $sourceItemData['quantity'] ?? 0,
                ]);
            }
        }

        return $conflicts;
    }

    /**
     * Backup user cart to guest session (for logout scenarios).
     */
    public function backupUserCartToGuest(int $userId, string $instance = 'default', ?string $guestSessionId = null): bool
    {
        $userIdentifier = $this->getIdentifier($userId);
        $guestIdentifier = $this->getIdentifier(null, $guestSessionId ?? session()->getId());

        $userItems = Cart::storage()->getItems($userIdentifier, $instance);
        $userConditions = Cart::storage()->getConditions($userIdentifier, $instance);

        // If user cart is empty, nothing to backup
        if (empty($userItems)) {
            return false;
        }

        // Copy items and conditions to guest cart
        Cart::storage()->putBoth($guestIdentifier, $instance, $userItems, $userConditions);

        return true;
    }

    /**
     * Automatically switch to appropriate cart identifier based on authentication state.
     * Note: The Cart system automatically determines the identifier based on authentication state,
     * so this method serves as a placeholder for potential future functionality.
     * 
     * IMPORTANT: This does NOT change instance names (like 'default', 'wishlist').
     * Instance names should only be changed explicitly by the developer.
     * This only affects WHO owns the cart (user ID vs session ID), not WHICH cart type.
     */
    public function autoSwitchCartIdentifier(): void
    {
        // The Cart class automatically determines the correct identifier based on:
        // - Auth::id() for authenticated users
        // - Session::getId() for guest users
        // No manual switching is needed, this is handled automatically by getIdentifier()

        // Instance names ('default', 'wishlist', etc.) remain unchanged
        // Only the cart identifier (who owns the cart) is managed automatically
    }

    /**
     * Automatically switch cart instance based on authentication status.
     * 
     * IMPORTANT: This method is intentionally a no-op to preserve package fundamentals.
     * Instance names ('default', 'wishlist', etc.) should only be changed explicitly
     * by the developer, not automatically by authentication state.
     * 
     * The cart package automatically manages WHO owns the cart (identifiers) based on
     * authentication, but WHICH cart type (instance) should remain developer-controlled.
     */
    public function autoSwitchCartInstance(): void
    {
        // Intentionally empty - preserves package fundamental that instance names
        // like 'default' should not be automatically changed by authentication state.
        // Only cart identifiers (who owns the cart) are managed automatically.
    }

    /**
     * Get current identifier based on authentication state.
     */
    public function getCurrentIdentifier(): string
    {
        if (Auth::check()) {
            return $this->getIdentifier(Auth::id());
        }

        return $this->getIdentifier(null, session()->getId());
    }

    /**
     * Get guest identifier for current or specified session.
     */
    public function getGuestIdentifier(?string $sessionId = null): string
    {
        return $this->getIdentifier(null, $sessionId ?? session()->getId());
    }

    /**
     * Get user identifier for specified user.
     */
    public function getUserIdentifier(int $userId): string
    {
        return $this->getIdentifier($userId);
    }

    /**
     * Migrate all instances from guest to user (default, wishlist, saved-for-later, etc.).
     */
    public function migrateAllGuestInstances(int $userId, ?string $oldSessionId = null): array
    {
        $guestIdentifier = $this->getGuestIdentifier($oldSessionId);
        $userIdentifier = $this->getUserIdentifier($userId);

        // Get all instances for the guest identifier
        $instances = Cart::storage()->getInstances($guestIdentifier);
        $results = [];

        foreach ($instances as $instance) {
            $success = $this->migrateGuestCartToUser($userId, $instance, $oldSessionId);
            $results[$instance] = $success;
        }

        return $results;
    }

    /**
     * Get a formatted instance name for a user or guest session.
     */
    public function getInstanceName(?int $userId = null, ?string $sessionId = null): string
    {
        if ($userId) {
            return "user_{$userId}";
        }

        if ($sessionId) {
            return "guest_{$sessionId}";
        }

        return 'guest_'.session()->getId();
    }

    /**
     * Merge cart conditions from guest to user cart.
     */
    protected function mergeConditionsData(array $guestConditions, array $userConditions): array
    {
        // For now, keep all conditions from both carts
        // In the future, we could add strategies for condition conflicts
        $mergedConditions = $userConditions;

        foreach ($guestConditions as $conditionName => $conditionData) {
            if (! isset($mergedConditions[$conditionName])) {
                $mergedConditions[$conditionName] = $conditionData;
            }
            // If condition exists in both, keep the user's version (could be configurable)
        }

        return $mergedConditions;
    }

    /**
     * Merge items arrays from guest cart and user cart.
     */
    protected function mergeItemsArray(array $guestItems, array $userItems): array
    {
        $mergedItems = $userItems; // Start with user items
        $mergeStrategy = config('cart.migration.merge_strategy', 'add_quantities');

        foreach ($guestItems as $itemId => $guestItemData) {
            $existingItem = $userItems[$itemId] ?? null;

            if ($existingItem) {
                // Handle conflict based on strategy
                $newQuantity = $this->resolveQuantityConflict(
                    $existingItem['quantity'] ?? 0,
                    $guestItemData['quantity'] ?? 0,
                    $mergeStrategy
                );

                // Update the quantity in merged items
                $mergedItems[$itemId]['quantity'] = $newQuantity;
            } else {
                // No conflict, add the guest item
                $mergedItems[$itemId] = $guestItemData;
            }
        }

        return $mergedItems;
    }
}
