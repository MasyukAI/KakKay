# Storage Drivers

The MasyukAI Cart package supports multiple storage drivers to persist cart data according to your application's needs.

## Available Drivers

### Session Storage (Default)

Stores cart data in user sessions. Best for guest users and temporary carts.

**Pros:**
- Simple setup
- Works out of the box
- Good for guest users
- Automatic cleanup on session expiry

**Cons:**
- Lost when session expires
- Not suitable for long-term storage
- Limited by session storage size

**Configuration:**
```php
'storage' => 'session',
'session' => [
    'key' => 'masyukai_cart',
],
```

### Cache Storage

Stores cart data in your configured cache system (Redis, Memcached, etc.).

**Pros:**
- Fast access
- Can be shared across multiple servers
- TTL-based expiration
- Good performance for high-traffic sites

**Cons:**
- May be evicted under memory pressure
- Requires cache configuration
- Not permanent storage

**Configuration:**
```php
'storage' => 'cache',
'cache' => [
    'store' => 'redis',        // Use specific cache store
    'prefix' => 'cart',        // Key prefix
    'ttl' => 86400,           // 24 hours
],
```

### Database Storage

Stores cart data in database tables. Best for persistent carts and user accounts.

**Pros:**
- Permanent storage
- Can survive server restarts
- Queryable data
- Good for analytics

**Cons:**
- Slower than memory-based storage
- Requires database setup
- May need cleanup jobs

**Configuration:**
```php
'storage' => 'database',
'database' => [
    'connection' => 'mysql',     // Database connection
    'table' => 'carts',   // Table name
],
```

## Setting Up Storage Drivers

### Session Storage Setup

Session storage works out of the box with Laravel's default session configuration:

```php
// config/cart.php
'storage' => 'session',
```

### Cache Storage Setup

1. Configure your cache driver in `config/cache.php`
2. Set cart configuration:

```php
// config/cart.php
'storage' => 'cache',
'cache' => [
    'store' => 'redis',
    'prefix' => 'cart',
    'ttl' => 86400, // 24 hours
],
```

3. Use in environment:

```env
CART_STORAGE_DRIVER=cache
CART_CACHE_STORE=redis
CART_CACHE_TTL=86400
```

### Database Storage Setup

1. Publish and run migrations:

```bash
php artisan vendor:publish --tag=cart-migrations
php artisan migrate
```

2. Configure database storage:

```php
// config/cart.php
'storage' => 'database',
'database' => [
    'connection' => null, // Use default connection
    'table' => 'carts',
],
```

3. Use in environment:

```env
CART_STORAGE_DRIVER=database
CART_DB_CONNECTION=mysql
CART_DB_TABLE=carts
```

## Database Schema

The database storage uses the following schema:

```php
Schema::create('carts', function (Blueprint $table) {
    $table->id();
    $table->string('key')->unique();
    $table->longText('value');
    $table->timestamp('expires_at')->nullable();
    $table->timestamps();
    
    $table->index(['key', 'expires_at']);
});
```

## Custom Storage Drivers

You can create custom storage drivers by implementing the `StorageInterface`:

```php
<?php

namespace App\Cart\Storage;

use MasyukAI\Cart\Storage\StorageInterface;

class CustomStorage implements StorageInterface
{
    public function get(string $key): mixed
    {
        // Retrieve data by key
        return $this->retrieveFromCustomSource($key);
    }
    
    public function put(string $key, mixed $value): void
    {
        // Store data with key
        $this->storeInCustomSource($key, $value);
    }
    
    public function has(string $key): bool
    {
        // Check if key exists
        return $this->customSourceHas($key);
    }
    
    public function forget(string $key): void
    {
        // Remove data by key
        $this->removeFromCustomSource($key);
    }
    
    public function flush(): void
    {
        // Clear all data
        $this->clearCustomSource();
    }
    
    private function retrieveFromCustomSource(string $key): mixed
    {
        // Your custom retrieval logic
    }
    
    private function storeInCustomSource(string $key, mixed $value): void
    {
        // Your custom storage logic
    }
    
    // Implement other private methods...
}
```

Register your custom driver:

```php
// In a service provider
public function register(): void
{
    $this->app->bind('cart.storage.custom', function ($app) {
        return new CustomStorage(
            // Your dependencies
        );
    });
}
```

Use your custom driver:

```php
// config/cart.php
'storage' => 'custom',
```

## Storage Performance Considerations

### Session Storage

- **Best for**: Guest users, temporary carts, low-traffic sites
- **Performance**: Good for small carts, can be slow for large sessions
- **Scalability**: Limited by session storage

### Cache Storage

- **Best for**: High-traffic sites, temporary carts, performance-critical applications
- **Performance**: Excellent read/write performance
- **Scalability**: Excellent with proper cache infrastructure

### Database Storage

- **Best for**: Persistent carts, user accounts, analytics requirements
- **Performance**: Good with proper indexing, slower than cache
- **Scalability**: Good with database scaling strategies

## Data Persistence

### Session Storage
```php
// Data persists for session lifetime
// Automatically cleaned up when session expires
Cart::add('product-1', 'Item', 99.99);
// Available until session ends
```

### Cache Storage
```php
// Data persists for TTL duration
// May be evicted under memory pressure
Cart::add('product-1', 'Item', 99.99);
// Available for configured TTL (e.g., 24 hours)
```

### Database Storage
```php
// Data persists permanently
// Manual cleanup required
Cart::add('product-1', 'Item', 99.99);
// Available until manually removed or expired
```

## Multi-Instance Support

All storage drivers support multiple cart instances:

```php
// Different instances use separate storage keys
$mainCart = Cart::instance('default');
$wishlist = Cart::instance('wishlist');  
$comparison = Cart::instance('comparison');

// Each instance maintains separate data
$mainCart->add('product-1', 'Main Item', 99.99);
$wishlist->add('product-2', 'Wish Item', 149.99);
// Stored separately in chosen storage driver
```

## Storage Keys

Storage keys are automatically generated:

```php
// Session storage keys
cart.default         // Default instance
cart.wishlist        // Wishlist instance

// Cart conditions
cart_conditions.default
cart_conditions.wishlist

// Cache storage keys (with prefix)
cart:cart.default
cart:cart_conditions.default

// Database storage keys
cart.default
cart_conditions.default
```

## Cleanup and Maintenance

### Session Storage
```php
// Automatic cleanup on session expiry
// No manual maintenance required
```

### Cache Storage
```php
// Automatic cleanup based on TTL
// Optional: Manual cleanup for expired entries
```

### Database Storage
```php
// Manual cleanup required
// Create a scheduled job for maintenance

// Cleanup expired carts
php artisan schedule:run

// In your scheduled job
DB::table('carts')
  ->where('expires_at', '<', now())
  ->delete();
```

## Storage Migration

You can migrate between storage drivers:

```php
// Export from current storage
$cartData = Cart::toArray();

// Switch storage driver
config(['cart.storage' => 'database']);

// Import to new storage
Cart::fromArray($cartData);
```

## Best Practices

1. **Choose appropriate driver**: Match storage driver to your use case
2. **Monitor performance**: Track storage operation performance
3. **Plan for cleanup**: Implement cleanup strategies for persistent storage
4. **Test thoroughly**: Test storage operations under load
5. **Backup important data**: Ensure cart data is backed up if critical
6. **Handle failures gracefully**: Implement fallbacks for storage failures

## Environment-Specific Configuration

### Development
```env
CART_STORAGE_DRIVER=session
```

### Staging
```env
CART_STORAGE_DRIVER=cache
CART_CACHE_STORE=redis
```

### Production
```env
CART_STORAGE_DRIVER=database
CART_DB_CONNECTION=mysql
```

## Troubleshooting

### Session Storage Issues
- Check session configuration
- Verify session storage permissions
- Ensure sessions are working properly

### Cache Storage Issues
- Verify cache driver configuration
- Check cache server connectivity
- Monitor cache memory usage

### Database Storage Issues
- Verify database connection
- Check table exists and permissions
- Monitor database performance

## Next Steps

- Learn about [Events](events.md) for storage-related operations
- Explore [Configuration](configuration.md) for advanced settings
- Check out [API Reference](api-reference.md) for storage methods
