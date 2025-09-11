<?php

declare(strict_types=1);

namespace MasyukAI\Cart\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use MasyukAI\Cart\Exceptions\CartConflictException;

class CartMetricsService
{
    private const METRICS_PREFIX = 'cart_metrics:';

    private const METRICS_TTL = 3600; // 1 hour

    /**
     * Record cart operation metrics
     */
    public function recordOperation(string $operation, array $context = []): void
    {
        $key = self::METRICS_PREFIX.'operations:'.$operation;
        $daily_key = $key.':'.now()->format('Y-m-d');

        // Increment counters
        Cache::increment($key, 1);
        Cache::increment($daily_key, 1);

        // Set TTL for daily metrics
        Cache::put($daily_key, Cache::get($daily_key, 0), now()->addDays(7));

        // Log detailed operation for debugging
        Log::channel('cart')->info("Cart operation: {$operation}", $context);
    }

    /**
     * Record cart conflict for monitoring
     */
    public function recordConflict(CartConflictException $exception, array $context = []): void
    {
        $key = self::METRICS_PREFIX.'conflicts';
        $daily_key = $key.':'.now()->format('Y-m-d');

        Cache::increment($key, 1);
        Cache::increment($daily_key, 1);
        Cache::put($daily_key, Cache::get($daily_key, 0), now()->addDays(7));

        // Track conflict details
        $conflictDetails = [
            'attempted_version' => $exception->getAttemptedVersion(),
            'current_version' => $exception->getCurrentVersion(),
            'version_difference' => $exception->getVersionDifference(),
            'is_minor_conflict' => $exception->isMinorConflict(),
            'resolution_suggestions' => $exception->getResolutionSuggestions(),
            'context' => $context,
        ];

        Log::channel('cart')->warning('Cart conflict detected', $conflictDetails);

        // Track by conflict severity
        $severity = $exception->isMinorConflict() ? 'minor' : 'major';
        Cache::increment(self::METRICS_PREFIX."conflicts:severity:{$severity}", 1);
    }

    /**
     * Record cart performance metrics
     */
    public function recordPerformance(string $operation, float $executionTime, array $context = []): void
    {
        $key = self::METRICS_PREFIX.'performance:'.$operation;

        // Store execution times in a sliding window
        $times = Cache::get($key, []);
        $times[] = [
            'time' => $executionTime,
            'timestamp' => microtime(true),
            'context' => $context,
        ];

        // Keep only last 100 measurements
        if (count($times) > 100) {
            $times = array_slice($times, -100);
        }

        Cache::put($key, $times, self::METRICS_TTL);

        // Log slow operations
        if ($executionTime > 1.0) { // Over 1 second
            Log::channel('cart')->warning("Slow cart operation: {$operation}", [
                'execution_time' => $executionTime,
                'context' => $context,
            ]);
        }
    }

    /**
     * Record cart abandonment
     */
    public function recordAbandonment(string $identifier, string $instance, array $context = []): void
    {
        $key = self::METRICS_PREFIX.'abandonments';
        $daily_key = $key.':'.now()->format('Y-m-d');

        Cache::increment($key, 1);
        Cache::increment($daily_key, 1);
        Cache::put($daily_key, Cache::get($daily_key, 0), now()->addDays(30));

        Log::channel('cart')->info('Cart abandoned', [
            'identifier' => $identifier,
            'instance' => $instance,
            'context' => $context,
        ]);
    }

    /**
     * Record cart conversion (checkout)
     */
    public function recordConversion(string $identifier, string $instance, array $context = []): void
    {
        $key = self::METRICS_PREFIX.'conversions';
        $daily_key = $key.':'.now()->format('Y-m-d');

        Cache::increment($key, 1);
        Cache::increment($daily_key, 1);
        Cache::put($daily_key, Cache::get($daily_key, 0), now()->addDays(30));

        Log::channel('cart')->info('Cart converted', [
            'identifier' => $identifier,
            'instance' => $instance,
            'context' => $context,
        ]);
    }

    /**
     * Get metrics summary
     */
    public function getMetricsSummary(): array
    {
        $today = now()->format('Y-m-d');

        return [
            'operations' => [
                'total' => Cache::get(self::METRICS_PREFIX.'operations', 0),
                'today' => Cache::get(self::METRICS_PREFIX."operations:{$today}", 0),
            ],
            'conflicts' => [
                'total' => Cache::get(self::METRICS_PREFIX.'conflicts', 0),
                'today' => Cache::get(self::METRICS_PREFIX."conflicts:{$today}", 0),
                'minor' => Cache::get(self::METRICS_PREFIX.'conflicts:severity:minor', 0),
                'major' => Cache::get(self::METRICS_PREFIX.'conflicts:severity:major', 0),
            ],
            'abandonments' => [
                'total' => Cache::get(self::METRICS_PREFIX.'abandonments', 0),
                'today' => Cache::get(self::METRICS_PREFIX."abandonments:{$today}", 0),
            ],
            'conversions' => [
                'total' => Cache::get(self::METRICS_PREFIX.'conversions', 0),
                'today' => Cache::get(self::METRICS_PREFIX."conversions:{$today}", 0),
            ],
            'performance' => $this->getPerformanceMetrics(),
        ];
    }

    /**
     * Get average cart conditions per cart
     */
    public function getAverageConditionsPerCart(): float
    {
        $totalConditions = Cache::get(self::METRICS_PREFIX.'total_conditions', 0);
        $totalCarts = Cache::get(self::METRICS_PREFIX.'total_carts', 1);

        return round($totalConditions / $totalCarts, 2);
    }

    /**
     * Get performance metrics
     */
    private function getPerformanceMetrics(): array
    {
        $operations = ['add', 'update', 'remove', 'clear', 'get'];
        $metrics = [];

        foreach ($operations as $operation) {
            $key = self::METRICS_PREFIX.'performance:'.$operation;
            $times = Cache::get($key, []);

            if (empty($times)) {
                $metrics[$operation] = null;

                continue;
            }

            $execTimes = array_column($times, 'time');
            $metrics[$operation] = [
                'avg' => round(array_sum($execTimes) / count($execTimes), 4),
                'min' => round(min($execTimes), 4),
                'max' => round(max($execTimes), 4),
                'count' => count($execTimes),
            ];
        }

        return $metrics;
    }

    /**
     * Clear all metrics (for testing)
     */
    public function clearMetrics(): void
    {
        $pattern = self::METRICS_PREFIX.'*';

        // Try to clear Redis keys if using Redis cache
        try {
            if (Cache::getStore() instanceof \Illuminate\Cache\RedisStore) {
                /** @var \Illuminate\Cache\RedisStore $store */
                $store = Cache::getStore();
                $redis = $store->connection();
                $keys = $redis->keys($pattern);

                if (! empty($keys)) {
                    $redis->del($keys);
                }
            } else {
                // Fallback for other cache stores - clear known metric keys
                $this->clearKnownMetricKeys();
            }
        } catch (\Exception $e) {
            // Fallback to clearing known keys
            $this->clearKnownMetricKeys();
        }
    }

    /**
     * Clear known metric keys for non-Redis stores
     */
    private function clearKnownMetricKeys(): void
    {
        $baseKeys = [
            'operations', 'conflicts', 'abandonments', 'conversions',
            'conflicts:severity:minor', 'conflicts:severity:major',
            'total_conditions', 'total_carts',
        ];

        $operations = ['add', 'update', 'remove', 'clear', 'get'];

        foreach ($baseKeys as $key) {
            Cache::forget(self::METRICS_PREFIX.$key);

            // Clear daily keys for the last 30 days
            for ($i = 0; $i < 30; $i++) {
                $date = now()->subDays($i)->format('Y-m-d');
                Cache::forget(self::METRICS_PREFIX.$key.':'.$date);
            }
        }

        foreach ($operations as $operation) {
            Cache::forget(self::METRICS_PREFIX.'performance:'.$operation);
        }
    }
}
