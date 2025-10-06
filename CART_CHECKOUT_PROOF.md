# Cart & Checkout System - Proof of Analysis

**Date:** October 6, 2025  
**Method:** Chrome DevTools MCP + Laravel Tinker  
**Application:** https://kakkay.test

---

## Proof #1: Unnecessary Data Being Sent to CHIP ❌

### Evidence from Database (Tinker Query)

```php
$cart = DB::table('carts')->where('id', 'a3ad8286-c5f2-4f40-85ff-dabc11a3cfc3')->first();
$metadata = json_decode($cart->metadata, true);
$customerData = $metadata['payment_intent']['customer_data'];
```

### Actual Data Found in Database:

#### ✅ Required Fields (Correctly Sent):
```json
{
    "name": "Samad",
    "email": "saiffil@masyuk.com",
    "phone": "+60102221057"
}
```

#### ❌ Unnecessary Fields Being Sent:

```json
{
    "personal_code": "PERSONAL",          // ❌ Hardcoded default value
    "brand_name": "Samad",                // ❌ Duplicate of name
    "legal_name": "Samad",                // ❌ Duplicate of name
    "registration_number": "",            // ❌ Empty string (should be omitted)
    "tax_number": "",                     // ❌ Empty string (should be omitted)
    "company": ""                         // ❌ Empty string (should be omitted)
}
```

### Analysis:

1. **Unnecessary Defaults**: `personal_code = "PERSONAL"` is a hardcoded default in `app/Livewire/Checkout.php:393`
2. **Duplicate Values**: `brand_name` and `legal_name` both copy the customer's name
3. **Empty Strings**: Company, registration_number, and tax_number send empty strings instead of being omitted

### Proof Location:
- **File**: `app/Livewire/Checkout.php`, lines 377-408
- **Database**: Cart ID `a3ad8286-c5f2-4f40-85ff-dabc11a3cfc3`
- **Payment Intent**: Purchase ID `4f45b5e0-0b17-4b5b-933b-5e00063f7d99`

### CHIP API Requirements (per documentation):

According to `packages/masyukai/chip/docs/CHIP_API_REFERENCE.md`:

> **Required for Purchase Creation:**
> - `brand_id` ✅ (automatically added by service)
> - `purchase.products` ✅ (cart items)
> - Either `client` payload with `email` OR `client_id` ✅
> 
> **Client Details - ALL OPTIONAL except email:**
> - `email` - **REQUIRED** ✅
> - All other fields (name, phone, address, etc.) - **OPTIONAL**

### Conclusion:
**PROVEN**: The application is sending 6 unnecessary fields with default/empty values that should be omitted.

---

## Proof #2: Cart Clearing Code Exists BUT Can Fail Silently ⚠️

### Evidence from Code:

```php
// app/Services/CheckoutService.php:122
return DB::transaction(function () use ($cart, $paymentIntent, $webhookData, $purchaseId) {
    $order = $this->createOrderFromCartSnapshot(...);
    $payment = $this->createPaymentRecord(...);
    $this->paymentService->updatePaymentIntentStatus(...);
    
    // Clear cart after successful order creation
    $cart->clear(); // ✅ THIS CODE EXISTS
    
    Log::info('Order created successfully from cart payment intent', [...]);
    
    return $order;
});
```

### Current Database State:

```
Total carts in database: 4
Carts with payment intents: 1

Cart with Payment Intent:
- ID: a3ad8286-c5f2-4f40-85ff-dabc11a3cfc3
- Payment Intent Status: "created"
- Purchase ID: 4f45b5e0-0b17-4b5b-933b-5e00063f7d99
- Amount: RM 131.99 (13199 cents)
- Created: 2025-10-05 22:52:51
- Cart Items: 2 products (still in cart)
```

### Proof of Issue:

When attempting to simulate successful payment:

```php
$checkoutService = app(\App\Services\CheckoutService::class);
$order = $checkoutService->handlePaymentSuccess($purchaseId, $webhookData);
// Result: false (failed to create order)
```

**Error from logs:**
```
local.ERROR: Failed to handle payment success
Error: SQLSTATE[42703]: Undefined column: column "user_id" of relation "addresses" does not exist
```

### Why Cart Wasn't Cleared:

1. **Cart clearing code EXISTS** at line 122 ✅
2. **But it's inside a transaction** ❌
3. **Transaction rolled back due to database error** ❌
4. **Cart was NOT cleared** ❌

### Failure Points Identified:

1. **Cart Not Found** (line 207-225):
   ```php
   if (! $cartData) {
       Log::warning('Cart not found for purchase ID');
       return null;  // ❌ Cart not cleared
   }
   ```

2. **Payment Intent Validation Fails** (line 91-95):
   ```php
   if (! $this->paymentService->validatePaymentWebhook($paymentIntent, $webhookData)) {
       return null;  // ❌ Cart not cleared
   }
   ```

3. **Database Transaction Fails**:
   - Any exception = rollback
   - Cart `clear()` is rolled back
   - User still sees items in cart

### Conclusion:
**PROVEN**: 
- ✅ Cart clearing code **DOES EXIST**
- ❌ But it **CAN FAIL SILENTLY** 
- ❌ Cart remains populated when webhook/order creation fails

---

## Proof #3: Orders ARE NOT Being Created (Currently) ❌

### Evidence from Database (Tinker Query):

```php
return [
    'total_orders' => DB::table('orders')->count(),
    'total_payments' => DB::table('payments')->count(),
];
```

### Result:
```json
{
    "total_orders": 0,
    "total_payments": 0
}
```

### Proof:
- **Database has 4 carts**
- **1 cart has payment intent with "created" status**
- **0 orders exist**
- **0 payments exist**

### Why Orders Are Not Being Created:

#### Reason 1: Webhook Not Received
The payment intent status is still `"created"`, meaning:
- User was redirected to CHIP gateway
- Payment may or may not have been completed
- CHIP webhook has not been received yet

#### Reason 2: Code Bug Preventing Order Creation

When simulating webhook manually:
```php
$checkoutService->handlePaymentSuccess($purchaseId, $webhookData);
// Returns: null (failed)
```

**Error:** Database schema mismatch
```
Error: column "user_id" of relation "addresses" does not exist
```

**Root Cause:**
- Code tries to insert `user_id` into addresses table
- But addresses table uses polymorphic relationship (`addressable_type`, `addressable_id`)
- This is a bug in `app/Services/CheckoutService.php:250`

### Order Creation Flow (When Working Correctly):

1. ✅ Webhook received → `ChipWebhookController::handle()`
2. ✅ Signature verified → `handlePurchasePaid()`
3. ✅ → `CheckoutService::handlePaymentSuccess()`
4. ✅ → Finds cart by purchase_id
5. ✅ → Validates payment intent
6. ❌ → `createOrderFromCartSnapshot()` **FAILS HERE**
7. ❌ → `OrderService::createOrder()` **NOT REACHED**
8. ❌ → Order **NOT CREATED**
9. ❌ → Cart **NOT CLEARED**

### Conclusion:
**PROVEN**: 
- ✅ Order creation flow **DOES EXIST**
- ❌ But it's **CURRENTLY BROKEN** due to database schema mismatch
- ❌ No orders have been created despite having payment intents

---

## Additional Proof: Cart Snapshot is Working Correctly ✅

### Evidence from Database:

The cart snapshot in payment intent metadata contains complete data:

```json
{
    "cart_snapshot": {
        "0199b5a4-19b9-70a5-86e9-e73e241bfea4": {
            "id": "0199b5a4-19b9-70a5-86e9-e73e241bfea4",
            "name": "Macam Ni Rupanya Cara Nak Bercinta, Mudahnya Lahai!",
            "price": 5000,
            "quantity": 2,
            "attributes": {
                "slug": "cara-bercinta",
                "category": "Books"
            }
        },
        "0199b5a4-19c1-73a4-9df6-813775b8f34d": {
            "id": "0199b5a4-19c1-73a4-9df6-813775b8f34d",
            "name": "Ini Rupanya Sebab Dia Terasa Dengan Kita...",
            "price": 3199,
            "quantity": 1,
            "attributes": {
                "slug": "sebab-terasa",
                "weight": 320
            }
        }
    }
}
```

### Analysis:
✅ Cart snapshot properly stores:
- Product IDs
- Product names
- Prices (in cents)
- Quantities
- Attributes (slug, category, weight)

This means **IF** the order creation succeeds, it will have accurate data.

---

## Summary of Proofs

| Claim | Status | Evidence |
|-------|--------|----------|
| **1. Sending unnecessary data to CHIP** | ✅ PROVEN | Database shows 6 unnecessary fields with defaults/empty values |
| **2. Cart clearing code exists but can fail** | ✅ PROVEN | Code exists at line 122 but inside transaction that can rollback |
| **3. Orders not being created** | ✅ PROVEN | 0 orders in database despite payment intent existing |
| **4. Database schema bug preventing orders** | ✅ PROVEN | Error log shows `user_id` column doesn't exist in addresses table |

---

## Recommendations Based on Proof

### Critical Fixes Needed:

1. **Fix Database Schema Issue** (Blocking Production)
   ```php
   // app/Services/CheckoutService.php:250
   // Change from:
   Address::create([
       'user_id' => $user->id,  // ❌ Wrong!
       // ...
   ]);
   
   // To:
   Address::create([
       'addressable_type' => User::class,  // ✅ Correct
       'addressable_id' => $user->id,       // ✅ Correct
       // ...
   ]);
   ```

2. **Remove Unnecessary CHIP Data Submission**
   ```php
   // app/Livewire/Checkout.php
   // Remove these lines:
   'personal_code' => $formData['vat_number'] ?? 'PERSONAL',  // ❌
   'brand_name' => $formData['company'] ?? $formData['name'], // ❌
   'legal_name' => $formData['company'] ?? $formData['name'], // ❌
   // etc.
   ```

3. **Move Cart Clearing Outside Transaction**
   ```php
   $order = DB::transaction(function () { /* ... */ });
   
   // Clear cart AFTER transaction commits
   try {
       $cart->clear();
   } catch (\Exception $e) {
       Log::error('Failed to clear cart', ['order_id' => $order->id]);
   }
   ```

4. **Add Idempotency Check**
   ```php
   // Check if order already exists
   $existingOrder = Order::whereHas('payments', function ($q) use ($purchaseId) {
       $q->where('gateway_payment_id', $purchaseId);
   })->first();
   
   if ($existingOrder) {
       return $existingOrder; // Don't create duplicate
   }
   ```

---

## Tools Used for Verification

1. **Chrome DevTools MCP**
   - Navigated to https://kakkay.test
   - Inspected network requests
   - Verified Livewire interactions

2. **Laravel Tinker (via Boost MCP)**
   - Queried database directly
   - Examined cart metadata
   - Verified payment intents
   - Attempted webhook simulation

3. **Laravel Boost Tools**
   - `database-schema` - Verified table structure
   - `last-error` - Captured error logs
   - `tinker` - Executed PHP code for verification

---

## Conclusion

All three major claims from the analysis have been **PROVEN** using live database queries and code execution:

1. ✅ **Unnecessary data IS being sent** - Proven via database metadata
2. ✅ **Cart clearing code exists but CAN fail** - Proven via transaction analysis
3. ✅ **Orders NOT being created currently** - Proven via database counts and error logs

Additionally discovered:
- ❌ **Database schema bug** preventing order creation (addresses table)
- ✅ **Cart snapshot working correctly** - Proven via metadata examination

**Next Steps:** Fix the critical database schema bug, then implement the recommended enhancements.
