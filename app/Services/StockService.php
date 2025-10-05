<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\OrderItem;
use App\Models\Product;
use App\Models\StockTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockService
{
    /**
     * Add stock to a product (admin restocking)
     */
    public function addStock(
        Product $product,
        int $quantity,
        string $reason = 'restock',
        ?string $note = null,
        ?string $userId = null
    ): StockTransaction {
        return $this->createTransaction(
            product: $product,
            quantity: $quantity,
            type: 'in',
            reason: $reason,
            note: $note,
            userId: $userId
        );
    }

    /**
     * Remove stock from a product (admin adjustment)
     */
    public function removeStock(
        Product $product,
        int $quantity,
        string $reason = 'adjustment',
        ?string $note = null,
        ?string $userId = null
    ): StockTransaction {
        return $this->createTransaction(
            product: $product,
            quantity: $quantity,
            type: 'out',
            reason: $reason,
            note: $note,
            userId: $userId
        );
    }

    /**
     * Record stock movement for a sale
     */
    public function recordSale(
        Product $product,
        OrderItem $orderItem,
        ?string $note = null
    ): StockTransaction {
        return $this->createTransaction(
            product: $product,
            quantity: $orderItem->quantity,
            type: 'out',
            reason: 'sale',
            note: $note ?? "Sale from Order #{$orderItem->order_id}",
            orderItemId: $orderItem->id
        );
    }

    /**
     * Record stock return (customer returns item)
     */
    public function recordReturn(
        Product $product,
        int $quantity,
        ?OrderItem $orderItem = null,
        ?string $note = null,
        ?string $userId = null
    ): StockTransaction {
        return $this->createTransaction(
            product: $product,
            quantity: $quantity,
            type: 'in',
            reason: 'return',
            note: $note,
            userId: $userId,
            orderItemId: $orderItem?->id
        );
    }

    /**
     * Adjust stock (manual correction)
     */
    public function adjustStock(
        Product $product,
        int $currentStock,
        int $actualStock,
        ?string $note = null,
        ?string $userId = null
    ): ?StockTransaction {
        $difference = $actualStock - $currentStock;

        if ($difference === 0) {
            return null; // No adjustment needed
        }

        $type = $difference > 0 ? 'in' : 'out';
        $quantity = abs($difference);

        return $this->createTransaction(
            product: $product,
            quantity: $quantity,
            type: $type,
            reason: 'adjustment',
            note: $note ?? "Stock count correction: {$currentStock} → {$actualStock}",
            userId: $userId
        );
    }

    /**
     * Get current stock level for a product
     */
    public function getCurrentStock(Product $product): int
    {
        $inbound = StockTransaction::forProduct($product->id)
            ->inbound()
            ->sum('quantity');

        $outbound = StockTransaction::forProduct($product->id)
            ->outbound()
            ->sum('quantity');

        return $inbound - $outbound;
    }

    /**
     * Get stock history for a product
     */
    public function getStockHistory(Product $product, int $limit = 50)
    {
        return StockTransaction::forProduct($product->id)
            ->with(['user', 'orderItem'])
            ->latest('transaction_date')
            ->limit($limit)
            ->get();
    }

    /**
     * Get low stock products
     */
    public function getLowStockProducts(int $threshold = 10)
    {
        return Product::select('products.*')
            ->selectSub(
                StockTransaction::selectRaw('
                    COALESCE(SUM(CASE WHEN type = "in" THEN quantity ELSE 0 END), 0) - 
                    COALESCE(SUM(CASE WHEN type = "out" THEN quantity ELSE 0 END), 0)
                ')
                    ->whereColumn('product_id', 'products.id'),
                'current_stock'
            )
            ->havingRaw('current_stock < ?', [$threshold])
            ->get();
    }

    /**
     * Create a stock transaction record
     */
    private function createTransaction(
        Product $product,
        int $quantity,
        string $type,
        string $reason,
        ?string $note = null,
        ?string $userId = null,
        ?string $orderItemId = null
    ): StockTransaction {
        return DB::transaction(function () use (
            $product,
            $quantity,
            $type,
            $reason,
            $note,
            $userId,
            $orderItemId
        ) {
            return StockTransaction::create([
                'product_id' => $product->id,
                'order_item_id' => $orderItemId,
                'user_id' => $userId ?? Auth::id(),
                'quantity' => $quantity,
                'type' => $type,
                'reason' => $reason,
                'note' => $note,
                'transaction_date' => now(),
            ]);
        });
    }
}
