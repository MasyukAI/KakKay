# Security Enhancements

This document outlines the security improvements and data integrity features implemented in the MasyukAI Cart package.

## Data Integrity & Concurrency Control

### Optimistic Locking
- **Version-based concurrency control** prevents race conditions during cart updates
- Each cart record includes a `version` column that increments on every update
- Concurrent modifications throw `RuntimeException` with retry instructions
- Ensures data consistency in high-traffic scenarios

### Data Size Limits
- **Configurable limits** prevent DoS attacks through oversized payloads
- Default 1MB limit per cart data (items, conditions, metadata)
- Maximum 1000 items per cart (configurable)
- Maximum 10,000 quantity per item (configurable)
- String length limits for names and IDs (255 characters default)

### JSON Error Handling
- **Strict JSON processing** with `JSON_THROW_ON_ERROR` flag
- Graceful degradation for corrupted data
- Comprehensive error logging for debugging
- Size validation before JSON encoding

### Database Safety
- **Transaction-wrapped operations** ensure atomicity
- Proper `created_at` handling (no reset on updates)
- Environment-restricted flush operations (testing/local only)
- SQL injection prevention through parameter binding

## Configuration

Add these settings to your `config/cart.php`:

```php
'limits' => [
    // Maximum number of items in a cart
    'max_items' => env('CART_MAX_ITEMS', 1000),

    // Maximum size of cart data in bytes
    'max_data_size_bytes' => env('CART_MAX_DATA_SIZE_BYTES', 1024 * 1024), // 1MB

    // Maximum quantity per item
    'max_item_quantity' => env('CART_MAX_ITEM_QUANTITY', 10000),

    // Maximum string length for item names/attributes
    'max_string_length' => env('CART_MAX_STRING_LENGTH', 255),
],
```

## Error Handling

The package throws specific exceptions for different security violations:

- `InvalidCartItemException` - Item validation failures
- `RuntimeException` - Concurrency conflicts (optimistic lock failures)
- `InvalidArgumentException` - JSON encoding/size limit violations

## Best Practices

1. **Handle concurrency conflicts** - Wrap cart operations in try-catch blocks
2. **Monitor cart sizes** - Set appropriate limits for your use case
3. **Use transactions** - For complex multi-step cart operations
4. **Regular cleanup** - Remove abandoned carts periodically
5. **Input validation** - Validate all user input before cart operations

## Migration Notes

When upgrading, ensure your database includes the `version` column:

```sql
ALTER TABLE carts ADD COLUMN version BIGINT DEFAULT 1 NOT NULL;
CREATE INDEX idx_carts_version ON carts(version);
```

Or run the included migration:
```bash
php artisan migrate
```
