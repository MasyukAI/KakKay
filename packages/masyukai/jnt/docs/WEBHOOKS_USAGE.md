# J&T Express Webhooks - Usage Guide

This guide explains how to use the J&T Express webhook system to receive automatic tracking status updates.

## Table of Contents

- [Overview](#overview)
- [Quick Start](#quick-start)
- [Configuration](#configuration)
- [Event Handling](#event-handling)
- [Testing](#testing)
- [Troubleshooting](#troubleshooting)
- [API Reference](#api-reference)

---

## Overview

The J&T Express webhook system allows your application to receive real-time notifications when shipment tracking statuses change. When J&T's systems detect a status update (collection, transit, delivery, etc.), they automatically POST the update to your configured webhook endpoint.

### How It Works

```
J&T Server → POST /webhooks/jnt/status
    ↓
[Signature Verification Middleware]
    ↓
[Webhook Controller]
    ↓
[TrackingStatusReceived Event]
    ↓
[Your Event Listeners]
```

---

## Quick Start

### 1. Configure Environment Variables

Add these to your `.env` file:

```env
# Required: Your J&T credentials
JNT_API_ACCOUNT=your_api_account
JNT_PRIVATE_KEY=your_private_key
JNT_CUSTOMER_CODE=your_customer_code
JNT_PASSWORD=your_password

# Optional: Webhook configuration
JNT_WEBHOOKS_ENABLED=true
JNT_WEBHOOK_ROUTE=webhooks/jnt/status
JNT_WEBHOOK_LOG_PAYLOADS=false
```

### 2. Register Event Listener

Create a listener to handle webhook events:

```bash
php artisan make:listener UpdateOrderTracking
```

In `app/Listeners/UpdateOrderTracking.php`:

```php
<?php

namespace App\Listeners;

use MasyukAI\Jnt\Events\TrackingStatusReceived;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

class UpdateOrderTracking
{
    public function handle(TrackingStatusReceived $event): void
    {
        // Find order by tracking number
        $order = Order::where('tracking_number', $event->getBillCode())->first();
        
        if (!$order) {
            Log::warning('Order not found for tracking number', [
                'tracking_number' => $event->getBillCode(),
            ]);
            return;
        }

        // Update order status
        $order->update([
            'tracking_status' => $event->getLatestStatus(),
            'tracking_description' => $event->getLatestDescription(),
            'tracking_location' => $event->getLatestLocation(),
            'tracking_updated_at' => $event->getLatestTimestamp(),
        ]);

        // Check for specific statuses
        if ($event->isDelivered()) {
            $order->markAsDelivered();
        }

        if ($event->hasProblem()) {
            $order->notifyCustomerOfProblem();
        }

        Log::info('Order tracking updated', [
            'order_id' => $order->id,
            'status' => $event->getLatestStatus(),
        ]);
    }
}
```

### 3. Register Listener in EventServiceProvider

In `app/Providers/EventServiceProvider.php`:

```php
protected $listen = [
    \MasyukAI\Jnt\Events\TrackingStatusReceived::class => [
        \App\Listeners\UpdateOrderTracking::class,
        \App\Listeners\NotifyCustomer::class,
    ],
];
```

### 4. Configure J&T Dashboard

In your J&T Express dashboard, configure your webhook URL:

```
https://yourdomain.com/webhooks/jnt/status
```

That's it! Your application will now receive and process tracking updates automatically.

---

## Configuration

### Published Config File

Publish the configuration file to customize settings:

```bash
php artisan vendor:publish --tag=jnt-config
```

This creates `config/jnt.php` with webhook settings:

```php
'webhooks' => [
    // Enable/disable webhook processing
    'enabled' => env('JNT_WEBHOOKS_ENABLED', true),
    
    // Webhook endpoint route
    'route' => env('JNT_WEBHOOK_ROUTE', 'webhooks/jnt/status'),
    
    // Middleware applied to webhook route
    'middleware' => ['api', 'jnt.verify.signature'],
    
    // Log all incoming webhook payloads (disable in production)
    'log_payloads' => env('JNT_WEBHOOK_LOG_PAYLOADS', false),
],
```

### Environment Variables

| Variable | Default | Description |
|----------|---------|-------------|
| `JNT_WEBHOOKS_ENABLED` | `true` | Enable/disable webhook system |
| `JNT_WEBHOOK_ROUTE` | `webhooks/jnt/status` | Webhook endpoint path |
| `JNT_WEBHOOK_LOG_PAYLOADS` | `false` | Log webhook payloads (debug only) |

### Disabling Webhooks

To temporarily disable webhook processing:

```env
JNT_WEBHOOKS_ENABLED=false
```

Or in config:

```php
'webhooks' => [
    'enabled' => false,
],
```

---

## Event Handling

### The TrackingStatusReceived Event

When a webhook is received, the `TrackingStatusReceived` event is dispatched with the following data:

```php
use MasyukAI\Jnt\Events\TrackingStatusReceived;

// In your listener
public function handle(TrackingStatusReceived $event): void
{
    // Access webhook data
    $event->webhookData;              // WebhookData instance
    
    // Get tracking information
    $event->getBillCode();            // J&T tracking number
    $event->getTxlogisticId();        // Your order reference (optional)
    
    // Get latest update
    $event->getLatestStatus();        // 'collection', 'dispatch', 'delivery', etc.
    $event->getLatestDescription();   // Human-readable description
    $event->getLatestLocation();      // "KL Hub, Kuala Lumpur, Wilayah Persekutuan"
    $event->getLatestTimestamp();     // "2024-01-15 10:30:00"
    
    // Check status types
    $event->isDelivered();            // true if delivered/signed
    $event->isCollected();            // true if collected
    $event->hasProblem();             // true if problem/return/reject
}
```

### Example: Send Customer Notification

```php
<?php

namespace App\Listeners;

use MasyukAI\Jnt\Events\TrackingStatusReceived;
use App\Models\Order;
use App\Notifications\OrderDelivered;
use App\Notifications\OrderOutForDelivery;

class NotifyCustomer
{
    public function handle(TrackingStatusReceived $event): void
    {
        $order = Order::where('tracking_number', $event->getBillCode())->first();
        
        if (!$order || !$order->customer) {
            return;
        }

        // Notify on specific statuses
        if ($event->isDelivered()) {
            $order->customer->notify(new OrderDelivered($order));
        } elseif ($event->getLatestStatus() === '派件') {
            $order->customer->notify(new OrderOutForDelivery($order));
        }
    }
}
```

### Example: Log All Updates

```php
<?php

namespace App\Listeners;

use MasyukAI\Jnt\Events\TrackingStatusReceived;
use App\Models\TrackingLog;

class LogTrackingUpdate
{
    public function handle(TrackingStatusReceived $event): void
    {
        TrackingLog::create([
            'tracking_number' => $event->getBillCode(),
            'status' => $event->getLatestStatus(),
            'description' => $event->getLatestDescription(),
            'location' => $event->getLatestLocation(),
            'timestamp' => $event->getLatestTimestamp(),
            'raw_data' => json_encode($event->webhookData->toArray()),
        ]);
    }
}
```

### Example: Queue Processing

For heavy processing, queue your listeners:

```php
<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use MasyukAI\Jnt\Events\TrackingStatusReceived;

class ProcessTrackingUpdate implements ShouldQueue
{
    public $queue = 'webhooks';
    
    public function handle(TrackingStatusReceived $event): void
    {
        // Heavy processing here
        // This runs in a queue worker
    }
}
```

---

## Testing

### Testing Webhooks Locally

Use a tunneling service to expose your local server to J&T:

**Using ngrok:**
```bash
ngrok http 8000
```

**Using Laravel Valet:**
```bash
valet share
```

**Using Expose:**
```bash
expose share http://localhost:8000
```

Configure the public URL in J&T dashboard:
```
https://your-tunnel-url.ngrok.io/webhooks/jnt/status
```

### Manual Testing with cURL

Simulate a webhook request:

```bash
# Generate signature
BIZCONTENT='{"billCode":"TEST123","details":[{"scanTime":"2024-01-15 10:00:00","desc":"Test","scanType":"collection","scanTypeCode":"CC","scanTypeName":"Collection"}]}'
PRIVATE_KEY="your-private-key"
SIGNATURE=$(echo -n "${BIZCONTENT}${PRIVATE_KEY}" | openssl dgst -md5 -binary | base64)

# Send webhook request
curl -X POST http://localhost:8000/webhooks/jnt/status \
  -H "Content-Type: application/json" \
  -H "digest: ${SIGNATURE}" \
  -d "{\"bizContent\": \"${BIZCONTENT}\"}"
```

### Automated Testing

The package includes comprehensive tests. Run them with:

```bash
# All webhook tests
php artisan test --filter=Webhook

# Specific test files
php artisan test tests/Unit/Data/WebhookDataTest.php
php artisan test tests/Unit/Services/WebhookServiceTest.php
php artisan test tests/Feature/WebhookEndpointTest.php
```

### Writing Your Own Tests

Example feature test:

```php
use Illuminate\Support\Facades\Event;
use MasyukAI\Jnt\Events\TrackingStatusReceived;

test('webhook updates order status', function () {
    Event::fake();
    
    $order = Order::factory()->create([
        'tracking_number' => 'JNTMY12345678',
    ]);

    $bizContent = json_encode([
        'billCode' => 'JNTMY12345678',
        'details' => [
            [
                'scanTime' => '2024-01-15 10:30:00',
                'desc' => 'Package delivered',
                'scanType' => 'delivery',
                'scanTypeCode' => 'DL',
                'scanTypeName' => 'Delivery',
            ],
        ],
    ]);

    $signature = base64_encode(md5($bizContent . config('jnt.private_key'), true));

    $response = $this->postJson('/webhooks/jnt/status', [
        'bizContent' => $bizContent,
    ], [
        'digest' => $signature,
    ]);

    $response->assertOk();
    
    Event::assertDispatched(TrackingStatusReceived::class);
    
    expect($order->fresh()->status)->toBe('delivered');
});
```

---

## Troubleshooting

### Webhook Not Receiving Requests

**Check 1: Webhook is enabled**
```php
// In config/jnt.php
'webhooks' => [
    'enabled' => true,
],
```

**Check 2: Route is registered**
```bash
php artisan route:list | grep jnt
```

You should see:
```
POST | webhooks/jnt/status | jnt.webhooks.status
```

**Check 3: Firewall/Network**
Ensure your server accepts POST requests from J&T IPs.

### Signature Verification Fails

**Check 1: Correct private key**
```php
// Verify in .env
JNT_PRIVATE_KEY=your_actual_private_key
```

**Check 2: Raw body content**
Signature is calculated on raw bizContent. Ensure your server doesn't modify the request body.

**Check 3: Logs**
Enable detailed logging:
```php
// In config/jnt.php
'webhooks' => [
    'log_payloads' => true,
],
```

Check logs:
```bash
tail -f storage/logs/laravel.log | grep "J&T webhook"
```

### Event Listeners Not Firing

**Check 1: Listener is registered**
```php
// In app/Providers/EventServiceProvider.php
protected $listen = [
    \MasyukAI\Jnt\Events\TrackingStatusReceived::class => [
        \App\Listeners\YourListener::class,
    ],
];
```

**Check 2: Clear event cache**
```bash
php artisan event:clear
php artisan optimize:clear
```

**Check 3: Listener syntax**
Ensure your listener has correct method signature:
```php
public function handle(TrackingStatusReceived $event): void
```

### Payload Parsing Errors

**Check logs for details:**
```bash
grep "J&T webhook processing failed" storage/logs/laravel.log
```

**Common issues:**
- Missing required fields (`billCode`, `details`)
- Invalid JSON format
- Empty details array
- Wrong data types

### 401 Unauthorized Response

**Cause:** Signature verification failed

**Solutions:**
1. Verify `JNT_PRIVATE_KEY` is correct
2. Check signature calculation matches J&T's algorithm
3. Ensure bizContent is sent as raw string, not parsed JSON
4. Check middleware is applied: `'middleware' => ['api', 'jnt.verify.signature']`

### 422 Unprocessable Entity Response

**Cause:** Invalid payload structure

**Solutions:**
1. Check `bizContent` is valid JSON
2. Ensure `billCode` field exists
3. Verify `details` is an array
4. Check detail objects have required fields (scanTime, desc, scanType, etc.)

---

## API Reference

### WebhookData

```php
readonly class WebhookData
{
    public string $billCode;           // J&T tracking number
    public ?string $txlogisticId;      // Customer order reference
    public array $details;             // TrackingDetailData[]
    
    // Create from HTTP request
    public static function fromRequest(Request $request): self;
    
    // Generate success response
    public function toResponse(): array;
    
    // Get latest tracking detail
    public function getLatestDetail(): ?TrackingDetailData;
    
    // Convert to array
    public function toArray(): array;
}
```

### WebhookService

```php
class WebhookService
{
    // Verify webhook signature (timing-safe)
    public function verifySignature(string $digest, string $bizContent): bool;
    
    // Generate signature
    public function generateSignature(string $bizContent): string;
    
    // Parse webhook request
    public function parseWebhook(Request $request): WebhookData;
    
    // Generate responses
    public function successResponse(): array;
    public function failureResponse(string $message = 'fail'): array;
    
    // Extract digest header
    public function extractDigest(Request $request): string;
    
    // Verify and parse in one call
    public function verifyAndParse(Request $request): ?WebhookData;
}
```

### TrackingStatusReceived Event

```php
class TrackingStatusReceived
{
    public readonly WebhookData $webhookData;
    
    // Getters
    public function getBillCode(): string;
    public function getTxlogisticId(): ?string;
    public function getLatestStatus(): ?string;
    public function getLatestDescription(): ?string;
    public function getLatestLocation(): ?string;
    public function getLatestTimestamp(): ?string;
    
    // Status checks
    public function isDelivered(): bool;
    public function isCollected(): bool;
    public function hasProblem(): bool;
}
```

### Webhook Request Format

J&T sends webhooks in this format:

```json
{
    "digest": "base64_encoded_signature",
    "bizContent": "{\"billCode\":\"JT001\",\"txlogisticId\":\"ORDER123\",\"details\":[...]}",
    "apiAccount": "your_api_account",
    "timestamp": "1622520000000"
}
```

### Webhook Response Format

Your endpoint returns:

**Success:**
```json
{
    "code": "1",
    "msg": "success",
    "data": "SUCCESS",
    "requestId": "uuid-v4"
}
```

**Failure:**
```json
{
    "code": "0",
    "msg": "error message",
    "data": null,
    "requestId": "uuid-v4"
}
```

---

## Best Practices

### Security

1. **Always verify signatures** - The middleware does this automatically
2. **Use HTTPS** - Never expose webhooks over HTTP in production
3. **Don't log sensitive data** - Disable `log_payloads` in production
4. **Rate limiting** - Consider adding rate limiting to webhook endpoint

### Performance

1. **Queue heavy processing** - Use `ShouldQueue` interface on listeners
2. **Batch database updates** - Don't update on every detail change
3. **Cache frequently accessed data** - Reduce database queries
4. **Monitor webhook response times** - J&T may timeout slow endpoints

### Reliability

1. **Idempotent processing** - Handle duplicate webhooks gracefully
2. **Error logging** - Log all failures for debugging
3. **Fallback polling** - Don't rely solely on webhooks for critical updates
4. **Health monitoring** - Track webhook success/failure rates

### Development

1. **Use tunneling for local testing** - ngrok, Valet share, or Expose
2. **Mock webhooks in tests** - Don't rely on external services
3. **Version control configs** - Track webhook URL changes
4. **Document custom listeners** - Help future developers understand the flow

---

## Support

For issues or questions:

- **Package Issues:** [GitHub Issues](https://github.com/masyukai/jnt)
- **J&T API Questions:** J&T Express Support
- **Laravel Help:** [Laravel Documentation](https://laravel.com/docs)

---

## Changelog

### Version 1.0.0
- Initial webhook implementation
- TrackingStatusReceived event
- Automatic signature verification
- Comprehensive test coverage
