<?php

declare(strict_types=1);

namespace App\Services\Traits;

use AIArmada\Cart\Cart;
use AIArmada\Cart\CartManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

trait ManagesCartIdentifiers
{
    /**
     * Find cart by reference (cart ID) with multiple fallback strategies
     *
     * Strategy:
     * 1. Direct DB lookup by cart ID (fastest - primary key)
     * 2. Scan cart metadata for matching purchase_id (fallback)
     * 3. Query CHIP API for reference (last resort)
     */
    protected function findCartByReference(string $reference, ?string $purchaseId = null, ?callable $apiLookup = null): ?Cart
    {
        // Strategy 1: Direct cart ID lookup (blazing fast primary key lookup)
        $cartData = DB::table('carts')->where('id', $reference)->first();

        if ($cartData) {
            Log::debug('Cart found by direct reference lookup', [
                'cart_id' => $reference,
                'instance' => $cartData->instance,
                'identifier' => $cartData->identifier,
            ]);

            return $this->reconstructCartFromData($cartData);
        }

        // Strategy 2: Metadata scan for purchase_id (if provided)
        if ($purchaseId) {
            $cartData = $this->findCartByPurchaseId($purchaseId);

            if ($cartData) {
                return $this->reconstructCartFromData($cartData);
            }
        }

        // Strategy 3: API lookup (if callback provided)
        if ($apiLookup) {
            try {
                $apiReference = $apiLookup($purchaseId ?? $reference);

                if ($apiReference) {
                    $cartData = DB::table('carts')->where('id', $apiReference)->first();

                    if ($cartData) {
                        Log::info('Cart found via API lookup', [
                            'cart_id' => $apiReference,
                            'original_reference' => $reference,
                        ]);

                        return $this->reconstructCartFromData($cartData);
                    }
                }
            } catch (Throwable $e) {
                Log::warning('API lookup failed', [
                    'reference' => $reference,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::warning('Cart not found after all lookup strategies', [
            'reference' => $reference,
            'purchase_id' => $purchaseId,
        ]);

        return null;
    }

    /**
     * Find cart by scanning metadata for matching purchase_id
     */
    protected function findCartByPurchaseId(string $purchaseId): ?object
    {
        Log::debug('Scanning cart metadata for purchase_id', ['purchase_id' => $purchaseId]);

        $cartCandidates = DB::table('carts')
            ->whereNotNull('metadata')
            ->get();

        foreach ($cartCandidates as $candidate) {
            $metadata = json_decode($candidate->metadata ?? '', true) ?: [];
            $intent = $metadata['payment_intent'] ?? [];

            if (($intent['purchase_id'] ?? null) === $purchaseId) {
                Log::debug('Cart located via metadata scan', [
                    'cart_id' => $candidate->id,
                    'purchase_id' => $purchaseId,
                ]);

                return $candidate;
            }
        }

        Log::debug('Cart metadata scan found no match', [
            'purchase_id' => $purchaseId,
            'scanned_carts' => $cartCandidates->count(),
        ]);

        return null;
    }

    /**
     * Reconstruct Cart instance from database row
     */
    protected function reconstructCartFromData(object $cartData): Cart
    {
        $cartManager = app(CartManager::class);

        return $cartManager->getCartInstance(
            $cartData->instance,
            $cartData->identifier
        );
    }

    /**
     * Extract cart reference from various data sources
     */
    /** @phpstan-ignore-next-line */
    protected function extractCartReference(array $data): ?string
    {
        return $data['reference']
            ?? $data['cart_reference']
            ?? $data['cart_id']
            ?? $data['customer_data']['reference'] ?? null;
    }
}
