# Security Best Practices

Secure your cart implementation against common vulnerabilities.

## Security Overview

The MasyukAI Cart package implements multiple security layers:

- 🔒 **Input Validation**: All data is validated and sanitized
- 🛡️ **CSRF Protection**: Laravel's built-in CSRF protection
- 🔐 **Session Security**: Secure session handling and storage
- ⚡ **Rate Limiting**: Protection against abuse
- 🧪 **XSS Prevention**: Output escaping and content filtering
- 🔑 **Authorization**: User-based cart access control

## Input Validation & Sanitization

### 1. Price Validation

**Always validate prices server-side:**

```php
class CartController extends Controller
{
    public function addItem(AddToCartRequest $request)
    {
        // ✅ Server-side price validation
        $product = Product::findOrFail($request->product_id);
        
        // Never trust client-side prices
        Cart::add(
            $product->id,
            $product->name,
            $product->price, // Use database price, not request price
            $request->quantity
        );
    }
}
```

**Form Request Validation:**

```php
class AddToCartRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'product_id' => 'required|integer|exists:products,id',
            'quantity' => 'required|integer|min:1|max:999',
            'attributes' => 'nullable|array|max:10',
            'attributes.*' => 'string|max:255',
        ];
    }
    
    public function prepareForValidation(): void
    {
        // Sanitize quantity
        $this->merge([
            'quantity' => (int) $this->quantity,
        ]);
    }
}
```

### 2. Quantity Limits

**Prevent quantity manipulation:**

```php
class Cart
{
    protected const MAX_QUANTITY_PER_ITEM = 999;
    protected const MAX_TOTAL_ITEMS = 100;
    
    public function add(string $id, string $name, float $price, int $quantity): CartItem
    {
        // Validate quantity limits
        if ($quantity > self::MAX_QUANTITY_PER_ITEM) {
            throw new InvalidQuantityException("Quantity cannot exceed " . self::MAX_QUANTITY_PER_ITEM);
        }
        
        if ($this->countItems() >= self::MAX_TOTAL_ITEMS) {
            throw new CartLimitException("Cannot add more than " . self::MAX_TOTAL_ITEMS . " items");
        }
        
        // Validate price is positive
        if ($price < 0) {
            throw new InvalidPriceException("Price cannot be negative");
        }
        
        return $this->addItem($id, $name, $price, $quantity);
    }
}
```

### 3. Attribute Sanitization

**Sanitize user attributes:**

```php
class CartItem
{
    public function setAttribute(string $key, mixed $value): void
    {
        // Whitelist allowed attribute keys
        $allowedKeys = ['color', 'size', 'variant', 'gift_message'];
        
        if (!in_array($key, $allowedKeys)) {
            throw new InvalidAttributeException("Attribute '{$key}' is not allowed");
        }
        
        // Sanitize value
        $sanitizedValue = match($key) {
            'gift_message' => strip_tags($value),
            'color', 'size', 'variant' => preg_replace('/[^a-zA-Z0-9-_]/', '', $value),
            default => $value,
        };
        
        $this->attributes[$key] = $sanitizedValue;
    }
}
```

## Access Control & Authorization

### 1. User-Based Cart Isolation

**Secure cart instance access:**

```php
class SecureCart extends Cart
{
    public function instanceId(): string
    {
        // Combine user ID with session ID for security
        $userId = auth()->id() ?? 'guest';
        $sessionId = session()->getId();
        
        return hash('sha256', $userId . '|' . $sessionId . '|' . config('app.key'));
    }
    
    public function switchInstance(string $instance): void
    {
        // Only allow switching to owned instances
        if (!$this->canAccessInstance($instance)) {
            throw new UnauthorizedAccessException("Access denied to cart instance");
        }
        
        parent::switchInstance($instance);
    }
    
    protected function canAccessInstance(string $instance): bool
    {
        // Verify user owns this instance
        return str_starts_with($instance, auth()->id() ?? 'guest');
    }
}
```

### 2. Guest Cart Security

**Secure guest cart handling:**

```php
class GuestCartMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Rate limit guest operations
        $key = 'cart_operations:' . $request->ip();
        
        if (RateLimiter::tooManyAttempts($key, 100)) {
            throw new TooManyRequestsException('Too many cart operations');
        }
        
        RateLimiter::hit($key, 3600); // 1 hour window
        
        return $next($request);
    }
}
```

### 3. Admin Cart Access

**Secure admin cart management:**

```php
class AdminCartController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }
    
    public function viewUserCart(User $user)
    {
        // Log admin access
        Log::info('Admin cart access', [
            'admin_id' => auth()->id(),
            'user_id' => $user->id,
            'ip' => request()->ip(),
        ]);
        
        $cart = Cart::instance("user_{$user->id}");
        
        return view('admin.cart.view', compact('cart', 'user'));
    }
}
```

## Session Security

### 1. Session Configuration

**Secure session settings:**

```php
// config/session.php
return [
    'lifetime' => 120, // 2 hours
    'expire_on_close' => false,
    'encrypt' => true,
    'secure' => env('SESSION_SECURE_COOKIE', true),
    'http_only' => true,
    'same_site' => 'strict',
];
```

### 2. Session Regeneration

**Prevent session fixation:**

```php
class CartAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Regenerate session on login
        if ($request->user() && !session()->has('cart_user_verified')) {
            session()->regenerate();
            session()->put('cart_user_verified', true);
        }
        
        return $next($request);
    }
}
```

### 3. Cross-Device Security

**Secure cart transfer between devices:**

```php
class CartTransferService
{
    public function transferToAuthenticatedUser(User $user): void
    {
        $guestCartId = session()->getId();
        $userCartId = "user_{$user->id}";
        
        // Create secure transfer token
        $token = hash('sha256', $guestCartId . $userCartId . config('app.key'));
        
        // Verify transfer is authorized
        if (!$this->verifyTransferToken($token)) {
            throw new UnauthorizedTransferException('Cart transfer not authorized');
        }
        
        // Merge carts securely
        $this->mergeCartsSecurely($guestCartId, $userCartId);
    }
    
    protected function mergeCartsSecurely(string $fromId, string $toId): void
    {
        $fromCart = Cart::instance($fromId);
        $toCart = Cart::instance($toId);
        
        // Validate all items before merging
        foreach ($fromCart->content() as $item) {
            $this->validateItemSecurity($item);
        }
        
        $toCart->merge($fromCart);
        $fromCart->clear();
    }
}
```

## XSS Prevention

### 1. Output Escaping

**Safe data display:**

```blade
{{-- ✅ Always escape user data --}}
<div class="item-name">{{ $item->name }}</div>
<div class="item-attributes">
    @foreach($item->attributes as $key => $value)
        <span>{{ $key }}: {{ $value }}</span>
    @endforeach
</div>

{{-- ❌ Never use unescaped output for user data --}}
<div class="item-description">{!! $item->description !!}</div>

{{-- ✅ Use a safe HTML purifier if HTML is needed --}}
<div class="item-description">{!! clean($item->description) !!}</div>
```

### 2. JSON Data Security

**Safe JSON output:**

```php
class CartController extends Controller
{
    public function getCartData(): JsonResponse
    {
        $cart = Cart::content()->map(function($item) {
            return [
                'id' => $item->id,
                'name' => htmlspecialchars($item->name, ENT_QUOTES, 'UTF-8'),
                'price' => number_format($item->price, 2),
                'quantity' => (int) $item->quantity,
                'attributes' => $this->sanitizeAttributes($item->attributes),
            ];
        });
        
        return response()->json($cart);
    }
    
    protected function sanitizeAttributes(array $attributes): array
    {
        return array_map(function($value) {
            return is_string($value) ? htmlspecialchars($value, ENT_QUOTES, 'UTF-8') : $value;
        }, $attributes);
    }
}
```

### 3. Livewire Security

**Secure Livewire components:**

```php
class AddToCart extends Component
{
    public string $productId = '';
    public int $quantity = 1;
    
    protected $rules = [
        'productId' => 'required|string|exists:products,id',
        'quantity' => 'required|integer|min:1|max:999',
    ];
    
    public function addToCart(): void
    {
        $this->validate();
        
        // Get product data from database, not component properties
        $product = Product::findOrFail($this->productId);
        
        // Rate limit user actions
        $this->rateLimit(10, 60); // 10 actions per minute
        
        Cart::add(
            $product->id,
            $product->name,
            $product->price, // Always use server-side price
            $this->quantity
        );
        
        $this->dispatch('cart-updated');
    }
    
    protected function rateLimit(int $attempts, int $decayMinutes): void
    {
        $key = 'cart_add:' . (auth()->id() ?? request()->ip());
        
        if (RateLimiter::tooManyAttempts($key, $attempts)) {
            throw ValidationException::withMessages([
                'quantity' => 'Too many requests. Please try again later.',
            ]);
        }
        
        RateLimiter::hit($key, $decayMinutes * 60);
    }
}
```

## CSRF Protection

### 1. Laravel CSRF

**Ensure CSRF protection is enabled:**

```php
// bootstrap/app.php
->withMiddleware(function (Middleware $middleware) {
    $middleware->web(append: [
        \App\Http\Middleware\VerifyCsrfToken::class,
    ]);
})
```

### 2. AJAX CSRF

**CSRF for AJAX requests:**

```javascript
// Ensure CSRF token is included
document.addEventListener('DOMContentLoaded', function() {
    // Get CSRF token
    const token = document.querySelector('meta[name="csrf-token"]').content;
    
    // Set default headers for all AJAX requests
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
    
    // For fetch requests
    window.fetch = new Proxy(window.fetch, {
        apply(target, thisArg, argumentsList) {
            const [url, options = {}] = argumentsList;
            
            if (options.method && options.method.toLowerCase() !== 'get') {
                options.headers = {
                    ...options.headers,
                    'X-CSRF-TOKEN': token,
                };
            }
            
            return target.apply(thisArg, argumentsList);
        }
    });
});
```

### 3. Livewire CSRF

**CSRF with Livewire:**

```blade
{{-- CSRF is automatically handled, but ensure proper form structure --}}
<form wire:submit.prevent="addToCart">
    @csrf
    <input type="hidden" wire:model="productId" value="{{ $product->id }}">
    <input type="number" wire:model="quantity" min="1" max="999">
    <button type="submit">Add to Cart</button>
</form>
```

## Rate Limiting

### 1. Global Rate Limiting

**Protect cart operations:**

```php
// routes/web.php
Route::middleware(['throttle:cart'])->group(function () {
    Route::post('/cart/add', [CartController::class, 'add']);
    Route::patch('/cart/update', [CartController::class, 'update']);
    Route::delete('/cart/remove', [CartController::class, 'remove']);
});
```

**Custom rate limiter:**

```php
// AppServiceProvider.php
public function boot(): void
{
    RateLimiter::for('cart', function (Request $request) {
        $key = auth()->check() 
            ? 'cart:user:' . auth()->id()
            : 'cart:ip:' . $request->ip();
            
        return Limit::perMinute(60)->by($key);
    });
}
```

### 2. Progressive Rate Limiting

**Escalating limits for suspicious activity:**

```php
class ProgressiveRateLimiter
{
    public function checkLimits(Request $request): void
    {
        $identifier = auth()->id() ?? $request->ip();
        
        // Normal operations: 60 per minute
        $normalKey = "cart_normal:{$identifier}";
        if (RateLimiter::tooManyAttempts($normalKey, 60)) {
            throw new TooManyRequestsException('Rate limit exceeded');
        }
        
        // Suspicious patterns: 10 rapid additions
        $rapidKey = "cart_rapid:{$identifier}";
        if (RateLimiter::tooManyAttempts($rapidKey, 10)) {
            // Escalate to stricter limits
            $this->escalateRestrictions($identifier);
        }
        
        RateLimiter::hit($normalKey, 60);
        RateLimiter::hit($rapidKey, 10);
    }
}
```

## Data Storage Security

### 1. Database Encryption

**Encrypt sensitive cart data:**

```php
class EncryptedCartStorage implements CartStorageInterface
{
    public function store(string $identifier, array $data): void
    {
        $encryptedData = encrypt(json_encode($data));
        
        DB::table('cart_storage')->updateOrInsert(
            ['id' => $identifier],
            [
                'cart_data' => $encryptedData,
                'updated_at' => now(),
            ]
        );
    }
    
    public function retrieve(string $identifier): array
    {
        $record = DB::table('cart_storage')
            ->where('id', $identifier)
            ->first();
            
        if (!$record) {
            return [];
        }
        
        return json_decode(decrypt($record->cart_data), true);
    }
}
```

### 2. Secure Cache Storage

**Cache security configuration:**

```php
// config/cache.php
'stores' => [
    'secure_redis' => [
        'driver' => 'redis',
        'connection' => 'cache',
        'prefix' => hash('sha256', config('app.key')),
        'serializer' => 'php', // More secure than JSON
    ],
],
```

## Audit Logging

### 1. Security Event Logging

**Log security-relevant events:**

```php
class CartSecurityLogger
{
    public static function logSuspiciousActivity(string $event, array $context = []): void
    {
        Log::warning("Cart Security Event: {$event}", array_merge([
            'user_id' => auth()->id(),
            'session_id' => session()->getId(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toISOString(),
        ], $context));
    }
}

// Usage examples
CartSecurityLogger::logSuspiciousActivity('rapid_quantity_changes', [
    'item_id' => $itemId,
    'old_quantity' => $oldQuantity,
    'new_quantity' => $newQuantity,
]);

CartSecurityLogger::logSuspiciousActivity('unusual_price_detected', [
    'item_id' => $itemId,
    'expected_price' => $expectedPrice,
    'submitted_price' => $submittedPrice,
]);
```

### 2. Failed Attempt Monitoring

**Monitor and respond to failed attempts:**

```php
class SecurityMonitor
{
    public function monitorFailedAttempts(Exception $exception, Request $request): void
    {
        $attempts = Cache::increment("failed_cart:{$request->ip()}", 1, 3600);
        
        if ($attempts > 50) {
            // Block IP temporarily
            Cache::put("blocked_ip:{$request->ip()}", true, 3600);
            
            // Alert administrators
            Mail::to(config('app.admin_email'))->send(
                new SuspiciousActivityAlert($request->ip(), $attempts)
            );
        }
    }
}
```

## Security Checklist

**Implementation Checklist:**

- [ ] **Input Validation**: All user inputs validated and sanitized
- [ ] **Price Verification**: Server-side price validation implemented  
- [ ] **Quantity Limits**: Maximum quantity and item limits enforced
- [ ] **CSRF Protection**: CSRF tokens required for all state changes
- [ ] **XSS Prevention**: All output properly escaped
- [ ] **Session Security**: Secure session configuration and regeneration
- [ ] **Rate Limiting**: API endpoints protected with rate limiting
- [ ] **Access Control**: User-based cart isolation implemented
- [ ] **Data Encryption**: Sensitive data encrypted at rest
- [ ] **Audit Logging**: Security events logged and monitored
- [ ] **Error Handling**: No sensitive information in error messages
- [ ] **HTTPS Only**: All cart operations over HTTPS

**Regular Security Tasks:**

- [ ] Review audit logs weekly
- [ ] Update dependencies monthly  
- [ ] Conduct security audits quarterly
- [ ] Review rate limits based on usage patterns
- [ ] Test security measures with penetration testing

## Incident Response

### Quick Response Actions

**Suspected security breach:**

1. **Isolate**: Block suspicious IP addresses
2. **Audit**: Review recent cart operations  
3. **Validate**: Check for data integrity issues
4. **Clean**: Clear potentially compromised sessions
5. **Monitor**: Increase logging and monitoring
6. **Report**: Document and report incident

**Emergency cart lockdown:**

```php
// Emergency middleware to disable cart operations
class CartEmergencyLockdown
{
    public function handle(Request $request, Closure $next)
    {
        if (config('cart.emergency_lockdown')) {
            return response()->json([
                'error' => 'Cart operations temporarily disabled for maintenance'
            ], 503);
        }
        
        return $next($request);
    }
}
```

For security concerns or incident reporting, contact: **security@masyukai.com** 🔒
