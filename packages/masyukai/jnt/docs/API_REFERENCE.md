# J&T Express API Reference

**Package:** MasyukAI/JNT Express Integration  
**Version:** 1.0.0  
**Laravel:** 11+  
**PHP:** 8.2+

---

## Table of Contents

1. [Installation](#installation)
2. [Configuration](#configuration)
3. [Service Methods](#service-methods)
4. [Data Objects](#data-objects)
5. [Enums](#enums)
6. [Events](#events)
7. [Notifications](#notifications)
8. [Artisan Commands](#artisan-commands)
9. [Webhooks](#webhooks)
10. [Error Handling](#error-handling)
11. [Testing](#testing)

---

## Installation

```bash
composer require masyukai/jnt
```

### Publish Configuration

```bash
php artisan vendor:publish --tag=jnt-config
```

### Verify Configuration

```bash
php artisan jnt:config:check
```

---

## Configuration

### Environment Variables

Add these to your `.env` file:

```env
# Required
JNT_CUSTOMER_CODE=your_customer_code
JNT_PASSWORD=your_password
JNT_PRIVATE_KEY=your_private_key

# Optional
JNT_ENVIRONMENT=production  # or 'sandbox'
JNT_BASE_URL=https://api.jtexpress.com.my
JNT_TIMEOUT=30
JNT_RETRY_TIMES=3
JNT_RETRY_DELAY=100

# Webhooks
JNT_WEBHOOKS_ENABLED=true
JNT_WEBHOOKS_ROUTE=/webhooks/jnt/status
JNT_WEBHOOKS_MIDDLEWARE=web

# Logging
JNT_LOG_REQUESTS=false
JNT_LOG_CHANNEL=stack
```

### Configuration File

```php
// config/jnt.php
return [
    'customer_code' => env('JNT_CUSTOMER_CODE'),
    'password' => env('JNT_PASSWORD'),
    'private_key' => env('JNT_PRIVATE_KEY'),
    'environment' => env('JNT_ENVIRONMENT', 'production'),
    'base_url' => env('JNT_BASE_URL'),
    
    'http' => [
        'timeout' => env('JNT_TIMEOUT', 30),
        'retry_times' => env('JNT_RETRY_TIMES', 3),
        'retry_delay' => env('JNT_RETRY_DELAY', 100),
    ],
    
    'webhooks' => [
        'enabled' => env('JNT_WEBHOOKS_ENABLED', true),
        'route' => env('JNT_WEBHOOKS_ROUTE', '/webhooks/jnt/status'),
        'middleware' => explode(',', env('JNT_WEBHOOKS_MIDDLEWARE', 'web')),
    ],
    
    'logging' => [
        'enabled' => env('JNT_LOG_REQUESTS', false),
        'channel' => env('JNT_LOG_CHANNEL', 'stack'),
    ],
];
```

---

## Service Methods

### Creating Orders

#### Using Facade (Recommended)

```php
use MasyukAI\Jnt\Facades\JntExpress;
use MasyukAI\Jnt\Data\{AddressData, ItemData, PackageInfoData};
use MasyukAI\Jnt\Enums\{ExpressType, ServiceType, PaymentType, GoodsType};

// Prepare data
$sender = new AddressData(
    name: 'Merchant Store',
    phone: '+60123456789',
    countryCode: 'MY',
    address: '123 Main Street, Taman ABC',
    postCode: '50000',
    prov: 'Wilayah Persekutuan',
    city: 'Kuala Lumpur',
    area: 'Bukit Bintang'
);

$receiver = new AddressData(
    name: 'John Customer',
    phone: '+60198765432',
    countryCode: 'MY',
    address: '456 Customer Road, Taman XYZ',
    postCode: '47000',
    prov: 'Selangor',
    city: 'Petaling Jaya',
    area: 'SS2'
);

$items = [
    new ItemData(
        description: 'T-Shirt - Blue (Size M)',
        quantity: 2,
        itemValue: 50.00,
        weight: 300 // grams
    ),
    new ItemData(
        description: 'Jeans - Black (Size 32)',
        quantity: 1,
        itemValue: 120.00,
        weight: 600 // grams
    ),
];

$packageInfo = new PackageInfoData(
    weight: 0.9, // kg
    length: 30.0, // cm
    width: 20.0, // cm
    height: 10.0, // cm
    expressType: ExpressType::DOMESTIC,
    serviceType: ServiceType::DOOR_TO_DOOR,
    paymentType: PaymentType::PREPAID_POSTPAID,
    goodsType: GoodsType::PACKAGE
);

// Create order
$order = JntExpress::createOrder(
    sender: $sender,
    receiver: $receiver,
    items: $items,
    packageInfo: $packageInfo,
    orderId: 'ORDER-2025-001'  // Your unique order ID
);

// Access response
echo "Order created: {$order->orderId}\n";
echo "Tracking number: {$order->trackingNumber}\n";
echo "Chargeable weight: {$order->chargeableWeight} kg\n";
```

#### Using Service (Dependency Injection)

```php
use MasyukAI\Jnt\Services\JntExpressService;

class OrderController
{
    public function __construct(
        protected JntExpressService $jnt
    ) {}
    
    public function store(Request $request)
    {
        $order = $this->jnt->createOrder(
            sender: $sender,
            receiver: $receiver,
            items: $items,
            packageInfo: $packageInfo,
            orderId: $request->input('order_id')
        );
        
        return response()->json($order);
    }
}
```

#### Using Builder Pattern

```php
$order = JntExpress::createOrderBuilder()
    ->orderId('ORDER-2025-001')
    ->sender($sender)
    ->receiver($receiver)
    ->addItem($items[0])
    ->addItem($items[1])
    ->packageInfo($packageInfo)
    ->create();
```

---

### Tracking Parcels

#### By Order ID

```php
$tracking = JntExpress::trackParcel(orderId: 'ORDER-2025-001');

echo "Status: {$tracking->status}\n";
echo "Current location: {$tracking->getCurrentLocation()}\n";
echo "Is delivered: " . ($tracking->isDelivered() ? 'Yes' : 'No') . "\n";

// Access tracking details
foreach ($tracking->details as $detail) {
    echo "{$detail->scanTime}: {$detail->description}\n";
    echo "  Location: {$detail->scanNetworkCity}, {$detail->scanNetworkProvince}\n";
}
```

#### By Tracking Number

```php
$tracking = JntExpress::trackParcel(trackingNumber: 'JT123456789MY');
```

#### Check Status

```php
if ($tracking->isDelivered()) {
    // Parcel has been delivered
    $deliveryTime = $tracking->getDeliveryTime();
    $signatureUrl = $tracking->getSignatureUrl();
}

if ($tracking->isInTransit()) {
    // Parcel is on the way
    $currentLocation = $tracking->getCurrentLocation();
    $estimatedDelivery = $tracking->getEstimatedDelivery();
}

if ($tracking->hasProblems()) {
    // There's an issue
    $problemDescription = $tracking->getProblemDescription();
    $problemType = $tracking->getProblemType();
}
```

---

### Cancelling Orders

```php
use MasyukAI\Jnt\Enums\CancellationReason;

// Using enum (recommended)
$result = JntExpress::cancelOrder(
    orderId: 'ORDER-2025-001',
    reason: CancellationReason::OUT_OF_STOCK
);

// Using custom reason
$result = JntExpress::cancelOrder(
    orderId: 'ORDER-2025-001',
    reason: 'Customer requested cancellation due to wrong size'
);

// With tracking number (optional)
$result = JntExpress::cancelOrder(
    orderId: 'ORDER-2025-001',
    reason: CancellationReason::CUSTOMER_CHANGED_MIND,
    trackingNumber: 'JT123456789MY'
);

// Check if customer contact is required
if (CancellationReason::OUT_OF_STOCK->requiresCustomerContact()) {
    // Send notification to customer
}
```

---

### Printing Waybills

```php
// Generate waybill PDF
$waybill = JntExpress::printOrder(orderId: 'ORDER-2025-001');

// Save to file
$pdfContent = base64_decode($waybill['data']['base64EncodeContent']);
file_put_contents('waybill.pdf', $pdfContent);

// Or get download URL (for multi-parcel)
if (isset($waybill['data']['urlContent'])) {
    $downloadUrl = $waybill['data']['urlContent'];
}

// With custom template
$waybill = JntExpress::printOrder(
    orderId: 'ORDER-2025-001',
    templateName: 'thermal_80mm'
);
```

---

## Data Objects

### AddressData

```php
new AddressData(
    name: string,              // Required, max 200 chars
    phone: string,             // Required, max 50 chars
    countryCode: string,       // Required, 2-char ISO code (e.g., 'MY')
    address: string,           // Required, max 200 chars
    postCode: string,          // Required, max 20 chars
    prov: string,              // Required (optional for international)
    city: string,              // Required (optional for international)
    area: string               // Required (optional for international)
)
```

### ItemData

```php
new ItemData(
    description: string,       // Required, max 500 chars
    quantity: int|string,      // Required, positive integer
    itemValue: float|string,   // Required, min 0.01 (MYR)
    weight: int|string         // Required, in grams (50-999,999)
)
```

### PackageInfoData

```php
new PackageInfoData(
    weight: float|string,      // Required, in kg, 2 decimal places (0.01-999.99)
    length: float|string,      // Required, in cm, 2 decimal places (1.00-999.99)
    width: float|string,       // Required, in cm, 2 decimal places (1.00-999.99)
    height: float|string,      // Required, in cm, 2 decimal places (1.00-999.99)
    expressType: ExpressType,  // Required
    serviceType: ServiceType,  // Required
    paymentType: PaymentType,  // Required
    goodsType: GoodsType       // Required
)
```

### TrackingData

```php
readonly class TrackingData
{
    public string $orderId;
    public string $trackingNumber;
    public string $status;
    public array $details;  // Array of TrackingDetailData
    
    // Helper methods
    public function isDelivered(): bool;
    public function isInTransit(): bool;
    public function isCollected(): bool;
    public function hasProblems(): bool;
    public function getCurrentLocation(): ?string;
    public function getDeliveryTime(): ?string;
    public function getEstimatedDelivery(): ?string;
    public function getSignatureUrl(): ?string;
    public function getProblemDescription(): ?string;
    public function getProblemType(): ?string;
}
```

### OrderData

```php
readonly class OrderData
{
    public string $orderId;
    public string $trackingNumber;
    public array $additionalTrackingNumbers;
    public float $chargeableWeight;
    
    public function toArray(): array;
    public static function fromApiArray(array $data): self;
}
```

---

## Enums

### ExpressType

```php
enum ExpressType: string
{
    case DOMESTIC = 'EZ';       // Domestic shipping
    case NEXT_DAY = 'EX';       // Next day delivery
    case FRESH = 'FD';          // Fresh products (cold chain)
}
```

### ServiceType

```php
enum ServiceType: string
{
    case DOOR_TO_DOOR = '1';    // Standard pickup & delivery
    case WALK_IN = '6';         // Customer drop-off
}
```

### PaymentType

```php
enum PaymentType: string
{
    case PREPAID_POSTPAID = 'PP_PM';  // Prepaid + postpaid
    case PREPAID_CASH = 'PP_CASH';    // Prepaid cash
    case COLLECT_CASH = 'CC_CASH';    // Cash on delivery
}
```

### GoodsType

```php
enum GoodsType: string
{
    case DOCUMENT = 'ITN2';     // Documents
    case PACKAGE = 'ITN8';      // Packages/parcels
}
```

### CancellationReason

```php
enum CancellationReason: string
{
    // Customer-initiated
    case CUSTOMER_CHANGED_MIND = 'customer_changed_mind';
    case CUSTOMER_WRONG_ADDRESS = 'customer_wrong_address';
    case CUSTOMER_FOUND_CHEAPER = 'customer_found_cheaper';
    case CUSTOMER_NO_LONGER_NEEDED = 'customer_no_longer_needed';
    
    // Merchant-initiated
    case OUT_OF_STOCK = 'out_of_stock';
    case PRICE_ERROR = 'price_error';
    case PRODUCT_DISCONTINUED = 'product_discontinued';
    case UNABLE_TO_FULFILL = 'unable_to_fulfill';
    
    // Delivery issues
    case ADDRESS_INCORRECT = 'address_incorrect';
    case CUSTOMER_UNREACHABLE = 'customer_unreachable';
    case DELIVERY_AREA_NOT_COVERED = 'delivery_area_not_covered';
    
    // Payment issues
    case PAYMENT_FAILED = 'payment_failed';
    case FRAUD_SUSPECTED = 'fraud_suspected';
    
    // System issues
    case DUPLICATE_ORDER = 'duplicate_order';
    case SYSTEM_ERROR = 'system_error';
    case OTHER = 'other';
    
    // Helper methods
    public function getDescription(): string;
    public function getCategory(): string;
    public function requiresCustomerContact(): bool;
    public function isCustomerInitiated(): bool;
    public function isMerchantInitiated(): bool;
    public function isDeliveryIssue(): bool;
    public function isPaymentIssue(): bool;
    public static function fromString(string $value): self;
}
```

---

## Events

All events are automatically dispatched when their respective actions occur.

### OrderCreatedEvent

**Dispatched:** After successful order creation

```php
use MasyukAI\Jnt\Events\OrderCreatedEvent;

Event::listen(OrderCreatedEvent::class, function ($event) {
    $order = $event->order;  // OrderData
    $orderId = $event->orderId;
    $trackingNumber = $event->trackingNumber;
    $status = $event->status;
    $createdAt = $event->createdAt;
    
    // Send order confirmation email
    Mail::to($customer)->send(new OrderConfirmed($order));
});
```

### OrderCancelledEvent

**Dispatched:** After successful order cancellation

```php
use MasyukAI\Jnt\Events\OrderCancelledEvent;

Event::listen(OrderCancelledEvent::class, function ($event) {
    $orderId = $event->orderId;
    $reason = $event->reason;  // CancellationReason enum
    $trackingNumber = $event->trackingNumber;
    $success = $event->success;
    $message = $event->message;
    $cancelledAt = $event->cancelledAt;
    
    // Restore inventory
    Inventory::restore($orderId);
    
    // Notify customer
    if ($event->reason->requiresCustomerContact()) {
        Mail::to($customer)->send(new OrderCancelled($orderId, $reason));
    }
});
```

### TrackingUpdatedEvent

**Dispatched:** When tracking status changes (usually via webhook)

```php
use MasyukAI\Jnt\Events\TrackingUpdatedEvent;

Event::listen(TrackingUpdatedEvent::class, function ($event) {
    $tracking = $event->tracking;  // TrackingData
    $orderId = $event->orderId;
    $trackingNumber = $event->trackingNumber;
    $status = $event->status;
    
    // Check status
    if ($event->isDelivered()) {
        // Mark order as delivered
        Order::where('tracking_number', $trackingNumber)->update([
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);
    }
    
    if ($event->isInTransit()) {
        // Update tracking page
        Cache::put("tracking:{$trackingNumber}", $tracking, 3600);
    }
    
    if ($event->hasProblem()) {
        // Alert customer service
        Notification::route('slack', config('slack.cs_channel'))
            ->notify(new DeliveryProblem($tracking));
    }
});
```

### WaybillPrintedEvent

**Dispatched:** After waybill generation

```php
use MasyukAI\Jnt\Events\WaybillPrintedEvent;

Event::listen(WaybillPrintedEvent::class, function ($event) {
    $waybill = $event->waybill;  // PrintWaybillData
    $orderId = $event->orderId;
    $trackingNumber = $event->trackingNumber;
    
    // Save PDF for records
    if ($event->canSavePdf()) {
        $path = storage_path("waybills/{$orderId}.pdf");
        $waybill->savePdf($path);
    }
    
    // Email to warehouse
    if ($event->hasBase64Content()) {
        Mail::to($warehouse)->send(new WaybillReady($waybill));
    }
});
```

---

## Notifications

All notifications implement `ShouldQueue` for async processing and support both mail and database channels.

### OrderShippedNotification

```php
use MasyukAI\Jnt\Notifications\OrderShippedNotification;

$user->notify(new OrderShippedNotification(
    tracking: $trackingData,
    estimatedDelivery: '2025-01-15'  // optional
));
```

**Email Content:**
- Subject: "Your Order Has Been Shipped"
- Tracking number
- Order ID (if available)
- Estimated delivery date
- Current location

**Database Storage:**
```php
[
    'type' => 'order_shipped',
    'tracking_number' => 'JT123456789MY',
    'order_id' => 'ORDER-2025-001',
    'estimated_delivery' => '2025-01-15',
    'current_location' => 'Kuala Lumpur, Wilayah Persekutuan',
    'message' => '...',
]
```

### OrderDeliveredNotification

```php
use MasyukAI\Jnt\Notifications\OrderDeliveredNotification;

$user->notify(new OrderDeliveredNotification($trackingData));
```

**Email Content:**
- Subject: "Your Order Has Been Delivered"
- Tracking number
- Order ID (if available)
- Delivery time
- Delivery location
- Delivered by (courier name)
- Signature availability

### OrderProblemNotification

```php
use MasyukAI\Jnt\Notifications\OrderProblemNotification;

$user->notify(new OrderProblemNotification(
    tracking: $trackingData,
    supportContact: 'support@example.com'  // optional
));
```

**Email Content:**
- Subject: "Issue with Your Order"
- Tracking number
- Order ID (if available)
- Problem description
- Problem type
- Reported time
- Support contact

---

## Artisan Commands

### jnt:config:check

Validate package configuration

```bash
php artisan jnt:config:check
```

**Output:**
```
 ✓ API Account: Configured (CUST123)
 ✓ Environment: production
 ✓ Base URL: https://api.jtexpress.com.my
 ✓ Private Key: Valid RSA-2048 format
 ✓ Webhooks: Enabled
 ✓ Connectivity: Connection successful (120ms)

 SUCCESS  All configuration checks passed!
```

### jnt:order:create

Create order interactively

```bash
php artisan jnt:order:create \
  --sender-name="Merchant Store" \
  --sender-phone="+60123456789" \
  --receiver-name="John Customer" \
  --receiver-phone="+60198765432"
```

### jnt:order:track

Track parcel status

```bash
php artisan jnt:order:track ORDER-2025-001
php artisan jnt:order:track --tracking-number=JT123456789MY
```

### jnt:order:cancel

Cancel an order

```bash
php artisan jnt:order:cancel ORDER-2025-001 --reason="out_of_stock"
```

### jnt:order:print

Generate waybill

```bash
php artisan jnt:order:print ORDER-2025-001 --output=waybills/
```

### jnt:webhook:test

Test webhook endpoint

```bash
php artisan jnt:webhook:test
```

---

## Webhooks

J&T Express sends status updates to your webhook endpoint.

### Setup

1. **Configure webhook URL** in J&T Express merchant portal
2. **Enable webhooks** in `config/jnt.php`
3. **Add route** (automatically registered)

### Handling Webhooks

The package automatically:
- Verifies RSA signatures
- Parses webhook payload
- Dispatches `TrackingUpdatedEvent`
- Returns proper response to J&T

### Custom Webhook Handling

```php
use MasyukAI\Jnt\Events\TrackingUpdatedEvent;

Event::listen(TrackingUpdatedEvent::class, function ($event) {
    $tracking = $event->tracking;
    
    // Find your order
    $order = Order::where('tracking_number', $tracking->trackingNumber)->first();
    
    if (!$order) {
        return;
    }
    
    // Update order status
    $order->update([
        'shipping_status' => $tracking->status,
        'current_location' => $tracking->getCurrentLocation(),
    ]);
    
    // Trigger notifications
    if ($tracking->isDelivered()) {
        $order->user->notify(new OrderDeliveredNotification($tracking));
    }
    
    if ($tracking->hasProblems()) {
        $order->user->notify(new OrderProblemNotification($tracking));
    }
});
```

### Webhook Payload Structure

```json
{
  "billCode": "JT123456789MY",
  "txlogisticId": "ORDER-2025-001",
  "details": [
    {
      "scanTime": "2025-01-08 10:30:00",
      "desc": "Package received at facility",
      "scanType": "parcel_pickup",
      "scanTypeCode": "10",
      "scanNetworkCity": "Kuala Lumpur",
      "scanNetworkProvince": "Wilayah Persekutuan"
    }
  ]
}
```

---

## Error Handling

### Exception Hierarchy

```
JntValidationException     - Input validation failures
JntApiException           - J&T API errors
JntNetworkException       - Network/connection issues
JntConfigurationException - Configuration problems
```

### Handling Exceptions

```php
use MasyukAI\Jnt\Exceptions\{
    JntValidationException,
    JntApiException,
    JntNetworkException,
    JntConfigurationException
};

try {
    $order = JntExpress::createOrder(...);
} catch (JntValidationException $e) {
    // Invalid input data
    $errors = $e->getErrors();  // Array of validation errors
    Log::error('Validation failed', ['errors' => $errors]);
    
} catch (JntApiException $e) {
    // J&T API returned error
    $response = $e->getApiResponse();  // Full API response
    Log::error('API error', ['response' => $response]);
    
} catch (JntNetworkException $e) {
    // Network/connection failure
    Log::error('Network error', ['message' => $e->getMessage()]);
    
} catch (JntConfigurationException $e) {
    // Configuration issue
    Log::critical('Config error', ['message' => $e->getMessage()]);
}
```

### Common API Errors

| Code | Message | Handling |
|------|---------|----------|
| 145003010 | API account does not exist | Check `customer_code` |
| 145003030 | Signature verification failed | Check `private_key` and payload |
| 145003050 | Illegal parameters | Validate input data |
| 999001030 | Data cannot be found | Order/tracking number not found |
| 999002010 | Order cannot be cancelled | Order already shipped/delivered |

---

## Testing

### Unit Tests

```bash
# Run all tests
vendor/bin/pest

# Run specific test suite
vendor/bin/pest tests/Unit/
vendor/bin/pest tests/Feature/

# Run with coverage
vendor/bin/pest --coverage
```

### Integration Tests

See `docs/INTEGRATION_TESTING.md` for complete integration test guide.

```php
use MasyukAI\Jnt\Facades\JntExpress;

test('complete order lifecycle', function () {
    // 1. Create order
    $order = JntExpress::createOrder(...);
    expect($order->orderId)->not->toBeNull();
    
    // 2. Track order
    $tracking = JntExpress::trackParcel(orderId: $order->orderId);
    expect($tracking->trackingNumber)->toBe($order->trackingNumber);
    
    // 3. Print waybill
    $waybill = JntExpress::printOrder(orderId: $order->orderId);
    expect($waybill)->toHaveKey('data');
    
    // 4. Cancel order (if not shipped)
    $result = JntExpress::cancelOrder(
        orderId: $order->orderId,
        reason: 'Testing cancellation'
    );
    expect($result)->toBeArray();
});
```

### Mocking in Tests

```php
use Illuminate\Support\Facades\Http;

test('handles API errors gracefully', function () {
    Http::fake([
        '*' => Http::response([
            'code' => '999001030',
            'msg' => 'Data cannot be found',
        ], 200),
    ]);
    
    expect(fn() => JntExpress::queryOrder('INVALID'))
        ->toThrow(JntApiException::class);
});
```

---

## Best Practices

### 1. Use Enums

```php
// ✅ Good - Type safe
$reason = CancellationReason::OUT_OF_STOCK;

// ❌ Bad - String literals
$reason = 'out_of_stock';
```

### 2. Handle Exceptions

```php
// ✅ Good - Graceful error handling
try {
    $order = JntExpress::createOrder(...);
} catch (JntValidationException $e) {
    return response()->json(['errors' => $e->getErrors()], 422);
}

// ❌ Bad - Uncaught exceptions
$order = JntExpress::createOrder(...);
```

### 3. Use Events for Side Effects

```php
// ✅ Good - Decouple logic with events
Event::listen(OrderCreatedEvent::class, SendOrderConfirmation::class);

// ❌ Bad - Tight coupling
JntExpress::createOrder(...);
Mail::to($customer)->send(new OrderConfirmed());
```

### 4. Queue Notifications

```php
// ✅ Good - Already queued by default
$user->notify(new OrderShippedNotification($tracking));

// ❌ Bad - Blocking notification
$user->notifyNow(new OrderShippedNotification($tracking));
```

### 5. Validate Before Creating Orders

```php
// ✅ Good - Validate early
$request->validate([
    'phone' => 'required|regex:/^\+60\d{9,10}$/',
    'postcode' => 'required|size:5',
]);

// ❌ Bad - Let API validate
// Wastes API calls and slows response
```

---

## Support

- **Documentation:** `/docs`
- **GitHub Issues:** https://github.com/masyukai/jnt/issues
- **Email:** support@masyukai.com

---

**Last Updated:** 2025-01-08  
**Package Version:** 1.0.0
