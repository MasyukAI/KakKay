# Laravel Reverb Implementation Guide for Real-Time Cart Updates

**Status:** Ready for Implementation (Good to Have, Not Priority)  
**Estimated Effort:** 4-6 hours  
**Dependencies:** Laravel Reverb, Laravel Echo, Pusher JS

---

## Why Implement This?

When an admin deactivates a global condition while customers have active carts:

- **Current Behavior:** Customer only sees price change after page refresh
- **With Reverb:** Customer sees real-time notification + cart automatically updates
- **Result:** Better UX, fewer checkout failures, increased trust

---

## Implementation Steps

### 1. Install Dependencies

```bash
# Install Reverb
composer require laravel/reverb

# Install Echo & Pusher JS
npm install --save-dev laravel-echo pusher-js

# Publish config
php artisan vendor:publish --tag=reverb-config
```

### 2. Configure Environment

```env
# .env
BROADCAST_CONNECTION=reverb

REVERB_APP_ID=app-id
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

### 3. Create Broadcast Event

Create: `app/Events/CartConditionsChanged.php`

```php
<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CartConditionsChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $userId,
        public array $changes
    ) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('App.Models.User.'.$this->userId);
    }

    public function broadcastAs(): string
    {
        return 'cart.conditions.changed';
    }

    public function broadcastWith(): array
    {
        return [
            'message' => 'Your cart was updated due to a promotion change',
            'changes' => $this->changes,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
```

### 4. Update CartConditionBatchRemoval

Modify: `packages/masyukai/filament-cart/src/Services/CartConditionBatchRemoval.php`

```php
use App\Events\CartConditionsChanged;

// In removeConditionFromAllCarts() method, after syncing:
if ($conditionRemoved) {
    // Sync the cart to update the database snapshot
    $this->syncManager->sync($cart);
    $cartsUpdated++;
    
    // Broadcast to authenticated users (not guest sessions)
    if ($snapshot->identifier && is_numeric($snapshot->identifier)) {
        try {
            broadcast(new CartConditionsChanged(
                userId: (int) $snapshot->identifier,
                changes: [
                    'condition_removed' => $conditionName,
                    'new_subtotal' => $cart->subtotal()->format(),
                    'new_total' => $cart->total()->format(),
                    'savings' => $cart->savings()->format(),
                ]
            ))->toOthers();
        } catch (\Exception $e) {
            // Log but don't fail the operation
            Log::warning('Failed to broadcast cart update', [
                'user_id' => $snapshot->identifier,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
```

### 5. Setup Frontend Echo

Update: `resources/js/bootstrap.js`

```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});

// Listen for cart updates (only for authenticated users)
if (window.Laravel && window.Laravel.userId) {
    window.Echo.private(`App.Models.User.${window.Laravel.userId}`)
        .listen('.cart.conditions.changed', (event) => {
            // Dispatch custom event for Livewire/Alpine to handle
            window.dispatchEvent(new CustomEvent('cart-conditions-changed', {
                detail: event
            }));
        });
}
```

### 6. Update Livewire Cart Component

Update your cart Livewire component view (e.g., `resources/views/livewire/cart-page.blade.php`):

```php
<div 
    x-data="{
        listening: false,
        init() {
            if (!this.listening) {
                this.listening = true;
                window.addEventListener('cart-conditions-changed', (event) => {
                    // Show toast notification
                    this.$dispatch('toast', {
                        type: 'info',
                        message: event.detail.message,
                        duration: 5000
                    });
                    
                    // Refresh cart data from server
                    this.$wire.$refresh();
                });
            }
        }
    }"
    wire:key="cart-component"
>
    <!-- Your cart content here -->
</div>
```

### 7. Pass User ID to Frontend

Update: `resources/views/layouts/app.blade.php`

```blade
@auth
    <script>
        window.Laravel = window.Laravel || {};
        window.Laravel.userId = {{ auth()->id() }};
    </script>
@endauth
```

### 8. Start Reverb Server

```bash
# Development
php artisan reverb:start

# Production (with Supervisor)
# Create supervisor config: /etc/supervisor/conf.d/reverb.conf
[program:reverb]
command=php /path/to/artisan reverb:start
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/path/to/logs/reverb.log
```

### 9. Build Frontend Assets

```bash
npm run build
```

---

## Testing the Implementation

### Manual Testing

1. **Setup:**
   ```bash
   # Terminal 1: Start Reverb
   php artisan reverb:start
   
   # Terminal 2: Start dev server
   npm run dev
   ```

2. **Test Flow:**
   - Login as User A
   - Add items to cart with a global condition
   - Keep browser open on cart page
   - In another browser (as Admin), deactivate the global condition
   - Click "Remove from All Carts"
   - **Expected:** User A sees toast notification + cart updates automatically

### Automated Testing

Create: `tests/Feature/Broadcasting/CartConditionsChangedTest.php`

```php
<?php

use App\Events\CartConditionsChanged;
use App\Models\User;
use Illuminate\Support\Facades\Event;

test('it broadcasts cart conditions changed event', function () {
    Event::fake([CartConditionsChanged::class]);
    
    $user = User::factory()->create();
    
    broadcast(new CartConditionsChanged(
        userId: $user->id,
        changes: ['condition_removed' => 'test-discount']
    ));
    
    Event::assertDispatched(CartConditionsChanged::class, function ($event) use ($user) {
        return $event->userId === $user->id
            && $event->broadcastOn()->name === 'private-App.Models.User.'.$user->id;
    });
});
```

---

## Production Considerations

### 1. Scaling

For high-traffic applications, enable Redis-based horizontal scaling:

```env
# .env
REVERB_SCALING_ENABLED=true
```

Configure Redis in `config/database.php`:

```php
'redis' => [
    'reverb' => [
        'url' => env('REDIS_URL'),
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'password' => env('REDIS_PASSWORD'),
        'port' => env('REDIS_PORT', 6379),
        'database' => env('REDIS_REVERB_DB', 1),
    ],
],
```

### 2. Nginx Configuration

For production, proxy Reverb through Nginx:

```nginx
# /etc/nginx/sites-available/your-app
location / {
    proxy_http_version 1.1;
    proxy_set_header Host $http_host;
    proxy_set_header Scheme $scheme;
    proxy_set_header SERVER_PORT $server_port;
    proxy_set_header REMOTE_ADDR $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection "Upgrade";
    
    proxy_pass http://127.0.0.1:8080;
}
```

### 3. Monitoring

Monitor Reverb connections and performance:

```php
// Add to monitoring dashboard
use Illuminate\Support\Facades\Cache;

$connections = Cache::get('reverb:connections', 0);
$messagesPerSecond = Cache::get('reverb:messages:per_second', 0);
```

---

## Performance Impact

| Metric | Before | After | Notes |
|--------|--------|-------|-------|
| Page Load | Same | Same | No impact on initial load |
| Cart Updates | Refresh needed | Real-time | Better UX |
| Server Load | Low | Low + WebSocket | ~100 concurrent connections ≈ 50MB RAM |
| Network | HTTP only | HTTP + WebSocket | WebSocket: ~1-2KB per message |

### Resource Requirements

- **CPU:** Minimal (<5% increase)
- **RAM:** ~0.5MB per connection (10,000 connections ≈ 5GB)
- **Network:** ~50Kbps per 1000 active connections

---

## Rollback Plan

If issues arise, disable without code changes:

```env
# .env - Stop broadcasting
BROADCAST_CONNECTION=log

# Or in config/filament-cart.php
'features' => [
    'broadcast_cart_updates' => false,
],
```

Then check for this flag before broadcasting:

```php
if (config('filament-cart.features.broadcast_cart_updates', false)) {
    broadcast(new CartConditionsChanged(/* ... */));
}
```

---

## Alternative: Polling (Simpler but Less Efficient)

If Reverb is too complex, use polling as alternative:

```javascript
// Every 30 seconds, check for cart updates
setInterval(() => {
    fetch('/api/cart/check-updates')
        .then(response => response.json())
        .then(data => {
            if (data.updated) {
                window.dispatchEvent(new CustomEvent('cart-conditions-changed', {
                    detail: { message: data.message }
                }));
            }
        });
}, 30000);
```

**Pros:** Simpler, no WebSocket server needed  
**Cons:** Higher latency, more server requests, less elegant

---

## Summary

✅ **Benefits:**
- Real-time cart updates
- Better user experience
- Fewer checkout failures
- Increased platform trust

⚠️ **Considerations:**
- Requires WebSocket server (Reverb)
- Additional infrastructure complexity
- Monitoring needed for production

🎯 **Recommendation:**
Implement when cart usage grows or customer feedback indicates confusion about price changes. For now, the three-layer defense system (passive cleanup + checkout validation + admin batch removal) provides solid protection without WebSocket complexity.

---

**Next Steps When Prioritized:**

1. ✅ Install Reverb and Echo (1 hour)
2. ✅ Create broadcast event (30 mins)
3. ✅ Update CartConditionBatchRemoval (30 mins)
4. ✅ Setup frontend Echo (1 hour)
5. ✅ Update Livewire components (1 hour)
6. ✅ Test end-to-end (1 hour)
7. ✅ Deploy to staging (30 mins)
8. ✅ Monitor and fine-tune (ongoing)

**Total Estimated Time:** 4-6 hours
