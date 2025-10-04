# Concurrency & Retry

High-traffic carts frequently encounter conflicting writes. MasyukAI Cart guards data integrity with optimistic locking and structured retries.

## Optimistic Locking

The database driver stores a `version` column per cart row.

1. When writing, it selects the current row (optionally `FOR UPDATE`).
2. It updates the row only if the stored `version` matches the expected value.
3. On success it increments `version` and sets `updated_at`.
4. If the update count is zero, it fetches the latest `version` and throws `CartConflictException`.

### CartConflictException

Properties exposed:

- `getAttemptedVersion()` and `getCurrentVersion()`.
- `getVersionDifference()` – difference between versions.
- `isMinorConflict()` – true if the cart was only one version behind.
- `getResolutionSuggestions()` – hints for clients (`retry_with_refresh`, `merge_changes`, `reload_cart`, …).
- Optional `getConflictedCart()` and `getConflictedData()` to inspect the cart that won the race.

Catch the exception around critical sections to surface meaningful UI messaging.

## Retry Helpers

`CartRetryService` implements exponential backoff with jitter:

```php
use MasyukAI\Cart\Services\CartRetryService;

$result = app(CartRetryService::class)->executeWithRetry(function () {
    return Cart::add('sku-1', 'Notebook', 5.00);
});
```

Variants:

- `executeWithSmartRetry()` – defaults to 3 attempts, 50 ms base delay.
- `executeWithAggressiveRetry()` – 5 attempts, 25 ms base delay (good for small payloads in conflict-heavy domains).
- `executeWithConservativeRetry()` – 2 attempts, longer delays.
- `createRetryableOperation($closure, $maxAttempts)` – wraps existing closures for reuse.

`CartManager::retryWithBackoff()` routes through the service when it is bound in the container, capturing metrics for success and conflict counts.

## When to Retry

Conflict handling strategy suggestions:

| Scenario | Strategy |
| --- | --- |
| Two browser tabs editing the same cart | Wrap writes in `retryWithBackoff()` to merge sequential updates. |
| API clients with stale cart state | Inspect `CartConflictException::getResolutionSuggestions()` and instruct clients to refresh cart data. |
| High-value checkout operations | Use `executeWithAggressiveRetry()` followed by a definitive reload. |

## Database Locking

Enable `config('cart.database.lock_for_update')` when you need strict serialization inside relational databases. This introduces more blocking but can reduce conflicts on write-intensive loads.

## Observability

Metrics (see [Metrics & Observability](metrics-and-observability.md)) track conflicts and retry outcomes. Use them to identify when backoff parameters need tuning or when you should revisit client behaviour.
