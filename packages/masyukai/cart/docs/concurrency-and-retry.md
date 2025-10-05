# Concurrency & Conflict Handling

High-traffic carts occasionally encounter conflicting writes. MasyukAI Cart guards data integrity with optimistic locking and provides detailed conflict information for proper error handling.

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

Catch the exception around critical sections to surface meaningful UI messaging or implement custom retry logic.

## Handling Conflicts

You can handle conflicts at the application level based on your requirements:

```php
use MasyukAI\Cart\Exceptions\CartConflictException;

try {
    Cart::add('sku-1', 'Notebook', 5.00);
} catch (CartConflictException $e) {
    // Option 1: Show user a message
    if ($e->isMinorConflict()) {
        return response()->json([
            'message' => 'Your cart was updated in another tab. Please refresh.',
            'suggestions' => $e->getResolutionSuggestions(),
        ], 409);
    }
    
    // Option 2: Implement custom retry logic
    $maxRetries = 3;
    for ($i = 0; $i < $maxRetries; $i++) {
        try {
            Cart::add('sku-1', 'Notebook', 5.00);
            break;
        } catch (CartConflictException $e) {
            if ($i === $maxRetries - 1) throw $e;
            usleep(100000 * ($i + 1)); // 100ms, 200ms, 300ms
        }
    }
    
    // Option 3: Reload cart and retry
    $cart = Cart::content(); // Get fresh cart data
    // Retry operation with fresh state
}
```

## Conflict Resolution Strategies

| Scenario | Strategy |
| --- | --- |
| Two browser tabs editing the same cart | Show message: "Cart updated in another tab. Please refresh." |
| API clients with stale cart state | Inspect `CartConflictException::getResolutionSuggestions()` and return 409 with instructions to refresh. |
| High-value checkout operations | Reload cart state and retry once, or enable `lock_for_update` to prevent conflicts. |
| Mobile app with poor network | Implement exponential backoff retry in the app layer. |

## Database Locking

Enable `config('cart.database.lock_for_update')` when you need strict serialization inside relational databases. This introduces more blocking but prevents conflicts entirely on write-intensive loads.

**Trade-offs:**

- **When false** (default): Uses optimistic locking only. Higher concurrency, but conflicts may occur rarely.
- **When true**: Adds pessimistic locking (`SELECT ... FOR UPDATE`). Prevents conflicts but reduces concurrency and may cause deadlocks.

Enable when multiple servers modify the same cart simultaneously AND you cannot tolerate any conflicts.

## Observability

Metrics (see [Metrics & Observability](metrics-and-observability.md)) track conflicts. Use them to identify if conflicts are frequent (indicating you might need `lock_for_update` or better client-side handling).
