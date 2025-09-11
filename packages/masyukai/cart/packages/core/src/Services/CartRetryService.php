<?php

declare(strict_types=1);

namespace MasyukAI\Cart\Services;

use Closure;
use Illuminate\Support\Facades\Log;
use MasyukAI\Cart\Exceptions\CartConflictException;

class CartRetryService
{
    private const DEFAULT_MAX_ATTEMPTS = 3;

    private const DEFAULT_BASE_DELAY = 100; // milliseconds

    private const DEFAULT_MAX_DELAY = 1000; // milliseconds

    /**
     * Execute operation with exponential backoff retry
     */
    public function executeWithRetry(
        Closure $operation,
        int $maxAttempts = self::DEFAULT_MAX_ATTEMPTS,
        int $baseDelay = self::DEFAULT_BASE_DELAY,
        int $maxDelay = self::DEFAULT_MAX_DELAY
    ): mixed {
        $attempt = 1;
        $lastException = null;

        while ($attempt <= $maxAttempts) {
            try {
                return $operation();
            } catch (CartConflictException $e) {
                $lastException = $e;

                // Don't retry if this is the last attempt
                if ($attempt >= $maxAttempts) {
                    break;
                }

                // Calculate delay with exponential backoff and jitter
                $delay = $this->calculateDelay($attempt, $baseDelay, $maxDelay);

                Log::channel('cart')->debug('Cart operation retry', [
                    'attempt' => $attempt,
                    'max_attempts' => $maxAttempts,
                    'delay_ms' => $delay,
                    'conflict_type' => $e->isMinorConflict() ? 'minor' : 'major',
                    'version_difference' => $e->getVersionDifference(),
                ]);

                // Wait before retrying
                usleep($delay * 1000); // Convert to microseconds

                $attempt++;
            }
        }

        // If we get here, all retry attempts failed
        throw $lastException;
    }

    /**
     * Execute operation with smart retry strategy based on conflict type
     */
    public function executeWithSmartRetry(Closure $operation): mixed
    {
        return $this->executeWithRetry(
            operation: $operation,
            maxAttempts: 3,
            baseDelay: 50,
            maxDelay: 500
        );
    }

    /**
     * Execute operation with aggressive retry for high-conflict scenarios
     */
    public function executeWithAggressiveRetry(Closure $operation): mixed
    {
        return $this->executeWithRetry(
            operation: $operation,
            maxAttempts: 5,
            baseDelay: 25,
            maxDelay: 200
        );
    }

    /**
     * Execute operation with conservative retry for low-priority operations
     */
    public function executeWithConservativeRetry(Closure $operation): mixed
    {
        return $this->executeWithRetry(
            operation: $operation,
            maxAttempts: 2,
            baseDelay: 200,
            maxDelay: 1000
        );
    }

    /**
     * Calculate delay with exponential backoff and jitter
     */
    private function calculateDelay(int $attempt, int $baseDelay, int $maxDelay): int
    {
        // Exponential backoff: baseDelay * (2 ^ (attempt - 1))
        $exponentialDelay = $baseDelay * (2 ** ($attempt - 1));

        // Add jitter (±25% randomization)
        $jitter = random_int(-25, 25) / 100;
        $delayWithJitter = $exponentialDelay * (1 + $jitter);

        // Cap at maximum delay
        return min((int) $delayWithJitter, $maxDelay);
    }

    /**
     * Create a retryable operation wrapper
     */
    public function createRetryableOperation(
        Closure $operation,
        int $maxAttempts = self::DEFAULT_MAX_ATTEMPTS
    ): Closure {
        return function (...$args) use ($operation, $maxAttempts) {
            return $this->executeWithRetry(
                fn () => $operation(...$args),
                $maxAttempts
            );
        };
    }

    /**
     * Check if exception is retryable
     */
    public function isRetryable(\Throwable $exception): bool
    {
        return $exception instanceof CartConflictException;
    }

    /**
     * Get retry strategy based on conflict severity
     */
    public function getRetryStrategy(CartConflictException $exception): array
    {
        if ($exception->isMinorConflict()) {
            return [
                'max_attempts' => 5,
                'base_delay' => 25,
                'max_delay' => 200,
                'strategy' => 'aggressive',
            ];
        }

        return [
            'max_attempts' => 3,
            'base_delay' => 100,
            'max_delay' => 500,
            'strategy' => 'standard',
        ];
    }
}
