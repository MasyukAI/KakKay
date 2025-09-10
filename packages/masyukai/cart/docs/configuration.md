# Configuration

The MasyukAI Cart package provides extensive configuration options to customize its behavior according to your application's needs.

## Publishing Configuration

First, publish the configuration file:

```bash
php artisan vendor:publish --tag=cart-config
```

This will create a `config/cart.php` file in your Laravel application.

## Configuration Options

### Storage Driver

Choose your preferred storage method:

```php
// config/cart.php
'storage' => env('CART_STORAGE_DRIVER', 'session'),
```

**Available Drivers:**
- `session` - Store cart data in user sessions (default)
- `database` - Store cart data in database tables
- `cache` - Store cart data in your configured cache driver

### Session Storage Configuration

```php
'session' => [
    'key' => env('CART_SESSION_KEY', 'masyukai_cart'),
],
```

### Database Storage Configuration

```php
'database' => [
    'connection' => env('CART_DB_CONNECTION', null),
    'table' => env('CART_DB_TABLE', 'carts'),
],
```

To use database storage, publish and run the migrations:

```bash
php artisan vendor:publish --tag=cart-migrations
php artisan migrate
```

### Cache Storage Configuration

```php
'cache' => [
    'store' => env('CART_CACHE_STORE', null),
    'prefix' => env('CART_CACHE_PREFIX', 'cart'),
    'ttl' => env('CART_CACHE_TTL', 86400), // 24 hours
],
```

## Default Instance

Set the default cart instance name:

```php
'default_instance' => env('CART_DEFAULT_INSTANCE', 'default'),
```

## Number Formatting

Configure how numbers are displayed:

```php
'format_numbers' => env('CART_FORMAT_NUMBERS', false),
'decimals' => env('CART_DECIMALS', 2),
'decimal_point' => env('CART_DECIMAL_POINT', '.'),
'thousands_separator' => env('CART_THOUSANDS_SEPARATOR', ','),
```

## Events

Enable or disable cart events:

```php
'events' => env('CART_EVENTS_ENABLED', true),
```

When enabled, the cart will dispatch events for various operations (add, update, remove, etc.).

## Validation

Configure validation behavior:

```php
'strict_validation' => env('CART_STRICT_VALIDATION', true),
```

When enabled, the cart performs strict validation on all operations.

## Tax Configuration

Configure default tax settings:

```php
'tax' => [
    'enabled' => env('CART_TAX_ENABLED', false),
    'rate' => env('CART_TAX_RATE', 0.1), // 10%
    'inclusive' => env('CART_TAX_INCLUSIVE', false),
],
```

## Currency Configuration

Set default currency formatting:

```php
'currency' => [
    'code' => env('CART_CURRENCY_CODE', 'USD'),
    'symbol' => env('CART_CURRENCY_SYMBOL', '$'),
    'position' => env('CART_CURRENCY_POSITION', 'before'), // before, after
],
```

## Auto-destroy Empty Carts

Automatically clean up empty carts:

```php
'destroy_empty_after' => env('CART_DESTROY_EMPTY_AFTER', null), // minutes
```

Set to `null` to disable auto-cleanup.

## Environment Variables

You can configure most options using environment variables in your `.env` file:

```env
# Storage
CART_STORAGE_DRIVER=session
CART_SESSION_KEY=masyukai_cart

# Database storage
CART_DB_CONNECTION=mysql
CART_DB_TABLE=carts

# Cache storage  
CART_CACHE_STORE=redis
CART_CACHE_PREFIX=cart
CART_CACHE_TTL=86400

# General
CART_DEFAULT_INSTANCE=default
CART_EVENTS_ENABLED=true
CART_STRICT_VALIDATION=true

# Formatting
CART_FORMAT_NUMBERS=false
CART_DECIMALS=2
CART_DECIMAL_POINT=.
CART_THOUSANDS_SEPARATOR=,

# Tax
CART_TAX_ENABLED=true
CART_TAX_RATE=0.08
CART_TAX_INCLUSIVE=false

# Currency
CART_CURRENCY_CODE=USD
CART_CURRENCY_SYMBOL=$
CART_CURRENCY_POSITION=before

# Cleanup
CART_DESTROY_EMPTY_AFTER=1440
```

## Advanced Configuration

### Custom Storage Driver

You can create custom storage drivers by implementing the `StorageInterface`:

```php
use MasyukAI\Cart\Storage\StorageInterface;

class CustomStorage implements StorageInterface
{
    public function get(string $key): mixed
    {
        // Your implementation
    }
    
    public function put(string $key, mixed $value): void
    {
        // Your implementation  
    }
    
    // Implement other required methods...
}
```

Register your custom driver in a service provider:

```php
$this->app->bind('cart.storage.custom', function ($app) {
    return new CustomStorage();
});
```

### Runtime Configuration

You can override configuration at runtime:

```php
use MasyukAI\Cart\Cart;
use MasyukAI\Cart\Storage\SessionStorage;

$storage = new SessionStorage(session());
$cart = new Cart(
    storage: $storage,
    events: app('events'),
    instanceName: 'custom',
    eventsEnabled: false,
    config: [
        'decimals' => 3,
        'strict_validation' => false,
    ]
);
```
