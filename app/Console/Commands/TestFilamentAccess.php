<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\StockTransaction;
use App\Models\User;
use Illuminate\Console\Command;

final class TestFilamentAccess extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:filament-access';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Filament access to stock transactions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 Testing Filament Admin Panel Data Access...');
        $this->newLine();

        // Test product data
        $productCount = Product::count();
        $this->info("📦 Total Products: {$productCount}");

        if ($productCount > 0) {
            $product = Product::first();
            $this->info("   Sample Product: {$product->name} (Stock: {$product->stock})");
        }

        // Test stock transactions
        $transactionCount = StockTransaction::count();
        $this->info("📊 Total Stock Transactions: {$transactionCount}");

        if ($transactionCount > 0) {
            $this->info("\n🔍 Recent Stock Transactions:");
            $transactions = StockTransaction::with(['product', 'user', 'orderItem'])
                ->latest()
                ->take(5)
                ->get();

            foreach ($transactions as $transaction) {
                $this->line(sprintf(
                    '   • %s %s %d units (%s) - %s - %s',
                    $transaction->product->name,
                    $transaction->type === 'in' ? '+' : '-',
                    $transaction->quantity,
                    $transaction->reason,
                    $transaction->user?->name ?? 'System',
                    $transaction->transaction_date->format('M j, Y H:i')
                ));
            }
        }

        // Test user data (for Filament admin)
        $userCount = User::count();
        $this->info("\n👥 Total Users: {$userCount}");

        if ($userCount > 0) {
            $user = User::first();
            $this->info("   Sample User: {$user->name} ({$user->email})");
        }

        $this->newLine();
        $this->comment('💡 Tips for Filament Admin:');
        $this->line('   1. Visit /admin to access the Filament panel');
        $this->line('   2. Navigate to Stock Transactions to view inventory movements');
        $this->line('   3. Create new stock transactions manually if needed');
        $this->line('   4. Monitor product stock levels and transaction history');

        $this->newLine();
        $this->info('✅ Filament data access test completed!');

        return Command::SUCCESS;
    }
}
