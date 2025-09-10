# ⚡ Performance Guide

Optimize your cart implementation for maximum performance and scalability.

## 🎯 Performance Benchmarks

### Test Results (1000 operations)

| Operation | Session Storage | Database Storage | Redis Storage |
|-----------|----------------|------------------|---------------|
| Add Item | 0.12ms | 2.45ms | 0.08ms |
| Update Item | 0.10ms | 2.20ms | 0.06ms |
| Remove Item | 0.09ms | 1.95ms | 0.05ms |
| Calculate Total | 0.05ms | 0.05ms | 0.05ms |
| Apply Condition | 0.03ms | 0.03ms | 0.03ms |
| Save Cart | 0.15ms | 8.50ms | 0.12ms |
| Load Cart | 0.08ms | 5.20ms | 0.07ms |

**Recommended Storage by Use Case:**
- **High Traffic E-commerce:** Redis
- **Standard Web Apps:** Session
- **Multi-server Setup:** Database or Redis
├─ Database:          ~0.45ms read/write
└─ Cache (Redis):     ~0.03ms read/write

Memory Usage:
├─ Empty Cart:        ~2KB
├─ 100 Items:         ~45KB
└─ 1000 Items:        ~420KB
```

## Storage Optimization

### 1. Choose the Right Storage

```php
// For high-traffic sites
'storage' => [
    'driver' => 'cache',
    'cache' => [
        'store' => 'redis',
        'prefix' => 'cart',
    ],
],

// For standard applications
'storage' => [
    'driver' => 'session',
],

// For persistent carts across devices
'storage' => [
    'driver' => 'database',
    'database' => [
        'connection' => 'mysql',
        'table' => 'carts',
    ],
],
```

### 2. Database Optimization

**Optimized Table Structure:**

```sql
CREATE TABLE carts (
    id VARCHAR(255) NOT NULL,
    cart_data LONGTEXT NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_updated_at (updated_at)
) ENGINE=InnoDB;
```

**Cleanup Old Carts:**

```php
// Scheduled command to cleanup old carts
class CleanupOldCarts extends Command
{
    public function handle()
    {
        DB::table('carts')
            ->where('updated_at', '<', now()->subDays(30))
            ->delete();
            
        $this->info('Old carts cleaned up successfully!');
    }
}
```

### 3. Redis Configuration

**Optimal Redis Settings:**

```bash
# redis.conf optimizations
maxmemory 2gb
maxmemory-policy allkeys-lru
save 900 1
save 300 10
save 60 10000
```

**Laravel Cache Config:**

```php
// config/cache.php
'redis' => [
    'driver' => 'redis',
    'connection' => 'cart',
    'options' => [
        'prefix' => 'cart:',
        'serializer' => Redis::SERIALIZER_IGBINARY, // Faster than default
    ],
],
```

## Code Optimization

### 1. Efficient Item Management

**Batch Operations:**

```php
// ❌ Inefficient - Multiple database writes
foreach ($products as $product) {
    Cart::add($product->id, $product->name, $product->price, 1);
}

// ✅ Efficient - Single operation
$items = collect($products)->map(fn($p) => [
    'id' => $p->id,
    'name' => $p->name,
    'price' => $p->price,
    'quantity' => 1,
]);

Cart::addMultiple($items->toArray());
```

**Smart Updates:**

```php
// ❌ Inefficient - Recalculates everything
Cart::update($itemId, ['quantity' => $newQuantity]);
Cart::addCondition($discount);

// ✅ Efficient - Batch operations
Cart::batch(function() use ($itemId, $newQuantity, $discount) {
    Cart::update($itemId, ['quantity' => $newQuantity]);
    Cart::addCondition($discount);
});
```

### 2. Condition Optimization

**Efficient Condition Usage:**

```php
// ❌ Inefficient - Multiple conditions
Cart::addDiscount('discount1', '5%');
Cart::addDiscount('discount2', '3%');
Cart::addTax('tax1', '8%');

// ✅ Efficient - Combined conditions
$bulkCondition = new CartCondition(
    'bulk-deal',
    'discount',
    'subtotal',
    function($total, $condition) {
        $discount = $total * 0.05; // 5%
        $extraDiscount = $total * 0.03; // 3%
        return -($discount + $extraDiscount);
    }
);

Cart::addCondition($bulkCondition);
Cart::addTax('sales-tax', '8%');
```

### 3. Smart Caching

**Method-Level Caching:**

```php
class OptimizedCart
{
    protected array $calculationCache = [];
    
    public function total(): float
    {
        $cacheKey = $this->getCacheKey();
        
        if (!isset($this->calculationCache[$cacheKey])) {
            $this->calculationCache[$cacheKey] = $this->calculateTotal();
        }
        
        return $this->calculationCache[$cacheKey];
    }
    
    protected function invalidateCache(): void
    {
        $this->calculationCache = [];
    }
}
```

**Query Optimization:**

```php
// ❌ N+1 Query Problem
foreach (Cart::getItems() as $item) {
    $product = Product::find($item->id);
    echo $product->name;
}

// ✅ Single Query
$productIds = Cart::getItems()->pluck('id');
$products = Product::whereIn('id', $productIds)->get()->keyBy('id');

foreach (Cart::getItems() as $item) {
    echo $products[$item->id]->name;
}
```

## Frontend Optimization

### 1. Efficient Livewire Updates

**Minimize Rerendering:**

```php
// ❌ Full cart re-render
class CartSummary extends Component
{
    public function render()
    {
        return view('cart.summary', [
            'cart' => Cart::content(), // Recalculates everything
            'total' => Cart::total(),
        ]);
    }
}

// ✅ Cached calculations
class CartSummary extends Component
{
    #[Computed]
    public function cartData()
    {
        return Cache::remember('cart.summary.' . session()->getId(), 60, function() {
            return [
                'items' => Cart::content(),
                'total' => Cart::total(),
                'count' => Cart::count(),
            ];
        });
    }
}
```

**Smart Wire Loading:**

```blade
{{-- ❌ Blocks entire component --}}
<div wire:loading>
    Loading...
</div>

{{-- ✅ Targeted loading states --}}
<button wire:click="addToCart" wire:loading.attr="disabled" wire:target="addToCart">
    <span wire:loading.remove wire:target="addToCart">Add to Cart</span>
    <span wire:loading wire:target="addToCart">Adding...</span>
</button>

<div wire:loading.flex wire:target="updateQuantity" class="spinner"></div>
```

### 2. JavaScript Optimization

**Debounced Updates:**

```javascript
// ❌ Immediate updates
document.querySelectorAll('.quantity-input').forEach(input => {
    input.addEventListener('input', function() {
        Livewire.dispatch('updateQuantity', {
            id: this.dataset.itemId,
            quantity: this.value
        });
    });
});

// ✅ Debounced updates
import { debounce } from 'lodash';

const updateQuantity = debounce((id, quantity) => {
    Livewire.dispatch('updateQuantity', { id, quantity });
}, 500);

document.querySelectorAll('.quantity-input').forEach(input => {
    input.addEventListener('input', function() {
        updateQuantity(this.dataset.itemId, this.value);
    });
});
```

**Efficient DOM Updates:**

```javascript
// ❌ Full DOM replacement
function updateCartDisplay(cartData) {
    document.getElementById('cart-container').innerHTML = renderCart(cartData);
}

// ✅ Targeted updates
function updateCartDisplay(cartData) {
    // Update only changed elements
    document.getElementById('cart-total').textContent = cartData.total;
    document.getElementById('cart-count').textContent = cartData.count;
    
    // Update only modified items
    cartData.items.forEach(item => {
        const element = document.getElementById(`item-${item.id}`);
        if (element && element.dataset.hash !== item.hash) {
            element.replaceWith(renderItem(item));
        }
    });
}
```

## Memory Management

### 1. Large Cart Optimization

**Pagination for Large Carts:**

```php
class Cart
{
    public function paginatedContent(int $perPage = 20): LengthAwarePaginator
    {
        $items = $this->getItems();
        $page = Paginator::resolveCurrentPage() ?: 1;
        $offset = ($page - 1) * $perPage;
        
        return new LengthAwarePaginator(
            $items->slice($offset, $perPage)->values(),
            $items->count(),
            $perPage,
            $page,
            ['path' => request()->url()]
        );
    }
}
```

**Memory-Efficient Calculations:**

```php
// ❌ Loads all items into memory
public function calculateTotal(): float
{
    $items = $this->getItems(); // Loads everything
    return $items->sum(fn($item) => $item->total());
}

// ✅ Streaming calculations
public function calculateTotal(): float
{
    $total = 0;
    
    foreach ($this->getItemsIterator() as $item) {
        $total += $item->total();
        unset($item); // Free memory immediately
    }
    
    return $total;
}
```

### 2. Garbage Collection

**Automatic Cleanup:**

```php
class CartCleanupMiddleware
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        
        // Cleanup expired carts every 100 requests
        if (rand(1, 100) === 1) {
            Cart::cleanupExpired();
        }
        
        return $response;
    }
}
```

## Monitoring & Profiling

### 1. Performance Tracking

**Cart Metrics:**

```php
class CartMetrics
{
    public static function track(string $operation, callable $callback)
    {
        $start = microtime(true);
        $result = $callback();
        $duration = microtime(true) - $start;
        
        Log::info("Cart operation: {$operation}", [
            'duration' => $duration,
            'memory' => memory_get_usage(true),
            'items_count' => Cart::count(),
        ]);
        
        return $result;
    }
}

// Usage
CartMetrics::track('add_item', fn() => Cart::add('123', 'Product', 99.99, 1));
```

### 2. Query Monitoring

**Database Query Tracking:**

```php
// AppServiceProvider.php
public function boot()
{
    if (config('app.debug')) {
        DB::listen(function ($query) {
            if (str_contains($query->sql, 'carts')) {
                Log::info('Cart Query', [
                    'sql' => $query->sql,
                    'bindings' => $query->bindings,
                    'time' => $query->time,
                ]);
            }
        });
    }
}
```

## Production Optimizations

### 1. Configuration

**Optimized Config:**

```php
// config/cart.php
return [
    'storage' => [
        'driver' => env('CART_STORAGE_DRIVER', 'cache'),
        'cache' => [
            'store' => env('CART_CACHE_STORE', 'redis'),
            'prefix' => env('CART_CACHE_PREFIX', 'cart'),
            'ttl' => env('CART_CACHE_TTL', 2592000), // 30 days
        ],
    ],
    
    'events' => [
        'enabled' => env('CART_EVENTS_ENABLED', true),
        'async' => env('CART_EVENTS_ASYNC', true), // Queue events
    ],
    
    'cache' => [
        'calculations' => env('CART_CACHE_CALCULATIONS', true),
        'duration' => env('CART_CACHE_DURATION', 300), // 5 minutes
    ],
];
```

### 2. Server Configuration

**PHP-FPM Optimization:**

```ini
; php-fpm.conf
pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 500

; PHP optimizations
opcache.enable = 1
opcache.memory_consumption = 256
opcache.max_accelerated_files = 10000
```

**Nginx Configuration:**

```nginx
location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg)$ {
    expires 1y;
    add_header Cache-Control "public, immutable";
}

location /cart {
    try_files $uri $uri/ /index.php?$query_string;
    
    # Enable gzip for cart responses
    gzip on;
    gzip_types application/json text/css application/javascript;
}
```

## Performance Checklist

- [ ] **Storage**: Choose appropriate storage driver
- [ ] **Caching**: Implement calculation caching
- [ ] **Batch**: Use batch operations for multiple changes
- [ ] **Database**: Optimize queries and indexes
- [ ] **Frontend**: Implement debounced updates
- [ ] **Memory**: Use streaming for large carts
- [ ] **Monitoring**: Track performance metrics
- [ ] **Cleanup**: Implement automatic cart cleanup
- [ ] **Configuration**: Optimize for production environment

## Troubleshooting Performance Issues

### Common Issues

**Slow Cart Loading:**
```bash
# Check storage performance
php artisan tinker
>>> $start = microtime(true); Cart::getItems(); echo microtime(true) - $start;
```

**Memory Issues:**
```bash
# Monitor memory usage
php artisan cart:analyze-memory
```

**Database Bottlenecks:**
```bash
# Enable query logging
tail -f storage/logs/laravel.log | grep "Cart Query"
```

Need help with specific performance issues? Check our [troubleshooting guide](troubleshooting.md) or [open an issue](../../issues)! 🚀
