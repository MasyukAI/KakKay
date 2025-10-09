# J&T Express Webhook Troubleshooting Guide

This guide helps you diagnose and fix common issues with J&T Express webhook integration.

## Table of Contents

1. [Webhook Not Receiving Requests](#1-webhook-not-receiving-requests)
2. [Signature Verification Failures](#2-signature-verification-failures)
3. [Event Listeners Not Firing](#3-event-listeners-not-firing)
4. [Payload Parsing Errors](#4-payload-parsing-errors)
5. [Performance Issues](#5-performance-issues)
6. [Debugging Tools](#6-debugging-tools)
7. [Common Error Codes](#7-common-error-codes)

---

## 1. Webhook Not Receiving Requests

### Symptom
J&T dashboard shows webhooks sent, but your application never receives them.

### Checklist

#### ✅ Check if webhooks are enabled

```php
// Check config
php artisan tinker
>>> config('jnt.webhooks.enabled')
=> true
```

If false, enable in `.env`:
```env
JNT_WEBHOOKS_ENABLED=true
```

#### ✅ Verify route is registered

```bash
php artisan route:list | grep jnt
```

Expected output:
```
POST | webhooks/jnt/status | jnt.webhooks.status
```

If missing, check:
1. ServiceProvider is registered in `config/app.php`
2. Route file is being loaded
3. Cache cleared: `php artisan route:clear`

#### ✅ Check middleware configuration

```php
// In config/jnt.php
'webhooks' => [
    'middleware' => ['api', 'jnt.verify.signature'],
],
```

Ensure middleware isn't blocking requests:
- Remove CSRF protection from webhook route (already excluded in api middleware)
- Check rate limiting isn't too restrictive
- Verify IP whitelist allows J&T servers

#### ✅ Test endpoint manually

```bash
# Simple test (will fail signature, but proves endpoint exists)
curl -X POST https://yourdomain.com/webhooks/jnt/status \
  -H "Content-Type: application/json" \
  -d '{"bizContent": "{}"}'
```

Expected: 401 (signature failure) or 422 (validation error)
If you get 404, route isn't registered properly.

#### ✅ Check server/firewall configuration

- **HTTPS Required:** J&T only sends webhooks to HTTPS endpoints
- **Firewall:** Ensure your firewall allows incoming POST requests
- **Load Balancer:** Verify load balancer forwards webhooks to application
- **Reverse Proxy:** Check nginx/Apache config allows POST to webhook route

#### ✅ Verify J&T dashboard configuration

1. Log in to J&T Express dashboard
2. Go to Webhook Settings
3. Verify webhook URL is correct: `https://yourdomain.com/webhooks/jnt/status`
4. Check webhook is enabled
5. Test webhook from dashboard

---

## 2. Signature Verification Failures

### Symptom
Webhooks reach your server but return 401 Unauthorized.

### Root Cause
Signature verification in middleware is failing.

### Solutions

#### ✅ Verify private key is correct

```php
php artisan tinker
>>> config('jnt.private_key')
=> "your_private_key_here"
```

**Common Mistakes:**
- Using API account instead of private key
- Extra whitespace in `.env` file
- Wrong environment (sandbox vs production key)

**Fix:**
```env
# Correct format
JNT_PRIVATE_KEY=your_actual_private_key

# Wrong (has extra quotes)
JNT_PRIVATE_KEY="your_actual_private_key"

# Wrong (has whitespace)
JNT_PRIVATE_KEY= your_actual_private_key
```

After fixing, clear config:
```bash
php artisan config:clear
```

#### ✅ Check signature algorithm

The package uses: `base64_encode(md5($bizContent . $privateKey, true))`

Test signature generation:
```php
php artisan tinker

$bizContent = '{"billCode":"TEST","details":[]}';
$privateKey = config('jnt.private_key');
$signature = base64_encode(md5($bizContent . $privateKey, true));

echo "Signature: {$signature}\n";
```

#### ✅ Verify bizContent is not modified

The signature is calculated on the **raw bizContent string** before parsing.

**Problem:** Some middleware modifies request body
- JSON beautification
- Charset conversion
- Trimming whitespace

**Solution:** Exclude webhook route from body-modifying middleware

```php
// In bootstrap/app.php or middleware
$middleware->excludeFromMiddleware([
    \App\Http\Middleware\TrimStrings::class,
    \App\Http\Middleware\ConvertEmptyStringsToNull::class,
]);
```

#### ✅ Enable signature debugging

Temporarily log signature details:

```php
// In config/jnt.php
'webhooks' => [
    'log_payloads' => true, // Enable detailed logging
],
```

Check logs:
```bash
tail -f storage/logs/laravel.log | grep "J&T webhook"
```

#### ✅ Test with known good signature

```bash
#!/bin/bash

# Test script
PRIVATE_KEY="your_private_key"
BIZCONTENT='{"billCode":"TEST123","details":[{"scanTime":"2024-01-15 10:00:00","desc":"Test","scanType":"collection","scanTypeCode":"CC","scanTypeName":"Collection"}]}'

# Generate signature
SIGNATURE=$(echo -n "${BIZCONTENT}${PRIVATE_KEY}" | openssl dgst -md5 -binary | base64)

# Send request
curl -X POST https://yourdomain.com/webhooks/jnt/status \
  -H "Content-Type: application/json" \
  -H "digest: ${SIGNATURE}" \
  -d "{\"bizContent\": \"${BIZCONTENT}\"}" \
  -v
```

If this works but J&T webhooks fail, contact J&T support.

---

## 3. Event Listeners Not Firing

### Symptom
Webhook is received and returns 200, but event listeners never execute.

### Solutions

#### ✅ Verify listener is registered

```php
// In app/Providers/EventServiceProvider.php
protected $listen = [
    \MasyukAI\Jnt\Events\TrackingStatusReceived::class => [
        \App\Listeners\UpdateOrderTracking::class,
    ],
];
```

#### ✅ Clear event cache

```bash
php artisan event:clear
php artisan optimize:clear
php artisan config:clear
```

#### ✅ Check listener method signature

**Correct:**
```php
public function handle(TrackingStatusReceived $event): void
{
    // ...
}
```

**Wrong:**
```php
// Missing type hint
public function handle($event): void

// Wrong parameter name
public function handle(TrackingStatusReceived $webhook): void

// Missing void return type
public function handle(TrackingStatusReceived $event)
```

#### ✅ Test event dispatch manually

```php
php artisan tinker

use MasyukAI\Jnt\Events\TrackingStatusReceived;
use MasyukAI\Jnt\Data\WebhookData;

$webhookData = WebhookData::from([
    'billCode' => 'TEST123',
    'details' => [
        [
            'scanTime' => '2024-01-15 10:00:00',
            'desc' => 'Test',
            'scanType' => 'collection',
            'scanTypeCode' => 'CC',
            'scanTypeName' => 'Collection',
        ],
    ],
]);

event(new TrackingStatusReceived($webhookData));
```

If listener fires, the issue is with webhook processing, not the listener.

#### ✅ Check for listener exceptions

Add try-catch to listener:

```php
public function handle(TrackingStatusReceived $event): void
{
    try {
        // Your logic
    } catch (\Throwable $e) {
        Log::error('Listener failed', [
            'exception' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        throw $e;
    }
}
```

#### ✅ Verify queue configuration (if using ShouldQueue)

If listener implements `ShouldQueue`:

```bash
# Check queue worker is running
ps aux | grep "queue:work"

# Check failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all
```

---

## 4. Payload Parsing Errors

### Symptom
Webhook returns 422 Unprocessable Entity.

### Common Issues

#### ✅ Missing required fields

**Error:** "The bill code field is required"

**Cause:** J&T didn't send `billCode` field

**Solution:** Log raw payload and contact J&T:
```php
Log::warning('Missing billCode in webhook', [
    'raw_payload' => request()->all(),
]);
```

#### ✅ Invalid JSON in bizContent

**Error:** "Invalid payload"

**Cause:** bizContent is not valid JSON

**Solution:** Check if bizContent needs decoding:
```php
$bizContent = request('bizContent');
$decoded = json_decode($bizContent, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    Log::error('Invalid JSON in bizContent', [
        'error' => json_last_error_msg(),
        'bizContent' => $bizContent,
    ]);
}
```

#### ✅ Empty details array

**Error:** "Validation failed"

**Cause:** Details array is empty or missing

**Solution:** Handle empty details gracefully:
```php
if (empty($webhookData->details)) {
    Log::warning('Webhook has no tracking details', [
        'tracking_number' => $webhookData->billCode,
    ]);
    return;
}
```

#### ✅ Wrong field data types

**Error:** "The scan time field must be a valid date"

**Cause:** scanTime format doesn't match expected format

**Solution:** Use flexible date parsing:
```php
use Carbon\Carbon;

try {
    $timestamp = Carbon::parse($detail->scanTime);
} catch (\Exception $e) {
    Log::error('Invalid date format', [
        'scanTime' => $detail->scanTime,
    ]);
    $timestamp = now(); // Fallback
}
```

---

## 5. Performance Issues

### Symptom
Webhook response is slow, timeouts, or causes server load.

### Solutions

#### ✅ Queue heavy processing

**Problem:** Synchronous processing blocks webhook response

**Solution:** Use queued listeners
```php
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateOrderTracking implements ShouldQueue
{
    public function handle(TrackingStatusReceived $event): void
    {
        // Heavy processing here
    }
}
```

#### ✅ Optimize database queries

**Problem:** N+1 queries or missing indexes

**Solution:**
```php
// Bad: N+1 query
$order = Order::where('tracking_number', $trackingNumber)->first();
$customer = $order->customer; // Extra query

// Good: Eager loading
$order = Order::with('customer')
    ->where('tracking_number', $trackingNumber)
    ->first();
```

Add database indexes:
```php
Schema::table('orders', function (Blueprint $table) {
    $table->index('tracking_number');
});
```

#### ✅ Reduce logging overhead

**Problem:** Excessive logging slows down processing

**Solution:**
```php
// Disable payload logging in production
JNT_WEBHOOK_LOG_PAYLOADS=false
```

#### ✅ Cache frequently accessed data

**Problem:** Repeated config/database lookups

**Solution:**
```php
// Cache order lookup
$order = Cache::remember(
    "order:tracking:{$trackingNumber}",
    60,
    fn() => Order::where('tracking_number', $trackingNumber)->first()
);
```

#### ✅ Monitor response time

Add middleware to track webhook response time:
```php
class TrackWebhookPerformance
{
    public function handle(Request $request, Closure $next)
    {
        $start = microtime(true);
        
        $response = $next($request);
        
        $duration = (microtime(true) - $start) * 1000;
        
        Log::info('Webhook performance', [
            'duration_ms' => $duration,
            'tracking_number' => $request->input('bizContent.billCode'),
        ]);
        
        return $response;
    }
}
```

---

## 6. Debugging Tools

### Enable Debug Mode

```env
APP_DEBUG=true
JNT_WEBHOOK_LOG_PAYLOADS=true
```

### Log Everything

```php
// In WebhookController
Log::info('Webhook received', [
    'raw_request' => $request->all(),
    'headers' => $request->headers->all(),
    'ip' => $request->ip(),
]);
```

### Test Signature Verification

```php
php artisan tinker

use MasyukAI\Jnt\Services\WebhookService;

$service = app(WebhookService::class);

$bizContent = '{"billCode":"TEST"}';
$signature = $service->generateSignature($bizContent);
$isValid = $service->verifySignature($signature, $bizContent);

echo "Generated: {$signature}\n";
echo "Valid: " . ($isValid ? 'Yes' : 'No') . "\n";
```

### Inspect Webhook Data

```php
php artisan tinker

use MasyukAI\Jnt\Data\WebhookData;

$json = '{"billCode":"TEST","details":[...]}';
$data = WebhookData::from(json_decode($json, true));

dd($data);
```

### Monitor Failed Jobs

```bash
# View failed jobs
php artisan queue:failed

# Retry specific job
php artisan queue:retry <job-id>

# Clear failed jobs
php artisan queue:flush
```

### Check Package Version

```bash
composer show masyukai/jnt
```

---

## 7. Common Error Codes

### HTTP 401 Unauthorized

**Meaning:** Signature verification failed

**Causes:**
- Wrong private key
- Modified bizContent
- Incorrect signature algorithm

**Fix:** See [Signature Verification Failures](#2-signature-verification-failures)

### HTTP 422 Unprocessable Entity

**Meaning:** Payload validation failed

**Causes:**
- Missing required fields (billCode, details)
- Invalid JSON format
- Wrong data types

**Fix:** See [Payload Parsing Errors](#4-payload-parsing-errors)

### HTTP 500 Internal Server Error

**Meaning:** Application error during processing

**Causes:**
- Exception in listener
- Database error
- Configuration error

**Fix:** Check logs
```bash
tail -f storage/logs/laravel.log
```

### HTTP 200 but Event Doesn't Fire

**Meaning:** Webhook received but event system issue

**Causes:**
- Listener not registered
- Event cache not cleared
- Queue worker not running

**Fix:** See [Event Listeners Not Firing](#3-event-listeners-not-firing)

---

## Getting Help

### Before Asking for Help

1. ✅ Check this troubleshooting guide
2. ✅ Enable debug logging
3. ✅ Review Laravel logs: `storage/logs/laravel.log`
4. ✅ Test signature generation manually
5. ✅ Verify configuration: `config('jnt.webhooks')`

### Information to Include

When reporting issues, provide:

```bash
# Package version
composer show masyukai/jnt

# Laravel version
php artisan --version

# PHP version
php --version

# Configuration
php artisan tinker
>>> config('jnt.webhooks')

# Recent logs
tail -100 storage/logs/laravel.log | grep "webhook"

# Test signature
php artisan tinker
>>> app(\MasyukAI\Jnt\Services\WebhookService::class)->generateSignature('{"test":true}')
```

### Contact Support

- **Package Issues:** [GitHub Issues](https://github.com/masyukai/jnt/issues)
- **J&T API Issues:** J&T Express Technical Support
- **Laravel Issues:** [Laravel Forums](https://laracasts.com/discuss)

---

## Quick Reference

### Verify Setup Checklist

```bash
# 1. Config is correct
php artisan config:clear
php artisan tinker
>>> config('jnt.webhooks')

# 2. Route is registered
php artisan route:list | grep jnt

# 3. Listener is registered
php artisan event:list | grep TrackingStatusReceived

# 4. Queue worker running (if using queues)
ps aux | grep "queue:work"

# 5. Test endpoint
curl -X POST https://yourdomain.com/webhooks/jnt/status \
  -H "Content-Type: application/json" \
  -d '{"bizContent": "{}"}'
```

### Debug Commands

```bash
# Clear all caches
php artisan optimize:clear

# View logs live
tail -f storage/logs/laravel.log

# Test event dispatch
php artisan tinker
>>> event(new \MasyukAI\Jnt\Events\TrackingStatusReceived(...))

# Check failed jobs
php artisan queue:failed

# Generate test signature
php artisan tinker
>>> app(\MasyukAI\Jnt\Services\WebhookService::class)->generateSignature('test')
```

---

## Conclusion

Most webhook issues fall into these categories:
1. Configuration errors (wrong keys, disabled webhooks)
2. Signature verification failures (wrong key, modified content)
3. Event system issues (listener not registered, queue not running)
4. Payload parsing errors (missing fields, invalid JSON)

Follow this guide systematically to identify and fix your specific issue. If problems persist, collect the diagnostic information listed above and seek support.
