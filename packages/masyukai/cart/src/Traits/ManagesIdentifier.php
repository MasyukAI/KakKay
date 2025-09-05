<?php

declare(strict_types=1);

namespace MasyukAI\Cart\Traits;

use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Foundation\Application;
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

        // Handle testing environment fallback
        if ($this->isTestingEnvironment($app)) {
            return $this->getTestIdentifier();
        }

        // Production fallback - throw exception
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
            // Log auth service errors in production for debugging
            if (! $this->isTestingEnvironment($app)) {
                $this->logServiceError('auth', $e);
            }

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
            // Log session service errors in production for debugging
            if (! $this->isTestingEnvironment($app)) {
                $this->logServiceError('session', $e);
            }

            return null;
        }
    }

    /**
     * Check if running in testing environment
     */
    protected function isTestingEnvironment(Container $app): bool
    {
        // First, check if app environment is testing (with fallback for mock objects)
        try {
            if ($app instanceof Application) {
                return $app->environment('testing');
            }
        } catch (\Throwable $e) {
            // Fall back to other checks if environment() is not mocked
        }

        // Fallback checks for PHPUnit/testing
        return defined('PHPUNIT_COMPOSER_INSTALL') ||
               defined('__PHPUNIT_PHAR__') ||
               (isset($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === 'testing');
    }

    /**
     * Get test environment identifier
     */
    protected function getTestIdentifier(): string
    {
        try {
            return config('cart.test_identifier', 'test_session_id');
        } catch (\Throwable $e) {
            return 'test_session_id';
        }
    }

    /**
     * Log service errors for debugging
     */
    protected function logServiceError(string $service, \Throwable $e): void
    {
        try {
            if (function_exists('logger')) {
                logger()->warning("Cart {$service} service error: ".$e->getMessage());
            }
        } catch (\Throwable $logError) {
            // Silently fail if logging is not available
        }
    }
}
