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
     * Get the appropriate cart instance name for a user or guest session.
     */
    public function getInstanceName(?int $userId = null, ?string $sessionId = null): string
    {
        if ($userId) {
            $prefix = config('cart.migration.user_instance_prefix', 'user');
            return "{$prefix}_{$userId}";
        }

        if ($sessionId) {
            $prefix = config('cart.migration.guest_instance_prefix', 'guest');
            return "{$prefix}_{$sessionId}";
        }

        return config('cart.default_instance', 'default');
    }

    /**
     * Migrate guest cart to user cart when user logs in.
     */
    public function migrateGuestCartToUser(string $guestInstance, int $userId): bool
    {
        $userInstance = $this->getInstanceName($userId);
        
        // Get guest cart content
        $guestCart = Cart::setInstance($guestInstance);
        $guestItems = $guestCart->content();

        // If guest cart is empty, nothing to migrate
        if ($guestItems->isEmpty()) {
            return false;
        }

        // Switch to user cart and merge
        $mergedItems = $this->mergeCartInstances($guestInstance, $userInstance);

        // Clear the guest cart after successful migration
        Cart::setInstance($guestInstance)->clear();

        // Dispatch cart merged event
        $conflicts = $this->getConflictItems($guestInstance, $userInstance);
        $cartManager = Cart::getFacadeRoot();
        event(new CartMerged(
            targetCart: $cartManager->getCartInstance($userInstance),
            sourceCart: $cartManager->getCartInstance($guestInstance),
            mergedItems: $mergedItems,
            targetInstance: $userInstance,
            sourceInstance: $guestInstance,
            totalItemsMerged: $mergedItems->sum('quantity'),
            hadConflicts: $conflicts->isNotEmpty()
        ));

        return true;
    }

    /**
     * Migrate guest cart to user cart when user logs in (user object version).
     */
    public function migrateGuestCartForUser($user, ?string $oldSessionId = null): object
    {
        // Use provided old session ID or fall back to current session ID
        $sessionId = $oldSessionId ?? session()->getId();
        $guestInstance = $this->getInstanceName(null, $sessionId);
        $success = $this->migrateGuestCartToUser($guestInstance, $user->id);
        
        return (object) [
            'success' => $success,
            'itemsMerged' => $success ? 1 : 0, // Simplified for now
            'conflicts' => collect(),
            'message' => $success ? 'Cart migration completed successfully' : 'No items to migrate'
        ];
    }

    /**
     * Merge cart instances with conflict resolution.
     */
    protected function mergeCartInstances(string $sourceInstance, string $targetInstance): CartCollection
    {
        // Get source items first
        Cart::setInstance($sourceInstance);
        $sourceItems = Cart::content();
        
        // Get target items 
        Cart::setInstance($targetInstance);
        $targetItems = Cart::content();
        
        $mergedItems = new CartCollection();
        $mergeStrategy = config('cart.migration.merge_strategy', 'add_quantities');

        foreach ($sourceItems as $sourceItem) {
            $existingItem = $targetItems->where('id', $sourceItem->id)->first();
            
            if ($existingItem) {
                // Handle conflict based on strategy
                $newQuantity = $this->resolveQuantityConflict(
                    $existingItem->quantity,
                    $sourceItem->quantity,
                    $mergeStrategy
                );
                
                // Ensure we're on target instance and update existing item
                Cart::setInstance($targetInstance);
                Cart::remove($existingItem->id);
                $newItem = Cart::add(
                    $sourceItem->id,
                    $sourceItem->name,
                    $sourceItem->price,
                    $newQuantity,
                    $existingItem->attributes->toArray()
                );
                $mergedItems->put($newItem->id, $newItem);
            } else {
                // Add new item from source cart to target cart
                Cart::setInstance($targetInstance);
                $newItem = Cart::add(
                    $sourceItem->id,
                    $sourceItem->name,
                    $sourceItem->price,
                    $sourceItem->quantity,
                    $sourceItem->attributes->toArray()
                );
                
                $mergedItems->put($newItem->id, $newItem);
            }
        }

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
    protected function getConflictItems(string $sourceInstance, string $targetInstance): \Illuminate\Support\Collection
    {
        $sourceItems = Cart::setInstance($sourceInstance)->content();
        $targetItems = Cart::setInstance($targetInstance)->content();
        
        $conflicts = collect();
        
        foreach ($sourceItems as $sourceItem) {
            $existingItem = $targetItems->where('id', $sourceItem->id)->first();
            if ($existingItem) {
                $conflicts->push([
                    'id' => $sourceItem->id,
                    'name' => $sourceItem->name,
                    'user_quantity' => $existingItem->quantity,
                    'guest_quantity' => $sourceItem->quantity,
                ]);
            }
        }

        return $conflicts;
    }

    /**
     * Backup user cart to guest session (for logout scenarios).
     */
    public function backupUserCartToGuest(int $userId, string $guestSessionId): bool
    {
        $userInstance = $this->getInstanceName($userId);
        $guestInstance = $this->getInstanceName(null, $guestSessionId);
        
        $userCart = Cart::setInstance($userInstance);
        $userItems = $userCart->content();

        // If user cart is empty, nothing to backup
        if ($userItems->isEmpty()) {
            return false;
        }

        // Copy items to guest cart
        $guestCart = Cart::setInstance($guestInstance);
        
        foreach ($userItems as $item) {
            $guestCart->add(
                $item->id,
                $item->name,
                $item->price,
                $item->quantity,
                $item->attributes->toArray()
            );
        }

        return true;
    }

    /**
     * Automatically switch to appropriate cart instance based on authentication state.
     */
    public function autoSwitchCartInstance(): void
    {
        if (Auth::check()) {
            Cart::setInstance($this->getInstanceName(Auth::id()));
        } else {
            Cart::setInstance($this->getInstanceName(null, session()->getId()));
        }
    }
}
