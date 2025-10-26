<?php

declare(strict_types=1);

namespace AIArmada\Cart;

use AIArmada\Cart\Services\CartConditionResolver;
use AIArmada\Cart\Storage\StorageInterface;
use Illuminate\Contracts\Events\Dispatcher;
use RuntimeException;
use Throwable;

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
        private bool $eventsEnabled = true,
        private ?CartConditionResolver $conditionResolver = null
    ) {
        $this->conditionResolver ??= app(CartConditionResolver::class);

        $this->currentCart = new Cart(
            storage: $this->storage,
            identifier: $this->resolveIdentifier(),
            events: $this->events,
            instanceName: $this->currentInstance,
            eventsEnabled: $this->eventsEnabled,
            conditionResolver: $this->conditionResolver
        );
    }

    /**
     * Proxy all other method calls to the current cart instance
     *
     * @param  array<string, mixed>  $arguments
     */
    public function __call(string $method, array $arguments): mixed
    {
        return $this->currentCart->{$method}(...$arguments);
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
            eventsEnabled: $this->eventsEnabled,
            conditionResolver: $this->conditionResolver
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
                eventsEnabled: $this->eventsEnabled,
                conditionResolver: $this->conditionResolver
            );
        }

        return $this;
    }

    /**
     * Set the current cart identifier globally
     */
    public function setIdentifier(string $identifier): static
    {
        if ($this->currentCart->getIdentifier() !== $identifier) {
            $this->currentCart = new Cart(
                storage: $this->storage,
                identifier: $identifier,
                events: $this->events,
                instanceName: $this->currentInstance,
                eventsEnabled: $this->eventsEnabled,
                conditionResolver: $this->conditionResolver
            );
        }

        return $this;
    }

    /**
     * Reset cart identifier to default (session/user ID)
     *
     * Useful for reverting to automatic identifier resolution after
     * setting a custom identifier during runtime.
     */
    public function forgetIdentifier(): static
    {
        $defaultIdentifier = $this->resolveIdentifier();

        if ($this->currentCart->getIdentifier() !== $defaultIdentifier) {
            $this->currentCart = new Cart(
                storage: $this->storage,
                identifier: $defaultIdentifier,
                events: $this->events,
                instanceName: $this->currentInstance,
                eventsEnabled: $this->eventsEnabled,
                conditionResolver: $this->conditionResolver
            );
        }

        return $this;
    }

    /**
     * Get session storage access for session-specific operations
     */
    public function session(?string $sessionKey = null): StorageInterface
    {
        if ($this->storage instanceof Storage\SessionStorage) {
            return $this->storage;
        }

        // If not using session storage, create a temporary session storage instance
        $session = app(\Illuminate\Session\SessionManager::class)->driver();

        return new Storage\SessionStorage($session, $sessionKey ?? config('cart.session.key', 'cart'));
    }

    /**
     * Get a cart instance by its UUID
     *
     * This loads a cart object using its database primary key (UUID).
     * Useful for retrieving carts from payment systems, orders, or webhooks.
     *
     * @param  string  $uuid  The cart UUID (primary key from carts table)
     * @return Cart|null Cart instance or null if not found
     */
    public function getById(string $uuid): ?Cart
    {
        $tableName = config('cart.storage.database.table', 'carts');

        $snapshot = app('db')->table($tableName)
            ->where('id', $uuid)
            ->first();

        if (! $snapshot) {
            return null;
        }

        return $this->getCartInstance($snapshot->instance, $snapshot->identifier);
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
        $migrationService = new Services\CartMigrationService([], $this->storage);

        return $migrationService->swap($oldIdentifier, $newIdentifier, $instance);
    }

    /**
     * Resolve storage identifier (auth()->id() for authenticated users, session()->id() for guests)
     */
    private function resolveIdentifier(?string $customIdentifier = null): string
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
        throw new RuntimeException(
            'Cart identifier cannot be determined: neither auth nor session services are available'
        );
    }

    /**
     * Get authenticated user ID if available
     */
    private function getAuthenticatedUserId(): ?string
    {
        try {
            if (! app()->bound('auth')) {
                return null;
            }

            $auth = app(\Illuminate\Contracts\Auth\Factory::class);
            $guard = $auth->guard();

            return $guard->check() ? (string) $guard->id() : null;
        } catch (Throwable $e) {
            return null;
        }
    }

    /**
     * Get session ID if available
     */
    private function getSessionId(): ?string
    {
        try {
            if (! app()->bound('session')) {
                return null;
            }

            $session = app(\Illuminate\Session\SessionManager::class);

            return $session->getId();
        } catch (Throwable $e) {
            return null;
        }
    }
}
