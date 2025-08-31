<?php

declare(strict_types=1);

namespace MasyukAI\Cart\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Cache;
use MasyukAI\Cart\Services\CartMigrationService;

class HandleUserLogin implements ShouldQueue
{
    public function __construct(
        private CartMigrationService $migrationService
    ) {}

    /**
     * Handle the user login event
     */
    public function handle(Login $event): void
    {
        // Try to retrieve the old session ID from cache
        $userIdentifier = $this->getUserIdentifier($event->user);
        $oldSessionId = null;
        
        if ($userIdentifier) {
            $oldSessionId = Cache::pull("cart_migration_{$userIdentifier}");
        }
        
        // Migrate guest cart to user cart using old session ID
        $result = $this->migrationService->migrateGuestCartForUser($event->user, $oldSessionId);
        
        if ($result->success && $result->itemsMerged > 0) {
            // Store migration result in session for potential display to user
            session()->flash('cart_migration', [
                'items_merged' => $result->itemsMerged,
                'has_conflicts' => false, // Simplified
                'conflicts' => $result->conflicts,
                'message' => $result->message ?? 'Cart migration completed'
            ]);
        }

        // Switch to user cart instance
        $this->migrationService->autoSwitchCartInstance();
    }

    /**
     * Extract user identifier from user object.
     */
    private function getUserIdentifier($user): ?string
    {
        // Try common user identifier fields
        return $user->email 
            ?? $user->username 
            ?? $user->phone 
            ?? null;
    }
}
