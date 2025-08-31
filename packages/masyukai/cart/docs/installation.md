# Installation Guide

Complete installation instructions for the MasyukAI Cart package.

## System Requirements

Before installing, ensure your system meets these requirements:

### Required
- **PHP**: 8.4 or higher
- **Laravel**: 12.0 or higher  
- **Composer**: 2.0 or higher

### Optional (for full features)
- **Livewire**: 3.0+ (for reactive UI components)
- **Redis**: For cache storage driver
- **Database**: MySQL 8.0+, PostgreSQL 13+, or SQLite 3.35+

### PHP Extensions
The following PHP extensions are required:
- `json` (for data serialization)
- `mbstring` (for string handling)
- `openssl` (for Laravel requirements)

## Installation Methods

### Method 1: Composer (Recommended)

```bash
# Install the package
composer require masyukai/cart

# The package will be auto-discovered by Laravel
```

### Method 2: Manual Installation

If auto-discovery is disabled, manually register the service provider:

```php
// config/app.php
'providers' => [
    // Other providers...
    MasyukAI\Cart\CartServiceProvider::class,
],

'aliases' => [
    // Other aliases...
    'Cart' => MasyukAI\Cart\Facades\Cart::class,
],
```

## Configuration

### 1. Publish Configuration File

```bash
php artisan vendor:publish --tag=cart-config
```

This creates `config/cart.php` with default settings:

```php
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Storage Driver
    |--------------------------------------------------------------------------
    */
    'storage' => [
        'driver' => env('CART_STORAGE_DRIVER', 'session'),
        
        'database' => [
            'connection' => env('CART_DB_CONNECTION'),
            'table' => env('CART_DB_TABLE', 'cart_storage'),
        ],
        
        'cache' => [
            'store' => env('CART_CACHE_STORE'),
            'prefix' => env('CART_CACHE_PREFIX', 'cart'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Events Configuration  
    |--------------------------------------------------------------------------
    */
    'events' => [
        'enabled' => env('CART_EVENTS_ENABLED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Settings
    |--------------------------------------------------------------------------
    */
    'defaults' => [
        'instance' => 'default',
        'decimals' => 2,
    ],
];
```

### 2. Environment Variables

Add these to your `.env` file:

```env
# Storage driver: session, database, cache
CART_STORAGE_DRIVER=session

# Database storage (if using database driver)
CART_DB_CONNECTION=mysql
CART_DB_TABLE=cart_storage

# Cache storage (if using cache driver)  
CART_CACHE_STORE=redis
CART_CACHE_PREFIX=cart

# Features
CART_EVENTS_ENABLED=true
```

## Storage Setup

### Session Storage (Default)

No additional setup required. Works out of the box.

**Pros:**
- Zero configuration
- Perfect for development
- Automatic cleanup on session end

**Cons:**
- Limited to single server
- Lost when session expires

### Database Storage

For persistent, scalable storage:

```bash
# Publish migrations
php artisan vendor:publish --tag=cart-migrations

# Run migrations
php artisan migrate
```

**Pros:**
- Persistent storage
- Multi-server compatible
- Advanced querying capabilities

**Cons:**
- Requires database setup
- Slightly slower than memory storage

### Cache Storage

For high-performance scenarios:

```bash
# Configure cache in config/cache.php
# No additional setup if Redis/Memcached already configured
```

**Pros:**
- Extremely fast
- Multi-server compatible
- Built-in expiration

**Cons:**
- Requires cache server
- Can lose data if cache is cleared

## Livewire Integration

If you want to use the included Livewire components:

```bash
# Install Livewire (if not already installed)
composer require livewire/livewire

# Publish Livewire components (optional)
php artisan vendor:publish --tag=cart-livewire
```

## Verification

Test your installation:

```php
// In tinker or a test route
use MasyukAI\Cart\Facades\Cart;

// Add a test item
Cart::add('test-1', 'Test Product', 99.99);

// Verify it works
dump(Cart::content());
dump(Cart::total()); // Should output 99.99
```

## Troubleshooting

### Common Issues

#### "Class 'Cart' not found"

**Solution:** Clear config cache and ensure auto-discovery is working:

```bash
php artisan config:clear
php artisan cache:clear
composer dump-autoload
```

#### Database connection errors

**Solution:** Verify database configuration:

```bash
# Test database connection
php artisan tinker
>>> DB::connection()->getPdo();
```

#### Session storage not persisting

**Solution:** Check session configuration:

```bash
# Verify session is working
php artisan tinker
>>> session()->put('test', 'value');
>>> session()->get('test'); // Should return 'value'
```

#### Cache storage errors

**Solution:** Verify cache is configured:

```bash
# Test cache connection
php artisan tinker
>>> Cache::put('test', 'value', 60);
>>> Cache::get('test'); // Should return 'value'
```

### Performance Optimization

#### For High Traffic Sites

```php
// config/cart.php
'storage' => [
    'driver' => 'cache', // Use cache for speed
    'cache' => [
        'store' => 'redis', // Use Redis for persistence
        'prefix' => 'cart',
    ],
],
```

#### For Development

```php
// config/cart.php
'storage' => [
    'driver' => 'session', // Simple session storage
],
'events' => [
    'enabled' => false, // Disable events for faster testing
],
```

## Next Steps

After successful installation:

1. **[Quick Start Guide](quick-start.md)** - Get familiar with basic usage
2. **[Configuration Guide](configuration.md)** - Customize for your needs
3. **[Basic Usage](basic-usage.md)** - Learn core functionality
4. **[Livewire Integration](livewire.md)** - Add reactive UI components

## Support

If you encounter issues during installation:

- 📖 **[Documentation](./)**
- 🐛 **[GitHub Issues](../../issues)**
- 💬 **[Community Discussions](../../discussions)**
- 📧 **Email**: support@masyukai.com
