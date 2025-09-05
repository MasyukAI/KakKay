<?php

declare(strict_types=1);

namespace MasyukAI\Cart;

use Illuminate\Contracts\Events\Dispatcher;
use MasyukAI\Cart\Storage\StorageInterface;
use MasyukAI\Cart\Traits\ManagesPricing;

/**
 * Cart Manager handles global instance switching for the Cart facade
 */
class CartManager
{
    use ManagesPricing;
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
     * Get session storage access for session-specific operations
     */
    public function session(?string $sessionKey = null): StorageInterface
    {
        if ($this->storage instanceof \MasyukAI\Cart\Storage\SessionStorage) {
            return $this->storage;
        }

        // If not using session storage, create a temporary session storage instance
        $session = app('session')->driver();

        return new \MasyukAI\Cart\Storage\SessionStorage($session, $sessionKey ?? config('cart.session.key', 'cart'));
    }

    /**
     * Enable formatting for all price outputs
     */
    public function formatted(): static
    {
        \MasyukAI\Cart\Support\PriceFormatManager::enableFormatting();
        return $this;
    }

    /**
     * Disable formatting for all price outputs
     */
    public function raw(): static
    {
        \MasyukAI\Cart\Support\PriceFormatManager::disableFormatting();
        return $this;
    }

    /**
     * Set currency and enable formatting
     */
    public function currency(?string $currency = null): static
    {
        \MasyukAI\Cart\Support\PriceFormatManager::setCurrency($currency);
        return $this;
    }

    /**
     * Proxy all other method calls to the current cart instance
     */
    public function __call(string $method, array $arguments): mixed
    {
        return $this->currentCart->{$method}(...$arguments);
    }
}
