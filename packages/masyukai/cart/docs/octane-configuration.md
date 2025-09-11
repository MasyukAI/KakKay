# Laravel Octane Configuration for Cart Package

This file provides recommended Octane configuration for optimal cart package performance.

## Add to your `config/octane.php`:

```php
<?php

return [
    // ... existing configuration

    'listeners' => [
        // ... existing listeners
        
        \Laravel\Octane\Events\OperationTerminated::class => [
            // ... existing listeners
            \MasyukAI\Cart\Listeners\ResetCartState::class,
        ],
    ],

    'warm' => [
        ...Octane::defaultServicesToWarm(),
        
        // Warm cart services for better performance
        'cart',
        \MasyukAI\Cart\Services\CartMigrationService::class,
        \MasyukAI\Cart\Services\PriceFormatterService::class,
    ],

    'flush' => [
        // Add cart services that should be flushed if needed
        // 'cart', // Uncomment if you experience state issues
    ],
];
```

## Environment Variables for Octane

Add these to your `.env` file:

```env
# Use cache storage for better Octane performance
CART_STORAGE_DRIVER=cache
CACHE_STORE=redis

# Octane-specific cart settings
CART_OCTANE_AUTO_LISTENERS=true
CART_OCTANE_PREFER_CACHE=true
CART_OCTANE_QUEUE_EVENTS=true
CART_OCTANE_RESET_STATE=true

# Redis cache configuration for Octane
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_CACHE_DB=1
```

## Queue Configuration

For optimal performance, ensure your queue is properly configured:

```env
QUEUE_CONNECTION=redis
```

And in `config/queue.php`:

```php
'redis' => [
    'driver' => 'redis',
    'connection' => env('REDIS_QUEUE_CONNECTION', 'default'),
    'queue' => env('REDIS_QUEUE', 'default'),
    'retry_after' => 90,
    'block_for' => null,
    'after_commit' => false,
],
```

## Cache Configuration

Recommended cache configuration in `config/cache.php`:

```php
'redis' => [
    'driver' => 'redis',
    'connection' => env('REDIS_CACHE_CONNECTION', 'cache'),
    'lock_connection' => env('REDIS_CACHE_LOCK_CONNECTION', 'default'),
],
```

## Performance Tips

1. **Use Redis**: Both for cache and queue for best performance
2. **Queue Events**: Cart events should be queued to avoid blocking requests
3. **Monitor Memory**: Use `php artisan octane:status` to monitor worker memory
4. **Worker Limits**: Set appropriate worker limits in Octane config
5. **Database Connections**: Use persistent connections for database storage

## Testing Octane Compatibility

Run these commands to test:

```bash
# Start Octane
php artisan octane:start --workers=4

# Run cart tests with Octane
RUN_STRESS_TESTS=true php vendor/bin/pest

# Monitor performance
php artisan octane:status
```

## Troubleshooting

### Memory Leaks
If you notice memory growth:
1. Ensure `CART_OCTANE_RESET_STATE=true`
2. Add cart services to the flush array in octane config
3. Monitor with `php artisan octane:status`

### State Issues
If you experience state bleeding between requests:
1. Verify the ResetCartState listener is registered
2. Check that static properties are properly reset
3. Use cache storage instead of session storage

### Performance Issues
For performance problems:
1. Use Redis for cache and queue
2. Enable event queuing: `CART_OCTANE_QUEUE_EVENTS=true`
3. Monitor database connection pooling
4. Consider increasing worker count
