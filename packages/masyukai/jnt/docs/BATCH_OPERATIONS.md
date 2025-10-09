# Batch Operations

The J&T Express package includes powerful batch operation methods that allow you to process multiple orders, track multiple parcels, cancel multiple orders, and print multiple waybills in a single operation. These methods are not part of the official J&T API but provide a convenient developer experience for common bulk operations.

> **Clean Field Names:** This package uses clean, developer-friendly field names like `orderId` instead of J&T's internal `txlogisticId`. All input arrays are automatically converted to the correct API format. This ensures consistency with the single order creation API.

## Table of Contents

- [Overview](#overview)
- [Return Structure](#return-structure)
- [Batch Create Orders](#batch-create-orders)
- [Batch Track Parcels](#batch-track-parcels)
- [Batch Cancel Orders](#batch-cancel-orders)
- [Batch Print Waybills](#batch-print-waybills)
- [Error Handling](#error-handling)
- [Performance Considerations](#performance-considerations)
- [Best Practices](#best-practices)
- [Integration Workflow Example](#integration-workflow-example)

## Overview

All batch operations follow a consistent pattern:

1. Accept an array of data/identifiers
2. Process each item sequentially
3. Collect successful results and failures separately
4. Return a structured array with both successful and failed items

This approach ensures **partial success** - if some operations fail, the successful ones are still processed and returned. You get detailed error information for each failure, including the exception object for debugging.

## Return Structure

All batch methods return an array with this structure:

```php
[
    'successful' => [
        // Array of successful results (type varies by method)
    ],
    'failed' => [
        [
            'orderId' => 'ORDER123',           // Identifier that failed
            'error' => 'Error message',        // Human-readable error message
            'exception' => Throwable,          // Full exception object
        ],
        // ... more failed items
    ]
]
```

## Batch Create Orders

Create multiple orders in a single call.

### Method Signature

```php
public function batchCreateOrders(array $ordersData): array
```

### Parameters

- `$ordersData` - Array of order data arrays (same format as `createOrderFromArray()`)

### Returns

```php
[
    'successful' => [OrderData, OrderData, ...],
    'failed' => [
        [
            'orderId' => string,
            'error' => string,
            'exception' => Throwable
        ],
        ...
    ]
]
```

### Example Usage

```php
use MasyukAI\Jnt\Facades\JntExpress;

$ordersData = [
    [
        'orderId' => 'ORDER001',  // ✅ Clean field name - automatically converted
        'sender' => [
            'name' => 'John Doe',
            'phone' => '0123456789',
            'address' => '123 Main St',
            'city' => 'Kuala Lumpur',
            'postcode' => '50000',
            'state' => 'Kuala Lumpur',  // ✅ Clean: state → prov
        ],
        'receiver' => [
            'name' => 'Jane Smith',
            'phone' => '0198765432',
            'address' => '456 Oak Rd',
            'city' => 'Petaling Jaya',
            'postcode' => '46000',
            'state' => 'Selangor',  // ✅ Clean: state → prov
        ],
        'items' => [
            [
                'name' => 'Widget',  // ✅ Clean: name → itemName
                'quantity' => 2,     // ✅ Clean: quantity → number
                'price' => 50.00,    // ✅ Clean: price → itemValue
                'weight' => 200,     // Weight in grams
            ],
        ],
        'packageInfo' => [
            'quantity' => 1,  // ✅ Clean: quantity → packageQuantity
            'weight' => 1.5,  // Weight in kg
            'value' => 100.00,  // ✅ Clean: value → packageValue
            'goodsType' => 'ITN8',
            'length' => 30,
            'width' => 20,
            'height' => 10,
        ],
    ],
    [
        'orderId' => 'ORDER002',  // ✅ Use clean field names
        // ... more order data
    ],
];

$result = JntExpress::batchCreateOrders($ordersData);

// Process successful orders
foreach ($result['successful'] as $order) {
    echo "✓ Order {$order->orderId} created with tracking {$order->trackingNumber}\n";
}

// Handle failures
foreach ($result['failed'] as $failure) {
    echo "✗ Order {$failure['orderId']} failed: {$failure['error']}\n";
    // Log the full exception for debugging
    logger()->error('Order creation failed', [
        'orderId' => $failure['orderId'],
        'exception' => $failure['exception'],
    ]);
}
```

## Batch Track Parcels

Track multiple parcels by order IDs and/or tracking numbers.

### Method Signature

```php
public function batchTrackParcels(
    array $orderIds = [],
    array $trackingNumbers = []
): array
```

### Parameters

- `$orderIds` - Array of order IDs (your system's order identifiers)
- `$trackingNumbers` - Array of J&T tracking numbers

You can provide one or both parameter arrays.

### Returns

```php
[
    'successful' => [TrackingData, TrackingData, ...],
    'failed' => [
        [
            'identifier' => string,         // The ID or tracking number that failed
            'type' => 'orderId|trackingNumber',
            'error' => string,
            'exception' => Throwable
        ],
        ...
    ]
]
```

### Example Usage

```php
use MasyukAI\Jnt\Facades\JntExpress;

// Track by order IDs
$result = JntExpress::batchTrackParcels(
    orderIds: ['ORDER001', 'ORDER002', 'ORDER003']
);

// Track by tracking numbers
$result = JntExpress::batchTrackParcels(
    trackingNumbers: ['JT123456', 'JT789012']
);

// Track by both
$result = JntExpress::batchTrackParcels(
    orderIds: ['ORDER001', 'ORDER002'],
    trackingNumbers: ['JT123456']
);

// Process results
foreach ($result['successful'] as $tracking) {
    echo "Parcel {$tracking->billCode}: {$tracking->lastStatus}\n";
    if ($tracking->isDelivered()) {
        echo "  ✓ Delivered!\n";
    }
}

foreach ($result['failed'] as $failure) {
    echo "Failed to track {$failure['type']}: {$failure['identifier']}\n";
}
```

## Batch Cancel Orders

Cancel multiple orders with the same cancellation reason.

### Method Signature

```php
public function batchCancelOrders(
    array $orderIds,
    CancellationReason|string $reason
): array
```

### Parameters

- `$orderIds` - Array of order IDs to cancel
- `$reason` - Cancellation reason (enum or custom string)

### Returns

```php
[
    'successful' => [array, array, ...],  // API response data for each success
    'failed' => [
        [
            'orderId' => string,
            'error' => string,
            'exception' => Throwable
        ],
        ...
    ]
]
```

### Example Usage

```php
use MasyukAI\Jnt\Facades\JntExpress;
use MasyukAI\Jnt\Enums\CancellationReason;

// Using predefined enum (recommended)
$result = JntExpress::batchCancelOrders(
    orderIds: ['ORDER001', 'ORDER002', 'ORDER003'],
    reason: CancellationReason::OUT_OF_STOCK
);

// Using custom string
$result = JntExpress::batchCancelOrders(
    orderIds: ['ORDER004', 'ORDER005'],
    reason: 'Customer requested cancellation due to address change'
);

// Process results
echo "Successfully cancelled: " . count($result['successful']) . " orders\n";
echo "Failed to cancel: " . count($result['failed']) . " orders\n";

foreach ($result['failed'] as $failure) {
    echo "✗ Failed to cancel {$failure['orderId']}: {$failure['error']}\n";
}
```

## Batch Print Waybills

Print waybills for multiple orders.

### Method Signature

```php
public function batchPrintWaybills(
    array $orderIds,
    ?string $templateName = null
): array
```

### Parameters

- `$orderIds` - Array of order IDs to print waybills for
- `$templateName` - Optional template name (applied to all waybills)

### Returns

```php
[
    'successful' => [
        [
            'orderId' => string,
            'data' => PrintWaybillData  // Waybill data object
        ],
        ...
    ],
    'failed' => [
        [
            'orderId' => string,
            'error' => string,
            'exception' => Throwable
        ],
        ...
    ]
]
```

### Example Usage

```php
use MasyukAI\Jnt\Facades\JntExpress;
use Illuminate\Support\Facades\Storage;

// Print waybills with default template
$result = JntExpress::batchPrintWaybills(
    orderIds: ['ORDER001', 'ORDER002', 'ORDER003']
);

// Print with custom template
$result = JntExpress::batchPrintWaybills(
    orderIds: ['ORDER004', 'ORDER005'],
    templateName: 'THERMAL_80MM'
);

// Save all successful waybills to storage
foreach ($result['successful'] as $item) {
    $orderId = $item['orderId'];
    $waybillData = $item['data'];
    
    if ($waybillData->hasBase64Content()) {
        // Save PDF to storage
        $filename = "waybills/{$orderId}.pdf";
        $waybillData->savePdf(Storage::path($filename));
        
        echo "✓ Waybill saved for order {$orderId}\n";
        echo "  Size: {$waybillData->formattedSize}\n";
    }
}

// Handle failures
foreach ($result['failed'] as $failure) {
    echo "✗ Failed to print waybill for {$failure['orderId']}: {$failure['error']}\n";
}
```

## Error Handling

All batch operations catch **any throwable** (exceptions and errors) to ensure one failure doesn't stop processing. Each failed item includes:

1. **Identifier** - The order ID, tracking number, or identifier that failed
2. **Error message** - Human-readable error description from the exception
3. **Exception object** - Full exception with stack trace for debugging

### Best Practices for Error Handling

```php
$result = JntExpress::batchCreateOrders($ordersData);

// Log all failures for monitoring
if (!empty($result['failed'])) {
    foreach ($result['failed'] as $failure) {
        logger()->error('Batch order creation failed', [
            'order_id' => $failure['orderId'],
            'error' => $failure['error'],
            'exception' => $failure['exception'],
        ]);
    }
    
    // Send alert if too many failures
    $failureRate = count($result['failed']) / count($ordersData);
    if ($failureRate > 0.2) { // More than 20% failed
        // Send notification to admin
        Notification::route('mail', config('admin.email'))
            ->notify(new HighBatchFailureRate($result));
    }
}

// Update order status in your database
foreach ($result['successful'] as $order) {
    Order::where('order_id', $order->orderId)->update([
        'tracking_number' => $order->trackingNumber,
        'status' => 'shipped',
        'shipped_at' => now(),
    ]);
}

foreach ($result['failed'] as $failure) {
    Order::where('order_id', $failure['orderId'])->update([
        'status' => 'failed',
        'error_message' => $failure['error'],
    ]);
}
```

## Performance Considerations

### Sequential Processing

Batch operations process items **sequentially** (one at a time), not in parallel. This is intentional to:

1. Avoid overwhelming the J&T API with concurrent requests
2. Respect rate limits
3. Maintain predictable behavior
4. Simplify error handling

### HTTP Retries

The J&T HTTP client includes automatic retry logic for 5xx errors (default: 3 retries). When using batch operations:

- Each failed item will be retried automatically
- This adds to total processing time
- Consider disabling retries for very large batches if speed is critical

### Recommendations

For optimal performance:

- **Small batches**: Use batch operations for 10-50 items at a time
- **Large datasets**: Consider using Laravel queues to process batches in background
- **Monitor timing**: Track how long batch operations take in production
- **Implement pagination**: Split very large batches into chunks

### Example: Processing Large Batches with Queue

```php
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ProcessOrderBatch implements ShouldQueue
{
    use InteractsWithQueue, Queueable;
    
    public function __construct(
        public array $ordersData
    ) {}
    
    public function handle(): void
    {
        // Process batch of orders
        $result = JntExpress::batchCreateOrders($this->ordersData);
        
        // Store results
        foreach ($result['successful'] as $order) {
            // Update database...
        }
        
        foreach ($result['failed'] as $failure) {
            // Log failure...
        }
    }
}

// Dispatch multiple jobs for large datasets
$allOrders = Order::where('status', 'pending')->get();
$chunks = $allOrders->chunk(25); // 25 orders per batch

foreach ($chunks as $chunk) {
    $ordersData = $chunk->map(fn($order) => $order->toJntArray())->toArray();
    ProcessOrderBatch::dispatch($ordersData);
}
```

## Best Practices

### 1. Validate Before Batching

Validate your data before calling batch operations to minimize failures:

```php
use Illuminate\Support\Facades\Validator;

$ordersData = [...];

// Validate all orders first
foreach ($ordersData as $index => $orderData) {
    $validator = Validator::make($orderData, [
        'orderId' => 'required|string|max:50',  // ✅ Clean field name
        'sender.name' => 'required|string|max:64',
        'sender.phone' => 'required|string|regex:/^[0-9]{10,11}$/',
        // ... more rules
    ]);
    
    if ($validator->fails()) {
        logger()->error("Order #{$index} validation failed", $validator->errors()->toArray());
        unset($ordersData[$index]); // Remove invalid order
    }
}

// Now process only valid orders
$result = JntExpress::batchCreateOrders($ordersData);
```

### 2. Monitor Success Rates

Track batch operation success rates over time:

```php
$result = JntExpress::batchCreateOrders($ordersData);

$total = count($ordersData);
$successful = count($result['successful']);
$failed = count($result['failed']);
$successRate = $total > 0 ? ($successful / $total) * 100 : 0;

// Log metrics
logger()->info('Batch order creation completed', [
    'total' => $total,
    'successful' => $successful,
    'failed' => $failed,
    'success_rate' => $successRate,
]);

// Track in monitoring system (e.g., Prometheus, DataDog)
Metrics::gauge('jnt.batch.success_rate', $successRate, ['operation' => 'create_orders']);
```

### 3. Implement Retry Logic for Failed Items

Retry failed items separately after fixing the issue:

```php
$result = JntExpress::batchCreateOrders($ordersData);

if (!empty($result['failed'])) {
    // Store failed orders for manual review/retry
    foreach ($result['failed'] as $failure) {
        FailedOrderBatch::create([
            'order_id' => $failure['orderId'],
            'error_message' => $failure['error'],
            'order_data' => $ordersData->firstWhere('orderId', $failure['orderId']),  // ✅ Clean field
            'retry_count' => 0,
        ]);
    }
}

// Later, retry failed orders
$failedOrders = FailedOrderBatch::where('retry_count', '<', 3)->get();
$retryData = $failedOrders->pluck('order_data')->toArray();

if (!empty($retryData)) {
    $retryResult = JntExpress::batchCreateOrders($retryData);
    
    // Update retry counts...
}
```

### 4. Use Transactions When Updating Database

Wrap database updates in transactions to ensure consistency:

```php
use Illuminate\Support\Facades\DB;

$result = JntExpress::batchCreateOrders($ordersData);

DB::transaction(function () use ($result) {
    foreach ($result['successful'] as $order) {
        Order::where('order_id', $order->orderId)->update([
            'tracking_number' => $order->trackingNumber,
            'status' => 'shipped',
        ]);
    }
    
    foreach ($result['failed'] as $failure) {
        Order::where('order_id', $failure['orderId'])->update([
            'status' => 'failed',
            'error_message' => $failure['error'],
        ]);
    }
});
```

## Integration Workflow Example

Here's a complete example showing how to use batch operations together:

```php
use MasyukAI\Jnt\Facades\JntExpress;
use MasyukAI\Jnt\Enums\CancellationReason;
use Illuminate\Support\Facades\Storage;

class ShippingService
{
    public function processDailyShipments(): array
    {
        // 1. Get pending orders from database
        $pendingOrders = Order::where('status', 'pending')
            ->where('payment_status', 'paid')
            ->limit(50)
            ->get();
        
        // 2. Prepare order data for J&T
        $ordersData = $pendingOrders->map(function ($order) {
            return [
                'orderId' => $order->order_number,  // ✅ Clean field name
                'sender' => $order->getSenderData(),
                'receiver' => $order->getReceiverData(),
                'items' => $order->getItemsData(),
                'packageInfo' => $order->getPackageInfo(),
            ];
        })->toArray();
        
        // 3. Create orders in batch
        $createResult = JntExpress::batchCreateOrders($ordersData);
        
        // 4. Update successful orders
        $orderIds = [];
        foreach ($createResult['successful'] as $orderData) {
            Order::where('order_number', $orderData->orderId)->update([
                'tracking_number' => $orderData->trackingNumber,
                'status' => 'shipped',
                'shipped_at' => now(),
            ]);
            $orderIds[] = $orderData->orderId;
        }
        
        // 5. Print waybills for successful orders
        if (!empty($orderIds)) {
            $printResult = JntExpress::batchPrintWaybills(
                orderIds: $orderIds,
                templateName: 'THERMAL_80MM'
            );
            
            // 6. Save waybills to storage
            foreach ($printResult['successful'] as $item) {
                $orderId = $item['orderId'];
                $waybillData = $item['data'];
                
                if ($waybillData->hasBase64Content()) {
                    $path = "waybills/{$orderId}.pdf";
                    $waybillData->savePdf(Storage::path($path));
                    
                    Order::where('order_number', $orderId)->update([
                        'waybill_path' => $path,
                    ]);
                }
            }
        }
        
        // 7. Handle failed orders
        foreach ($createResult['failed'] as $failure) {
            Order::where('order_number', $failure['orderId'])->update([
                'status' => 'failed',
                'error_message' => $failure['error'],
            ]);
            
            // Log for monitoring
            logger()->error('Order creation failed', [
                'order_id' => $failure['orderId'],
                'error' => $failure['error'],
            ]);
        }
        
        // 8. Track all shipped orders
        $trackResult = JntExpress::batchTrackParcels(orderIds: $orderIds);
        
        foreach ($trackResult['successful'] as $tracking) {
            Order::where('order_number', $tracking->orderId)->update([  // ✅ Uses clean property
                'last_tracking_status' => $tracking->lastStatus,
                'last_tracking_update' => now(),
            ]);
        }
        
        return [
            'total_orders' => count($pendingOrders),
            'shipped' => count($createResult['successful']),
            'failed' => count($createResult['failed']),
            'waybills_printed' => count($printResult['successful'] ?? []),
        ];
    }
    
    public function cancelOutOfStockOrders(): array
    {
        // Get orders that are out of stock
        $outOfStockOrders = Order::where('status', 'shipped')
            ->whereHas('items', function ($query) {
                $query->where('stock', 0);
            })
            ->pluck('order_number')
            ->toArray();
        
        if (empty($outOfStockOrders)) {
            return ['cancelled' => 0];
        }
        
        // Cancel in batch
        $result = JntExpress::batchCancelOrders(
            orderIds: $outOfStockOrders,
            reason: CancellationReason::OUT_OF_STOCK
        );
        
        // Update database
        foreach ($result['successful'] as $cancellation) {
            // Extract orderId from response (clean property name)
            $orderId = $cancellation['orderId'] ?? null;  // ✅ Clean field name
            if ($orderId) {
                Order::where('order_number', $orderId)->update([
                    'status' => 'cancelled',
                    'cancelled_at' => now(),
                    'cancellation_reason' => 'Out of stock',
                ]);
            }
        }
        
        return [
            'total' => count($outOfStockOrders),
            'cancelled' => count($result['successful']),
            'failed' => count($result['failed']),
        ];
    }
}
```

---

## Summary

Batch operations provide a powerful, convenient way to work with multiple J&T Express orders at once. Key takeaways:

- ✅ **Partial success** - One failure doesn't stop processing
- ✅ **Detailed errors** - Every failure includes exception details
- ✅ **Consistent API** - All batch methods follow the same pattern
- ✅ **Production-ready** - Built-in error handling and logging support
- ✅ **Flexible** - Works with queues, jobs, and synchronous processing

For additional help, see:
- [API Reference](API_REFERENCE.md) - Complete API documentation
- [Integration Testing](INTEGRATION_TESTING.md) - Testing strategies
- [README](../README.md) - Package overview and setup
