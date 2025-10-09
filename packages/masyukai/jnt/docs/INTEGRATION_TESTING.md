# Integration Testing Guide

**Package:** MasyukAI/JNT Express Integration  
**Purpose:** Real-world testing with J&T Express Sandbox API

---

## Table of Contents

1. [Prerequisites](#prerequisites)
2. [Sandbox Setup](#sandbox-setup)
3. [Test Environment Configuration](#test-environment-configuration)
4. [Integration Test Suite](#integration-test-suite)
5. [Common Test Scenarios](#common-test-scenarios)
6. [Troubleshooting](#troubleshooting)

---

## Prerequisites

### 1. J&T Express Sandbox Account

Contact J&T Express to obtain sandbox credentials:
- Customer Code
- Password (hashed)
- Private Key (for signature generation)
- Sandbox API Base URL

### 2. Testing Requirements

```bash
# Install package with dev dependencies
composer require masyukai/jnt --dev

# Ensure Pest is available
composer require pestphp/pest --dev
```

### 3. Environment Setup

Create a `.env.testing` file:

```env
# J&T Sandbox Credentials
JNT_CUSTOMER_CODE=SANDBOX_CUSTOMER_CODE
JNT_PASSWORD=SANDBOX_PASSWORD_HASH
JNT_PRIVATE_KEY=SANDBOX_PRIVATE_KEY

# Sandbox Configuration
JNT_ENVIRONMENT=sandbox
JNT_BASE_URL=https://sandbox-api.jtexpress.com.my
JNT_TIMEOUT=60  # Longer timeout for sandbox

# Test Settings
JNT_LOG_REQUESTS=true  # Log all requests for debugging
JNT_LOG_CHANNEL=single

# Webhook Testing
JNT_WEBHOOKS_ENABLED=false  # Disable for integration tests
```

---

## Sandbox Setup

### 1. Verify Sandbox Connectivity

```bash
php artisan jnt:config:check --env=testing
```

Expected output:
```
 ✓ API Account: Configured (SANDBOX_XXX)
 ✓ Environment: sandbox
 ✓ Base URL: https://sandbox-api.jtexpress.com.my
 ✓ Private Key: Valid RSA-2048 format
 ✓ Connectivity: Connection successful (250ms)

 SUCCESS  All configuration checks passed!
```

### 2. Test Authentication

```php
// tests/Integration/AuthenticationTest.php
<?php

use MasyukAI\Jnt\Facades\JntExpress;
use MasyukAI\Jnt\Exceptions\JntApiException;

test('sandbox authentication works', function () {
    // This will fail if credentials are invalid
    expect(fn() => JntExpress::queryOrder('TEST_ORDER_001'))
        ->not->toThrow(JntApiException::class, 'API account does not exist');
})->group('integration', 'auth');
```

---

## Test Environment Configuration

### PHPUnit Configuration

Update `phpunit.xml`:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit>
    <testsuites>
        <testsuite name="Integration">
            <directory>tests/Integration</directory>
        </testsuite>
    </testsuites>
    
    <php>
        <env name="APP_ENV" value="testing"/>
        <env name="JNT_ENVIRONMENT" value="sandbox"/>
    </php>
</phpunit>
```

### Pest Configuration

Update `tests/Pest.php`:

```php
<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class)
    ->in('Integration');

// Helper function for integration tests
function skipIfNoSandboxCredentials(): void
{
    if (empty(config('jnt.customer_code'))) {
        test()->markTestSkipped('Sandbox credentials not configured');
    }
}
```

---

## Integration Test Suite

### Complete Order Lifecycle Test

```php
<?php

declare(strict_types=1);

namespace Tests\Integration;

use MasyukAI\Jnt\Facades\JntExpress;
use MasyukAI\Jnt\Data\{AddressData, ItemData, PackageInfoData};
use MasyukAI\Jnt\Enums\{ExpressType, ServiceType, PaymentType, GoodsType, CancellationReason};

beforeEach(function () {
    skipIfNoSandboxCredentials();
});

test('complete order lifecycle with sandbox', function () {
    // 1. CREATE ORDER
    $this->info('Step 1: Creating order...');
    
    $sender = new AddressData(
        name: 'Test Merchant',
        phone: '+60123456789',
        countryCode: 'MY',
        address: '123 Test Street, Taman Test',
        postCode: '50000',
        prov: 'Wilayah Persekutuan',
        city: 'Kuala Lumpur',
        area: 'Bukit Bintang'
    );
    
    $receiver = new AddressData(
        name: 'Test Customer',
        phone: '+60198765432',
        countryCode: 'MY',
        address: '456 Customer Road, Taman Customer',
        postCode: '47000',
        prov: 'Selangor',
        city: 'Petaling Jaya',
        area: 'SS2'
    );
    
    $items = [
        new ItemData(
            description: 'Test Product',
            quantity: 1,
            itemValue: 100.00,
            weight: 500
        ),
    ];
    
    $packageInfo = new PackageInfoData(
        weight: 0.5,
        length: 20.0,
        width: 15.0,
        height: 10.0,
        expressType: ExpressType::DOMESTIC,
        serviceType: ServiceType::DOOR_TO_DOOR,
        paymentType: PaymentType::PREPAID_POSTPAID,
        goodsType: GoodsType::PACKAGE
    );
    
    $order = JntExpress::createOrder(
        sender: $sender,
        receiver: $receiver,
        items: $items,
        packageInfo: $packageInfo,
        orderId: 'TEST_' . time()
    );
    
    expect($order->orderId)->not->toBeNull();
    expect($order->trackingNumber)->not->toBeNull();
    $this->info("✓ Order created: {$order->orderId}");
    $this->info("  Tracking: {$order->trackingNumber}");
    
    // Small delay for J&T system processing
    sleep(2);
    
    // 2. QUERY ORDER
    $this->info('Step 2: Querying order...');
    
    $queryResult = JntExpress::queryOrder($order->orderId);
    
    expect($queryResult)->toBeArray();
    expect($queryResult)->toHaveKey('data');
    $this->info("✓ Order query successful");
    
    // 3. TRACK PARCEL
    $this->info('Step 3: Tracking parcel...');
    
    $tracking = JntExpress::trackParcel(orderId: $order->orderId);
    
    expect($tracking->orderId)->toBe($order->orderId);
    expect($tracking->trackingNumber)->toBe($order->trackingNumber);
    expect($tracking->details)->toBeArray();
    $this->info("✓ Tracking successful");
    $this->info("  Status: {$tracking->status}");
    $this->info("  Location: " . ($tracking->getCurrentLocation() ?? 'N/A'));
    
    // 4. PRINT WAYBILL
    $this->info('Step 4: Printing waybill...');
    
    $waybill = JntExpress::printOrder(orderId: $order->orderId);
    
    expect($waybill)->toBeArray();
    expect($waybill)->toHaveKey('data');
    $this->info("✓ Waybill generation successful");
    
    // 5. CANCEL ORDER (only if not shipped)
    if (!$tracking->isInTransit() && !$tracking->isDelivered()) {
        $this->info('Step 5: Cancelling order...');
        
        $cancelResult = JntExpress::cancelOrder(
            orderId: $order->orderId,
            reason: CancellationReason::SYSTEM_ERROR,
            trackingNumber: $order->trackingNumber
        );
        
        expect($cancelResult)->toBeArray();
        $this->info("✓ Order cancellation successful");
    } else {
        $this->info('Step 5: Skipping cancellation (order already in transit)');
    }
    
    $this->info('✅ Complete lifecycle test passed!');
    
})->group('integration', 'lifecycle')->timeout(30);
```

### Order Creation Test

```php
test('creates order with all required fields', function () {
    skipIfNoSandboxCredentials();
    
    $order = JntExpress::createOrder(
        sender: getSandboxSenderAddress(),
        receiver: getSandboxReceiverAddress(),
        items: [getSandboxTestItem()],
        packageInfo: getSandboxPackageInfo(),
        orderId: 'TEST_CREATE_' . time()
    );
    
    expect($order)
        ->toBeInstanceOf(\MasyukAI\Jnt\Data\OrderData::class)
        ->and($order->orderId)->not->toBeNull()
        ->and($order->trackingNumber)->not->toBeNull()
        ->and($order->chargeableWeight)->toBeGreaterThan(0);
        
})->group('integration', 'order-creation');

test('validates required fields in order creation', function () {
    skipIfNoSandboxCredentials();
    
    expect(fn() => JntExpress::createOrder(
        sender: getSandboxSenderAddress(),
        receiver: getSandboxReceiverAddress(),
        items: [],  // Empty items - should fail
        packageInfo: getSandboxPackageInfo(),
        orderId: 'TEST_INVALID_' . time()
    ))->toThrow(\MasyukAI\Jnt\Exceptions\JntValidationException::class);
    
})->group('integration', 'order-creation');
```

### Tracking Test

```php
test('tracks parcel by order ID', function () {
    skipIfNoSandboxCredentials();
    
    // First create an order
    $order = createSandboxTestOrder();
    sleep(2);  // Wait for system processing
    
    // Then track it
    $tracking = JntExpress::trackParcel(orderId: $order->orderId);
    
    expect($tracking)
        ->toBeInstanceOf(\MasyukAI\Jnt\Data\TrackingData::class)
        ->and($tracking->orderId)->toBe($order->orderId)
        ->and($tracking->trackingNumber)->toBe($order->trackingNumber)
        ->and($tracking->details)->toBeArray();
        
})->group('integration', 'tracking');

test('tracks parcel by tracking number', function () {
    skipIfNoSandboxCredentials();
    
    // First create an order
    $order = createSandboxTestOrder();
    sleep(2);
    
    // Track by tracking number
    $tracking = JntExpress::trackParcel(trackingNumber: $order->trackingNumber);
    
    expect($tracking->trackingNumber)->toBe($order->trackingNumber);
    
})->group('integration', 'tracking');

test('handles tracking for non-existent order', function () {
    skipIfNoSandboxCredentials();
    
    expect(fn() => JntExpress::trackParcel(orderId: 'NONEXISTENT_ORDER'))
        ->toThrow(\MasyukAI\Jnt\Exceptions\JntApiException::class);
        
})->group('integration', 'tracking');
```

### Cancellation Test

```php
test('cancels order with enum reason', function () {
    skipIfNoSandboxCredentials();
    
    $order = createSandboxTestOrder();
    sleep(2);
    
    $result = JntExpress::cancelOrder(
        orderId: $order->orderId,
        reason: CancellationReason::OUT_OF_STOCK
    );
    
    expect($result)->toBeArray();
    
})->group('integration', 'cancellation');

test('cancels order with custom reason', function () {
    skipIfNoSandboxCredentials();
    
    $order = createSandboxTestOrder();
    sleep(2);
    
    $result = JntExpress::cancelOrder(
        orderId: $order->orderId,
        reason: 'Custom test cancellation reason'
    );
    
    expect($result)->toBeArray();
    
})->group('integration', 'cancellation');

test('prevents cancellation of shipped order', function () {
    skipIfNoSandboxCredentials();
    
    // Note: This test requires an order that's already shipped
    // In sandbox, you may need to use a pre-existing shipped order
    
    expect(fn() => JntExpress::cancelOrder(
        orderId: 'SHIPPED_ORDER_ID',  // Use actual shipped order from sandbox
        reason: CancellationReason::CUSTOMER_CHANGED_MIND
    ))->toThrow(\MasyukAI\Jnt\Exceptions\JntApiException::class);
    
})->group('integration', 'cancellation')->skip('Requires shipped order');
```

### Waybill Printing Test

```php
test('prints waybill for order', function () {
    skipIfNoSandboxCredentials();
    
    $order = createSandboxTestOrder();
    sleep(2);
    
    $waybill = JntExpress::printOrder(orderId: $order->orderId);
    
    expect($waybill)
        ->toBeArray()
        ->toHaveKey('data')
        ->and($waybill['data'])->toBeArray();
        
    // Check for either base64 content or URL
    $hasContent = isset($waybill['data']['base64EncodeContent']) || 
                  isset($waybill['data']['urlContent']);
    expect($hasContent)->toBeTrue();
    
})->group('integration', 'waybill');

test('prints waybill with custom template', function () {
    skipIfNoSandboxCredentials();
    
    $order = createSandboxTestOrder();
    sleep(2);
    
    $waybill = JntExpress::printOrder(
        orderId: $order->orderId,
        templateName: 'thermal_80mm'
    );
    
    expect($waybill)->toBeArray()->toHaveKey('data');
    
})->group('integration', 'waybill');
```

---

## Common Test Scenarios

### Helper Functions

Add these to `tests/Helpers.php`:

```php
<?php

use MasyukAI\Jnt\Facades\JntExpress;
use MasyukAI\Jnt\Data\{AddressData, ItemData, PackageInfoData};
use MasyukAI\Jnt\Enums\{ExpressType, ServiceType, PaymentType, GoodsType};

function getSandboxSenderAddress(): AddressData
{
    return new AddressData(
        name: 'Sandbox Merchant',
        phone: '+60123456789',
        countryCode: 'MY',
        address: '123 Sandbox Street, Taman Test',
        postCode: '50000',
        prov: 'Wilayah Persekutuan',
        city: 'Kuala Lumpur',
        area: 'Bukit Bintang'
    );
}

function getSandboxReceiverAddress(): AddressData
{
    return new AddressData(
        name: 'Sandbox Customer',
        phone: '+60198765432',
        countryCode: 'MY',
        address: '456 Test Road, Taman Customer',
        postCode: '47000',
        prov: 'Selangor',
        city: 'Petaling Jaya',
        area: 'SS2'
    );
}

function getSandboxTestItem(): ItemData
{
    return new ItemData(
        description: 'Sandbox Test Product',
        quantity: 1,
        itemValue: 100.00,
        weight: 500
    );
}

function getSandboxPackageInfo(): PackageInfoData
{
    return new PackageInfoData(
        weight: 0.5,
        length: 20.0,
        width: 15.0,
        height: 10.0,
        expressType: ExpressType::DOMESTIC,
        serviceType: ServiceType::DOOR_TO_DOOR,
        paymentType: PaymentType::PREPAID_POSTPAID,
        goodsType: GoodsType::PACKAGE
    );
}

function createSandboxTestOrder(): \MasyukAI\Jnt\Data\OrderData
{
    return JntExpress::createOrder(
        sender: getSandboxSenderAddress(),
        receiver: getSandboxReceiverAddress(),
        items: [getSandboxTestItem()],
        packageInfo: getSandboxPackageInfo(),
        orderId: 'TEST_' . time() . '_' . rand(1000, 9999)
    );
}
```

### Running Integration Tests

```bash
# Run all integration tests
vendor/bin/pest --group=integration

# Run specific test group
vendor/bin/pest --group=integration,order-creation
vendor/bin/pest --group=integration,tracking
vendor/bin/pest --group=integration,cancellation

# Run with logging
vendor/bin/pest --group=integration --log-junit results.xml
```

### CI/CD Integration

Add to `.github/workflows/integration-tests.yml`:

```yaml
name: Integration Tests

on:
  schedule:
    - cron: '0 0 * * *'  # Daily at midnight
  workflow_dispatch:

jobs:
  integration:
    runs-on: ubuntu-latest
    
    steps:
      - uses: actions/checkout@v3
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
          
      - name: Install dependencies
        run: composer install
        
      - name: Run integration tests
        env:
          JNT_CUSTOMER_CODE: ${{ secrets.JNT_SANDBOX_CUSTOMER_CODE }}
          JNT_PASSWORD: ${{ secrets.JNT_SANDBOX_PASSWORD }}
          JNT_PRIVATE_KEY: ${{ secrets.JNT_SANDBOX_PRIVATE_KEY }}
        run: vendor/bin/pest --group=integration
```

---

## Troubleshooting

### Common Issues

#### 1. Authentication Failures

```
Error: API account does not exist (145003010)
```

**Solution:**
- Verify `customer_code` is correct
- Ensure you're using sandbox credentials for sandbox environment
- Check environment is set to 'sandbox' in config

#### 2. Signature Verification Failures

```
Error: Signature verification failed (145003030)
```

**Solution:**
- Verify `private_key` is correct and matches format
- Ensure no whitespace in private key
- Check payload is being constructed correctly

#### 3. Timeout Issues

```
Error: Connection timeout
```

**Solution:**
- Increase timeout in config: `JNT_TIMEOUT=60`
- Check sandbox API is accessible
- Verify network connectivity

#### 4. Order Not Found

```
Error: Data cannot be found (999001030)
```

**Solution:**
- Add delay after order creation: `sleep(2)`
- Verify order ID is correct
- Check if order was actually created successfully

### Debug Mode

Enable detailed logging:

```php
// config/jnt.php
'logging' => [
    'enabled' => true,
    'channel' => 'single',
    'level' => 'debug',
],
```

View logs:

```bash
tail -f storage/logs/laravel.log
```

### Sandbox Limitations

- Sandbox may have rate limits
- Some features may behave differently than production
- Actual shipping won't occur
- Some tracking statuses may not be available
- Webhook delivery may be delayed or not work

---

## Best Practices

### 1. Use Unique Order IDs

```php
// ✅ Good
$orderId = 'TEST_' . time() . '_' . rand(1000, 9999);

// ❌ Bad
$orderId = 'TEST_ORDER';  // Will fail if already exists
```

### 2. Add Delays Between Operations

```php
// ✅ Good
$order = JntExpress::createOrder(...);
sleep(2);  // Wait for system processing
$tracking = JntExpress::trackParcel(orderId: $order->orderId);

// ❌ Bad
$order = JntExpress::createOrder(...);
$tracking = JntExpress::trackParcel(orderId: $order->orderId);  // May fail
```

### 3. Clean Up Test Data

```php
afterEach(function () {
    // Cancel test orders to keep sandbox clean
    if (isset($this->testOrder)) {
        try {
            JntExpress::cancelOrder(
                orderId: $this->testOrder->orderId,
                reason: 'Test cleanup'
            );
        } catch (\Exception $e) {
            // Ignore errors during cleanup
        }
    }
});
```

### 4. Skip Tests Without Credentials

```php
beforeEach(function () {
    skipIfNoSandboxCredentials();
});
```

### 5. Use Test Groups

```php
test('...')->group('integration', 'order-creation', 'critical');
```

---

## Support

For sandbox access or integration testing issues:

- **J&T Support:** support@jtexpress.com.my
- **Package Issues:** https://github.com/masyukai/jnt/issues

---

**Last Updated:** 2025-01-08  
**Package Version:** 1.0.0
