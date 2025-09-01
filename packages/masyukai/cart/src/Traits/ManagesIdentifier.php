<?php

declare(strict_types=1);

namespace MasyukAI\Cart\Traits;

trait ManagesIdentifier
{
    /**
     * Get storage identifier (auth()->id() for authenticated users, session()->id() for guests)
     */
    private function getIdentifier(): string
    {
        // Identifier is ALWAYS determined by authentication state, never by instance name

        // Try to get identifier from auth first
        try {
            if (app()->bound('auth') && app('auth')->check()) {
                return (string) app('auth')->id();
            }
        } catch (\Exception $e) {
            // Auth not available, continue to session
        }

        // Fall back to session ID for guests
        try {
            if (app()->bound('session')) {
                return app('session')->getId();
            }
        } catch (\Exception $e) {
            // Session not available, use test default
        }

        // For testing environments, provide a consistent test identifier
        return 'test_session_id';
    }
}
