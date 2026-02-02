# aiarmada/checkout Integration

## Summary

The `aiarmada/checkout` package has been integrated into KakKay with a hybrid approach due to model schema incompatibilities.

## What Was Done

### 1. Package Installation
- Added `aiarmada/checkout: dev-develop` to composer.json
- Symlinked from `/Users/Saiffil/Herd/commerce/packages/checkout`

### 2. Database Migration
- Published and ran `create_checkout_sessions_table` migration
- Uses `jsonb` column type for PostgreSQL compatibility

### 3. Configuration (`config/checkout.php`)
Disabled steps that conflict with existing app architecture:
```php
'enabled_steps' => [
    'process_payment' => true,
    'create_order' => false,      // Using custom listener instead
    'calculate_shipping' => false, // App handles shipping differently
    'calculate_tax' => false,      // No tax implementation
    'reserve_inventory' => false,  // Not using inventory reservations
    'dispatch_documents' => false, // Not using document dispatch
],
```

### 4. Routes
Package auto-registers checkout payment callback routes:
- `GET /checkout/payment/success` - Payment success callback
- `GET /checkout/payment/failure` - Payment failure callback
- `GET /checkout/payment/cancel` - Payment cancellation callback
- `POST /webhooks/checkout` - Webhook handler

### 5. Livewire Checkout Component
Updated `App\Livewire\Checkout` to:
- Use `AIArmada\Checkout\Facades\Checkout` for session management
- Store `billing_data` and `shipping_data` on CheckoutSession
- Handle payment redirects from `CheckoutResult`

### 6. Order Creation Bridge
Created `App\Listeners\CreateOrderFromCheckout`:
- Listens for `CheckoutStepCompleted` event when payment step completes
- Creates `App\Models\Order` using existing `OrderService`
- Creates `App\Models\Address` and `App\Models\Payment`
- Dispatches `OrderPaid` event for invoice generation
- Updates CheckoutSession with `order_id`

## Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                   User Submits Checkout Form                     │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│  App\Livewire\Checkout::submitCheckout()                         │
│  - Validates form data                                           │
│  - Calls CheckoutFacade::startCheckout($cartId)                 │
│  - Stores billing_data, shipping_data on session                │
│  - Calls CheckoutFacade::processCheckout($session)              │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│  AIArmada\Checkout Package                                       │
│  - ProcessPaymentStep creates CHIP purchase                      │
│  - Redirects to payment gateway                                  │
│  - Handles callback (success/failure/cancel)                     │
│  - Dispatches CheckoutStepCompleted event                        │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│  App\Listeners\CreateOrderFromCheckout                           │
│  - Listens for stepIdentifier === 'process_payment'              │
│  - Creates User (if guest checkout)                              │
│  - Creates Address using shipping_data                           │
│  - Creates Order using OrderService                              │
│  - Creates Payment record                                        │
│  - Dispatches OrderPaid event                                    │
│  - Clears cart                                                   │
└─────────────────────────────────────────────────────────────────┘
```

## Package Improvement Suggestions

### 1. Order Model Flexibility
**Issue:** `CreateOrderStep` assumes `AIArmada\Orders\Models\Order` schema which uses:
- `first_name`, `last_name` vs app's `name`
- `line1`, `line2` vs app's `street1`, `street2`

**Suggestion:** Allow configurable Order model class:
```php
// config/checkout.php
'models' => [
    'order' => App\Models\Order::class,
],
```

### 2. Address Schema Compatibility
**Issue:** Package address DTOs use different field names than applications may have.

**Suggestion:** Add address field mapping configuration:
```php
'address_mapping' => [
    'line1' => 'street1',
    'line2' => 'street2',
    'first_name' => null, // derive from 'name'
    'last_name' => null,
],
```

### 3. Session Data Transformation
**Issue:** Applications need to transform session data (billing_data, shipping_data) to their own schemas.

**Suggestion:** Add configurable transformer classes:
```php
'transformers' => [
    'billing' => App\Transformers\BillingDataTransformer::class,
    'shipping' => App\Transformers\ShippingDataTransformer::class,
],
```

### 4. Event-Based Order Creation Hook
**Issue:** When `create_order` step is disabled, there's no built-in hook for custom order creation.

**Suggestion:** Add a dedicated `CheckoutPaymentCompleted` event that fires after payment success, separate from generic `CheckoutStepCompleted`, with more contextual data:
```php
// Fires specifically when payment succeeds
CheckoutPaymentCompleted::class => [
    'session' => CheckoutSession,
    'payment_id' => string,
    'payment_data' => array,
    'cart_snapshot' => array,
]
```

### 5. Non-aiarmada/orders Integration Guide
**Suggestion:** Add documentation for integrating with custom Order models, including:
- Required event listeners
- Data transformation examples
- Schema mapping strategies

### 6. Cart Snapshot Enhancement
**Issue:** Cart snapshot structure varies based on cart package version.

**Suggestion:** Standardize cart snapshot schema with explicit documentation:
```php
[
    'items' => [...],
    'totals' => [
        'subtotal' => int,
        'subtotal_without_conditions' => int,
        'total' => int,
    ],
    'conditions' => [...],
    'metadata' => [...],
]
```

## Files Modified

- `composer.json` - Added package dependency
- `config/checkout.php` - Published and customized
- `app/Livewire/Checkout.php` - Updated to use checkout facade
- `app/Listeners/CreateOrderFromCheckout.php` - Created (new)
- `app/Providers/AppServiceProvider.php` - Registered event listener
- `routes/web.php` - Removed old checkout routes (now using package routes)

## Testing

Run checkout-related tests:
```bash
php artisan test --filter=Checkout
```

## Debugging

Check checkout sessions:
```bash
php artisan tinker
>>> \AIArmada\Checkout\Models\CheckoutSession::latest()->first()
```

Check logs:
```bash
tail -f storage/logs/laravel.log | grep -i checkout
```
