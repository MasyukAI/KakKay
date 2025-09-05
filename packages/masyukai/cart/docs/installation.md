# 📦 Installation Guide

Complete installation guide for MasyukAI Cart package with all configuration options and deployment scenarios.

## 📋 Prerequisites

### System Requirements

| **Component** | **Minimum** | **Recommended** | **Notes** |
|---------------|-------------|-----------------|-----------|
| **PHP** | 8.4.0 | 8.4.10+ | Modern PHP features required |
| **Laravel** | 12.0 | 12.x.x | Latest stable recommended |
| **Livewire** | 3.0 | 3.x.x | For UI components |
| **Memory** | 64MB | 128MB+ | For large cart operations |

### Required PHP Extensions
```bash
# Standard extensions (usually included)
php -m | grep -E "(json|mbstring|openssl|pdo)"
```

---

## 🚀 Installation Methods

### Method 1: Composer (Recommended)

```bash
# Install the package
composer require masyukai/cart

# Verify installation
composer show masyukai/cart
```

### Method 2: Development Installation

```bash
# For contributing or development
git clone https://github.com/masyukai/cart.git
cd cart
composer install
./vendor/bin/pest  # Run tests
```

---

## ⚙️ Configuration

### Basic Setup (Session Storage)

No additional configuration needed! The package works out-of-the-box with session storage:

```php
use MasyukAI\Cart\Facades\Cart;

Cart::add('product-1', 'Test Product', 99.99);
echo Cart::total(); // 99.99
```

### Advanced Configuration

#### 1. Publish Configuration File

```bash
php artisan vendor:publish --tag=cart-config
```

This creates `config/cart.php`:

```php
<?php

return [
    // Default cart instance name
    'default_instance' => 'main',
    
    // Storage configuration
    'storage' => [
        'driver' => env('CART_STORAGE_DRIVER', 'session'),
        
        'session' => [
            'key' => 'shopping_cart',
        ],
        
        'database' => [
            'connection' => env('CART_DB_CONNECTION'),
            'table' => 'cart_storage',
        ],
        
        'cache' => [
            'store' => env('CART_CACHE_STORE'),
            'prefix' => 'cart',
            'ttl' => 86400, // 24 hours
        ],
    ],
    
    // Cart behavior
    'cart' => [
        'decimals' => 2,
        'decimal_point' => '.',
        'thousands_separator' => ',',
        'format_numbers' => true,
        'throw_exceptions' => true,
    ],
    
    // Event system
    'events' => [
        'enabled' => true,
        'listeners' => [],
    ],
    
    // Migration settings
    'migration' => [
        'auto_migrate_on_login' => true,
        'merge_strategy' => 'add_quantities',
        'clear_guest_cart_after_merge' => true,
    ],
    
    // Validation rules
    'validation' => [
        'item_id_max_length' => 255,
        'item_name_max_length' => 255,
        'max_quantity_per_item' => 9999,
        'max_items_in_cart' => 100,
        'min_price' => 0.01,
        'max_price' => 999999.99,
    ],
];
```

#### 2. Environment Configuration

Add to your `.env` file:

```env
# Cart Storage Driver
CART_STORAGE_DRIVER=session

# Database Storage (if using database driver)
CART_DB_CONNECTION=mysql

# Cache Storage (if using cache driver) 
CART_CACHE_STORE=redis
```

---

## 🗄️ Storage Drivers Setup

### Session Storage (Default)

**Best for:** Development, small applications

```php
// config/cart.php
'storage' => [
    'driver' => 'session',
    'session' => [
        'key' => 'shopping_cart',
    ],
],
```

**Pros:** Simple, no additional setup
**Cons:** Not persistent across browser sessions

### Database Storage

**Best for:** Production, persistent carts

#### Step 1: Publish and Run Migrations

```bash
php artisan vendor:publish --tag=cart-migrations
php artisan migrate
```

#### Step 2: Update Configuration

```php
// config/cart.php
'storage' => [
    'driver' => 'database',
    'database' => [
        'connection' => 'mysql', // or your preferred connection
        'table' => 'cart_storage',
    ],
],
```

#### Step 3: Database Schema

The migration creates this table:

```sql
CREATE TABLE `cart_storage` (
    `identifier` varchar(255) NOT NULL,
    `instance` varchar(255) NOT NULL,
    `items` longtext,
    `conditions` longtext,
    `metadata` longtext,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`identifier`, `instance`),
    KEY `cart_storage_identifier_index` (`identifier`),
    KEY `cart_storage_instance_index` (`instance`)
);
```

**Pros:** Persistent, scalable, backup-friendly
**Cons:** Requires database setup

### Cache Storage

**Best for:** High performance, Redis environments

#### Step 1: Configure Cache Store

```php
// config/cache.php
'stores' => [
    'redis' => [
        'driver' => 'redis',
        'connection' => 'cache',
        'lock_connection' => 'default',
    ],
],
```

#### Step 2: Update Cart Configuration

```php
// config/cart.php
'storage' => [
    'driver' => 'cache',
    'cache' => [
        'store' => 'redis',
        'prefix' => 'cart',
        'ttl' => 86400, // 24 hours
    ],
],
```

**Pros:** Very fast, automatically expires
**Cons:** Data loss on cache clear, requires Redis

---

## 🎨 Livewire Components Setup

### Publish Views (Optional)

```bash
php artisan vendor:publish --tag=cart-views
```

This publishes views to `resources/views/vendor/cart/`:

```
resources/views/vendor/cart/
├── livewire/
│   ├── add-to-cart.blade.php
│   ├── cart-summary.blade.php
│   └── cart-table.blade.php
└── demo/
    ├── index.blade.php
    └── cart.blade.php
```

### Include Livewire Styles/Scripts

In your main layout:

```blade
<!DOCTYPE html>
<html>
<head>
    @livewireStyles
</head>
<body>
    <!-- Your content -->
    
    @livewireScripts
</body>
</html>
```

### Basic Component Usage

```blade
<!-- Add to cart button -->
<livewire:add-to-cart 
    product-id="123" 
    product-name="iPhone 15" 
    product-price="999.99" 
/>

<!-- Cart summary -->
<livewire:cart-summary />

<!-- Full cart table -->
<livewire:cart-table />
```

---

## 🚀 Deployment

### Production Checklist

#### Environment Configuration

```env
# Production settings
CART_STORAGE_DRIVER=database
CART_DB_CONNECTION=mysql

# Cache settings for performance
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
```

#### Optimization Commands

```bash
# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
php artisan migrate --force

# Clear development caches
php artisan cache:clear
php artisan config:clear
```

#### Database Optimization

```sql
-- Add indexes for better performance
CREATE INDEX idx_cart_identifier_updated ON cart_storage(identifier, updated_at);
CREATE INDEX idx_cart_created_at ON cart_storage(created_at);

-- For cleanup queries
CREATE INDEX idx_cart_storage_updated_at ON cart_storage(updated_at);
```

---

## ✅ Verification

### Test Installation

```bash
# Run basic functionality test
php artisan tinker
```

```php
// In tinker
use MasyukAI\Cart\Facades\Cart;

Cart::add('test-1', 'Test Product', 99.99);
Cart::count(); // Should return 1
Cart::total(); // Should return 99.99
```

### Run Test Suite

```bash
# Run all tests
./vendor/bin/pest

# Run specific installation tests  
./vendor/bin/pest tests/Feature/InstallationTest.php
```

### Check Configuration

```php
// Check current configuration
php artisan tinker

config('cart.storage.driver'); // Current storage driver
config('cart.default_instance'); // Default instance name
```

---

## 🔧 Troubleshooting

### Common Issues

#### 1. **Class 'Cart' not found**

**Solution:** Ensure auto-discovery is working:

```bash
composer dump-autoload
php artisan package:discover
```

#### 2. **Storage driver not found**

**Solution:** Check configuration and ensure driver is supported:

```php
// config/cart.php
'storage' => [
    'driver' => 'session', // Use valid driver: session, database, cache
],
```

#### 3. **Database migration issues**

**Solution:** Check database connection and permissions:

```bash
php artisan migrate:status
php artisan migrate --force
```

#### 4. **Cache store not found**

**Solution:** Ensure cache store exists in config:

```php
// config/cache.php
'stores' => [
    'redis' => [
        'driver' => 'redis',
        // ... configuration
    ],
],
```

### Debug Mode

Enable debug logging:

```php
// config/cart.php
'debug' => env('CART_DEBUG', false),
```

View logs:

```bash
tail -f storage/logs/laravel.log | grep cart
```

---

## 📚 Next Steps

After installation:

1. **[🚀 Quick Start Guide](quick-start.md)** - Get up and running in 5 minutes
2. **[🏃‍♂️ Basic Usage](basic-usage.md)** - Learn fundamental operations  
3. **[🎨 Livewire Components](livewire.md)** - Add reactive UI components
4. **[⚙️ Configuration](configuration.md)** - Customize behavior
5. **[📖 API Reference](api-reference.md)** - Complete method documentation

---

## 🤝 Support

Need help with installation?

- 📖 **Documentation:** [Complete guides](../README.md#documentation)
- 🐛 **Issues:** [Report installation problems](../../issues/new)
- 💬 **Community:** [Get help from others](../../discussions)
- 📧 **Email:** support@masyukai.com
