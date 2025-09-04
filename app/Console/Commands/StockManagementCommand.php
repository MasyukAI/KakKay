<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\User;
use App\Services\StockService;
use Illuminate\Console\Command;

class StockManagementCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'stock:demo';

    /**
     * The console command description.
     */
    protected $description = 'Demonstrate stock management functionality';

    /**
     * Execute the console command.
     */
    public function handle(StockService $stockService): int
    {
        $this->info('🏪 Stock Management Demo');
        $this->newLine();

        // Get or create a category first
        $category = \App\Models\Category::firstOrCreate(
            ['name' => 'Demo Category']
        );

        // Get or create a test product
        $product = Product::firstOrCreate(
            ['name' => 'Demo Product'],
            [
                'slug' => 'demo-product',
                'description' => 'A demo product for stock management',
                'price' => 2500, // RM 25.00
                'category_id' => $category->id,
                'is_active' => true,
            ]
        );

        // Get or create an admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Stock Admin',
                'password' => bcrypt('password'),
            ]
        );

        $this->info("📦 Product: {$product->name}");
        $this->info("👤 Admin: {$admin->name}");
        $this->newLine();

        // Show current stock
        $currentStock = $stockService->getCurrentStock($product);
        $this->info("📊 Current Stock: {$currentStock} units");
        $this->newLine();

        // Demo: Add stock (restock)
        $this->info('➕ Adding 100 units (restock from supplier)...');
        $stockService->addStock(
            product: $product,
            quantity: 100,
            reason: 'restock',
            note: 'New shipment from supplier XYZ',
            userId: $admin->id
        );

        $currentStock = $stockService->getCurrentStock($product);
        $this->info("📊 Stock after restock: {$currentStock} units");
        $this->newLine();

        // Demo: Remove stock (damaged items)
        $this->info('➖ Removing 5 units (damaged items)...');
        $stockService->removeStock(
            product: $product,
            quantity: 5,
            reason: 'damaged',
            note: 'Items damaged during inspection',
            userId: $admin->id
        );

        $currentStock = $stockService->getCurrentStock($product);
        $this->info("📊 Stock after damage removal: {$currentStock} units");
        $this->newLine();

        // Demo: Stock adjustment (physical count correction)
        $this->info('🔧 Physical count shows 90 units (adjustment needed)...');
        $actualStock = 90;
        $stockService->adjustStock(
            product: $product,
            currentStock: $currentStock,
            actualStock: $actualStock,
            note: 'Physical inventory count correction',
            userId: $admin->id
        );

        $currentStock = $stockService->getCurrentStock($product);
        $this->info("📊 Stock after adjustment: {$currentStock} units");
        $this->newLine();

        // Show recent transactions
        $this->info('📋 Recent Stock Transactions:');
        $history = $stockService->getStockHistory($product, 5);

        $this->table(
            ['Date', 'Type', 'Quantity', 'Reason', 'Note', 'Admin'],
            $history->map(function ($transaction) {
                return [
                    $transaction->transaction_date->format('Y-m-d H:i'),
                    strtoupper($transaction->type),
                    $transaction->quantity,
                    $transaction->reason,
                    $transaction->note,
                    $transaction->user->name ?? 'System',
                ];
            })->toArray()
        );

        $this->newLine();
        $this->info('✅ Stock management demo completed!');

        return Command::SUCCESS;
    }
}
