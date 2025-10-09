# J&T Express Webhook Integration Examples

This document provides complete, production-ready examples for integrating J&T Express webhooks into your Laravel application.

## Table of Contents

1. [Basic Order Status Update](#1-basic-order-status-update)
2. [Customer Notifications](#2-customer-notifications)
3. [Tracking History Log](#3-tracking-history-log)
4. [Problem Status Handler](#4-problem-status-handler)
5. [Queue-Based Processing](#5-queue-based-processing)
6. [Multi-Tenant Application](#6-multi-tenant-application)
7. [Webhook Analytics](#7-webhook-analytics)

---

## 1. Basic Order Status Update

### Use Case
Update order tracking status when webhooks are received.

### Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('tracking_number')->nullable()->index();
            $table->string('tracking_status')->nullable();
            $table->text('tracking_description')->nullable();
            $table->string('tracking_location')->nullable();
            $table->timestamp('tracking_updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'tracking_number',
                'tracking_status',
                'tracking_description',
                'tracking_location',
                'tracking_updated_at',
            ]);
        });
    }
};
```

### Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'order_number',
        'tracking_number',
        'tracking_status',
        'tracking_description',
        'tracking_location',
        'tracking_updated_at',
        'status',
    ];

    protected $casts = [
        'tracking_updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isDelivered(): bool
    {
        return in_array($this->tracking_status, ['delivery', 'signed']);
    }

    public function hasTrackingProblem(): bool
    {
        return in_array($this->tracking_status, ['problem', 'return', 'reject']);
    }
}
```

### Listener

```php
<?php

namespace App\Listeners;

use App\Models\Order;
use Illuminate\Support\Facades\Log;
use MasyukAI\Jnt\Events\TrackingStatusReceived;

class UpdateOrderTracking
{
    public function handle(TrackingStatusReceived $event): void
    {
        $order = Order::where('tracking_number', $event->getBillCode())->first();

        if (!$order) {
            Log::warning('J&T webhook received for unknown tracking number', [
                'tracking_number' => $event->getBillCode(),
                'txlogistic_id' => $event->getTxlogisticId(),
            ]);
            return;
        }

        $order->update([
            'tracking_status' => $event->getLatestStatus(),
            'tracking_description' => $event->getLatestDescription(),
            'tracking_location' => $event->getLatestLocation(),
            'tracking_updated_at' => $event->getLatestTimestamp(),
        ]);

        // Update order status based on tracking status
        if ($event->isDelivered() && $order->status !== 'delivered') {
            $order->update(['status' => 'delivered']);
        }

        Log::info('Order tracking updated via webhook', [
            'order_id' => $order->id,
            'tracking_number' => $event->getBillCode(),
            'status' => $event->getLatestStatus(),
        ]);
    }
}
```

### Register Listener

```php
// app/Providers/EventServiceProvider.php
protected $listen = [
    \MasyukAI\Jnt\Events\TrackingStatusReceived::class => [
        \App\Listeners\UpdateOrderTracking::class,
    ],
];
```

---

## 2. Customer Notifications

### Use Case
Send email/SMS notifications to customers when shipment status changes.

### Notifications

```php
<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderShipped extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Order $order,
        public string $trackingUrl,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Order Has Shipped')
            ->greeting("Hello {$notifiable->name}!")
            ->line("Your order #{$this->order->order_number} has been shipped.")
            ->line("Tracking Number: {$this->order->tracking_number}")
            ->action('Track Your Order', $this->trackingUrl)
            ->line('Thank you for shopping with us!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'tracking_number' => $this->order->tracking_number,
            'message' => 'Your order has been shipped',
        ];
    }
}
```

```php
<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderOutForDelivery extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Order $order,
        public string $location,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Order Is Out For Delivery')
            ->greeting("Hello {$notifiable->name}!")
            ->line("Your order #{$this->order->order_number} is out for delivery.")
            ->line("Current Location: {$this->location}")
            ->line('Expect delivery today!')
            ->line('Thank you for your patience!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'location' => $this->location,
            'message' => 'Your order is out for delivery',
        ];
    }
}
```

```php
<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderDelivered extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Order $order) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Order Has Been Delivered')
            ->greeting("Hello {$notifiable->name}!")
            ->line("Your order #{$this->order->order_number} has been delivered.")
            ->line('We hope you enjoy your purchase!')
            ->action('Leave a Review', url("/orders/{$this->order->id}/review"))
            ->line('Thank you for shopping with us!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'message' => 'Your order has been delivered',
        ];
    }
}
```

### Listener

```php
<?php

namespace App\Listeners;

use App\Models\Order;
use App\Notifications\OrderDelivered;
use App\Notifications\OrderOutForDelivery;
use App\Notifications\OrderShipped;
use Illuminate\Support\Facades\Log;
use MasyukAI\Jnt\Events\TrackingStatusReceived;

class NotifyCustomerOfTrackingUpdate
{
    public function handle(TrackingStatusReceived $event): void
    {
        $order = Order::with('user')->where('tracking_number', $event->getBillCode())->first();

        if (!$order || !$order->user) {
            return;
        }

        $status = $event->getLatestStatus();

        match ($status) {
            'collection' => $this->notifyShipped($order, $event),
            '派件' => $this->notifyOutForDelivery($order, $event),
            'delivery', 'signed' => $this->notifyDelivered($order),
            default => null,
        };
    }

    protected function notifyShipped(Order $order, TrackingStatusReceived $event): void
    {
        $trackingUrl = route('orders.tracking', ['number' => $event->getBillCode()]);
        $order->user->notify(new OrderShipped($order, $trackingUrl));

        Log::info('Customer notified: Order shipped', [
            'order_id' => $order->id,
            'user_id' => $order->user_id,
        ]);
    }

    protected function notifyOutForDelivery(Order $order, TrackingStatusReceived $event): void
    {
        $location = $event->getLatestLocation() ?? 'your area';
        $order->user->notify(new OrderOutForDelivery($order, $location));

        Log::info('Customer notified: Order out for delivery', [
            'order_id' => $order->id,
            'user_id' => $order->user_id,
        ]);
    }

    protected function notifyDelivered(Order $order): void
    {
        $order->user->notify(new OrderDelivered($order));

        Log::info('Customer notified: Order delivered', [
            'order_id' => $order->id,
            'user_id' => $order->user_id,
        ]);
    }
}
```

---

## 3. Tracking History Log

### Use Case
Store complete tracking history for audit trail and customer viewing.

### Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tracking_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('tracking_number')->index();
            $table->string('status');
            $table->text('description')->nullable();
            $table->string('location')->nullable();
            $table->timestamp('occurred_at');
            $table->json('raw_data')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tracking_events');
    }
};
```

### Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrackingEvent extends Model
{
    protected $fillable = [
        'order_id',
        'tracking_number',
        'status',
        'description',
        'location',
        'occurred_at',
        'raw_data',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'raw_data' => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
```

### Listener

```php
<?php

namespace App\Listeners;

use App\Models\Order;
use App\Models\TrackingEvent;
use Illuminate\Support\Facades\Log;
use MasyukAI\Jnt\Events\TrackingStatusReceived;

class LogTrackingHistory
{
    public function handle(TrackingStatusReceived $event): void
    {
        $order = Order::where('tracking_number', $event->getBillCode())->first();

        if (!$order) {
            Log::warning('Cannot log tracking history: Order not found', [
                'tracking_number' => $event->getBillCode(),
            ]);
            return;
        }

        // Log each detail from the webhook
        foreach ($event->webhookData->details as $detail) {
            $this->createTrackingEvent($order, $event->getBillCode(), $detail);
        }

        Log::info('Tracking history logged', [
            'order_id' => $order->id,
            'event_count' => count($event->webhookData->details),
        ]);
    }

    protected function createTrackingEvent(Order $order, string $trackingNumber, object $detail): void
    {
        // Avoid duplicates
        $exists = TrackingEvent::where('order_id', $order->id)
            ->where('occurred_at', $detail->scanTime)
            ->where('status', $detail->scanType)
            ->exists();

        if ($exists) {
            return;
        }

        TrackingEvent::create([
            'order_id' => $order->id,
            'tracking_number' => $trackingNumber,
            'status' => $detail->scanType,
            'description' => $detail->description,
            'location' => $this->formatLocation($detail),
            'occurred_at' => $detail->scanTime,
            'raw_data' => [
                'scan_type_code' => $detail->scanTypeCode,
                'scan_type_name' => $detail->scanTypeName,
                'site_code' => $detail->siteCode ?? null,
                'site_name' => $detail->siteName ?? null,
                'site_type' => $detail->siteType ?? null,
            ],
        ]);
    }

    protected function formatLocation(object $detail): ?string
    {
        $parts = array_filter([
            $detail->siteName ?? null,
            $detail->city ?? null,
            $detail->province ?? null,
        ]);

        return !empty($parts) ? implode(', ', $parts) : null;
    }
}
```

### Display in Blade

```blade
{{-- resources/views/orders/tracking.blade.php --}}
<div class="tracking-timeline">
    <h3>Tracking History</h3>
    
    @foreach($order->trackingEvents()->orderByDesc('occurred_at')->get() as $event)
        <div class="timeline-item">
            <div class="timeline-marker"></div>
            <div class="timeline-content">
                <div class="font-bold">{{ $event->description }}</div>
                <div class="text-sm text-gray-600">
                    {{ $event->location }}
                </div>
                <div class="text-xs text-gray-500">
                    {{ $event->occurred_at->format('M d, Y h:i A') }}
                </div>
            </div>
        </div>
    @endforeach
</div>
```

---

## 4. Problem Status Handler

### Use Case
Automatically handle problem statuses (returns, rejects, issues).

### Listener

```php
<?php

namespace App\Listeners;

use App\Models\Order;
use App\Notifications\OrderHasProblem;
use App\Services\CustomerSupportService;
use Illuminate\Support\Facades\Log;
use MasyukAI\Jnt\Events\TrackingStatusReceived;

class HandleTrackingProblems
{
    public function __construct(
        protected CustomerSupportService $supportService,
    ) {}

    public function handle(TrackingStatusReceived $event): void
    {
        // Only handle problem statuses
        if (!$event->hasProblem()) {
            return;
        }

        $order = Order::with('user')->where('tracking_number', $event->getBillCode())->first();

        if (!$order) {
            return;
        }

        $status = $event->getLatestStatus();

        match ($status) {
            'problem' => $this->handleProblem($order, $event),
            'return' => $this->handleReturn($order, $event),
            'reject' => $this->handleReject($order, $event),
            default => null,
        };
    }

    protected function handleProblem(Order $order, TrackingStatusReceived $event): void
    {
        $order->update([
            'status' => 'problem',
            'problem_description' => $event->getLatestDescription(),
        ]);

        // Create support ticket
        $this->supportService->createTicket([
            'order_id' => $order->id,
            'category' => 'shipping_problem',
            'priority' => 'high',
            'subject' => "Shipment Problem: {$order->tracking_number}",
            'description' => $event->getLatestDescription(),
            'metadata' => [
                'tracking_number' => $event->getBillCode(),
                'location' => $event->getLatestLocation(),
            ],
        ]);

        // Notify customer
        if ($order->user) {
            $order->user->notify(new OrderHasProblem($order, $event->getLatestDescription()));
        }

        Log::warning('Shipment problem detected', [
            'order_id' => $order->id,
            'tracking_number' => $event->getBillCode(),
            'description' => $event->getLatestDescription(),
        ]);
    }

    protected function handleReturn(Order $order, TrackingStatusReceived $event): void
    {
        $order->update([
            'status' => 'return_to_sender',
            'return_reason' => $event->getLatestDescription(),
        ]);

        // Create support ticket for returns
        $this->supportService->createTicket([
            'order_id' => $order->id,
            'category' => 'return_to_sender',
            'priority' => 'high',
            'subject' => "Package Returned: {$order->tracking_number}",
            'description' => $event->getLatestDescription(),
        ]);

        Log::warning('Package returned to sender', [
            'order_id' => $order->id,
            'tracking_number' => $event->getBillCode(),
            'reason' => $event->getLatestDescription(),
        ]);
    }

    protected function handleReject(Order $order, TrackingStatusReceived $event): void
    {
        $order->update([
            'status' => 'rejected',
            'reject_reason' => $event->getLatestDescription(),
        ]);

        // Initiate refund process
        if ($order->isPaid()) {
            dispatch(new ProcessRefund($order, 'Package rejected by courier'));
        }

        Log::error('Package rejected by courier', [
            'order_id' => $order->id,
            'tracking_number' => $event->getBillCode(),
            'reason' => $event->getLatestDescription(),
        ]);
    }
}
```

---

## 5. Queue-Based Processing

### Use Case
Process webhooks asynchronously to prevent blocking webhook responses.

### Listener

```php
<?php

namespace App\Listeners;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use MasyukAI\Jnt\Events\TrackingStatusReceived;

class ProcessWebhookInQueue implements ShouldQueue
{
    use Queueable;

    public $queue = 'webhooks';
    public $tries = 3;
    public $backoff = [60, 180, 600]; // 1min, 3min, 10min

    public function handle(TrackingStatusReceived $event): void
    {
        $order = Order::where('tracking_number', $event->getBillCode())->first();

        if (!$order) {
            Log::warning('Queue: Order not found for tracking update', [
                'tracking_number' => $event->getBillCode(),
            ]);
            return;
        }

        // Heavy processing here
        $this->updateOrderStatus($order, $event);
        $this->syncInventory($order, $event);
        $this->updateAnalytics($order, $event);
        $this->notifyThirdPartyServices($order, $event);

        Log::info('Queue: Webhook processed successfully', [
            'order_id' => $order->id,
            'tracking_number' => $event->getBillCode(),
        ]);
    }

    public function failed(TrackingStatusReceived $event, \Throwable $exception): void
    {
        Log::error('Queue: Webhook processing failed', [
            'tracking_number' => $event->getBillCode(),
            'exception' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);

        // Optionally send alert to monitoring system
    }

    protected function updateOrderStatus(Order $order, TrackingStatusReceived $event): void
    {
        $order->update([
            'tracking_status' => $event->getLatestStatus(),
            'tracking_description' => $event->getLatestDescription(),
            'tracking_location' => $event->getLatestLocation(),
            'tracking_updated_at' => $event->getLatestTimestamp(),
        ]);
    }

    protected function syncInventory(Order $order, TrackingStatusReceived $event): void
    {
        // Update inventory when order is delivered
        if ($event->isDelivered()) {
            // ... inventory logic
        }
    }

    protected function updateAnalytics(Order $order, TrackingStatusReceived $event): void
    {
        // Track delivery metrics
        // ... analytics logic
    }

    protected function notifyThirdPartyServices(Order $order, TrackingStatusReceived $event): void
    {
        // Sync with external systems
        // ... integration logic
    }
}
```

### Queue Configuration

```php
// config/queue.php
'connections' => [
    'redis' => [
        'driver' => 'redis',
        'connection' => 'default',
        'queue' => env('REDIS_QUEUE', 'default'),
        'retry_after' => 90,
        'block_for' => null,
        'after_commit' => false,
    ],
],

// Dedicated queue for webhooks
'webhooks' => [
    'driver' => 'redis',
    'connection' => 'default',
    'queue' => 'webhooks',
    'retry_after' => 180,
],
```

### Worker Command

```bash
# Run dedicated webhook queue worker
php artisan queue:work redis --queue=webhooks --tries=3
```

---

## 6. Multi-Tenant Application

### Use Case
Handle webhooks for multiple tenants/shops in a single application.

### Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('tracking_number')->unique();
            $table->string('jnt_api_account');
            $table->timestamps();

            $table->index(['tenant_id', 'tracking_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_shipments');
    }
};
```

### Listener

```php
<?php

namespace App\Listeners;

use App\Models\Order;
use App\Models\Tenant;
use App\Models\TenantShipment;
use Illuminate\Support\Facades\Log;
use MasyukAI\Jnt\Events\TrackingStatusReceived;

class HandleMultiTenantWebhook
{
    public function handle(TrackingStatusReceived $event): void
    {
        // Find shipment with tenant info
        $shipment = TenantShipment::with(['tenant', 'order'])
            ->where('tracking_number', $event->getBillCode())
            ->first();

        if (!$shipment) {
            Log::warning('Multi-tenant: Shipment not found', [
                'tracking_number' => $event->getBillCode(),
            ]);
            return;
        }

        // Set tenant context
        $this->setTenantContext($shipment->tenant);

        // Update order in tenant context
        $shipment->order->update([
            'tracking_status' => $event->getLatestStatus(),
            'tracking_description' => $event->getLatestDescription(),
            'tracking_location' => $event->getLatestLocation(),
            'tracking_updated_at' => $event->getLatestTimestamp(),
        ]);

        Log::info('Multi-tenant: Webhook processed', [
            'tenant_id' => $shipment->tenant_id,
            'order_id' => $shipment->order_id,
            'tracking_number' => $event->getBillCode(),
        ]);
    }

    protected function setTenantContext(Tenant $tenant): void
    {
        // Set database connection, config, etc. for tenant
        config(['database.default' => "tenant_{$tenant->id}"]);
        app()->instance('current_tenant', $tenant);
    }
}
```

---

## 7. Webhook Analytics

### Use Case
Track webhook metrics for monitoring and debugging.

### Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->string('source')->default('jnt');
            $table->string('tracking_number')->index();
            $table->string('status');
            $table->boolean('processed')->default(true);
            $table->text('error')->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('received_at');
            $table->timestamps();

            $table->index(['source', 'received_at']);
            $table->index(['processed', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_logs');
    }
};
```

### Listener

```php
<?php

namespace App\Listeners;

use App\Models\WebhookLog;
use MasyukAI\Jnt\Events\TrackingStatusReceived;

class LogWebhookMetrics
{
    public function handle(TrackingStatusReceived $event): void
    {
        WebhookLog::create([
            'source' => 'jnt',
            'tracking_number' => $event->getBillCode(),
            'status' => $event->getLatestStatus(),
            'processed' => true,
            'payload' => $event->webhookData->toArray(),
            'received_at' => now(),
        ]);
    }
}
```

### Analytics Dashboard

```php
<?php

namespace App\Http\Controllers;

use App\Models\WebhookLog;
use Illuminate\Support\Facades\DB;

class WebhookAnalyticsController extends Controller
{
    public function index()
    {
        return view('admin.webhook-analytics', [
            'totalWebhooks' => WebhookLog::count(),
            'successRate' => $this->calculateSuccessRate(),
            'statusDistribution' => $this->getStatusDistribution(),
            'recentFailures' => $this->getRecentFailures(),
            'hourlyVolume' => $this->getHourlyVolume(),
        ]);
    }

    protected function calculateSuccessRate(): float
    {
        $total = WebhookLog::count();
        $successful = WebhookLog::where('processed', true)->count();

        return $total > 0 ? ($successful / $total) * 100 : 0;
    }

    protected function getStatusDistribution(): array
    {
        return WebhookLog::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
    }

    protected function getRecentFailures()
    {
        return WebhookLog::where('processed', false)
            ->latest()
            ->limit(10)
            ->get();
    }

    protected function getHourlyVolume(): array
    {
        return WebhookLog::select(
            DB::raw('DATE_FORMAT(received_at, "%Y-%m-%d %H:00:00") as hour'),
            DB::raw('count(*) as count')
        )
            ->where('received_at', '>=', now()->subDay())
            ->groupBy('hour')
            ->orderBy('hour')
            ->pluck('count', 'hour')
            ->toArray();
    }
}
```

---

## Summary

These examples provide production-ready patterns for:

1. ✅ Basic order tracking updates
2. ✅ Multi-channel customer notifications  
3. ✅ Complete tracking history logging
4. ✅ Automated problem status handling
5. ✅ Queue-based async processing
6. ✅ Multi-tenant architecture
7. ✅ Webhook analytics and monitoring

Each example includes:
- Database migrations
- Eloquent models
- Event listeners
- Error handling
- Logging
- Best practices

Choose the patterns that fit your application's needs and combine them as required.
