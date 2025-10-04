# API Reference

Quick lookup for primary classes, methods, and console commands. Refer to PHPDoc in the source for signatures and return types.

## Facade: `MasyukAI\Cart\Facades\Cart`

| Method | Purpose |
| --- | --- |
| `setInstance(string $name)` | Switch the active instance for the current request. Returns the `CartManager`. |
| `instance(): string` | Current instance name. |
| `getCurrentCart(): Cart` | Underlying `Cart` object for the active instance. |
| `storage(): StorageInterface` | Expose the configured storage driver. |
| `session(?string $key = null): StorageInterface` | Access a session storage driver for direct operations. |
| `add(...)` | Add one or multiple items (see [Cart Operations](cart-operations.md)). |
| `update(string $id, array $data)` | Update an existing item. |
| `remove(string $id)` | Remove an item. |
| `get(string $id)` | Fetch a `CartItem`. |
| `getItems()` | Retrieve the `CartCollection` for the instance. |
| `content()` / `toArray()` | Structured array snapshot. |
| `total()` / `subtotal()` / `savings()` | Totals with conditions applied. |
| `count()` / `countItems()` | Quantity and line item counts. |
| `addCondition(...)` / `removeCondition(...)` / `clearConditions()` | Manage cart-level conditions. |
| `addItemCondition(...)` / `removeItemCondition(...)` | Manage item-level conditions. |
| `addDiscount()` / `addTax()` / `addFee()` / `addShipping()` | Convenience wrappers for common condition patterns. |
| `getShipping()` / `removeShipping()` | Shipping helpers. |
| `setMetadata()` / `getMetadata()` / `removeMetadata()` | Manage metadata. |
| `swap($oldIdentifier, $newIdentifier, $instance = 'default')` | Transfer cart ownership. |
| `getMetrics()` | Return metrics summary (if metrics enabled). |

## Manager: `MasyukAI\Cart\CartManager`

- `getCurrentCart()`, `getCartInstance($name, $identifier = null)` – retrieve cart objects without switching global state.
- `setInstance($name)` – change the active instance globally.
- `retryWithBackoff(Closure $operation)` – execute with automatic retry.
- `recordConversion(array $context = [])`, `recordAbandonment(array $context = [])` – record analytics signals.
- Magic `__call` proxies to the active `Cart` and records metrics for tracked operations.

## Models

### `CartItem`

- `id`, `name`, `price`, `quantity`, `attributes`, `conditions`, `associatedModel` (string or object).
- Methods: `setQuantity()`, `setPrice()`, `setName()`, `addCondition()`, `removeCondition()`, `clearConditions()`, `hasCondition()`, `getConditions()`, `getPrice()`, `getSubtotal()`, `getDiscountAmount()`, `with(array $overrides)`.

### `CartCondition`

- Constructor parameters: `name`, `type`, `target`, `value`, `attributes`, `order`, `rules`.
- Methods: `apply($value)`, `getCalculatedValue($baseValue)`, `isDiscount()`, `isCharge()`, `isPercentage()`, `isDynamic()`, `shouldApply($cart, $item = null)`, `withoutRules()`, `toArray()`.

### Collections

- `CartCollection` (extends `Illuminate\Support\Collection`) – adds helpers like `filterByAttribute`, `sortByPrice`, `getStatistics`.
- `CartConditionCollection` – filters by type/target/value, calculates totals, summarises conditions.

## Services

| Service | Key Methods |
| --- | --- |
| `CartMigrationService` | `getIdentifier()`, `migrateGuestCartToUser()`, `swapGuestCartToUser()`, `swap()`, `mergeConditionsData()`. |
| `CartMetricsService` | `recordOperation()`, `recordPerformance()`, `recordConflict()`, `recordAbandonment()`, `recordConversion()`, `getMetricsSummary()`, `clearMetrics()`. |
| `CartRetryService` | `executeWithRetry()`, `executeWithSmartRetry()`, `executeWithAggressiveRetry()`, `executeWithConservativeRetry()`, `createRetryableOperation()`, `isRetryable()`. |

All services are registered in the container; resolve them via `app(Service::class)`.

## Exceptions

| Exception | Thrown When |
| --- | --- |
| `InvalidCartItemException` | Missing/invalid item attributes, prices, quantities, or exceeding limits. |
| `InvalidCartConditionException` | Malformed condition parameters. |
| `UnknownModelException` | Associated model class cannot be found. |
| `CartConflictException` | Optimistic locking detects a concurrent update. |
| `CartException` | Base class for cart-specific exceptions. |

## Console Commands

| Command | Description |
| --- | --- |
| `cart:metrics` | Display metrics. Options: `--clear`, `--json`. |
| `cart:clear-abandoned` | Remove carts older than the configured threshold. Options: `--days`, `--dry-run`, `--batch-size`. |

## Storage Interface

`MasyukAI\Cart\Storage\StorageInterface` defines the contract for custom drivers. Required methods include `has`, `forget`, `flush`, `getInstances`, `forgetIdentifier`, `getItems`, `putItems`, `getConditions`, `putConditions`, `putBoth`, `putMetadata`, `getMetadata`, and `swapIdentifier`.

Implement the full interface to avoid partial behaviour when swapping identifiers or migrating carts.
