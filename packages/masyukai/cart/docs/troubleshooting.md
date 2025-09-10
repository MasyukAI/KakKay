# 🔧 Troubleshooting Guide

Common issues and solutions when working with MasyukAI Cart package.

## 🚨 Common Issues

### Installation & Setup Issues

#### Package Not Found During Installation

**Problem:** Composer can't find the package
```bash
Could not find package masyukai/cart
```

**Solutions:**
1. Check your repository configuration in `composer.json`:
```json
{
    "repositories": [
        {
            "type": "path",
            "url": "packages/masyukai/cart"
        }
    ]
}
```

2. Ensure the package is in the correct directory
3. Clear Composer cache:
```bash
composer clear-cache
composer update
```

4. Check minimum PHP version requirements:
```bash
php -v  # Should be 8.1 or higher
```

#### Service Provider Not Registered

**Problem:** Cart functions are not available
```
Class 'MasyukAI\Cart\Facades\Cart' not found
```

**Solutions:**
1. Check if service provider is auto-discovered in `composer.json`:
```json
{
    "extra": {
        "laravel": {
            "providers": [
                "MasyukAI\\Cart\\CartServiceProvider"
            ],
            "aliases": {
                "Cart": "MasyukAI\\Cart\\Facades\\Cart"
            }
        }
    }
}
```

2. Manually register in `config/app.php`:
```php
'providers' => [
    // ...
    MasyukAI\Cart\CartServiceProvider::class,
],

'aliases' => [
    // ...
    'Cart' => MasyukAI\Cart\Facades\Cart::class,
]
```

3. Clear application cache:
```bash
php artisan config:clear
php artisan cache:clear
```

---

### Runtime Issues

#### Session Storage Not Working

**Problem:** Cart data is lost between requests
```
Cart is empty after page refresh
```

**Solutions:**
1. Check session configuration in `config/session.php`:
```php
'driver' => env('SESSION_DRIVER', 'file'),
'lifetime' => env('SESSION_LIFETIME', 120),
```

2. Ensure session middleware is active:
```php
// In app/Http/Kernel.php
protected $middleware = [
    // ...
    \Illuminate\Session\Middleware\StartSession::class,
];
```

3. Check session storage permissions:
```bash
chmod -R 755 storage/framework/sessions
```

4. Verify session configuration:
```php
// Test session
session(['test' => 'value']);
dd(session('test')); // Should output 'value'
```

#### Database Storage Issues

**Problem:** Database cart storage not working
```
SQLSTATE[42S02]: Base table or view not found: 'carts'
```

**Solutions:**
1. Run migrations:
```bash
php artisan migrate
```

2. Publish and run cart migrations:
```bash
php artisan vendor:publish --tag=cart-migrations
php artisan migrate
```

3. Check database connection:
```bash
php artisan tinker
DB::connection()->getPdo();
```

4. Manually create table if needed:
```sql
CREATE TABLE carts (
    id VARCHAR(255) PRIMARY KEY,
    data TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

---

### Memory & Performance Issues

#### Cart Operations Are Slow

**Problem:** Cart operations take too long
```
Cart->add() takes several seconds
```

**Solutions:**
1. Check storage driver performance:
```php
// Time different storage drivers
$start = microtime(true);
Cart::add('test', 'Test Item', 10.00);
$time = microtime(true) - $start;
echo "Cart add took: " . $time . " seconds";
```

2. Optimize database storage:
```sql
-- Add indexes to carts table
ALTER TABLE carts ADD INDEX idx_created_at (created_at);
ALTER TABLE carts ADD INDEX idx_updated_at (updated_at);
```

3. Use Redis for better performance:
```php
// config/cart.php
'default_storage' => 'redis',
'storage' => [
    'redis' => [
        'driver' => 'redis',
        'connection' => 'cache',
        'key_prefix' => 'cart:',
    ],
],
```

4. Enable cart caching:
```php
// Cache cart content for faster access
$cartContent = Cache::remember("cart_{$userId}", 300, function () {
    return Cart::content();
});
```

#### Memory Limit Exceeded

**Problem:** Large carts cause memory issues
```
Fatal error: Allowed memory size exhausted
```

**Solutions:**
1. Implement cart item limits:
```php
// config/cart.php
'validation' => [
    'max_items' => 50,        // Limit total items
    'max_quantity' => 999,    // Limit per-item quantity
],
```

2. Use pagination for large carts:
```php
$cartItems = Cart::getItems()
    ->chunk(20)
    ->first(); // Process in smaller chunks
```

3. Optimize condition calculations:
```php
// Avoid complex conditions on large carts
if (Cart::count() > 100) {
    // Use simplified pricing logic
    Cart::clearConditions();
}
```

---

### Data Integrity Issues

#### Cart Totals Don't Add Up

**Problem:** Subtotal and total calculations are incorrect
```
Subtotal: $100.00, Tax: $8.00, Total: $95.00 (should be $108.00)
```

**Solutions:**
1. Check condition order and types:
```php
// Debug conditions
foreach (Cart::getConditions() as $condition) {
    echo sprintf(
        "Name: %s, Type: %s, Value: %s, Calculated: %s\n",
        $condition->getName(),
        $condition->getType(),
        $condition->getValue(),
        $condition->getCalculatedValue(Cart::subtotal())
    );
}
```

2. Verify condition targets:
```php
// Ensure conditions target correct values
Cart::addTax('sales-tax', '8%', ['target' => 'subtotal']);
Cart::addDiscount('coupon', '10%', ['target' => 'subtotal']);
```

3. Check for conflicting conditions:
```php
// Remove duplicate or conflicting conditions
Cart::removeCondition(['old-tax', 'old-discount']);
Cart::addTax('sales-tax', '8%');
```

4. Validate numeric precision:
```php
// Use proper decimal precision
$subtotal = round(Cart::subtotal(), 2);
$total = round(Cart::total(), 2);
```

#### Items Disappearing from Cart

**Problem:** Cart items vanish unexpectedly
```
Added 5 items, only 3 remain after reload
```

**Solutions:**
1. Check for ID conflicts:
```php
// Ensure unique item IDs
$uniqueId = $productId . '_' . $variantId . '_' . time();
Cart::add($uniqueId, $name, $price, $quantity);
```

2. Verify storage persistence:
```php
// Test storage directly
Cart::store('test_storage');
Cart::clear();
Cart::restore('test_storage');
// Check if items returned
```

3. Check for automatic cleanup:
```php
// Disable automatic cleanup during testing
// config/cart.php
'cleanup' => [
    'enabled' => false,
],
```

4. Validate item data:
```php
// Ensure all required fields are present
try {
    Cart::add($id, $name, $price, $quantity, $attributes);
} catch (InvalidArgumentException $e) {
    Log::error('Cart add failed: ' . $e->getMessage());
}
```

---

### Integration Issues

#### Livewire Component Not Updating

**Problem:** Cart changes not reflected in Livewire components
```
Added item but cart count doesn't update
```

**Solutions:**
1. Emit events after cart operations:
```php
// In your Livewire component
public function addToCart($productId)
{
    Cart::add($productId, 'Product', 10.00);
    
    // Emit event to update other components
    $this->emit('cartUpdated');
    $this->emit('refreshCartCount');
}

// Listen for events
protected $listeners = ['cartUpdated' => '$refresh'];
```

2. Use `wire:poll` for automatic updates:
```blade
<div wire:poll.5s>
    Cart Items: {{ Cart::count() }}
</div>
```

3. Refresh component manually:
```php
public function refreshCart()
{
    // Force refresh of cart data
    $this->emit('$refresh');
}
```

#### API Responses Not Formatted

**Problem:** Cart data in API responses is not user-friendly
```json
{
    "cart": {
        "items": "[object Object]"
    }
}
```

**Solutions:**
1. Use proper serialization:
```php
// In your API controller
return response()->json([
    'cart' => Cart::content()->toArray(),
    'total' => Cart::total(),
    'count' => Cart::count(),
]);
```

2. Create dedicated API resources:
```php
// CartResource.php
public function toArray($request)
{
    return [
        'items' => $this->items->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'price' => number_format($item->price, 2),
                'quantity' => $item->quantity,
                'total' => number_format($item->getPriceSum(), 2),
            ];
        }),
        'totals' => [
            'subtotal' => number_format($this->subtotal, 2),
            'total' => number_format($this->total, 2),
        ],
    ];
}
```

---

## 🧪 Debugging Tools

### Enable Debug Mode

```php
// In your controller or middleware
Cart::enableDebug();

// This will log all cart operations
Cart::add('product-1', 'Test Product', 10.00);
// Logs: "Cart Debug: Added item 'product-1' to instance 'default'"
```

### Cart Diagnostic Command

Create a custom Artisan command for cart diagnostics:

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use MasyukAI\Cart\Facades\Cart;

class CartDiagnostic extends Command
{
    protected $signature = 'cart:diagnose {instance?}';
    protected $description = 'Diagnose cart issues';

    public function handle()
    {
        $instance = $this->argument('instance') ?? 'default';
        
        $this->info("=== Cart Diagnostic Report ===");
        $this->info("Instance: {$instance}");
        
        $cart = Cart::instance($instance);
        
        $this->info("Items Count: " . $cart->count());
        $this->info("Total Quantity: " . $cart->quantity());
        $this->info("Subtotal: $" . number_format($cart->subtotal(), 2));
        $this->info("Total: $" . number_format($cart->total(), 2));
        $this->info("Is Empty: " . ($cart->isEmpty() ? 'Yes' : 'No'));
        
        $this->info("\n=== Items ===");
        foreach ($cart->getItems() as $item) {
            $this->line(sprintf(
                "ID: %s | Name: %s | Price: $%.2f | Qty: %d | Total: $%.2f",
                $item->id,
                $item->name,
                $item->price,
                $item->quantity,
                $item->getPriceSum()
            ));
        }
        
        $this->info("\n=== Conditions ===");
        foreach ($cart->getConditions() as $condition) {
            $this->line(sprintf(
                "Name: %s | Type: %s | Value: %s | Calculated: $%.2f",
                $condition->getName(),
                $condition->getType(),
                $condition->getValue(),
                $condition->getCalculatedValue($cart->subtotal())
            ));
        }
        
        $this->info("\n=== Storage Test ===");
        try {
            $cart->store('diagnostic_test');
            $this->info("✓ Storage write successful");
            
            $testCart = Cart::instance('diagnostic_temp');
            $testCart->restore('diagnostic_test');
            $this->info("✓ Storage read successful");
            
            Cart::forget('diagnostic_test');
            $this->info("✓ Storage cleanup successful");
        } catch (\Exception $e) {
            $this->error("✗ Storage error: " . $e->getMessage());
        }
    }
}
```

### Performance Profiler

```php
<?php

namespace App\Services;

class CartProfiler
{
    private static $timings = [];
    
    public static function start(string $operation): void
    {
        self::$timings[$operation] = microtime(true);
    }
    
    public static function end(string $operation): float
    {
        if (!isset(self::$timings[$operation])) {
            return 0;
        }
        
        $duration = microtime(true) - self::$timings[$operation];
        unset(self::$timings[$operation]);
        
        return $duration;
    }
    
    public static function profile(callable $callback, string $operation): mixed
    {
        self::start($operation);
        $result = $callback();
        $duration = self::end($operation);
        
        if ($duration > 0.1) { // Log operations taking > 100ms
            \Log::warning("Slow cart operation: {$operation} took {$duration}s");
        }
        
        return $result;
    }
}

// Usage:
$result = CartProfiler::profile(function () {
    return Cart::add('product-1', 'Test Product', 10.00);
}, 'cart.add');
```

---

## 📞 Getting Help

### Before Reporting Issues

1. **Check version compatibility:**
```bash
composer show masyukai/cart
php artisan --version
```

2. **Run diagnostics:**
```bash
php artisan cart:diagnose
```

3. **Enable debug logging:**
```php
// config/logging.php
'channels' => [
    'cart' => [
        'driver' => 'daily',
        'path' => storage_path('logs/cart.log'),
        'level' => 'debug',
    ],
],
```

### Creating Bug Reports

Include the following information:

1. **Environment details:**
   - PHP version
   - Laravel version
   - Cart package version
   - Storage driver used

2. **Steps to reproduce:**
   - Exact code that causes the issue
   - Expected vs actual behavior

3. **Error messages:**
   - Complete stack traces
   - Log entries

4. **Configuration:**
   - Relevant config files
   - Environment variables

### Community Resources

- **GitHub Issues:** Report bugs and feature requests
- **Documentation:** Complete guide with examples
- **Test Suite:** 562 comprehensive tests with 96.2% coverage
- **API Reference:** Complete method documentation

Remember: Most issues are configuration-related. Double-check your setup before reporting bugs!
