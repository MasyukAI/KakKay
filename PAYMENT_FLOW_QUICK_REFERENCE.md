# Payment Flow: Quick Reference

## Two Main Phases

### 🛒 Phase 1: Checkout (Bayar Sekarang Clicked)

**What happens:**
```
Customer clicks "Bayar Sekarang"
    ↓
Form validation passes
    ↓
CheckoutService::processCheckout()
    ↓
PaymentService::createPaymentIntent()
    ↓
CHIP API called → Purchase created (pur_abc123)
    ↓
Payment intent stored in carts.metadata (JSONB):
{
  "payment_intent": {
    "purchase_id": "pur_abc123",
    "amount": 15000,
    "cart_snapshot": [{...}],  ← Full cart items
    "customer_data": {...},     ← Form data
    "status": "created"
  }
}
    ↓
Customer redirected to CHIP payment page
```

**Database changes:**
- ✅ `carts` table: metadata column updated
- ❌ NO orders created
- ❌ NO payments created
- ❌ NO users created
- ❌ NO addresses created

---

### 💳 Phase 2: Webhook (Payment Successful)

**What happens:**
```
Customer completes payment on CHIP
    ↓
CHIP sends webhook: POST /webhooks/chip
Payload: {
  "event": "purchase.paid",
  "data": {
    "id": "pur_abc123",
    "amount": 15000,
    ...
  }
}
    ↓
ChipWebhookController::handle()
    ↓
Verify signature ✅
    ↓
handlePurchasePaid()
    ↓
CheckoutService::handlePaymentSuccess()
    ↓
Find cart by purchase_id (pur_abc123)
    ↓
Get payment_intent from cart.metadata
    ↓
Validate webhook data vs intent
    ↓
DB::transaction {
  1. Create User (if guest)
  2. Create Address
  3. Create Order + Order Items
  4. Create Payment
}
    ↓
Clear cart (deletes entire cart)
    ↓
Return order
```

**Database changes:**
- ✅ `users` table: new record (if guest)
- ✅ `addresses` table: new record
- ✅ `orders` table: new record
- ✅ `order_items` table: new records (one per cart item)
- ✅ `payments` table: new record
- ✅ `carts` table: metadata updated, then cart cleared

---

## Data Sources

### Where does order data come from?

| Database Table | Data Source | Example |
|----------------|-------------|---------|
| **users** | `payment_intent.customer_data.email` | customer@example.com |
| | `payment_intent.customer_data.name` | Ahmad |
| | `payment_intent.customer_data.phone` | +60123456789 |
| **addresses** | `payment_intent.customer_data.street1` | 123 Jalan Merdeka |
| | `payment_intent.customer_data.city` | Kuala Lumpur |
| | `payment_intent.customer_data.state` | Wilayah Persekutuan |
| | `payment_intent.customer_data.postcode` | 50000 |
| **orders** | `payment_intent.cart_snapshot` | Full cart items array |
| | `payment_intent.amount` | 15000 (cents) |
| | `payment_intent.customer_data` | Full form data |
| **order_items** | `payment_intent.cart_snapshot[*]` | Each item in array |
| | `cart_snapshot[0].product_id` | uuid |
| | `cart_snapshot[0].quantity` | 1 |
| | `cart_snapshot[0].price` | 10000 |
| **payments** | `$purchaseId` from webhook | pur_abc123 |
| | `$webhookData['amount']` | 15000 |
| | `$webhookData['payment']['id']` | pay_xyz789 |
| | `$webhookData['transaction_data']['payment_method']` | fpx_b2c |

---

## Important Concepts

### 1. Cart Metadata (JSONB)
```json
{
  "payment_intent": {
    "purchase_id": "pur_abc123",        // Links to CHIP
    "amount": 15000,                    // Total in cents
    "cart_version": 5,                  // For validation
    "cart_snapshot": [...],             // Frozen cart state
    "customer_data": {...},             // Form data
    "status": "created" | "completed",  // Payment status
    "checkout_url": "https://...",      // CHIP redirect URL
    "created_at": "2025-10-06T10:30:00Z",
    "expires_at": "2025-10-06T11:00:00Z" // 30 min expiry
  }
}
```

**Why cart snapshot?**
- Preserves exact prices at checkout time
- Even if product prices change, order uses original prices
- Includes all cart conditions (discounts, shipping, etc.)

### 2. Idempotency
Prevents duplicate orders if webhook is sent multiple times:
```php
$existingOrder = Order::whereHas('payments', function($query) use ($purchaseId) {
    $query->where('gateway_payment_id', $purchaseId);
})->first();

if ($existingOrder) {
    return $existingOrder; // Already created!
}
```

### 3. Validation
Ensures webhook data matches payment intent:
```php
// Purchase ID must match
if ($paymentIntent['purchase_id'] !== $webhookData['purchase_id']) {
    return false;
}

// Amount must match (prevents tampering)
if ($paymentIntent['amount'] !== $webhookData['amount']) {
    return false;
}

// Payment intent must be in 'created' status
if ($paymentIntent['status'] !== 'created') {
    return false;
}
```

### 4. Database Transaction
All-or-nothing approach:
```php
DB::transaction(function() {
    $user = createOrFindUser();
    $address = createAddress($user);
    $order = createOrder($user, $address);
    $payment = createPayment($order);
    
    return $order;
}); // If any fails, all rolled back

// Cart cleared AFTER transaction succeeds
$cart->clear(); // Deletes cart entirely
```

---

## Quick Debugging

### Check if payment intent exists:
```sql
SELECT 
    id,
    identifier,
    metadata::jsonb->'payment_intent'->>'purchase_id' as purchase_id,
    metadata::jsonb->'payment_intent'->>'status' as status,
    metadata::jsonb->'payment_intent'->>'amount' as amount
FROM carts
WHERE metadata::jsonb->'payment_intent' IS NOT NULL;
```

### Find cart by purchase ID:
```sql
SELECT * FROM carts
WHERE metadata::jsonb->'payment_intent'->>'purchase_id' = 'pur_abc123';
```

### Check if order was created:
```sql
SELECT o.*, p.gateway_payment_id
FROM orders o
JOIN payments p ON p.order_id = o.id
WHERE p.gateway_payment_id = 'pur_abc123';
```

### View full payment intent:
```sql
SELECT 
    identifier,
    jsonb_pretty(metadata::jsonb->'payment_intent') as payment_intent
FROM carts
WHERE identifier = 'session_abc123';
```

---

## Timeline Example

```
10:30:00 - Customer clicks "Bayar Sekarang"
10:30:01 - Payment intent created and stored in cart metadata
10:30:02 - Customer redirected to CHIP
10:32:15 - Customer completes payment on CHIP
10:32:16 - CHIP sends webhook to your app
10:32:17 - Webhook verified and processed
10:32:18 - Order created (users, addresses, orders, order_items, payments)
10:32:19 - Cart cleared
10:32:20 - Customer sees success page
```

**Key insight:** Order creation happens **only** when webhook arrives, not when "Bayar Sekarang" is clicked!

---

## Summary

### Checkout Phase:
- Input: Customer form data
- Action: Create CHIP purchase, store intent in cart
- Output: Redirect URL to CHIP

### Webhook Phase:
- Input: CHIP webhook with purchase_id
- Action: Create all database records from cart metadata
- Output: Complete order with user, address, items, payment

### Everything comes from cart metadata!
The `payment_intent` object in cart metadata contains:
- ✅ Cart snapshot (items, prices, conditions)
- ✅ Customer data (name, email, address, etc.)
- ✅ Payment details (purchase_id, amount, checkout URL)
- ✅ Timestamps (created, expires)
- ✅ Status tracking (created → completed)

This is an elegant, secure, and efficient design! 🎯
