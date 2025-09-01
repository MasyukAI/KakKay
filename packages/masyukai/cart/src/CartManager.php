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
        private bool $eventsEnabled = true,
        private array $config = []
    ) {
        $this->currentCart = new Cart(
            storage: $this->storage,
            events: $this->events,
            instanceName: $this->currentInstance,
            eventsEnabled: $this->eventsEnabled,
            config: $this->config
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
    public function getCartInstance(string $name): Cart
    {
        return new Cart(
            storage: $this->storage,
            events: $this->events,
            instanceName: $name,
            eventsEnabled: $this->eventsEnabled,
            config: $this->config
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
            $this->currentInstance = $name;
            $this->currentCart = new Cart(
                storage: $this->storage,
                events: $this->events,
                instanceName: $name,
                eventsEnabled: $this->eventsEnabled,
                config: $this->config
            );
        }

        return $this;
    }

    /**
     * Get items (wrapper for getItems)
     */
    public function getItems(): mixed
    {
        return $this->currentCart->getItems();
    }

    /**
     * Get total (wrapper for getTotal)
     */
    public function total(): float
    {
        return $this->currentCart->getTotal();
    }

    /**
     * Get subtotal (wrapper for getSubTotal)
     */
    public function subtotal(): float
    {
        return $this->currentCart->getSubTotal();
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
        $session = app('session');

        return new \MasyukAI\Cart\Storage\SessionStorage($session, $sessionKey ?? config('cart.session.key', 'cart'));
    }

    /**
     * Proxy all other method calls to the current cart instance
     */
    public function __call(string $method, array $arguments): mixed
    {
        return $this->currentCart->{$method}(...$arguments);
    }
}
