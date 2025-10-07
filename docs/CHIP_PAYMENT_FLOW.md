# CHIP Payment Flow Documentation

## Overview

This document explains how CHIP payment integration works in the application, based on actual behavior observed in production logs.

## Payment Flow

### 1. User Initiates Checkout

- User clicks "Pay Now" button
- `CheckoutService::processCheckout()` creates a payment intent
- Payment intent stored in cart metadata with:
  - `purchase_id`: CHIP purchase ID
  - `amount`: Total amount
  - `cart_version`: Current cart version
  - `reference`: Cart ID (for fast lookup)
- User redirected to CHIP checkout page

### 2. User Completes Payment

After successful payment on CHIP's page, CHIP sends **TWO separate requests**:

#### A. Success Callback (Immediate)
- **URL**: `POST /webhooks/chip` (no webhook_id)
- **Timing**: Immediately after payment
- **Purpose**: Primary order creation mechanism
- **Payload**: Complete purchase data with `event_type: "purchase.paid"`
- **Request Type**: Identified as `success_callback` (no webhook_id in URL)

#### B. Webhook (Slightly Delayed)
- **URL**: `POST /webhooks/chip/{webhook_id}` (with webhook_id)
- **Timing**: Shortly after success callback (1-2 seconds delay)
- **Purpose**: Backup order creation + additional processing
- **Payload**: Identical to success callback with `event_type: "purchase.paid"`
- **Request Type**: Identified as `webhook` (webhook_id present in URL)

### 3. User Redirected to Success Page

- **URL**: `GET /checkout/success/{reference}` 
- **Timing**: After payment completion
- **Purpose**: Display success message and order details
- **Fallback**: If webhooks/callbacks fail, this page attempts order creation

## Request Processing

Both success callbacks and webhooks are processed identically through `ChipController::handle()`:

```php
public function handle(Request $request, ?string $webhookId = null): Response
{
    // 1. Determine request type based on webhook_id in URL
    $requestType = $webhookId !== null ? 'webhook' : 'success_callback';
    
    // 2. Verify RSA signature
    if (!$this->webhookService->verifySignature($request, publicKey: $publicKey)) {
        return response('Unauthorized', 401);
    }
    
    // 3. Create Webhook data object from request
    $webhook = ChipWebhookFactory::fromRequest($request, $webhookId, $publicKey);
    
    // 4. Record in database for debugging
    $this->chipDataRecorder->recordWebhook($webhook);
    
    // 5. Process through WebhookProcessor
    $this->webhookProcessor->handle($webhook);
    
    return response('OK', 200);
}
```

## Event Processing

The `WebhookProcessor` handles different event types:

```php
match ($event) {
    'purchase.paid' => $this->handlePurchasePaid($webhook, $purchaseData, $payload),
    'purchase.payment_failure' => $this->handlePaymentFailure($webhook, $purchaseData),
    default => $this->logInformationalEvent($webhook, $event, $purchaseData),
};
```

## Order Creation

`CheckoutService::handlePaymentSuccess()` creates orders with **idempotency protection**:

### Idempotency Check

```php
// Check if order already exists for this purchase
$existingOrder = Order::whereHas('payments', function ($query) use ($purchaseId) {
    $query->where('gateway_payment_id', $purchaseId);
})->first();

if ($existingOrder) {
    Log::info('Order already exists, skipping duplicate creation');
    return $existingOrder; // Return existing order
}
```

### Order Creation Flow

1. **Locate Cart**: Find cart by reference from webhook payload
2. **Validate Intent**: Verify payment intent matches webhook data
3. **Create Order**: 
   - Create Order record
   - Create Payment record (linked to order)
   - Link cart items to order
   - Update order status to 'completed'
4. **Clear Cart**: Remove cart data after successful order creation
5. **Update Metadata**: Mark payment intent as processed

## Key Features

### 1. Idempotency

- Both success callback and webhook may arrive
- Order creation is idempotent - checks for existing orders
- Only first request creates order, second skips creation
- No duplicate orders even if both requests succeed

### 2. Payload Structure

Both requests contain identical data:

```json
{
  "id": "c9f158c0-8056-4f11-b8b9-0e29dcf63164",
  "event_type": "purchase.paid",
  "status": "paid",
  "reference": "d82cf0da-6e78-45ed-9040-3fc7f8207740",
  "payment": {
    "amount": 5000,
    "currency": "MYR",
    "paid_on": 1759868976
  },
  "purchase": {
    "total": 5000,
    "products": [...]
  },
  "client": {...},
  "success_redirect": "https://kakkay.test/checkout/success/...",
  "success_callback": "https://local.kakkay.my/webhooks/chip",
  "cancel_redirect": "https://kakkay.test/checkout/cancel/...",
  "failure_redirect": "https://kakkay.test/checkout/failure/..."
}
```

### 3. Signature Verification

- All requests (success callbacks and webhooks) include `X-Signature` header
- RSA signature verified using CHIP's public key
- Requests without valid signature are rejected (401 Unauthorized)

### 4. Comprehensive Logging

All stages are logged for debugging:

```php
// Request received
Log::debug('CHIP request received', ['type' => $requestType, ...]);

// Signature verification
Log::debug('CHIP signature verified successfully', [...]);

// Webhook processing
Log::info('CHIP webhook processing started', [...]);

// Order creation
Log::info('Order successfully created from webhook', [...]);

// Completion
Log::info('CHIP request processed successfully', [...]);
```

## Configuration

### Environment Variables

```env
# CHIP API Configuration
CHIP_COLLECT_API_KEY=your_api_key
CHIP_COLLECT_BRAND_ID=your_brand_id
CHIP_COMPANY_PUBLIC_KEY=your_public_key

# Public URL for callbacks (use tunnel for local dev)
PUBLIC_URL=https://local.kakkay.my
```

### Purchase Configuration

Set in `ChipPaymentGateway::createPurchase()`:

```php
$purchase = $this->chipService->createCheckoutPurchase(
    $chipProducts,
    $clientDetails,
    [
        'success_redirect' => route('checkout.success', ['reference' => $cartReference]),
        'failure_redirect' => route('checkout.failure', ['reference' => $cartReference]),
        'cancel_redirect' => route('checkout.cancel', ['reference' => $cartReference]),
        'success_callback' => config('app.public_url') . '/webhooks/chip',
        'send_receipt' => true,
        'reference' => CartFacade::getId(),
    ]
);
```

## Testing Locally

### Using Cloudflare Tunnel

1. **Start Tunnel** (keep running in dedicated terminal):
   ```bash
   cloudflared tunnel run kakkay-local
   ```

2. **Configure Public URL** in `.env`:
   ```env
   PUBLIC_URL=https://local.kakkay.my
   SESSION_DOMAIN=.kakkay.my
   SESSION_SECURE_COOKIE=true
   ```

3. **Test Payment Flow**:
   - Browse to `http://kakkay.test`
   - Add items to cart
   - Proceed to checkout
   - Complete payment
   - Check logs: `tail -f storage/logs/laravel.log`

4. **Verify Orders**:
   ```bash
   php artisan tinker
   >>> App\Models\Order::latest()->first();
   ```

## Troubleshooting

### No Orders Created

1. **Check logs** for webhook/callback reception:
   ```bash
   grep "CHIP request received" storage/logs/laravel.log
   ```

2. **Verify signature verification**:
   ```bash
   grep "signature verification" storage/logs/laravel.log
   ```

3. **Check order creation attempts**:
   ```bash
   grep "Order successfully created" storage/logs/laravel.log
   ```

### Duplicate Orders

- Should not happen due to idempotency protection
- If occurring, check `handlePaymentSuccess()` for proper duplicate detection

### Webhook Not Received

1. **Verify tunnel is running** (for local dev)
2. **Check CHIP dashboard** webhook configuration
3. **Verify public URL** is accessible from internet
4. **Check signature verification** in logs

## Database Tables

### chip_webhooks

Stores all received webhooks/callbacks:

- `id`: Unique identifier (purchase_id or generated)
- `event_type`: Event name (e.g., 'purchase.paid')
- `payload`: Complete request payload (JSON)
- `signature`: RSA signature from request header
- `verified`: Whether signature was valid
- `processed`: Whether event was processed successfully
- `processing_error`: Error message if processing failed

### orders

Stores created orders:

- `id`: UUID
- `order_number`: Human-readable order number (e.g., ORD25-1441DE)
- `user_id`: Customer (nullable)
- `status`: Order status (completed, pending, etc.)
- `total`: Order total amount

### payments

Links orders to CHIP purchases:

- `id`: UUID
- `order_id`: Foreign key to orders
- `gateway`: Always 'chip'
- `gateway_payment_id`: CHIP purchase ID
- `status`: Payment status (completed, pending, etc.)
- `amount`: Payment amount

## Summary

✅ **Success callbacks work**: POST to `/webhooks/chip` creates orders
✅ **Webhooks work**: POST to `/webhooks/chip/{id}` creates orders  
✅ **Idempotency works**: No duplicate orders when both arrive
✅ **Signature verification works**: All requests are verified
✅ **Logging works**: Complete audit trail in logs

The payment integration is **fully functional** with robust error handling and idempotency protection.
