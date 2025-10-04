# Metrics & Observability

Cart insights power product decisions and alert you to operational issues. MasyukAI Cart collects metrics automatically and exposes them through services and artisan commands.

## How Metrics Are Recorded

`CartManager` wraps common mutating operations (`add`, `update`, `remove`, `clear`, `get`, totals, conditions, etc.) and reports to `CartMetricsService` when `config('cart.metrics.enabled')` is `true`.

Metrics include:

- Operation counts (per method and aggregate).
- Success/failure info for retry cycles.
- Conflict counters (minor vs major) when optimistic locking triggers.
- Performance windows (avg/min/max execution time across the last 100 samples).
- Conversion and abandonment counters for analytics funnels.

## Configuration

See [Configuration](configuration.md#metrics--observability) for a complete table of flags. Highlights:

- `metrics.enabled` toggles the entire system.
- `metrics.track_conflicts` narrows focus to conflict logging.
- `metrics.slow_operation_threshold` defines what “slow” means.
- `metrics.log_channel` optionally routes logs to a named channel; unknown channels fall back to Laravel’s default logger.

## Artisan Command

```bash
php artisan cart:metrics
```

This renders operation summaries, conflict breakdowns, conversions vs abandonments, and performance stats.

Options:

- `--json` – output as JSON for ingestion into external dashboards.
- `--clear` – purge all metric keys (useful in tests or when resetting baselines).

## Recording Custom Signals

Inject the service to record bespoke events:

```php
use MasyukAI\Cart\Services\CartMetricsService;

$metrics = app(CartMetricsService::class);

$metrics->recordConversion(
    identifier: $cartId,
    instance: 'default',
    context: ['order_id' => $order->id],
);

$metrics->recordAbandonment(
    identifier: $cartId,
    instance: 'default',
    context: ['reason' => 'timeout'],
);
```

Retrieve the current snapshot at any time:

```php
$summary = Cart::getMetrics(); // proxy to CartManager
```

Expect an array containing `operations`, `conflicts`, `abandonments`, `conversions`, and `performance` buckets.

## Logging

The metrics service writes to logs when slow operations or conflicts occur. If a custom channel is configured, the service attempts to use it; if that channel is not defined Laravel throws an `InvalidArgumentException`, so the service automatically falls back to the default channel.

Ensure you configure `logging.channels.cart` (or your chosen name) if you want a dedicated sink.

## Cleaning Up

`CartMetricsService::clearMetrics()` wipes metric keys, supporting both Redis-backed stores (using pattern deletes) and array/file stores (manual key listing). Invoke this in integration tests to prevent cross-test leakage.
