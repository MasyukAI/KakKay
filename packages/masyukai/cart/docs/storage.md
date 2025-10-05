# Storage Drivers

MasyukAI Cart ships with three storage drivers out of the box. Choose the one that matches your durability, performance, and deployment requirements.

## Session Driver (Default)

- **Use when:** building prototypes, low-volume stores, or per-session carts.
- **Persistence:** lives inside the Laravel session payload (`cart.session.key`).
- **Strengths:** zero configuration, respects existing session encryption, no external dependencies.
- **Considerations:** carts disappear when the session expires or the user switches devices/browsers.

### Configuration

```php
// config/cart.php
'storage' => 'session',
'session' => [
    'key' => 'cart',
],
```

No migrations or infrastructure changes are required.

## Cache Driver

- **Use when:** you need shared, fast access across multiple web servers or workers.
- **Persistence:** stored through Laravel's cache repository (`cart.cache.prefix`, `cart.cache.ttl`).
- **Strengths:** low-latency read/write, optional locking for concurrent writes (when the cache store supports locks, e.g., Redis).
- **Considerations:** TTL determines how long carts survive; coordinate with business requirements.

### Enabling Locking

Locking is automatically leveraged whenever the underlying cache store supports `lock()`. Configure Redis or Memcached as your default cache store.

```php
'cache' => [
    'prefix' => 'cart',
    'ttl' => 86400, // seconds
],
```

## Database Driver

- **Use when:** you require durable carts that survive across devices, need analytics, or expect high concurrency.
- **Persistence:** `carts` table containing JSON columns for items, conditions, and metadata.
- **Strengths:** optimistic locking through a `version` column, conflict detection with rich diagnostics, and easy querying for abandoned carts.
- **Considerations:** migration required, remember to prune stale rows periodically.

### Setup

```bash
php artisan vendor:publish --tag=cart-migrations
php artisan migrate
```

Check that your migration aligns with `config('cart.database.table')`.

### Concurrency

- Each write increments a `version` column to detect conflicting updates.
- Set `cart.database.lock_for_update` to `true` to wrap updates in `FOR UPDATE` locks if your workload demands strict serialization.
- Conflicts raise `CartConflictException`. Handle these at the application level with try/catch blocks and custom retry logic as needed.

## Swapping Drivers

Switch at runtime by updating configuration and clearing caches:

```php
config(['cart.storage' => 'database']);
app()->forgetInstance('cart');
```

The service provider will rebuild the `CartManager` with the new storage driver on the next request.

## Custom Drivers

Binding custom implementations is as simple as registering the `StorageInterface` binding before the service provider resolves the cart:

```php
app()->bind(MasyukAI\Cart\Storage\StorageInterface::class, function ($app) {
    return new App\Cart\Storage\DynamoDbStorage($app['dynamodb']);
});
```

Ensure the class honours the complete interface (items, conditions, metadata, and identifier swapping).

## Choosing the Right Driver

| Requirement | Recommended Driver |
| --- | --- |
| "Ready in 30 seconds" | Session |
| Shared cart between web servers | Cache |
| Persistent carts for authenticated users | Database |
| Analytics on abandoned carts | Database |
| Long-lived worker processes | Cache or Database |

Switch drivers intentionally and test migrations between drivers via `CartMigrationService::swap()` if you need to move existing carts.
