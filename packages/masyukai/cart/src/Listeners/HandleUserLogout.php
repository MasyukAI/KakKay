<?php

declare(strict_types=1);

namespace MasyukAI\Cart\Listeners;

use Illuminate\Auth\Events\Logout;
use Illuminate\Contracts\Queue\ShouldQueue;
use MasyukAI\Cart\Services\CartMigrationService;

class HandleUserLogout implements ShouldQueue
{
    public function __construct(
        private CartMigrationService $migrationService
    ) {}

    /**
     * Handle the user logout event
     */
    public function handle(Logout $event): void
    {
        // Optionally backup user cart to guest session
        if (config('cart.migration.backup_on_logout', false)) {
            $this->migrationService->backupUserCartToGuest(
                $event->user->getAuthIdentifier(), 
                'default', 
                session()->getId()
            );
        }

        // Switch to guest cart instance
        $this->migrationService->autoSwitchCartInstance();
    }
}
