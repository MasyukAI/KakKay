# Installation Guide

This guide will walk you through installing the Filament Cart Plugin with normalized cart items and conditions for enhanced performance.

## Prerequisites

- Laravel 12+
- PHP 8.4+
- Filament 4.x
- MasyukAI Cart Package

## Step 1: Install the Package

```bash
composer require masyukai/filament-cart
```

## Step 2: Publish and Run Migrations

The plugin includes migrations for normalized cart items and conditions:

```bash
# Publish migrations
php artisan vendor:publish --tag="filament-cart-migrations"

# Run migrations to create normalized tables
php artisan migrate
```

This creates the following tables:
- `cart_items` - Normalized cart items for fast searching
- `cart_conditions` - Normalized conditions (discounts, taxes, fees)

## Step 3: Publish Configuration

```bash
php artisan vendor:publish --tag="filament-cart-config"
```

## Step 4: Configure Event Synchronization

The plugin automatically registers event listeners to keep normalized models in sync with cart operations. Configure synchronization in `config/filament-cart.php`:

```php
return [
    // Enable normalized models for performance (recommended)
    'enable_normalized_models' => true,
    
    // Queue synchronization for better performance
    'synchronization' => [
        'queue_sync' => true,
        'queue_connection' => 'default',
        'queue_name' => 'cart-sync',
        'retry_failed_jobs' => true,
        'max_retry_attempts' => 3,
    ],
    
    // Enable/disable resources
    'resources' => [
        'enable_cart_items' => true,
        'enable_cart_conditions' => true,
    ],
];
```

## Step 5: Configure Queue Worker (Recommended)

For optimal performance, set up a queue worker to handle cart synchronization:

```bash
# Start queue worker for cart synchronization
php artisan queue:work --queue=cart-sync
```

Or add to your supervisor configuration:

```ini
[program:cart-sync-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/your/application/artisan queue:work --queue=cart-sync --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=2
```

## Step 6: Verify Installation

1. **Check Resources**: Visit your Filament admin panel and verify the following resources appear:
   - Carts
   - Cart Items
   - Cart Conditions

2. **Test Synchronization**: Add items to a cart and verify they appear in the Cart Items resource.

3. **Run Tests**: 
   ```bash
   cd packages/masyukai/filament-cart
   vendor/bin/pest
   ```

## Configuration Options

### Navigation

```php
'navigation_group' => 'E-commerce',
'navigation_icon' => 'heroicon-o-shopping-cart',
```

### Cart Instances

```php
'instances' => [
    'default' => 'Default',
    'wishlist' => 'Wishlist',
    'comparison' => 'Comparison',
    'quote' => 'Quote',
    'bulk' => 'Bulk Order',
    'subscription' => 'Subscription',
],
```

### Performance Settings

```php
'polling_interval' => 30, // Real-time updates interval
'per_page_options' => [10, 25, 50, 100],
```

## Troubleshooting

### Common Issues

1. **Migrations not found**:
   ```bash
   php artisan vendor:publish --tag="filament-cart-migrations" --force
   ```

2. **Queue jobs not processing**:
   - Ensure queue worker is running
   - Check queue configuration
   - Verify `queue_sync` is enabled

3. **Resources not appearing**:
   - Check resource configuration in `config/filament-cart.php`
   - Clear cache: `php artisan config:clear`

4. **Synchronization issues**:
   - Check logs for event processing errors
   - Verify cart package events are firing
   - Test with `queue_sync` disabled for debugging

### Performance Optimization

1. **Database Indexes**: The migrations include optimized indexes for fast queries.

2. **Queue Processing**: Use dedicated queue workers for cart synchronization.

3. **Cache Configuration**: Ensure proper cache driver for session storage.

## Next Steps

- Review the [README](README.md) for usage examples
- Explore advanced filtering and search capabilities
- Set up analytics queries using normalized models
- Customize resources and extend functionality

## Support

For issues and questions:
- Check the test suite for usage examples
- Review event listeners for synchronization logic
- Examine the normalized models for query capabilities