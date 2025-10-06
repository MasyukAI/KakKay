# Payment Flow: Visual Diagram

## Complete Flow Visualization

```
┌──────────────────────────────────────────────────────────────────────────┐
│                         CHECKOUT PHASE                                   │
│                     (Bayar Sekarang Clicked)                             │
└──────────────────────────────────────────────────────────────────────────┘

┌─────────────┐
│  Customer   │  Fills checkout form
│   Browser   │  Clicks "Bayar Sekarang"
└──────┬──────┘
       │
       │ POST /checkout
       ▼
┌────────────────────┐
│ Checkout Component │  submitCheckout()
│  (Livewire)        │  Validates form data
└────────┬───────────┘
         │
         │ processCheckout($customerData)
         ▼
┌────────────────────┐
│ CheckoutService    │  Main orchestrator
└────────┬───────────┘
         │
         │ createPaymentIntent($cart, $customerData)
         ▼
┌────────────────────┐
│ PaymentService     │  Handles CHIP API
└────────┬───────────┘
         │
         │ 1. POST to CHIP API
         │    /purchases/
         │    {
         │      "client": {"email": "..."},
         │      "purchase": {"products": [...]}
         │    }
         ▼
┌────────────────────┐
│   CHIP Gateway     │  Creates purchase
│                    │  Returns purchase_id & checkout_url
└────────┬───────────┘
         │
         │ Response: {
         │   "id": "pur_abc123",
         │   "checkout_url": "https://gate.chip-in.asia/..."
         │ }
         ▼
┌────────────────────┐
│ PaymentService     │  Stores in cart metadata
│                    │
│ cart.setMetadata('payment_intent', {
│   purchase_id: "pur_abc123",
│   amount: 15000,
│   cart_snapshot: [...],      ← FULL CART STATE
│   customer_data: {...},      ← FORM DATA
│   status: "created",
│   checkout_url: "https://...",
│   created_at: "2025-10-06T10:30:00Z",
│   expires_at: "2025-10-06T11:00:00Z"
│ })
└────────┬───────────┘
         │
         │ Database Write
         ▼
┌─────────────────────────────────────────────────────────────┐
│                    CARTS TABLE                              │
├─────────────────────────────────────────────────────────────┤
│ id: uuid                                                    │
│ identifier: "session_abc123"                                │
│ instance: "default"                                         │
│ items: {...}                                                │
│ conditions: {...}                                           │
│ metadata: {                                                 │
│   "payment_intent": {          ← ONLY THIS UPDATED         │
│     "purchase_id": "pur_abc123",                            │
│     "amount": 15000,                                        │
│     "cart_snapshot": [{...}],                               │
│     "customer_data": {...},                                 │
│     "status": "created"                                     │
│   }                                                         │
│ }                                                           │
│ version: 5                                                  │
└─────────────────────────────────────────────────────────────┘
         │
         │ Return checkout_url
         ▼
┌────────────────────┐
│ Checkout Component │  redirect($checkout_url)
└────────┬───────────┘
         │
         │ HTTP 302 Redirect
         ▼
┌────────────────────┐
│  Customer Browser  │  Redirected to CHIP
│                    │  https://gate.chip-in.asia/purchases/pur_abc123
└────────┬───────────┘
         │
         │ Customer:
         │ - Sees CHIP payment page
         │ - Selects payment method (FPX, Card, etc.)
         │ - Enters banking credentials
         │ - Completes payment
         ▼
┌────────────────────┐
│   CHIP Gateway     │  Payment successful!
└────────────────────┘




┌──────────────────────────────────────────────────────────────────────────┐
│                         WEBHOOK PHASE                                    │
│                    (Payment Successful)                                  │
└──────────────────────────────────────────────────────────────────────────┘

┌────────────────────┐
│   CHIP Gateway     │  Sends webhook
│                    │  POST /webhooks/chip
│                    │
│                    │  Payload:
│                    │  {
│                    │    "event": "purchase.paid",
│                    │    "data": {
│                    │      "id": "pur_abc123",
│                    │      "amount": 15000,
│                    │      "status": "paid",
│                    │      "payment": {...}
│                    │    }
│                    │  }
└────────┬───────────┘
         │
         │ HTTP POST (signed with RSA)
         ▼
┌─────────────────────────┐
│ ChipWebhookController   │  handle(Request $request)
│                         │
│  1. Verify signature ✓  │
│  2. Extract event type  │
│  3. Route to handler    │
└────────┬────────────────┘
         │
         │ event = "purchase.paid"
         ▼
┌─────────────────────────┐
│ ChipWebhookController   │  handlePurchasePaid($purchaseData)
│                         │
│  $purchaseId = "pur_abc123"
└────────┬────────────────┘
         │
         │ handlePaymentSuccess($purchaseId, $webhookData)
         ▼
┌─────────────────────────┐
│  CheckoutService        │  Main order creation logic
└────────┬────────────────┘
         │
         │ Step 1: Check idempotency
         ▼
┌─────────────────────────────────────────────────────────────┐
│  Idempotency Check                                          │
│                                                             │
│  Order::whereHas('payments', function($q) use ($purchaseId) {│
│      $q->where('gateway_payment_id', $purchaseId);         │
│  })->first();                                               │
│                                                             │
│  If found → Return existing order (prevent duplicate)       │
│  If not found → Continue to create order                    │
└────────┬────────────────────────────────────────────────────┘
         │
         │ Step 2: Find cart by purchase_id
         ▼
┌─────────────────────────────────────────────────────────────┐
│  Find Cart (Database Query)                                 │
│                                                             │
│  SELECT * FROM carts                                        │
│  WHERE metadata::jsonb->'payment_intent'->>'purchase_id'    │
│        = 'pur_abc123';                                      │
│                                                             │
│  Returns: Cart instance with payment intent in metadata     │
└────────┬────────────────────────────────────────────────────┘
         │
         │ Step 3: Extract payment intent
         ▼
┌─────────────────────────────────────────────────────────────┐
│  Payment Intent (from cart metadata)                        │
│                                                             │
│  {                                                          │
│    "purchase_id": "pur_abc123",                             │
│    "amount": 15000,                                         │
│    "cart_snapshot": [                                       │
│      {                                                      │
│        "id": "prod-123",                                    │
│        "name": "Buku Cara Bercinta",                        │
│        "price": 10000,                                      │
│        "quantity": 1,                                       │
│        "attributes": {...}                                  │
│      }                                                      │
│    ],                                                       │
│    "customer_data": {                                       │
│      "email": "customer@example.com",                       │
│      "name": "Ahmad",                                       │
│      "phone": "+60123456789",                               │
│      "street1": "123 Jalan Merdeka",                        │
│      "city": "Kuala Lumpur",                                │
│      "state": "Wilayah Persekutuan",                        │
│      "postcode": "50000",                                   │
│      ...                                                    │
│    },                                                       │
│    "status": "created"                                      │
│  }                                                          │
└────────┬────────────────────────────────────────────────────┘
         │
         │ Step 4: Validate webhook data
         ▼
┌─────────────────────────────────────────────────────────────┐
│  Validation Checks                                          │
│                                                             │
│  ✓ Purchase ID matches?                                     │
│    intent.purchase_id === webhook.purchase_id               │
│                                                             │
│  ✓ Amount matches?                                          │
│    intent.amount === webhook.amount                         │
│                                                             │
│  ✓ Status is "created"?                                     │
│    intent.status === "created"                              │
│                                                             │
│  If all pass → Continue                                     │
│  If any fail → Return null (prevent order creation)         │
└────────┬────────────────────────────────────────────────────┘
         │
         │ Step 5: Create all database records
         ▼
┌─────────────────────────────────────────────────────────────┐
│              DB::transaction(function() {                   │
│                                                             │
│    ┌─────────────────────────────────────────────────┐    │
│    │  A. Create/Find User                            │    │
│    │                                                 │    │
│    │  User::where('email', $email)->first()          │    │
│    │  ?: User::create([                              │    │
│    │      email: customer@example.com,   ← FROM      │    │
│    │      name: Ahmad,                    PAYMENT    │    │
│    │      phone: +60123456789,            INTENT     │    │
│    │      is_guest: true                  customer_  │    │
│    │  ])                                  data       │    │
│    └─────────────┬───────────────────────────────────┘    │
│                  │                                         │
│                  ▼                                         │
│    ┌─────────────────────────────────────────────────┐    │
│    │  B. Create Address                              │    │
│    │                                                 │    │
│    │  Address::create([                              │    │
│    │      addressable_type: User::class,             │    │
│    │      addressable_id: $user->id,                 │    │
│    │      name: Ahmad,                 ← FROM        │    │
│    │      street1: 123 Jalan Merdeka,   PAYMENT     │    │
│    │      street2: Taman Melati,        INTENT      │    │
│    │      city: Kuala Lumpur,           customer_   │    │
│    │      state: Wilayah Persekutuan,   data        │    │
│    │      postcode: 50000,                           │    │
│    │      phone: +60123456789,                       │    │
│    │      type: billing,                             │    │
│    │      is_primary: true                           │    │
│    │  ])                                             │    │
│    └─────────────┬───────────────────────────────────┘    │
│                  │                                         │
│                  ▼                                         │
│    ┌─────────────────────────────────────────────────┐    │
│    │  C. Create Order + Order Items                  │    │
│    │                                                 │    │
│    │  Order::create([                                │    │
│    │      order_number: ORD-2025-001,                │    │
│    │      user_id: $user->id,                        │    │
│    │      address_id: $address->id,                  │    │
│    │      cart_items: [...],        ← FROM           │    │
│    │      total: 15000,              PAYMENT         │    │
│    │      status: processing,         INTENT         │    │
│    │      checkout_form_data: {...}   cart_snapshot  │    │
│    │  ])                              & customer_data│    │
│    │                                                 │    │
│    │  foreach ($cartSnapshot as $item) {             │    │
│    │      OrderItem::create([                        │    │
│    │          order_id: $order->id,                  │    │
│    │          product_id: $item['id'],               │    │
│    │          quantity: $item['quantity'],           │    │
│    │          unit_price: $item['price']             │    │
│    │      ])                                         │    │
│    │  }                                              │    │
│    └─────────────┬───────────────────────────────────┘    │
│                  │                                         │
│                  ▼                                         │
│    ┌─────────────────────────────────────────────────┐    │
│    │  D. Create Payment                              │    │
│    │                                                 │    │
│    │  Payment::create([                              │    │
│    │      order_id: $order->id,                      │    │
│    │      gateway_payment_id: pur_abc123, ← FROM    │    │
│    │      gateway_transaction_id: pay_xyz789, WEBHOOK│    │
│    │      amount: 15000,                      DATA   │    │
│    │      status: completed,                         │    │
│    │      method: fpx_b2c,                           │    │
│    │      currency: MYR,                             │    │
│    │      paid_at: NOW(),                            │    │
│    │      gateway_response: {...full webhook}        │    │
│    │  ])                                             │    │
│    └─────────────────────────────────────────────────┘    │
│                                                             │
│    return $order;                                          │
│                                                             │
│  }) ← Transaction ends                                     │
└────────┬────────────────────────────────────────────────────┘
         │
         │ Transaction committed successfully ✓
         ▼
┌─────────────────────────────────────────────────────────────┐
│  Clear Cart                                                 │
│                                                             │
│  $cart->clear();                                            │
│                                                             │
│  Removes all items, conditions, and metadata from cart      │
└─────────────────────────────────────────────────────────────┘
         │
         │ Return order
         ▼
┌─────────────────────────────────────────────────────────────┐
│  Final Result                                               │
│                                                             │
│  ✅ users table: 1 new record                               │
│  ✅ addresses table: 1 new record                           │
│  ✅ orders table: 1 new record                              │
│  ✅ order_items table: N new records (one per cart item)    │
│  ✅ payments table: 1 new record                            │
│  ✅ carts table: cart DELETED entirely (clear() method)     │
│                                                             │
│  Order created successfully! 🎉                             │
└─────────────────────────────────────────────────────────────┘
```

## Key Points Summary

### Phase 1 (Checkout):
```
Customer Input → Form Data
                    ↓
              CHIP API Call
                    ↓
            Store in Cart Metadata
                    ↓
          Redirect to CHIP Gateway
```

**Database Impact:** Only `carts.metadata` updated

---

### Phase 2 (Webhook):
```
CHIP Webhook → Verify Signature
                    ↓
            Find Cart by purchase_id
                    ↓
         Get payment_intent from metadata
                    ↓
            Validate webhook data
                    ↓
           Create ALL records:
           - users
           - addresses
           - orders
           - order_items
           - payments
                    ↓
              Clear cart
```

**Database Impact:** 5+ tables with new records

---

## Data Flow Map

```
┌──────────────────┐
│  Customer Form   │
│                  │
│  - Name          │
│  - Email         │
│  - Phone         │
│  - Address       │
└────────┬─────────┘
         │
         ▼
┌──────────────────┐
│ Cart Metadata    │
│ payment_intent   │  Stored during checkout
│                  │
│ {                │
│   cart_snapshot, │ ← Cart items at checkout time
│   customer_data, │ ← Form data
│   purchase_id    │ ← CHIP reference
│ }                │
└────────┬─────────┘
         │
         │ Retrieved during webhook
         ▼
┌──────────────────┐
│ Database Tables  │
│                  │
│ users ←──────────┼─ customer_data.email, name, phone
│ addresses ←──────┼─ customer_data.street1, city, etc.
│ orders ←─────────┼─ cart_snapshot, customer_data, amount
│ order_items ←────┼─ cart_snapshot[*]
│ payments ←───────┼─ purchase_id, webhook data
│                  │
└──────────────────┘
```

---

**Everything flows through cart metadata! It's the single source of truth for order creation.** 🎯
