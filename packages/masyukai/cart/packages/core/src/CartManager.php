<?php

declare(strict_types=1);

namespace MasyukAI\Cart;

use Illuminate\Contracts\Events\Dispatcher;
use MasyukAI\Cart\Exceptions\CartConflictException;
use MasyukAI\Cart\Services\CartMetricsService;
use MasyukAI\Cart\Services\CartRetryService;
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

    private ?CartMetricsService $metricsService = null;

    private ?CartRetryService $retryService = null;

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

        // Initialize services if available
        if (app()->bound(CartMetricsService::class)) {
            $this->metricsService = app(CartMetricsService::class);
        }

        if (app()->bound(CartRetryService::class)) {
            $this->retryService = app(CartRetryService::class);
        }
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
        $session = app(\Illuminate\Session\SessionManager::class)->driver();

        return new \MasyukAI\Cart\Storage\SessionStorage($session, $sessionKey ?? config('cart.session.key', 'cart'));
    }

    /**
     * Enable formatting globally.
     */
    public function enableFormatting(): void
    {
        \MasyukAI\Cart\Support\CartMoney::enableFormatting();
    }

    /**
     * Disable formatting globally.
     */
    public function disableFormatting(): void
    {
        \MasyukAI\Cart\Support\CartMoney::disableFormatting();
    }

    /**
     * Execute cart operation with automatic retry on conflicts
     */
    public function retryWithBackoff(\Closure $operation): mixed
    {
        if (! $this->retryService) {
            return $operation();
        }

        $startTime = microtime(true);

        try {
            $result = $this->retryService->executeWithSmartRetry($operation);

            // Record success metrics
            if ($this->metricsService) {
                $executionTime = microtime(true) - $startTime;
                $this->metricsService->recordPerformance('retry_operation', $executionTime);
                $this->metricsService->recordOperation('retry_success');
            }

            return $result;
        } catch (CartConflictException $e) {
            // Record conflict metrics
            if ($this->metricsService) {
                $this->metricsService->recordConflict($e, [
                    'operation' => 'retry_operation',
                    'instance' => $this->currentInstance,
                ]);
            }

            throw $e;
        }
    }

    /**
     * Get cart metrics summary
     */
    public function getMetrics(): array
    {
        return $this->metricsService?->getMetricsSummary() ?? [];
    }

    /**
     * Record cart conversion for analytics
     */
    public function recordConversion(array $context = []): void
    {
        if ($this->metricsService) {
            $this->metricsService->recordConversion(
                $this->currentCart->getIdentifier(),
                $this->currentInstance,
                $context
            );
        }
    }

    /**
     * Record cart abandonment for analytics
     */
    public function recordAbandonment(array $context = []): void
    {
        if ($this->metricsService) {
            $this->metricsService->recordAbandonment(
                $this->currentCart->getIdentifier(),
                $this->currentInstance,
                $context
            );
        }
    }

    /**
     * Proxy all other method calls to the current cart instance with metrics
     */
    public function __call(string $method, array $arguments): mixed
    {
        $startTime = microtime(true);

        try {
            $result = $this->currentCart->{$method}(...$arguments);

            // Record operation metrics for cart operations
            if ($this->metricsService && $this->isCartOperation($method)) {
                $executionTime = microtime(true) - $startTime;
                $this->metricsService->recordOperation($method);
                $this->metricsService->recordPerformance($method, $executionTime);
            }

            return $result;
        } catch (CartConflictException $e) {
            // Record conflict metrics
            if ($this->metricsService) {
                $this->metricsService->recordConflict($e, [
                    'operation' => $method,
                    'instance' => $this->currentInstance,
                    'arguments' => $this->sanitizeArguments($arguments),
                ]);
            }

            throw $e;
        }
    }

    /**
     * Check if method is a cart operation that should be tracked
     */
    private function isCartOperation(string $method): bool
    {
        return in_array($method, [
            'add', 'update', 'remove', 'clear', 'get', 'has',
            'addCondition', 'removeCondition', 'clearConditions',
            'associate', 'taxRate', 'count', 'content',
            'subtotal', 'total', 'tax', 'discount',
        ]);
    }

    /**
     * Sanitize arguments for logging (remove sensitive data)
     */
    private function sanitizeArguments(array $arguments): array
    {
        // Remove potentially sensitive data like payment info, personal details
        $sanitized = [];
        foreach ($arguments as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = $this->sanitizeArray($value);
            } elseif (is_string($value) && strlen($value) > 100) {
                $sanitized[$key] = substr($value, 0, 100).'...';
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }

    /**
     * Sanitize array values
     */
    private function sanitizeArray(array $data): array
    {
        $sensitive = ['password', 'token', 'secret', 'key', 'card', 'ssn', 'cvv'];
        $sanitized = [];

        foreach ($data as $key => $value) {
            if (is_string($key) && str_contains(strtolower($key), 'password')) {
                $sanitized[$key] = '[HIDDEN]';
            } elseif (is_string($key)) {
                foreach ($sensitive as $sensitiveKey) {
                    if (str_contains(strtolower($key), $sensitiveKey)) {
                        $sanitized[$key] = '[HIDDEN]';

                        continue 2;
                    }
                }
                $sanitized[$key] = is_array($value) ? $this->sanitizeArray($value) : $value;
            } else {
                $sanitized[$key] = is_array($value) ? $this->sanitizeArray($value) : $value;
            }
        }

        return $sanitized;
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
}
