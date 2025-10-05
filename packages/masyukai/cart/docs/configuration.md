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
| `money.default_currency` | `MYR` | Currency code used when instantiating `Money` objects. Override to align with your store's currency. |

All totals and subtotals return `Akaunting\Money\Money` instances.

## Events & Validation

| Key | Default | Purpose |
| --- | --- | --- |
| `events` | `true` | Enables event dispatching for add/update/remove and metadata changes. |

## Migration

| Key | Default | Purpose |
| --- | --- | --- |
| `migration.auto_migrate_on_login` | `true` | Listen to Laravel's login events and migrate the guest cart automatically. |
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

---

After tweaking configuration remember to cache it (`php artisan config:cache`) once you are satisfied.
