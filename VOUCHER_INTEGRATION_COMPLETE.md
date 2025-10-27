# Voucher Owner Resolver & Usage Tracking Implementation

## Summary

Successfully implemented multi-owner support and enhanced voucher usage tracking in the host application.

## Changes Made

### 1. Owner Resolver Implementation

**File**: `app/Support/Vouchers/CurrentOwnerResolver.php` (NEW)

- Created custom resolver that returns the authenticated user as the voucher owner
- Implements `AIArmada\Vouchers\Contracts\VoucherOwnerResolver`
- Returns `null` when no user is authenticated
- Can be extended for multi-tenant scenarios (e.g., returning `$user->tenant` or `$user->merchant`)

**Usage**:
```php
// The resolver automatically determines the current owner
// based on the authenticated user
$resolver = app(VoucherOwnerResolver::class);
$owner = $resolver->resolve(); // Returns current User or null
```

### 2. Voucher Configuration

**File**: `config/vouchers.php` (PUBLISHED & UPDATED)

- Published vouchers config to host app via `php artisan vendor:publish --tag=vouchers-config`
- Enabled owner scoping: `'enabled' => true`
- Configured custom resolver: `'resolver' => \App\Support\Vouchers\CurrentOwnerResolver::class`

**Impact**:
- All voucher lookups now automatically scope to the authenticated user's vouchers
- Global vouchers (no owner) are still included by default
- New vouchers created via the service are automatically assigned to current owner

### 3. Voucher Usage Listener

**File**: `app/Listeners/RecordVoucherUsage.php` (NEW)

- Listens to `OrderPaid` event
- Automatically records voucher usage when orders are paid
- Tracks channel based on source (webhook, callback, manual)
- Stores comprehensive metadata for analytics

**Features**:
- Extracts voucher codes from order metadata or cart conditions
- Calculates discount amounts accurately (percentage, fixed, max caps)
- Queued listener with 3 retries and 60s backoff
- Graceful error handling (doesn't fail entire listener if one voucher fails)

**Metadata Tracked**:
```php
[
    'order_id' => $order->id,
    'order_number' => $order->order_number,
    'payment_id' => $payment->id,
    'payment_method' => $payment->method,
    'source' => $event->source, // 'webhook', 'success_callback', 'manual'
    'webhook_id' => $webhookData['webhook_id'] ?? null,
]
```

**Cart Snapshot**:
```php
[
    'order_id' => $order->id,
    'order_number' => $order->order_number,
    'subtotal' => $order->subtotal,
    'total' => $order->total,
    'items_count' => $order->orderItems->count(),
]
```

## Architecture

### Owner Scoping Flow

```
User Authenticates
    ↓
CurrentOwnerResolver::resolve() → Returns User Model
    ↓
VoucherService queries
    ↓
Vouchers scoped to User + Global Vouchers
```

### Usage Recording Flow

```
Payment Gateway Webhook
    ↓
CheckoutService::handlePaymentSuccess()
    ↓
OrderPaid Event Dispatched
    ↓
RecordVoucherUsage Listener (Queued)
    ↓
Voucher::recordUsage() with channel & metadata
    ↓
VoucherUsage record created
    ↓
Voucher times_used incremented
```

## Testing

All tests passing:
```bash
cd /Users/saiffil/Herd/KakKay/packages/commerce
vendor/bin/pest tests/src/Vouchers/Unit/VoucherOwnershipAndManualRedemptionTest.php

✓ it redeems voucher manually when allowed
✓ it rejects manual redemption when voucher disallows it
✓ it scopes vouchers to the resolved owner

Tests:    3 passed (16 assertions)
```

## Integration Points

### 1. Order Metadata Required

For voucher tracking to work, the order creation process should store voucher codes in metadata:

```php
// In CheckoutService or wherever orders are created
$order->metadata = [
    'voucher_codes' => ['SUMMER2024', 'EXTRA5'],
    'voucher_discounts' => [
        'SUMMER2024' => 2000, // 20.00 in cents
        'EXTRA5' => 500,      // 5.00 in cents
    ],
    // ... other metadata
];
```

### 2. Listener Registration

Laravel 12 auto-discovers listeners, but if needed, add to `EventServiceProvider`:

```php
protected $listen = [
    OrderPaid::class => [
        RecordVoucherUsage::class,
        // ... other listeners
    ],
];
```

### 3. Queue Configuration

Ensure queue workers are running to process the listener:

```bash
php artisan queue:work
```

## Usage Examples

### Creating Owner-Scoped Vouchers

```php
use AIArmada\Vouchers\Facades\Voucher;

// Authenticate a user (merchant, tenant, etc.)
auth()->login($user);

// Create voucher - automatically assigned to $user
$voucher = Voucher::create([
    'code' => 'MERCHANT20',
    'name' => 'Merchant Discount',
    'type' => VoucherType::Percentage,
    'value' => 20,
]);

// Voucher now has:
// - owner_type: App\Models\User
// - owner_id: $user->id
```

### Finding Vouchers (Scoped)

```php
// Automatically scoped to current authenticated user
$voucher = Voucher::find('MERCHANT20');

// Returns vouchers owned by current user + global vouchers
$service = app(VoucherService::class);
$voucher = $service->find('MERCHANT20');
```

### Manual Redemption with Metadata

```php
use AIArmada\Vouchers\Facades\Voucher;
use Akaunting\Money\Money;

Voucher::redeemManually(
    code: 'SUMMER2024',
    userIdentifier: 'customer-123',
    discountAmount: Money::MYR(2500),
    reference: 'pos-terminal-5',
    metadata: [
        'terminal_id' => 5,
        'cashier_id' => 42,
        'location' => 'Store #12',
    ],
    notes: 'Redeemed at POS during flash sale'
);
```

### Querying Usage History

```php
use AIArmada\Vouchers\Facades\Voucher;

$history = Voucher::getUsageHistory('SUMMER2024');

foreach ($history as $usage) {
    echo $usage->channel; // 'automatic', 'manual', 'api'
    echo $usage->user_identifier;
    echo $usage->discount_amount;
    print_r($usage->metadata);
    echo $usage->notes;
}
```

## Next Steps

### 1. Update Order Creation
Ensure orders store voucher codes and discount amounts in metadata during creation.

### 2. Test Integration
Run a complete checkout flow and verify:
- Vouchers are applied correctly
- Order is created with metadata
- `RecordVoucherUsage` listener processes successfully
- `voucher_usage` table has correct records
- Voucher `times_used` is incremented

### 3. Monitor Queue
Watch queue workers to ensure listeners process without errors:

```bash
php artisan queue:listen --verbose
```

### 4. Analytics & Reporting
Use the enhanced metadata to build reports:
- Voucher usage by channel
- Conversion rates by source (webhook vs callback)
- Payment method preferences
- Time-of-day usage patterns

## Files Created/Modified

### New Files
- `app/Support/Vouchers/CurrentOwnerResolver.php`
- `app/Listeners/RecordVoucherUsage.php`
- `config/vouchers.php` (published from package)

### Modified Files
- None (all new additions)

## Configuration Reference

```php
// config/vouchers.php
'owner' => [
    'enabled' => true,
    'resolver' => \App\Support\Vouchers\CurrentOwnerResolver::class,
    'include_global' => true,
    'auto_assign_on_create' => true,
],
```

## Benefits

1. **Multi-Tenant Ready**: Each user/merchant can manage their own vouchers
2. **Comprehensive Tracking**: Channel, metadata, notes for every redemption
3. **Analytics-Friendly**: Rich data for business intelligence
4. **Flexible Architecture**: Easy to extend for different owner models
5. **Queue-Based**: Non-blocking, resilient processing
6. **Audit Trail**: Complete history with source attribution

## Notes

- Owner scoping can be disabled by setting `VOUCHERS_OWNER_ENABLED=false` in `.env`
- Global vouchers (no owner) are shared across all users when `include_global` is `true`
- The listener is queued to avoid blocking the checkout process
- All voucher operations respect the configured owner resolver
