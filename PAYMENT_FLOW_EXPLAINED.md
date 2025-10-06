# Complete Payment Flow Explanation

## Overview
Your application uses a **metadata-based payment intent system** where payment information is stored in the cart's metadata column (JSONB) instead of separate database tables. This is elegant and efficient.

---

## 🎯 Phase 1: Customer Clicks "Bayar Sekarang"

### What Happens in `Checkout.php`

```php
public function submitCheckout()
{
    $formData = $this->form->getState(); // Get customer info from form
    
    // Prepare customer data
    $customerData = [
        'email' => $formData['email'],  // ← Required for CHIP
        'name' => $formData['name'],
        'phone' => $formData['phone'],
        'street1' => $formData['street1'],
        'street2' => $formData['street2'],
        'city' => $formData['city'],
        'state' => $formData['state'],
        'country' => $formData['country'],
        'postcode' => $formData['postcode'],
        'company' => $formData['company'],
        'type' => 'billing',
    ];
    
    // Process checkout
    $result = $checkoutService->processCheckout($customerData);
    
    if ($result['success']) {
        // Redirect to CHIP payment page
        return $this->redirect($result['checkout_url']);
    }
}
```

**At this point, NO database tables are created yet!** Only cart metadata is updated.

---

## 💾 Phase 2: What Gets Stored in Database (During Checkout)

### Table: `carts` (Updated, Not Created)

The existing cart record in the `carts` table gets its `metadata` JSONB column updated with payment intent:

```json
{
  "metadata": {
    "payment_intent": {
      "purchase_id": "pur_abc123",
      "amount": 15000,  // RM 150.00 in cents
      "cart_version": 5,
      "cart_snapshot": [
        {
          "id": "prod-123",
          "name": "Buku Cara Bercinta",
          "price": 10000,
          "quantity": 1,
          "attributes": {...}
        }
      ],
      "customer_data": {
        "email": "customer@example.com",
        "name": "Ahmad",
        "phone": "+60123456789",
        "street1": "123 Jalan Merdeka",
        "city": "Kuala Lumpur",
        "state": "Wilayah Persekutuan",
        "postcode": "50000",
        ...
      },
      "created_at": "2025-10-06T10:30:00Z",
      "expires_at": "2025-10-06T11:00:00Z",  // 30 minutes expiry
      "status": "created",
      "checkout_url": "https://gate.chip-in.asia/purchases/pur_abc123"
    }
  }
}
```

### What This Means:

```
┌─────────────────────────────────────────────────────────┐
│ carts table                                             │
├─────────────────────────────────────────────────────────┤
│ id: uuid                                                │
│ identifier: "session_abc123"                            │
│ instance: "default"                                     │
│ items: {...cart items...}                               │
│ conditions: {...shipping, discounts...}                 │
│ metadata: {                                             │
│   "payment_intent": {                                   │
│     "purchase_id": "pur_abc123",   ← CHIP purchase ID   │
│     "amount": 15000,               ← Total in cents     │
│     "cart_snapshot": [...],        ← Full cart data     │
│     "customer_data": {...},        ← Customer info      │
│     "status": "created"            ← Payment status     │
│   }                                                     │
│ }                                                       │
│ version: 5                         ← For change tracking│
│ created_at: timestamp                                   │
│ updated_at: timestamp                                   │
└─────────────────────────────────────────────────────────┘
```

**Important Points:**
- ✅ Cart still exists (not cleared)
- ✅ Payment intent stored in cart metadata (JSONB)
- ✅ Cart snapshot captured (preserves exact state)
- ✅ Customer data stored with payment intent
- ✅ Cart version tracked for validation
- ❌ NO `orders` table record yet
- ❌ NO `payments` table record yet
- ❌ NO `addresses` table record yet
- ❌ NO `users` table record yet (if guest)

---

## 🌐 Phase 3: Customer Gets Redirected to CHIP

```
Customer Browser → CHIP Payment Gateway
URL: https://gate.chip-in.asia/purchases/pur_abc123
```

Customer now:
1. Sees CHIP's payment page
2. Selects payment method (FPX, Credit Card, etc.)
3. Completes payment
4. Gets redirected back to your site (success/failure page)

---

## ⚡ Phase 4: CHIP Sends Webhook (Payment Successful)

When payment is successful, CHIP sends a webhook POST request to your app:

```
POST https://yourapp.com/webhooks/chip
```

**Webhook Payload:**
```json
{
  "event": "purchase.paid",
  "data": {
    "id": "pur_abc123",
    "amount": 15000,
    "currency": "MYR",
    "status": "paid",
    "reference": "ORD-2025-001",
    "payment": {
      "id": "pay_xyz789",
      "method": "fpx_b2c",
      "bank": "Maybank"
    },
    "transaction_data": {
      "payment_method": "fpx_b2c",
      "bank_name": "Maybank"
    },
    "client": {
      "email": "customer@example.com"
    },
    ...
  }
}
```

### Webhook Handler: `ChipWebhookController.php`

```php
public function handle(Request $request): Response
{
    // 1. Verify signature (security)
    if (!$this->webhookService->verifySignature($request)) {
        return response('Unauthorized', 401);
    }
    
    $eventType = $request->input('event'); // "purchase.paid"
    $purchaseData = $request->input('data');
    
    // 2. Handle purchase paid event
    if ($eventType === 'purchase.paid') {
        $this->handlePurchasePaid($purchaseData);
    }
    
    return response('OK', 200);
}

protected function handlePurchasePaid(array $purchaseData): void
{
    $purchaseId = $purchaseData['id']; // "pur_abc123"
    
    // 3. Create order from cart payment intent
    $order = $this->checkoutService->handlePaymentSuccess(
        $purchaseId, 
        $purchaseData
    );
}
```

---

## 🏗️ Phase 5: Order Creation (The Magic Happens Here!)

### `CheckoutService::handlePaymentSuccess()`

This is where ALL database records are created:

```php
public function handlePaymentSuccess(string $purchaseId, array $webhookData): ?Order
{
    // 1. ✅ IDEMPOTENCY CHECK - Prevent duplicate orders
    $existingOrder = Order::whereHas('payments', function($query) use ($purchaseId) {
        $query->where('gateway_payment_id', $purchaseId);
    })->first();
    
    if ($existingOrder) {
        return $existingOrder; // Already created, don't duplicate!
    }
    
    // 2. Find cart by purchase_id using JSONB query
    $cart = $this->findCartByPurchaseId($purchaseId);
    
    // 3. Get payment intent from cart metadata
    $paymentIntent = $cart->getMetadata('payment_intent');
    /*
    $paymentIntent = [
        'purchase_id' => 'pur_abc123',
        'amount' => 15000,
        'cart_snapshot' => [...],
        'customer_data' => [...]
    ];
    */
    
    // 4. Validate webhook data against payment intent
    if (!$this->paymentService->validatePaymentWebhook($paymentIntent, $webhookData)) {
        return null; // Validation failed - amount mismatch, etc.
    }
    
    // 5. ✨ CREATE EVERYTHING IN A TRANSACTION
    $order = DB::transaction(function() use ($cart, $paymentIntent, $webhookData) {
        
        // A. Create order from cart snapshot
        $order = $this->createOrderFromCartSnapshot(
            $paymentIntent['cart_snapshot'],
            $paymentIntent['customer_data']
        );
        
        // B. Create payment record
        $payment = $this->createPaymentRecord($order, $paymentIntent, $webhookData);
        
        return $order;
    });
    
    // 6. Clear cart after successful transaction
    $cart->clear();
    
    return $order;
}
```

---

## 📊 Phase 6: Database Records Created (Finally!)

### A. `users` Table (if guest user)

```sql
INSERT INTO users (
    id, email, name, phone, is_guest, created_at
) VALUES (
    'uuid', 
    'customer@example.com', 
    'Ahmad', 
    '+60123456789', 
    true, 
    NOW()
);
```

**Source:** `$paymentIntent['customer_data']['email']` from cart metadata

---

### B. `addresses` Table (polymorphic)

```sql
INSERT INTO addresses (
    id, 
    addressable_type, 
    addressable_id,
    name, 
    street1, 
    street2, 
    city, 
    state, 
    country, 
    postcode, 
    phone,
    type,
    is_primary,
    created_at
) VALUES (
    'uuid',
    'App\\Models\\User',
    'user-uuid',
    'Ahmad',
    '123 Jalan Merdeka',
    'Taman Melati',
    'Kuala Lumpur',
    'Wilayah Persekutuan',
    'Malaysia',
    '50000',
    '+60123456789',
    'billing',
    true,
    NOW()
);
```

**Source:** `$paymentIntent['customer_data']` from cart metadata

**Why Polymorphic?**
- Uses `addressable_type` and `addressable_id`
- One address table can be used for Users, Orders, Shipments, etc.

---

### C. `orders` Table

```sql
INSERT INTO orders (
    id,
    order_number,
    user_id,
    address_id,
    cart_items,
    delivery_method,
    checkout_form_data,
    status,
    total,
    created_at
) VALUES (
    'uuid',
    'ORD-2025-001',         -- Generated unique order number
    'user-uuid',             -- From newly created user
    'address-uuid',          -- From newly created address
    '[{...cart items...}]',  -- From $paymentIntent['cart_snapshot']
    'standard',
    '{...form data...}',     -- From $paymentIntent['customer_data']
    'processing',            -- Order status after payment
    15000,                   -- From $paymentIntent['amount']
    NOW()
);
```

**Source:** 
- `cart_items`: `$paymentIntent['cart_snapshot']` (exact cart state when payment was created)
- `total`: `$paymentIntent['amount']` (in cents)
- `checkout_form_data`: `$paymentIntent['customer_data']`

---

### D. `order_items` Table

```sql
INSERT INTO order_items (
    id, 
    order_id, 
    product_id, 
    quantity, 
    unit_price,
    created_at
) VALUES (
    'uuid',
    'order-uuid',
    'product-uuid',
    1,
    10000,  -- Price in cents
    NOW()
);
```

**Source:** Extracted from `$paymentIntent['cart_snapshot']` array
- Each item in cart snapshot becomes an `order_items` record

---

### E. `payments` Table

```sql
INSERT INTO payments (
    id,
    order_id,
    gateway_payment_id,
    gateway_transaction_id,
    gateway_response,
    amount,
    status,
    method,
    currency,
    paid_at,
    reference,
    created_at
) VALUES (
    'uuid',
    'order-uuid',
    'pur_abc123',           -- CHIP purchase ID
    'pay_xyz789',           -- CHIP payment ID
    '{...webhook data...}', -- Full webhook payload
    15000,
    'completed',
    'fpx_b2c',
    'MYR',
    NOW(),
    'PAY-2025-001',         -- Generated payment reference
    NOW()
);
```

**Source:**
- `gateway_payment_id`: `$purchaseId` (from webhook URL)
- `gateway_transaction_id`: `$webhookData['payment']['id']`
- `gateway_response`: `$webhookData` (full webhook payload)
- `amount`: `$paymentIntent['amount']`
- `method`: `$webhookData['transaction_data']['payment_method']`

---

### F. `carts` Table (metadata updated)

```json
{
  "metadata": {
    "payment_intent": {
      ...existing fields...,
      "status": "completed",  ← Changed from "created"
      "order_id": "order-uuid",
      "payment_id": "payment-uuid",
      "completed_at": "2025-10-06T10:35:00Z",
      "updated_at": "2025-10-06T10:35:00Z"
    }
  }
}
```

Then the cart is cleared with `$cart->clear()`.

---

## 🔍 Data Flow Summary

### When Customer Clicks "Bayar Sekarang":

```
┌─────────────┐
│   Checkout  │
│ Component   │
└──────┬──────┘
       │
       │ submitCheckout()
       ▼
┌─────────────────┐
│ CheckoutService │
│ processCheckout │
└──────┬──────────┘
       │
       │ Creates payment intent
       ▼
┌─────────────────┐
│ PaymentService  │
│createPaymentIntent│
└──────┬──────────┘
       │
       │ 1. Call CHIP API
       │ 2. Store intent in cart metadata
       ▼
┌─────────────────┐
│  carts table    │
│ metadata updated│  ← ONLY THIS gets updated
└─────────────────┘
```

**At this point:**
- ❌ No `orders`
- ❌ No `payments`  
- ❌ No `users` (if guest)
- ❌ No `addresses`
- ✅ Only cart metadata updated

---

### When Webhook Arrives:

```
┌──────────────┐
│ CHIP Webhook │
│ "purchase.paid"│
└──────┬───────┘
       │
       │ Signed POST request
       ▼
┌──────────────────┐
│ChipWebhookController│
│ handle()         │
└──────┬───────────┘
       │
       │ Verify signature
       ▼
┌──────────────────┐
│ChipWebhookController│
│handlePurchasePaid│
└──────┬───────────┘
       │
       │ purchase_id
       ▼
┌─────────────────┐
│ CheckoutService │
│handlePaymentSuccess│
└──────┬──────────┘
       │
       │ 1. Check for duplicate (idempotency)
       │ 2. Find cart by purchase_id
       │ 3. Get payment intent from cart metadata
       │ 4. Validate webhook data
       ▼
┌────────────────────────────────────┐
│     DB::transaction(function() {   │
│                                    │
│  1. Create/Find User               │
│     ├─> users table                │
│                                    │
│  2. Create Address                 │
│     ├─> addresses table            │
│                                    │
│  3. Create Order                   │
│     ├─> orders table               │
│     └─> order_items table          │
│                                    │
│  4. Create Payment                 │
│     ├─> payments table             │
│                                    │
│ })                                 │
└────────────────────────────────────┘
       │
       │ Transaction committed
       ▼
┌─────────────────┐
│  Clear Cart     │
│ $cart->clear()  │
└─────────────────┘
```

---

## 🔐 Critical Security Features

### 1. Idempotency Protection

```php
// Prevent duplicate orders if webhook is sent multiple times
$existingOrder = Order::whereHas('payments', function($query) use ($purchaseId) {
    $query->where('gateway_payment_id', $purchaseId);
})->first();

if ($existingOrder) {
    return $existingOrder; // Don't create duplicate!
}
```

### 2. Webhook Validation

```php
// Validate webhook data matches payment intent
if ($paymentIntent['purchase_id'] !== $webhookData['purchase_id']) {
    return false; // Purchase ID mismatch
}

if ($paymentIntent['amount'] !== $webhookData['amount']) {
    return false; // Amount mismatch (tampering detected!)
}
```

### 3. Cart Snapshot Preservation

The cart state is frozen at payment creation time:

```
Customer's Cart at Checkout Time:
- Buku A: RM 50.00 × 1 = RM 50.00
- Buku B: RM 100.00 × 1 = RM 100.00
- Total: RM 150.00

↓ Snapshot stored in payment intent

Even if product prices change later:
- Buku A now RM 60.00 (updated in products table)
- Buku B now RM 120.00 (updated in products table)

↓ Order still uses original prices

Order created with:
- Buku A: RM 50.00 (from snapshot)
- Buku B: RM 100.00 (from snapshot)
- Total: RM 150.00 (from snapshot)
```

---

## 🎯 Key Takeaways

### During Checkout ("Bayar Sekarang" clicked):
1. ✅ Form data collected
2. ✅ CHIP purchase created via API
3. ✅ Payment intent stored in `carts.metadata` (JSONB)
4. ✅ Cart snapshot captured
5. ✅ Customer redirected to CHIP
6. ❌ **NO database tables created** (except cart metadata update)

### During Webhook (Payment successful):
1. ✅ Webhook signature verified
2. ✅ Cart found by `purchase_id`
3. ✅ Payment intent retrieved from cart metadata
4. ✅ Webhook data validated against intent
5. ✅ Database transaction opens
6. ✅ **ALL records created:**
   - `users` (if guest)
   - `addresses`
   - `orders`
   - `order_items`
   - `payments`
7. ✅ Cart metadata updated (status = completed)
8. ✅ Transaction committed
9. ✅ Cart cleared

### Data Sources:
- **Everything comes from cart metadata** (`payment_intent` object)
- Cart snapshot = exact state when "Bayar Sekarang" was clicked
- Customer data = form data from checkout
- Webhook data = payment confirmation from CHIP

### Why This Design is Excellent:
- ✅ **No premature database writes** - only after payment confirmed
- ✅ **Idempotency** - prevents duplicate orders
- ✅ **Audit trail** - full webhook payload stored
- ✅ **Price integrity** - cart snapshot preserves exact prices
- ✅ **Atomic operations** - all-or-nothing with DB transactions
- ✅ **Efficient** - uses JSONB for flexible metadata storage

---

## 🔎 How to Trace a Payment

### Find Cart by Purchase ID:
```sql
SELECT * 
FROM carts 
WHERE metadata::jsonb->'payment_intent'->>'purchase_id' = 'pur_abc123';
```

### Find Order by Purchase ID:
```sql
SELECT o.* 
FROM orders o
JOIN payments p ON p.order_id = o.id
WHERE p.gateway_payment_id = 'pur_abc123';
```

### Get Full Payment Intent:
```sql
SELECT metadata::jsonb->'payment_intent' as payment_intent
FROM carts
WHERE identifier = 'session_abc123';
```

---

**This is a robust, well-designed payment system! 🎉**
