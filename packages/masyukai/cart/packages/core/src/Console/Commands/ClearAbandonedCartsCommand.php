<?php

declare(strict_types=1);

namespace MasyukAI\Cart\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use MasyukAI\Cart\Services\CartMetricsService;

class ClearAbandonedCartsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'cart:clear-abandoned 
                          {--days=7 : Number of days after which cart is considered abandoned}
                          {--dry-run : Show what would be deleted without actually deleting}
                          {--batch-size=1000 : Number of records to process in each batch}';

    /**
     * The console command description.
     */
    protected $description = 'Clear abandoned shopping carts older than specified days';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');
        $dryRun = $this->option('dry-run');
        $batchSize = max(1, (int) $this->option('batch-size'));
        $table = config('cart.database.table', 'carts');

        $cutoffDate = now()->subDays($days);

        $this->info("Clearing carts abandoned before: {$cutoffDate->format('Y-m-d H:i:s')}");

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No data will be deleted');
        }

        $query = DB::table($table)
            ->where('updated_at', '<', $cutoffDate);

        $totalCount = $query->count();

        if ($totalCount === 0) {
            $this->info('No abandoned carts found.');

            return self::SUCCESS;
        }

        $this->info("Found {$totalCount} abandoned carts to clear.");

        if (! $dryRun) {
            $confirmed = $this->confirm('Are you sure you want to delete these carts?');
            if (! $confirmed) {
                $this->info('Operation cancelled.');

                return self::SUCCESS;
            }
        }

        $progressBar = $this->output->createProgressBar($totalCount);
        $progressBar->start();

        $deletedCount = 0;

        // Process in batches to avoid memory issues
        $query->chunk($batchSize, function ($carts) use (&$deletedCount, $progressBar, $dryRun, $table) {
            $ids = $carts->pluck('id')->toArray();

            if (! $dryRun) {
                $deleted = DB::table($table)->whereIn('id', $ids)->delete();
                $deletedCount += $deleted;

                // Record abandonment metrics
                if (app()->bound(CartMetricsService::class)) {
                    foreach ($carts as $cart) {
                        $cartData = (array) $cart;
                        app(CartMetricsService::class)->recordAbandonment(
                            $cartData['identifier'],
                            $cartData['instance'],
                            ['cleared_by_command' => true]
                        );
                    }
                }
            } else {
                $deletedCount += count($ids);
            }

            $progressBar->advance(count($ids));
        });

        $progressBar->finish();
        $this->newLine();

        if ($dryRun) {
            $this->info("Would delete {$deletedCount} abandoned carts.");
        } else {
            $this->info("Successfully deleted {$deletedCount} abandoned carts.");
        }

        return self::SUCCESS;
    }
}
