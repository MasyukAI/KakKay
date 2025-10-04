# Troubleshooting

Quick answers to the most common integration hiccups.

## "Call to undefined log channel cart"

- Configure `CART_METRICS_LOG_CHANNEL` to a defined channel in `config/logging.php`, **or** leave it unset so the package uses Laravel’s default logger.
- The metrics service automatically falls back to the default channel, but defining the channel removes the warning entirely.

## Totals Always Return Zero

- Ensure you are reading `->getAmount()` or `->format()` on the returned `Money` object instead of casting it to string implicitly.
- Verify items were added to the active instance (`Cart::instance()`), especially if you swap instances inside the same request.
- Check that item prices are numeric after sanitisation; strings like `"$"` alone resolve to zero.

## Guest Cart Disappears on Login

- Confirm `cart.migration.auto_migrate_on_login` is `true` (default).
- Ensure the `Attempting`/`Login` events are firing (they may be disabled in stateless APIs).
- If you regenerate sessions manually, capture the old session ID and pass it into `CartMigrationService::migrateGuestCartToUser()`.

## Database Conflicts After Deploy

- Run migrations to create/update the `carts` table. Missing the `version` column or indices causes unexpected behaviour.
- Review metrics for conflict spikes to decide whether to enable `cart.database.lock_for_update` or adjust retry strategies.

## Items Missing After Switching Drivers

- Different drivers maintain separate stores. Use `CartMigrationService::swap()` to transfer carts between identifiers when changing drivers.
- Clear caches (`php artisan cache:clear`) after switching from session to cache/database to avoid stale bindings.

## "Cart identifier cannot be determined"

- Ensure the request has access to either the authentication system or the session manager. In stateless API contexts, pass a custom identifier to `CartManager::getCartInstance()`.

## Metrics Count Stuck at Zero

- Verify `cart.metrics.enabled` is still `true` after configuration caching.
- Ensure you’re interacting through the `Cart` facade (directly instantiating `Cart` bypasses metrics unless you proxy through `CartManager`).
- If using a custom cache store, confirm it supports increment operations.

## Octane Workers See Stale Data

- Prefer the cache or database driver (session storage requires stickiness).
- Enable `cart.octane.reset_static_state` to clear state between requests.
- Queue events (`cart.octane.queue_events`) to avoid long-running listeners blocking the worker.

## Tests Fail Randomly with Shared State

- Run `Cart::clear()` inside `beforeEach` hooks.
- Flush the cache between tests (`Cache::store()->flush()`) when using array or Redis drivers.
- Disable metrics in tests that focus solely on business logic (`config()->set('cart.metrics.enabled', false)`).

Still stuck? Inspect the storage driver directly (`Cart::storage()->getItems($identifier, $instance)`) to view the raw payload.
