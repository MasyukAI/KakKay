<?php

declare(strict_types=1);

namespace MasyukAI\Cart\Traits;

use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Container\Container;
use Illuminate\Session\SessionManager;

trait ManagesIdentifier
{
    /**
     * Get storage identifier (auth()->id() for authenticated users, session()->id() for guests)
     */
    public function getIdentifier(): string
    {
        $app = $this->getApplication();

        // Try authenticated user first
        if ($authenticatedId = $this->getAuthenticatedUserId($app)) {
            return $authenticatedId;
        }

        // Fall back to session ID for guests
        if ($sessionId = $this->getSessionId($app)) {
            return $sessionId;
        }

        // If neither is available, throw exception
        throw new \RuntimeException(
            'Cart identifier cannot be determined: neither auth nor session services are available'
        );
    }

    /**
     * Get the Laravel application instance
     */
    protected function getApplication(): Container
    {
        return app();
    }

    /**
     * Get authenticated user ID if available
     */
    protected function getAuthenticatedUserId(Container $app): ?string
    {
        try {
            if (! $app->bound('auth')) {
                return null;
            }

            $auth = $app->make(AuthFactory::class);
            $guard = $auth->guard();

            return $guard->check() ? (string) $guard->id() : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Get session ID if available
     */
    protected function getSessionId(Container $app): ?string
    {
        try {
            if (! $app->bound('session')) {
                return null;
            }

            $session = $app->make(SessionManager::class);

            return $session->getId();
        } catch (\Throwable $e) {
            return null;
        }
    }
}
