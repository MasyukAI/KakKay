# Configuration Reference

Publish the configuration once you need to customise defaults:

```bash
php artisan vendor:publish --tag=cart-config
```

This creates `config/cart.php`. The following tables describe every option.

## Storage

| Key | Default | Purpose |
| --- | --- | --- |
| `storage` | `session` | Driver alias (`session`, `cache`, `database`). |
| `session.key` | `cart` | Root key used within the session payload. |
| `database.table` | `carts` | Table used by the database driver (also referenced by migrations). |
| `database.lock_for_update` | `false` | Apply `FOR UPDATE` locks during compare-and-swap updates. |
| `cache.prefix` | `cart` | Cache key prefix. |
| `cache.ttl` | `86400` | Time-to-live in seconds for cached items and conditions. |

See [Storage Drivers](storage.md) for implementation specifics.

## Money

| Key | Default | Purpose |
| --- | --- | --- |
| `money.default_currency` | `MYR` | Currency code used when instantiating `Money` objects. Override to align with your store’s currency. |

All totals and subtotals return `Akaunting\Money\Money` instances.

## Events & Validation

| Key | Default | Purpose |
| --- | --- | --- |
| `events` | `true` | Enables event dispatching for add/update/remove and metadata changes. |
| `strict_validation` | `true` | Enforces guardrails on IDs, names, price/quantity, and attribute sizes (recommended for production). |

## Migration

| Key | Default | Purpose |
| --- | --- | --- |
| `migration.auto_migrate_on_login` | `true` | Listen to Laravel’s login events and migrate the guest cart automatically. |
| `migration.merge_strategy` | `add_quantities` | Strategy when both guest and user carts contain the same item (`add_quantities`, `keep_highest_quantity`, `keep_user_cart`, `replace_with_guest`). |

See [Identifiers & Migration](identifiers-and-migration.md) for flow diagrams.

## Limits & Security

| Key | Default | Purpose |
| --- | --- | --- |
| `limits.max_items` | `1000` | Maximum number of line items per instance. |
| `limits.max_data_size_bytes` | `1048576` (1 MB) | Maximum combined payload size for items/conditions/metadata. |
| `limits.max_item_quantity` | `10000` | Cap for a single line item quantity. |
| `limits.max_string_length` | `255` | Guard for ID/name length. |

Breaching limits throws `InvalidCartItemException` to protect persistence layers.

## Metrics & Observability

| Key | Default | Purpose |
| --- | --- | --- |
| `metrics.enabled` | `true` | Toggle metrics collection globally. |
| `metrics.slow_operation_threshold` | `1.0` | Seconds after which an operation is logged as “slow”. |
| `metrics.track_conflicts` | `true` | Record cart conflict counts. |
| `metrics.log_channel` | `null` | Optional logging channel. If set, logs route through `Log::channel($channel)`; otherwise the default logger is used. |

Metrics power the `cart:metrics` Artisan command. More details in [Metrics & Observability](metrics-and-observability.md).

## Retry

| Key | Default | Purpose |
| --- | --- | --- |
| `retry.enabled` | `true` | Allow automatic retries for conflict-prone operations. |
| `retry.max_attempts` | `3` | Default max attempts for backoff strategies. |
| `retry.base_delay` | `100` | Base delay (ms) used when calculating exponential backoff. |
| `retry.max_delay` | `1000` | Upper bound (ms) for a single retry sleep. |
| `retry.exponential_backoff` | `true` | Indicates jitter should be built from exponential growth. |
| `retry.jitter` | `true` | Adds ±25% randomness to spread concurrent retries. |

The `CartRetryService` respects these defaults. See [Concurrency & Retry](concurrency-and-retry.md).

## Cleanup

| Key | Default | Purpose |
| --- | --- | --- |
| `cleanup.abandoned_after_days` | `7` | Age threshold for considering a cart abandoned. |
| `cleanup.auto_cleanup` | `false` | Reserved for future automation. Use `cart:clear-abandoned` today. |
| `cleanup.cleanup_batch_size` | `1000` | Batch size when processing abandoned carts. |

## Octane

| Key | Default | Purpose |
| --- | --- | --- |
| `octane.auto_register_listeners` | `true` | Registers Octane-specific listeners automatically. |
| `octane.prefer_cache_storage` | `true` | Suggests using cache storage under Octane to avoid session stickiness. |
| `octane.queue_events` | `true` | Queue cart events to avoid blocking worker responses. |
| `octane.reset_static_state` | `true` | Ensures per-request cleanliness inside long-lived workers. |

Read [Laravel Octane](octane.md) for deployment guidance.

---

After tweaking configuration remember to cache it (`php artisan config:cache`) once you are satisfied.
