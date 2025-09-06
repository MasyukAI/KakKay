# CHIP Payment Gateway Guidelines

## Overview
This application uses a custom `masyukai/chip` package for CHIP payment gateway integration. This is a modern, feature-complete Laravel package that provides seamless integration with both CHIP Collect (payment collection) and CHIP Send (money transfer) APIs.

**Package Location**: `packages/masyukai/chip/`  
**Namespace**: `Masyukai\Chip`  
**Facades**: `Masyukai\Chip\Facades\Chip` and `Masyukai\Chip\Facades\ChipSend`

## Key Features
- ✅ **Complete CHIP Collect API**: Payment links, subscriptions, pre-auth, refunds
- ✅ **CHIP Send Integration**: Money transfers and bank account management  
- ✅ **Webhook Security**: Automatic signature verification
- ✅ **Laravel Event System**: Purchase events and listeners
- ✅ **Type Safety**: Full PHPStan level 8 compliance
- ✅ **Comprehensive Testing**: PestPHP 4 test suite
- ✅ **Queue Support**: Background processing for webhooks
- ✅ **Caching & Rate Limiting**: Built-in performance optimizations

## Configuration

### Environment Variables
The package uses these environment variables (defined in `.env.example`):

```env
# CHIP Payment Gateway Configuration
CHIP_COLLECT_ENVIRONMENT=sandbox
CHIP_COLLECT_API_KEY=
CHIP_COLLECT_BRAND_ID=
CHIP_WEBHOOK_PUBLIC_KEY=
CHIP_WEBHOOK_VERIFY_SIGNATURE=true
CHIP_LOG_REQUESTS=false
CHIP_LOG_RESPONSES=false
CHIP_DEFAULT_CURRENCY=MYR
```

### Configuration File
Configuration is in `config/chip.php` with comprehensive options:

```php
// config/chip.php
return [
    'collect' => [
        'base_url' => env('CHIP_COLLECT_BASE_URL', 'https://gate.chip-in.asia/api/v1/'),
        'api_key' => env('CHIP_COLLECT_API_KEY'),
        'brand_id' => env('CHIP_COLLECT_BRAND_ID'),
        'timeout' => 30,
    ],
    'webhooks' => [
        'public_key' => env('CHIP_WEBHOOK_PUBLIC_KEY'),
        'verify_signature' => env('CHIP_WEBHOOK_VERIFY_SIGNATURE', true),
        'allowed_events' => [
            'purchase.created', 'purchase.paid', 'purchase.cancelled',
            'payment.created', 'payment.paid', 'payment.failed',
        ],
    ],
    'events' => [
        'dispatch_purchase_events' => true,
        'dispatch_payment_events' => true,
        'dispatch_webhook_events' => true,
    ],
    'logging' => [
        'log_requests' => env('CHIP_LOG_REQUESTS', false),
        'log_responses' => env('CHIP_LOG_RESPONSES', false),
        'log_webhooks' => true,
    ],
];
```

## Basic Usage

### Creating a Purchase

#### Simple Payment
```php
use Masyukai\Chip\Facades\Chip;

$purchase = Chip::createPurchase([
    'client' => [
        'email' => 'customer@example.com',
        'full_name' => 'John Doe',
    ],
    'purchase' => [
        'products' => [
            [
                'name' => 'Premium Subscription',
                'price' => 2990, // RM 29.90 in cents
            ],
        ],
    ],
]);

// Redirect customer to payment page
return redirect($purchase->checkout_url);
```

#### Advanced Purchase with Options
```php
$purchase = Chip::createPurchase([
    'client' => [
        'email' => 'customer@example.com',
        'full_name' => 'John Doe',
        'phone' => '+60123456789',
    ],
    'purchase' => [
        'timezone' => 'Asia/Kuala_Lumpur',
        'currency' => 'MYR',
        'products' => [
            [
                'name' => 'T-Shirt',
                'price' => 2999, // RM 29.99
                'quantity' => 2,
            ],
            [
                'name' => 'Shipping',
                'price' => 1500, // RM 15.00
                'quantity' => 1,
            ],
        ],
        'success_callback' => route('payment.success'),
        'failure_callback' => route('payment.failed'),
        'cancel_callback' => route('payment.cancelled'),
        'creator_agent' => 'YourApp v1.0',
    ],
]);
```

### Managing Purchases

#### Retrieve Purchase Details
```php
$purchase = Chip::getPurchase('pur_abc123');
$status = $purchase->status; // 'created', 'paid', 'cancelled', etc.
$amount = $purchase->total_amount; // in cents
```

#### Pre-Authorization (Hold & Capture)
```php
// Create purchase with pre-auth
$purchase = Chip::createPurchase([
    'client' => [...],
    'purchase' => [
        'skip_capture' => true, // Hold the payment
        'products' => [...],
    ],
]);

// Later, capture the payment
$capturedPurchase = Chip::capturePurchase($purchase->id);
// Or release the hold
$releasedPurchase = Chip::releasePurchase($purchase->id);
```

#### Cancel and Refund
```php
// Cancel unpaid purchase
$cancelledPurchase = Chip::cancelPurchase('pur_abc123');

// Refund paid purchase
$refund = Chip::refundPurchase('pur_abc123', 1000); // Partial refund RM 10.00
$fullRefund = Chip::refundPurchase('pur_abc123'); // Full refund
```

### Recurring Payments

#### Create Subscription
```php
$subscription = Chip::subscriptions()->create([
    'client_email' => 'customer@example.com',
    'amount' => 1990, // RM 19.90
    'interval' => 'monthly',
    'trial_days' => 7,
    'description' => 'Premium Monthly Subscription',
]);
```

#### Charge Recurring Token
```php
// After customer completes initial payment with force_recurring: true
$recurringPurchase = Chip::chargePurchase('pur_initial123', 'rec_token456');
```

### Client Management

#### Create and Manage Clients
```php
// Create client
$client = Chip::createClient([
    'email' => 'customer@example.com',
    'full_name' => 'John Doe',
    'phone' => '+60123456789',
]);

// Update client
$updatedClient = Chip::updateClient($client->id, [
    'full_name' => 'John Doe Jr.',
]);

// List clients
$clients = Chip::listClients(['email' => 'customer@example.com']);

// Get recurring tokens for client
$tokens = Chip::listRecurringTokens($client->id);
```

## Webhook Handling

### Webhook Controller Implementation
The application already has `ChipWebhookController` that handles webhook verification and processing:

```php
// app/Http/Controllers/ChipWebhookController.php
use Masyukai\Chip\Services\WebhookService;

class ChipWebhookController extends Controller
{
    public function handle(Request $request): Response
    {
        // Signature verification is automatic
        if (!$this->webhookService->verifySignature($request)) {
            return response('Unauthorized', 401);
        }

        $eventType = $request->input('event');
        $purchaseData = $request->input('data', []);

        switch ($eventType) {
            case 'purchase.paid':
                $this->handlePurchasePaid($purchaseData);
                break;
            case 'purchase.created':
                $this->handlePurchaseCreated($purchaseData);
                break;
            // ... other events
        }

        return response('OK');
    }
}
```

### Webhook Route
Already configured in `routes/web.php`:
```php
Route::post('/webhooks/chip', [ChipWebhookController::class, 'handle'])->name('webhooks.chip');
```

### Laravel Events Integration
The package dispatches Laravel events for webhook processing:

```php
use Masyukai\Chip\Events\{PurchaseCreated, PurchasePaid};

// Listen to purchase events
Event::listen(PurchasePaid::class, function (PurchasePaid $event) {
    $purchase = $event->purchase;
    $order = Order::where('chip_purchase_id', $purchase->id)->first();
    
    if ($order) {
        $order->update(['status' => 'paid']);
        // Send confirmation email, update inventory, etc.
    }
});
```

## CHIP Send (Money Transfers)

### Basic Transfer
```php
use Masyukai\Chip\Facades\ChipSend;

$transfer = ChipSend::createTransfer([
    'amount' => 10000, // RM 100.00 in cents
    'recipient' => [
        'bank_account' => '1234567890',
        'bank_code' => 'MBBEMYKL', // Maybank
        'name' => 'Jane Doe',
    ],
    'reference' => 'Salary Payment #123',
    'description' => 'Monthly salary payment',
]);
```

### Bank Account Management
```php
// Add bank account for recipient
$bankAccount = ChipSend::addBankAccount([
    'account_number' => '1234567890',
    'bank_code' => 'MBBEMYKL',
    'account_holder_name' => 'John Doe',
]);

// List available banks
$banks = ChipSend::getBanks();
```

## Payment Amount Handling

### Important: Always Use Cents
All amounts in CHIP APIs are in cents (Malaysian sen):

```php
// ✅ Correct
$amountInRM = 29.99;
$amountInCents = (int) ($amountInRM * 100); // 2999 for API

// ✅ For display
$displayAmount = number_format($amountInCents / 100, 2); // "29.99"

// ❌ Wrong - passing decimal values
Chip::createPurchase([
    'purchase' => [
        'products' => [
            ['name' => 'Item', 'price' => 29.99], // Wrong!
        ],
    ],
]);
```

## Payment Methods

### Available Payment Methods
```php
$paymentMethods = Chip::getPaymentMethods();

// Common methods:
// - 'visa', 'mastercard', 'maestro': Card payments
// - 'fpx': FPX Online Banking (Malaysia)
// - 'ewallet': E-wallet payments (Touch 'n Go, GrabPay, etc.)
// - 'duitnow_qr': DuitNow QR payments
// - 'bnpl': Buy Now, Pay Later (Atome, etc.)
```

### Restrict Payment Methods
```php
$purchase = Chip::createPurchase([
    'client' => [...],
    'purchase' => [
        'products' => [...],
        'payment_method_whitelist' => ['fpx', 'ewallet'], // Only allow these
    ],
]);
```

## Error Handling

### API Exceptions
```php
use Masyukai\Chip\Exceptions\{ChipException, ValidationException, PaymentException};

try {
    $purchase = Chip::createPurchase($data);
} catch (ValidationException $e) {
    // Handle validation errors
    $errors = $e->getErrors();
} catch (PaymentException $e) {
    // Handle payment-specific errors
    Log::error('Payment failed', ['error' => $e->getMessage()]);
} catch (ChipException $e) {
    // Handle general CHIP API errors
    Log::error('CHIP API error', ['error' => $e->getMessage()]);
}
```

### HTTP Status Handling
```php
// The package automatically handles HTTP errors and retries
// Configure retry behavior in config/chip.php
'retry' => [
    'attempts' => 3,
    'delay' => 1000, // milliseconds
],
```

## Testing

### Using Package in Tests
```php
use Masyukai\Chip\Facades\Chip;

class PaymentTest extends TestCase
{
    public function test_can_create_purchase()
    {
        $purchase = Chip::createPurchase([
            'client' => [
                'email' => 'test@example.com',
                'full_name' => 'Test User',
            ],
            'purchase' => [
                'products' => [
                    ['name' => 'Test Product', 'price' => 1000],
                ],
            ],
        ]);

        $this->assertNotNull($purchase->id);
        $this->assertEquals(1000, $purchase->total_amount);
    }
}
```

### Webhook Testing
```php
public function test_webhook_handling()
{
    $webhookPayload = [
        'event' => 'purchase.paid',
        'data' => [
            'id' => 'pur_test123',
            'status' => 'paid',
            'total_amount' => 1000,
        ],
    ];

    $response = $this->postJson('/webhooks/chip', $webhookPayload);
    $response->assertStatus(200);
}
```

## Best Practices

### 1. Always Store Purchase IDs
```php
// When creating an order
$purchase = Chip::createPurchase([...]);

Order::create([
    'chip_purchase_id' => $purchase->id,
    'user_id' => auth()->id(),
    'total_amount' => $purchase->total_amount,
    'status' => 'pending',
]);
```

### 2. Use Events for Order Processing
```php
// In EventServiceProvider
protected $listen = [
    PurchasePaid::class => [
        UpdateOrderStatus::class,
        SendOrderConfirmation::class,
        UpdateInventory::class,
    ],
];
```

### 3. Handle Different Purchase States
```php
public function handlePurchasePaid($purchaseData)
{
    $order = Order::where('chip_purchase_id', $purchaseData['id'])->first();
    
    if (!$order) {
        Log::warning('Order not found for purchase', ['purchase_id' => $purchaseData['id']]);
        return;
    }

    switch ($purchaseData['status']) {
        case 'paid':
            $order->markAsPaid();
            break;
        case 'cancelled':
            $order->markAsCancelled();
            break;
        case 'refunded':
            $order->markAsRefunded();
            break;
    }
}
```

### 4. Proper Amount Handling in Cart Integration
```php
// When integrating with the cart system
use MasyukAI\Cart\Facades\Cart;

public function createPaymentFromCart()
{
    $cartItems = Cart::content();
    $totalInCents = (int) (Cart::getRawTotal() * 100);

    $products = $cartItems->map(function ($item) {
        return [
            'name' => $item->name,
            'price' => (int) ($item->price * 100), // Convert to cents
            'quantity' => $item->quantity,
        ];
    })->toArray();

    return Chip::createPurchase([
        'client' => [
            'email' => auth()->user()->email,
            'full_name' => auth()->user()->name,
        ],
        'purchase' => [
            'products' => $products,
            'success_callback' => route('checkout.success'),
            'failure_callback' => route('checkout.failed'),
        ],
    ]);
}
```

### 5. Environment-Specific Configuration
```php
// Use different settings for different environments
if (app()->environment('production')) {
    // Production-specific settings
    config(['chip.collect.timeout' => 60]);
    config(['chip.logging.log_requests' => false]);
} else {
    // Development settings
    config(['chip.logging.log_requests' => true]);
    config(['chip.logging.log_responses' => true]);
}
```

This package provides a robust, modern payment gateway solution specifically built for this Laravel application. Use it for all payment processing, subscription management, and money transfer functionality.
- Current API endpoints and parameters
- Request/response formats
- Error handling patterns
- Authentication requirements
- Payment flow examples

### 2. Common Integration Patterns
- **Payment Links**: Create purchases and redirect to `checkout_url`
- **Pre-Authorization**: Use `skip_capture: true` for hold/capture workflows
- **Recurring Payments**: Use `force_recurring: true` to tokenize payment methods
- **Webhooks**: Always implement proper webhook handling for payment status updates

### 3. Laravel Best Practices
- Create a dedicated `ChipPaymentService` class
- Use Laravel HTTP client for API calls
- Implement proper error handling and logging
- Store purchase IDs for transaction tracking
- Use events for payment status changes

### 4. Security Considerations
- Always use HTTPS for webhook endpoints
- Validate webhook signatures when available
- Never store sensitive payment data
- Use test mode during development
- Handle PCI DSS compliance requirements

## Quick Reference

### Getting Documentation
```php
// Use Context7 MCP to get specific documentation
// Example topics to search for:
// - "create purchase API"
// - "capture payment"
// - "recurring payments"
// - "webhook handling"
// - "refund payment"
```

### Payment Amount Format
All amounts in CHIP Collect API are in cents:
```php
$amountInRM = 29.99;
$amountInCents = (int) ($amountInRM * 100); // 2999 for API
```

### Common Payment Methods
- `visa`, `mastercard`, `maestro`: Card payments
- `fpx`: FPX Online Banking (Malaysia)
- `ewallet`: E-wallet payments  
- `duitnow_qr`: DuitNow QR payments
- `bnpl`: Buy Now, Pay Later

For detailed implementation examples, API endpoints, request/response formats, and troubleshooting, always refer to the Context7 MCP tool for the most up-to-date CHIP Collect API documentation.
