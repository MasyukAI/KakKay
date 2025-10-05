# Identifiers & Migration

Carts follow users through identifiers. Understanding how identifiers are resolved and migrated ensures a seamless guest → user journey.

## Identifier Resolution

`CartManager` resolves the active identifier in this order:

1. **Custom identifier** passed to `CartManager::getCartInstance($name, $identifier)`.
2. **Authenticated user** via `Auth::guard()->id()`.
3. **Session ID** via `SessionManager::getId()`.

If neither auth nor session are available a runtime exception is thrown; ensure both services are registered when booting Laravel.

## Instances vs Identifiers

- **Identifier** (e.g., `42`, `session-abc123`): represents *who* owns the cart.
- **Instance** (`default`, `wishlist`, `quote`): represents *which* cart bucket they are interacting with.

Swapping identifiers never changes the instance name.

## CartMigrationService

`CartMigrationService` orchestrates migration scenarios.

```php
use MasyukAI\Cart\Services\CartMigrationService;

$migrator = app(CartMigrationService::class);

$migrator->migrateGuestCartToUser(
    userId: $user->id,
    instance: 'default',
    sessionId: session()->getId(),
);
```

### Merge Strategies

Configure via `cart.migration.merge_strategy`:

- `add_quantities` – merge quantities (default).
- `keep_highest_quantity` – choose the higher of user or guest quantity.
- `keep_user_cart` – ignore guest items if conflicts occur.
- `replace_with_guest` – override user quantities with guest quantities.

### Automatic Migration on Login

When `cart.migration.auto_migrate_on_login` is `true`, the service provider hooks Laravel’s `Attempting` and `Login` events to:

1. Capture the guest session ID before regeneration.
2. Migrate the guest cart into the authenticated user’s identifier post-login.

Events emitted during migration:

- `CartMerged` – includes the target cart, total items merged, merge strategy, and conflict flag.
- `CartUpdated` – dispatched via the event subscriber after merges.

### Manual Swaps

Swap one identifier for another without merging data:

```php
Cart::swap($oldIdentifier, $newIdentifier, 'default');
```

`StorageInterface::swapIdentifier()` implementations guarantee that cart ownership transfers atomically and the original identifier is cleared.

### Guest Cart Preservation


If you require cart preservation across logouts, store the last session ID in a cookie or table and pass it into `migrateGuestCartToUser()` explicitly.

## Handling Edge Cases

| Scenario | Guidance |
| --- | --- |
| User logs in on multiple devices simultaneously | Database driver + merge strategy `add_quantities` ensures graceful merges. |
| Need to inspect carts before migrating | Use the storage driver (`Cart::storage()->getItems($identifier, $instance)`) to preview data. |
| Aborted migrations | The service leaves guest carts untouched unless a successful swap or merge occurs. |

For analytics after successful login, query your database directly:

```php
$cart = Cart::instance('default');
$cartData = DB::table('carts')->where('identifier', auth()->id())->first();
// Track conversion in your analytics system
```

```
