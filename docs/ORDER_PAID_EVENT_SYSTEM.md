# OrderPaid Event System

This document describes the post-payment automation architecture implemented through the `OrderPaid` event system.

## Overview

After a successful order creation, the application dispatches an `OrderPaid` event that triggers several queued listeners for downstream processing. This architecture ensures the webhook response remains fast while orchestrating emails, invoices, shipping, and inventory management asynchronously.

## Event Dispatch

`CheckoutService::handlePaymentSuccess()` dispatches `OrderPaid` after the database transaction commits:

```php
OrderPaid::dispatch($order, $payment, $webhookData, $invocationSource);
```

### Event Properties

- `Order $order` – The newly created order
- `Payment $payment` – The associated payment record
- `array $webhookData` – Raw webhook payload for audit trails
- `string $source` – Invocation source ('webhook', 'callback', or 'manual')

## Queued Listeners

All listeners implement `ShouldQueue` with retry logic (3 attempts, 60-120s backoff):

| Listener | Purpose | Queue Config | Key Logic |
| --- | --- | --- | --- |
| `SendOrderConfirmationEmail` | Sends customer order confirmation email | 3 tries, 60s backoff | Uses `OrderConfirmation` notification with order details and view order link |
| `GenerateOrderInvoice` | Creates invoice PDF and stores path | 3 tries, 60s backoff | Placeholder implementation stores path in `Order::invoice_path`, updates `Order::invoice_generated_at` |
| `ProcessOrderShipping` | Creates shipment records for physical items | 3 tries, 120s backoff | Creates `Shipment` with tracking number, shipping address, estimated delivery |
| `DeductOrderStock` | Deducts inventory from stock | 3 tries, 60s backoff | Idempotent via `StockTransaction` lookup, calls `StockService::recordSale()` |

### SendOrderConfirmationEmail

Location: `app/Listeners/SendOrderConfirmationEmail.php`

Sends order confirmation emails to customers immediately after order creation:

```php
public function handle(OrderPaid $event): void
{
    $event->order->user->notify(new OrderConfirmation($event->order));
}
```

**Notification**: `OrderConfirmation` includes:
- Order number, total amount, status
- Itemized order items with prices
- Shipping and billing addresses
- Action button to view order details

### GenerateOrderInvoice

Location: `app/Listeners/GenerateOrderInvoice.php`

Creates invoice PDFs for completed orders:

```php
public function handle(OrderPaid $event): void
{
    // Placeholder: Generate invoice PDF
    $invoicePath = "invoices/order_{$event->order->id}.pdf";
    Storage::put($invoicePath, "Invoice content placeholder");
    
    $event->order->update([
        'invoice_path' => $invoicePath,
        'invoice_generated_at' => now(),
    ]);
}
```

**Current implementation**: Placeholder that stores a dummy PDF. Ready to integrate with actual PDF generation libraries.

### ProcessOrderShipping

Location: `app/Listeners/ProcessOrderShipping.php`

Creates shipment records for orders containing physical items:

```php
public function handle(OrderPaid $event): void
{
    // Check if order has physical items
    $hasPhysicalItems = $event->order->items()
        ->whereHas('product', fn($q) => $q->where('type', 'physical'))
        ->exists();
    
    if ($hasPhysicalItems) {
        Shipment::create([
            'order_id' => $event->order->id,
            'tracking_number' => 'TRK' . time(),
            'shipping_address' => $event->order->shipping_address,
            'estimated_delivery' => now()->addDays(7),
        ]);
    }
}
```

**Logic**:
- Only creates shipments for orders with physical products
- Generates tracking numbers (placeholder format)
- Sets estimated delivery to 7 days from order date

### DeductOrderStock

Location: `app/Listeners/DeductOrderStock.php`

Deducts purchased items from inventory:

```php
public function handle(OrderPaid $event): void
{
    foreach ($event->order->items as $item) {
        // Idempotent: Skip if already processed
        $existingTransaction = StockTransaction::where([
            'product_id' => $item->product_id,
            'reference_type' => Order::class,
            'reference_id' => $event->order->id,
        ])->exists();
        
        if (!$existingTransaction) {
            StockService::recordSale(
                $item->product_id,
                $item->quantity,
                $event->order
            );
        }
    }
}
```

**Key features**:
- Idempotent via `StockTransaction` lookup
- Prevents duplicate stock deductions on retries
- Uses `StockService` for consistent inventory management

## Configuration

### Disabling CHIP Package Events

The application consolidates all post-payment logic under the `OrderPaid` event. To prevent duplicate processing from the CHIP package's `PurchasePaid` event, set:

```env
CHIP_DISPATCH_PURCHASE_EVENTS=false
```

This disables `WebhookService` from dispatching `PurchasePaid` events when `purchase.paid` webhooks arrive, ensuring only the application's `OrderPaid` listeners execute.

### Queue Configuration

Listeners use Laravel's default queue connection (configured via `QUEUE_CONNECTION` in `.env`). For production:

```env
QUEUE_CONNECTION=database
```

Run queue workers:

```bash
php artisan queue:work --tries=3 --timeout=120
```

## Testing

### Unit Tests

`OrderPaidEventTest` provides comprehensive coverage:

```php
// Event dispatch verification
it('dispatches OrderPaid event after successful payment', function () {
    Event::fake();
    $checkoutService->handlePaymentSuccess($purchaseId, $webhookData);
    Event::assertDispatched(OrderPaid::class);
});

// Listener queuing
it('queues SendOrderConfirmationEmail listener', function () {
    Queue::fake();
    OrderPaid::dispatch($order, $payment, [], 'test');
    Queue::assertPushed(SendOrderConfirmationEmail::class);
});
```

### Integration Testing

To test the full flow:

1. Trigger a payment webhook (or use dev routes)
2. Verify order creation
3. Check job queue for listener jobs
4. Process queue: `php artisan queue:work --once`
5. Verify side effects (email sent, invoice created, shipment created, stock deducted)

## Monitoring & Debugging

### Logs

All listeners log their execution:

```
[timestamp] Sending order confirmation email for order #123
[timestamp] Generated invoice for order #123: invoices/order_123.pdf
[timestamp] Created shipment for order #123: TRK1234567890
[timestamp] Stock deducted for order #123 items: 2
```

Check logs: `storage/logs/laravel.log`

### Failed Jobs

When listeners fail after all retries, the `failed()` method logs critical errors:

```php
public function failed(OrderPaid $event, Throwable $exception): void
{
    Log::critical('Failed to send order confirmation email', [
        'order_id' => $event->order->id,
        'error' => $exception->getMessage(),
    ]);
}
```

View failed jobs:

```bash
php artisan queue:failed
```

Retry failed jobs:

```bash
php artisan queue:retry <job-id>
# or retry all
php artisan queue:retry all
```

## Architecture Benefits

1. **Fast webhook responses**: Offloads time-consuming operations to queues
2. **Reliability**: Automatic retries with exponential backoff
3. **Observability**: Comprehensive logging at each step
4. **Maintainability**: Each listener has a single responsibility
5. **Extensibility**: Easy to add new listeners for additional automation
6. **Idempotency**: Stock deduction is idempotent, safe for retries

## Adding New Listeners

To add a new listener for the `OrderPaid` event:

1. Create listener:
```bash
php artisan make:listener YourNewListener --event=OrderPaid
```

2. Implement `ShouldQueue`:
```php
use Illuminate\Contracts\Queue\ShouldQueue;

class YourNewListener implements ShouldQueue
{
    public $tries = 3;
    public $backoff = 60;
    
    public function handle(OrderPaid $event): void
    {
        // Your logic here
    }
    
    public function failed(OrderPaid $event, Throwable $exception): void
    {
        Log::critical('YourNewListener failed', [
            'order_id' => $event->order->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
```

3. Laravel 12 auto-discovers listeners - no registration needed!

## Related Files

| File | Purpose |
| --- | --- |
| `app/Events/OrderPaid.php` | Event class definition |
| `app/Listeners/SendOrderConfirmationEmail.php` | Email listener |
| `app/Listeners/GenerateOrderInvoice.php` | Invoice listener |
| `app/Listeners/ProcessOrderShipping.php` | Shipping listener |
| `app/Listeners/DeductOrderStock.php` | Stock listener |
| `app/Notifications/OrderConfirmation.php` | Email notification |
| `tests/Feature/OrderPaidEventTest.php` | Test coverage |
| `docs/CART_CHECKOUT_FLOW.md` | Complete checkout flow documentation |
