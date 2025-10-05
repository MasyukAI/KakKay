<?php

declare(strict_types=1);

namespace MasyukAI\Cart;

use Illuminate\Contracts\Events\Dispatcher;
use MasyukAI\Cart\Storage\StorageInterface;

/**
 * Cart Manager handles global instance switching for the Cart facade
 */
class CartManager
{
    private Cart $currentCart;

    private string $currentInstance = 'default';

    public function __construct(
        private StorageInterface $storage,
        private ?Dispatcher $events = null,
        private bool $eventsEnabled = true
    ) {
        $this->currentCart = new Cart(
            storage: $this->storage,
            identifier: $this->resolveIdentifier(),
            events: $this->events,
            instanceName: $this->currentInstance,
            eventsEnabled: $this->eventsEnabled
        );
    }

    /**
     * Get the current cart instance
     */
    public function getCurrentCart(): Cart
    {
        return $this->currentCart;
    }

    /**
     * Get a cart instance without changing the global state
     */
    public function getCartInstance(string $name, ?string $identifier = null): Cart
    {
        return new Cart(
            storage: $this->storage,
            identifier: $this->resolveIdentifier($identifier),
            events: $this->events,
            instanceName: $name,
            eventsEnabled: $this->eventsEnabled
        );
    }

    /**
     * Get the current instance name
     */
    public function instance(): string
    {
        return $this->currentInstance;
    }

    /**
     * Set the current cart instance globally
     */
    public function setInstance(string $name): static
    {
        if ($this->currentInstance !== $name) {
            $currentIdentifier = $this->currentCart->getIdentifier();
            $this->currentInstance = $name;
            $this->currentCart = new Cart(
                storage: $this->storage,
                identifier: $currentIdentifier,
                events: $this->events,
                instanceName: $name,
                eventsEnabled: $this->eventsEnabled
            );
        }

        return $this;
    }

    /**
     * Get session storage access for session-specific operations
     */
    public function session(?string $sessionKey = null): StorageInterface
    {
        if ($this->storage instanceof \MasyukAI\Cart\Storage\SessionStorage) {
            return $this->storage;
        }

        // If not using session storage, create a temporary session storage instance
        $session = app(\Illuminate\Session\SessionManager::class)->driver();

        return new \MasyukAI\Cart\Storage\SessionStorage($session, $sessionKey ?? config('cart.session.key', 'cart'));
    }

    /**
     * Proxy all other method calls to the current cart instance
     *
     * @param  array<mixed>  $arguments
     */
    public function __call(string $method, array $arguments): mixed
    {
        return $this->currentCart->{$method}(...$arguments);
    }

    /**
     * Swap cart ownership by transferring cart from old identifier to new identifier.
     *
     * This ensures the new identifier has an active cart by transferring
     * the cart from the old identifier. This prevents cart abandonment by
     * ensuring continued cart activity under the new identifier.
     *
     * @param  string  $oldIdentifier  The old identifier (e.g., guest session)
     * @param  string  $newIdentifier  The new identifier (e.g., user ID)
     * @param  string  $instance  The cart instance name (e.g., 'default', 'wishlist')
     * @return bool True if swap was successful (new identifier now has the cart)
     */
    public function swap(string $oldIdentifier, string $newIdentifier, string $instance = 'default'): bool
    {
        $migrationService = new \MasyukAI\Cart\Services\CartMigrationService([], $this->storage);

        return $migrationService->swap($oldIdentifier, $newIdentifier, $instance);
    }

    /**
     * Resolve storage identifier (auth()->id() for authenticated users, session()->id() for guests)
     */
    protected function resolveIdentifier(?string $customIdentifier = null): string
    {
        // Use custom identifier if provided
        if ($customIdentifier !== null) {
            return $customIdentifier;
        }

        // Try authenticated user first
        if ($authenticatedId = $this->getAuthenticatedUserId()) {
            return $authenticatedId;
        }

        // Fall back to session ID for guests
        if ($sessionId = $this->getSessionId()) {
            return $sessionId;
        }

        // If neither is available, throw exception
        throw new \RuntimeException(
            'Cart identifier cannot be determined: neither auth nor session services are available'
        );
    }

    /**
     * Get authenticated user ID if available
     */
    protected function getAuthenticatedUserId(): ?string
    {
        try {
            if (! app()->bound('auth')) {
                return null;
            }

            $auth = app(\Illuminate\Contracts\Auth\Factory::class);
            $guard = $auth->guard();

            return $guard->check() ? (string) $guard->id() : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Get session ID if available
     */
    protected function getSessionId(): ?string
    {
        try {
            if (! app()->bound('session')) {
                return null;
            }

            $session = app(\Illuminate\Session\SessionManager::class);

            return $session->getId();
        } catch (\Throwable $e) {
            return null;
        }
    }
}
