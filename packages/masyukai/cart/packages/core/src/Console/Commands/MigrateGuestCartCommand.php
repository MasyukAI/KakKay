<?php

declare(strict_types=1);

namespace MasyukAI\Cart\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use MasyukAI\Cart\Storage\DatabaseStorage;

class MigrateGuestCartCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'cart:migrate-guest-to-user 
                          {guest-identifier : Guest cart identifier}
                          {user-identifier : User cart identifier}
                          {--instance=default : Cart instance name}
                          {--merge : Merge with existing user cart instead of replacing}
                          {--dry-run : Show what would be migrated without actually doing it}';

    /**
     * The console command description.
     */
    protected $description = 'Migrate guest cart to authenticated user cart';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $guestIdentifier = $this->argument('guest-identifier');
        $userIdentifier = $this->argument('user-identifier');
        $instance = $this->option('instance');
        $merge = $this->option('merge');
        $dryRun = $this->option('dry-run');

        $this->info("Migrating cart from guest '{$guestIdentifier}' to user '{$userIdentifier}' (instance: {$instance})");

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No data will be modified');
        }

        // Use direct database access for cart migration
        $storage = app(DatabaseStorage::class);

        // Get guest cart data
        $guestItems = $storage->getItems($guestIdentifier, $instance);
        $guestConditions = $storage->getConditions($guestIdentifier, $instance);

        if (empty($guestItems) && empty($guestConditions)) {
            $this->error('Guest cart is empty or does not exist.');

            return self::FAILURE;
        }

        $this->info('Guest cart contains:');
        $this->info('- '.count($guestItems).' items');
        $this->info('- '.count($guestConditions).' conditions');

        // Check user cart
        $userItems = $storage->getItems($userIdentifier, $instance);
        $userConditions = $storage->getConditions($userIdentifier, $instance);

        if (! empty($userItems) && ! $merge) {
            $this->warn('User cart already exists with '.count($userItems).' items.');
            $confirmed = $this->confirm('This will replace the existing user cart. Continue?');
            if (! $confirmed) {
                $this->info('Operation cancelled.');

                return self::SUCCESS;
            }
        }

        if (! $dryRun) {
            DB::transaction(function () use ($storage, $guestIdentifier, $userIdentifier, $instance, $guestItems, $guestConditions, $userItems, $userConditions, $merge) {
                $finalItems = $guestItems;
                $finalConditions = $guestConditions;

                if ($merge && ! empty($userItems)) {
                    // Merge items by ID
                    foreach ($userItems as $userItem) {
                        $existingIndex = null;
                        foreach ($finalItems as $index => $guestItem) {
                            if ($guestItem['id'] === $userItem['id']) {
                                $existingIndex = $index;
                                break;
                            }
                        }

                        if ($existingIndex !== null) {
                            // Merge quantities
                            $finalItems[$existingIndex]['quantity'] += $userItem['quantity'];
                        } else {
                            // Add new item
                            $finalItems[] = $userItem;
                        }
                    }

                    // Merge conditions (guest takes precedence)
                    foreach ($userConditions as $userCondition) {
                        $exists = false;
                        foreach ($finalConditions as $guestCondition) {
                            if ($guestCondition['name'] === $userCondition['name']) {
                                $exists = true;
                                break;
                            }
                        }
                        if (! $exists) {
                            $finalConditions[] = $userCondition;
                        }
                    }
                }

                // Save to user cart
                if (! empty($finalItems)) {
                    $storage->putItems($userIdentifier, $instance, $finalItems);
                }
                if (! empty($finalConditions)) {
                    $storage->putConditions($userIdentifier, $instance, $finalConditions);
                }

                // Clear guest cart
                $storage->forget($guestIdentifier, $instance);
            });

            // Get final counts
            $finalItems = $storage->getItems($userIdentifier, $instance);
            $finalConditions = $storage->getConditions($userIdentifier, $instance);

            $this->info('Cart migration completed successfully!');
            $this->info('User cart now contains:');
            $this->info('- '.count($finalItems).' items');
            $this->info('- '.count($finalConditions).' conditions');
        } else {
            $this->info('DRY RUN: Migration would be performed successfully.');
        }

        return self::SUCCESS;
    }
}
