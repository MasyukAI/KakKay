# Cart & Checkout System Analysis

**Date:** October 6, 2025  
**Scope:** Data submission, enhancements, and business logic verification

---

## 1. Payment Gateway Data Submission Analysis

### Current Data Being Sent to CHIP

Based on `app/Services/ChipPaymentGateway.php` (lines 204-232), the following data is submitted:

```php
ClientDetails::fromArray([
    'full_name' => $customerData['name'],
    'email' => $customerData['email'],
    'phone' => $customerData['phone'] ?? '',
    'personal_code' => $customerData['personal_code'] ?? $customerData['id_number'] ?? '',
    'legal_name' => $customerData['company_name'] ?? $customerData['name'],
    'brand_name' => $customerData['brand_name'] ?? $customerData['company_name'] ?? $customerData['name'],
    'street_address' => $customerData['address'] ?? '',
    'country' => $customerData['country'] ?? 'MY',
    'city' => $customerData['city'] ?? '',
    'zip_code' => (string) ($customerData['zip'] ?? $customerData['postal_code'] ?? ''),
    'state' => $customerData['state'] ?? '',
    'registration_number' => $customerData['registration_number'] ?? '',
    'tax_number' => $customerData['tax_number'] ?? '',
    'bank_account' => $customerData['bank_account'] ?? null,
    'bank_code' => $customerData['bank_code'] ?? null,
    // Shipping details (duplicating billing)
    'shipping_street_address' => $customerData['shipping_address'] ?? $customerData['address'] ?? '',
    'shipping_country' => $customerData['shipping_country'] ?? $customerData['country'] ?? 'MY',
    'shipping_city' => $customerData['shipping_city'] ?? $customerData['city'] ?? '',
    'shipping_zip_code' => (string) ($customerData['shipping_zip'] ?? $customerData['zip'] ?? $customerData['postal_code'] ?? ''),
    'shipping_state' => $customerData['shipping_state'] ?? $customerData['state'] ?? '',
]);
```

### Checkout Form Preparation (app/Livewire/Checkout.php, lines 377-408)

```php
$customerData = [
    // Core address fields
    'name' => $formData['name'],
    'company' => $formData['company'] ?? '',
    'street1' => $formData['street1'],
    'street2' => $formData['street2'] ?? '',
    'city' => $formData['city'] ?? '',
    'state' => $formData['state'],
    'country' => $formData['country'],
    'postcode' => $formData['postcode'],
    'phone' => $formData['phone'],
    'email' => $formData['email'],
    
    // CHIP API specific fields
    'address' => $formData['street1'].($formData['street2'] ? ', '.$formData['street2'] : ''),
    'zip' => $formData['postcode'],
    
    // DEFAULT VALUES - POTENTIALLY UNNECESSARY
    'personal_code' => $formData['vat_number'] ?? 'PERSONAL',
    'brand_name' => $formData['company'] ?? $formData['name'],
    'legal_name' => $formData['company'] ?? $formData['name'],
    'registration_number' => $formData['vat_number'] ?? '',
    'tax_number' => $formData['vat_number'] ?? '',
    
    // Payment method whitelist - CURRENTLY EMPTY
    'payment_method_whitelist' => [],
];
```

### CHIP API Requirements (According to Documentation)

According to `packages/masyukai/chip/docs/CHIP_API_REFERENCE.md`:

**Required for Purchase Creation:**
- `brand_id` ✅ (automatically added by service)
- `purchase.products` ✅ (cart items)
- Either `client` payload with `email` OR `client_id` ✅

**Client Details - ALL OPTIONAL except email:**
- `email` - **REQUIRED** ✅
- All other fields (name, phone, address, etc.) - **OPTIONAL**

### Issues Identified

#### ❌ **Issue 1: Sending Empty Strings Instead of Omitting Fields**

The `ClientDetails::toArray()` method (line 104) filters out:
- `null` values ✅
- Empty arrays ✅  
- Empty strings ✅

However, the Checkout.php is providing **fallback empty strings** instead of null:
```php
'company' => $formData['company'] ?? '',  // Should be null if not provided
'street2' => $formData['street2'] ?? '',  // Should be null if not provided
```

#### ❌ **Issue 2: Unnecessary Default Values**

```php
'personal_code' => $formData['vat_number'] ?? 'PERSONAL',  // Don't send if not provided
'brand_name' => $formData['company'] ?? $formData['name'], // Don't send if not provided
'legal_name' => $formData['company'] ?? $formData['name'], // Don't send if not provided
```

These fields should be **omitted entirely** if not provided by the user, not filled with defaults.

#### ❌ **Issue 3: Duplicate Shipping Address**

Currently, billing address is duplicated as shipping address. This should only be done if explicitly chosen by user.

#### ❌ **Issue 4: Payment Method Whitelist is Empty**

```php
'payment_method_whitelist' => [],  // Not being used
```

The checkout form has payment method selection commented out, so the whitelist is always empty. This means CHIP will show ALL available payment methods instead of the user's selection.

### ✅ Recommendations

1. **Remove unnecessary defaults in Checkout.php:**
```php
$customerData = [
    'name' => $formData['name'],
    'email' => $formData['email'],
    'phone' => $formData['phone'],
    'street1' => $formData['street1'],
    'state' => $formData['state'],
    'country' => $formData['country'],
    'postcode' => $formData['postcode'],
    
    // Optional fields - only include if provided
    'company' => $formData['company'] ?? null,
    'street2' => $formData['street2'] ?? null,
    'city' => $formData['city'] ?? null,
    
    // CHIP specific - only if needed
    'address' => $formData['street1'].($formData['street2'] ? ', '.$formData['street2'] : ''),
    'zip' => $formData['postcode'],
];
```

2. **Remove business-related fields for consumer purchases:**
   - `personal_code`
   - `brand_name`
   - `legal_name`
   - `registration_number`
   - `tax_number`
   - `bank_account`
   - `bank_code`

3. **Only send shipping address if different from billing:**
   - Add checkbox "Ship to different address?"
   - Only populate shipping fields if checked

4. **Fix payment method whitelist:**
   - Either implement payment method selection properly
   - Or remove the empty array to let CHIP handle it

---

## 2. Cart & Checkout Enhancement Recommendations

### Current Architecture Strengths ✅

1. **Payment Intent Pattern** - Using cart metadata to track payment state
2. **Cart Snapshot** - Preserving cart state at checkout time
3. **Optimistic Locking** - Database version tracking for concurrency
4. **Webhook Processing** - Proper event-driven architecture
5. **Transaction Safety** - DB transactions for order creation

### Identified Issues & Enhancements

#### 🔴 **Critical Issues**

1. **Cart Not Clearing After Successful Payment**
   - **Location:** `app/Services/CheckoutService.php:122`
   - **Issue:** `$cart->clear()` is called, but cart instance might not be properly retrieved
   - **Root Cause:** `findCartByPurchaseId()` database query may fail to find cart
   - **Fix:** Add better error handling and logging

2. **No User Feedback After Payment**
   - **Issue:** User redirected from CHIP but doesn't see confirmation
   - **Fix:** Add success page with order details

3. **Webhook Failure = No Order**
   - **Issue:** If webhook fails, order is never created
   - **Fix:** Add background job to check pending payments

#### 🟡 **Medium Priority**

4. **Payment Method Selection Disabled**
   - **Location:** `app/Livewire/Checkout.php:137-187` (commented out)
   - **Issue:** Payment method selection UI is disabled
   - **Impact:** Users can't pre-select payment method
   - **Fix:** Enable payment method selection or remove whitelist entirely

5. **Cart Change Warning Not Prominent**
   - **Issue:** Warning about cart changes is not visible enough
   - **Fix:** Show modal/alert when cart changes after payment intent

6. **No Idempotency for Webhooks**
   - **Issue:** Replaying webhook could create duplicate orders
   - **Fix:** Check if order already exists before creating

7. **Missing Admin Notifications**
   - **Issue:** Admin doesn't know when order creation fails
   - **Fix:** Send email/notification on webhook failures

#### 🟢 **Low Priority / Nice to Have**

8. **Suggested Products After Removal**
   - Reload after each removal (already implemented ✅)

9. **Voucher/Discount System**
   - Currently placeholder only
   - Full implementation needed

10. **Order Confirmation Email**
    - No email sent after order creation
    - Should send receipt to customer

11. **Guest Checkout Optimization**
    - Currently creates User with `is_guest = true`
    - Could use session-only checkout without User creation

12. **Stock Validation at Checkout**
    - Should check stock availability before payment
    - Prevent overselling

### Enhancement Plan

#### Phase 1: Critical Fixes (Week 1)

1. ✅ Fix cart clearing logic with better error handling
2. ✅ Add idempotency to webhook processing
3. ✅ Create order success page with order details
4. ✅ Add background job to check pending payments

#### Phase 2: User Experience (Week 2)

5. ✅ Implement payment method selection properly
6. ✅ Make cart change warnings more prominent
7. ✅ Add order confirmation email
8. ✅ Show loading states during checkout

#### Phase 3: Admin & Monitoring (Week 3)

9. ✅ Admin notifications for failed orders
10. ✅ Dashboard for pending/failed payments
11. ✅ Webhook replay functionality
12. ✅ Order status tracking

#### Phase 4: Features (Week 4)

13. ✅ Voucher/discount system implementation
14. ✅ Stock validation at checkout
15. ✅ Guest checkout without user creation
16. ✅ Abandoned cart recovery

---

## 3. Business Logic Verification: Cart Clearing & Order Creation

### ✅ **Cart IS Cleared After Successful Payment**

**Evidence:**

```php
// app/Services/CheckoutService.php:122
return DB::transaction(function () use ($cart, $paymentIntent, $webhookData, $purchaseId) {
    // Create order from cart snapshot
    $order = $this->createOrderFromCartSnapshot(...);
    
    // Create payment record
    $payment = $this->createPaymentRecord(...);
    
    // Update payment intent status
    $this->paymentService->updatePaymentIntentStatus(...);
    
    // Clear cart after successful order creation
    $cart->clear(); // ✅ THIS IS CALLED
    
    Log::info('Order created successfully from cart payment intent', [...]);
    
    return $order;
});
```

**However, potential failure points:**

1. **Cart Not Found**
   - `findCartByPurchaseId()` database query fails
   - Returns `null`, so `clear()` is never reached

2. **Webhook Signature Fails**
   - `ChipWebhookController.php:33` returns 401
   - `handlePaymentSuccess()` is never called

3. **Transaction Rollback**
   - Any exception in the transaction
   - `clear()` is called but then rolled back

4. **Cart Instance Mismatch**
   - Cart retrieved from database might not match session cart
   - User still sees items in their browser

### ✅ **Order IS Created**

**Evidence:**

```php
// app/Http/Controllers/ChipWebhookController.php:91-108
protected function handlePurchasePaid(array $purchaseData): void
{
    $purchaseId = $purchaseData['id'];
    
    // Try to create order from cart payment intent first
    $order = $this->checkoutService->handlePaymentSuccess($purchaseId, $purchaseData);
    
    if ($order) {
        Log::info('Order created successfully from cart payment intent', [
            'order_id' => $order->id,  // ✅ ORDER IS CREATED
            'purchase_id' => $purchaseId,
        ]);
        
        // Dispatch payment success event
        event(new PurchasePaid($order->payments()->first(), $purchaseData));
        
        return;
    }
    
    // Fallback: Try to find existing payment record (for backward compatibility)
    $this->handleExistingPaymentRecord($purchaseData);
}
```

**Order creation flow:**

1. Webhook received → `ChipWebhookController::handle()`
2. Signature verified → `handlePurchasePaid()`
3. → `CheckoutService::handlePaymentSuccess()`
4. → Finds cart by purchase_id
5. → Validates payment intent
6. → `createOrderFromCartSnapshot()`
7. → `OrderService::createOrder()` ✅
8. → Creates `OrderItem` records ✅
9. → Creates `Payment` record ✅
10. → Clears cart ✅
11. → Returns order ✅

### ❌ **Known Issues in Production**

Based on the code analysis, here are scenarios where cart might NOT be cleared or order NOT created:

#### Issue 1: Cart Not Found in Database

```php
// app/Services/CheckoutService.php:207-225
private function findCartByPurchaseId(string $purchaseId): ?Cart
{
    $cartData = DB::table('carts')
        ->whereRaw(
            "metadata->>'payment_intent' IS NOT NULL AND ".
            "(metadata::jsonb->'payment_intent'->>'purchase_id')::text = ?",
            [$purchaseId]
        )
        ->first();
    
    if (! $cartData) {
        Log::warning('Cart not found for purchase ID', [
            'purchase_id' => $purchaseId,
        ]);
        
        return null;  // ❌ CART NOT FOUND
    }
    // ...
}
```

**Possible causes:**
- Cart metadata not saved properly
- Cart deleted before webhook received
- Database query syntax issue (JSONB operators)
- Race condition: cart cleared by another process

#### Issue 2: Payment Intent Validation Fails

```php
// app/Services/CheckoutService.php:91-95
// Validate webhook data against payment intent
if (! $this->paymentService->validatePaymentWebhook($paymentIntent, $webhookData)) {
    return null;  // ❌ VALIDATION FAILED
}
```

**Validation checks:**
- Purchase ID mismatch
- Amount mismatch
- Status not 'created'

#### Issue 3: Database Transaction Failure

Any exception in the transaction will rollback everything, including `cart->clear()`.

### 🔧 **Recommended Fixes**

#### Fix 1: Better Error Handling for Cart Retrieval

```php
private function findCartByPurchaseId(string $purchaseId): ?Cart
{
    try {
        $cartData = DB::table('carts')
            ->whereRaw(
                "metadata->>'payment_intent' IS NOT NULL AND ".
                "(metadata::jsonb->'payment_intent'->>'purchase_id')::text = ?",
                [$purchaseId]
            )
            ->first();
        
        if (! $cartData) {
            Log::error('Cart not found for purchase ID', [
                'purchase_id' => $purchaseId,
                'total_carts' => DB::table('carts')->count(),
                'carts_with_metadata' => DB::table('carts')
                    ->whereRaw("metadata->>'payment_intent' IS NOT NULL")
                    ->count(),
            ]);
            
            return null;
        }
        
        // ... rest of code
        
    } catch (\Exception $e) {
        Log::error('Error finding cart by purchase ID', [
            'purchase_id' => $purchaseId,
            'error' => $e->getMessage(),
        ]);
        
        return null;
    }
}
```

#### Fix 2: Idempotency Check

```php
public function handlePaymentSuccess(string $purchaseId, array $webhookData): ?Order
{
    try {
        // Check if order already exists for this purchase
        $existingOrder = Order::whereHas('payments', function ($query) use ($purchaseId) {
            $query->where('gateway_payment_id', $purchaseId);
        })->first();
        
        if ($existingOrder) {
            Log::info('Order already exists for purchase, skipping creation', [
                'order_id' => $existingOrder->id,
                'purchase_id' => $purchaseId,
            ]);
            
            return $existingOrder;
        }
        
        // ... rest of code
    }
}
```

#### Fix 3: Cart Clearing Outside Transaction

```php
return DB::transaction(function () use ($cart, $paymentIntent, $webhookData, $purchaseId) {
    $order = $this->createOrderFromCartSnapshot(...);
    $payment = $this->createPaymentRecord(...);
    $this->paymentService->updatePaymentIntentStatus(...);
    
    return $order;
});

// Clear cart AFTER transaction commits successfully
try {
    $cart->clear();
    Log::info('Cart cleared successfully', ['purchase_id' => $purchaseId]);
} catch (\Exception $e) {
    Log::error('Failed to clear cart after order creation', [
        'purchase_id' => $purchaseId,
        'order_id' => $order->id,
        'error' => $e->getMessage(),
    ]);
}
```

#### Fix 4: Background Job for Pending Payments

```php
// app/Console/Commands/CheckPendingPaymentsCommand.php

namespace App\Console\Commands;

use App\Services\CheckoutService;
use MasyukAI\Chip\Facades\Chip;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckPendingPaymentsCommand extends Command
{
    protected $signature = 'payments:check-pending';
    protected $description = 'Check for payments that completed but orders not created';
    
    public function handle(CheckoutService $checkoutService)
    {
        // Find carts with payment intents older than 10 minutes
        $pendingCarts = DB::table('carts')
            ->whereRaw(
                "metadata->>'payment_intent' IS NOT NULL AND ".
                "(metadata::jsonb->'payment_intent'->>'status')::text = 'created' AND ".
                "to_timestamp((metadata::jsonb->'payment_intent'->>'created_at')::bigint) < NOW() - INTERVAL '10 minutes'"
            )
            ->get();
        
        foreach ($pendingCarts as $cartData) {
            $paymentIntent = json_decode($cartData->metadata, true)['payment_intent'];
            $purchaseId = $paymentIntent['purchase_id'];
            
            // Check status with CHIP
            $purchase = Chip::getPurchase($purchaseId);
            
            if ($purchase && $purchase->status === 'paid') {
                $this->info("Found paid purchase without order: {$purchaseId}");
                
                // Create order
                $order = $checkoutService->handlePaymentSuccess(
                    $purchaseId,
                    $purchase->toArray()
                );
                
                if ($order) {
                    $this->info("Created order: {$order->id}");
                } else {
                    $this->error("Failed to create order for: {$purchaseId}");
                }
            }
        }
    }
}
```

Schedule this command to run every 15 minutes:

```php
// app/Console/Kernel.php or routes/console.php
Schedule::command('payments:check-pending')->everyFifteenMinutes();
```

---

## Summary

### 1. Payment Gateway Data
- ❌ **Sending unnecessary default values**
- ❌ **Empty strings instead of null**
- ✅ **Recommendation:** Send only required + provided fields

### 2. Enhancements Needed
- **Critical:** Cart clearing reliability, idempotency, user feedback
- **Medium:** Payment method selection, admin notifications
- **Low:** Vouchers, guest checkout, stock validation

### 3. Business Logic Status
- ✅ **Cart clearing:** Code exists but can fail silently
- ✅ **Order creation:** Works but vulnerable to edge cases
- ❌ **Issues:** Cart not found, validation failures, no idempotency

### 4. Action Items
1. Optimize CHIP data submission (remove defaults)
2. Add idempotency to webhook handling
3. Move cart clearing outside transaction
4. Add background job for pending payments
5. Improve error logging and admin notifications
6. Create order success page for users
