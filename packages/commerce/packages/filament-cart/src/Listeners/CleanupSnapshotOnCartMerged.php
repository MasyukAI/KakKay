<?php

declare(strict_types=1);

namespace AIArmada\FilamentCart\Listeners;

use AIArmada\Cart\Events\CartMerged;
use AIArmada\FilamentCart\Models\Cart as CartSnapshot;
use AIArmada\FilamentCart\Models\CartCondition;
use AIArmada\FilamentCart\Models\CartItem;

/**
 * Clean up old cart snapshots when carts are merged.
 *
 * When a guest cart is merged/swapped to a user cart, the source cart's
 * snapshot should be removed to prevent duplicate cart snapshots in the
 * database. The CartMerged event provides the original source identifier
 * (before the swap) so we can clean up the correct snapshot.
 *
 * This also cleans up the normalized data (items and conditions) associated
 * with the cart snapshot.
 */
final class CleanupSnapshotOnCartMerged
{
    public function handle(CartMerged $event): void
    {
        // Use the original source identifier if provided (this is the guest session)
        // After a swap, both carts have the same identifier, so we need the original
        $sourceIdentifier = $event->originalSourceIdentifier ?? $event->sourceCart->getIdentifier();
        $instance = $event->sourceCart->instance();

        // Find the cart snapshot to delete
        $cartSnapshot = CartSnapshot::query()
            ->where('identifier', $sourceIdentifier)
            ->where('instance', $instance)
            ->first();

        if (! $cartSnapshot) {
            return;
        }

        // Delete normalized items (cart_snapshot_items)
        CartItem::where('cart_id', $cartSnapshot->id)->delete();

        // Delete normalized conditions (cart_snapshot_conditions)
        CartCondition::where('cart_id', $cartSnapshot->id)->delete();

        // Finally, delete the cart snapshot itself
        $cartSnapshot->delete();

        // Note: The target cart snapshot will be updated by the normal
        // cart sync listeners (SyncCompleteCart, etc.)
    }
}
