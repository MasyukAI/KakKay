# Webhook Testing Guide - Live Browser Flow

This guide walks through testing the complete CHIP payment webhook flow using a real browser and Cloudflare tunnel.

## Current Test Setup

### 🟢 Active Configuration
- **Cloudflare Tunnel**: Running (`cloudflared tunnel run kakkay-local`)
- **Public URL**: `https://local.kakkay.my`
- **Local URL**: `https://kakkay.test`
- **Webhook Endpoint**: `https://local.kakkay.my/webhooks/chip/{webhook_id}`
- **Success Callback**: Currently has **EARLY RETURN** to prevent order processing
- **Database**: Clean state (no orders, payments, or carts)

### ⚠️ Temporary Code Changes
**File**: `app/Http/Controllers/CheckoutController.php`
**Method**: `success()`
**Change**: Added early return to prevent order processing via success callback

```php
// Line ~48-62: TEMPORARY early return code
Log::info('🔴 SUCCESS CALLBACK HIT - EARLY RETURN ACTIVE', [
    'reference' => $reference,
    'query_params' => $request->query(),
    'timestamp' => now()->toIso8601String(),
]);

return view('checkout.success', [
    'order' => null,
    'payment' => null,
    'reference' => $reference,
    'cartSnapshot' => null,
    'customerSnapshot' => null,
    'isCompleted' => false,
    'isPending' => true,
]);
```

**Purpose**: Isolate webhook processing from success callback to verify webhook creates the order independently.

## Testing Workflow

### Phase 1: Browser Checkout Flow

1. **Open Browser to Local Site**
   ```
   URL: https://kakkay.test
   ```

2. **Add Product to Cart**
   - Navigate to product page
   - Click "Add to Cart"
   - Verify cart shows item

3. **Proceed to Checkout**
   - Click checkout button
   - Fill in customer information:
     - Name
     - Email
     - Phone
     - Address (street1, city, state, postcode)
   - Submit checkout form

4. **CHIP Payment Gateway**
   - Browser redirects to CHIP payment page
   - **Do NOT close this window**
   - Select payment method (e.g., FPX)
   - Complete payment in sandbox mode

5. **Success Redirect**
   - CHIP redirects back to: `https://kakkay.test/checkout/success/{reference}`
   - Page shows "pending" state (due to early return)
   - **IMPORTANT**: Check browser console and network tab

### Phase 2: Database Verification

After success callback hits, check database state:

```sql
-- Check if cart was created and has payment intent
SELECT id, instance, identifier, metadata::text 
FROM carts 
ORDER BY created_at DESC 
LIMIT 1;

-- Check if webhook was received
SELECT id, event_type, payload::text, created_at 
FROM chip_webhooks 
ORDER BY created_at DESC 
LIMIT 3;

-- Check if payment was created (should be via webhook only)
SELECT id, gateway_payment_id, amount, status, reference, paid_at, created_at 
FROM payments 
ORDER BY created_at DESC 
LIMIT 1;

-- Check if order was created (should be via webhook only)
SELECT id, order_number, total, status, created_at 
FROM orders 
ORDER BY created_at DESC 
LIMIT 1;
```

### Phase 3: Expected Results

#### ✅ Success Callback (Early Return)
- **Log Entry**: "🔴 SUCCESS CALLBACK HIT - EARLY RETURN ACTIVE"
- **Database**: No order created, no payment record
- **View**: Shows pending state

#### ✅ Webhook Processing
- **Log Entry**: "CHIP request received" with type='webhook'
- **Log Entry**: "CHIP signature verified successfully"
- **Log Entry**: "CHIP request queued for processing"
- **Job Execution**: `ProcessWebhook` job runs
- **Database**: 
  - `chip_webhooks` table has webhook record
  - `chip_purchases` table has purchase data
  - `payments` table has payment record with status='completed'
  - `orders` table has order record with status='completed'
  - Order has order_number generated
  - Order is linked to payment

#### ✅ Idempotency Check
If you refresh the success page (triggering another callback):
- **Log Entry**: "Order already exists for purchase, skipping duplicate creation"
- **Database**: No duplicate orders or payments
- **Result**: Same order returned

## Verification Queries

### Check Cart State
```sql
SELECT 
    id,
    instance,
    identifier,
    jsonb_pretty(metadata) as metadata,
    created_at
FROM carts
ORDER BY created_at DESC
LIMIT 1;
```

### Check Webhook Received
```sql
SELECT 
    id,
    event_type,
    jsonb_pretty(payload) as payload,
    created_at,
    processed_at
FROM chip_webhooks
ORDER BY created_at DESC
LIMIT 1;
```

### Check Purchase Data Recorded
```sql
SELECT 
    id,
    status,
    amount,
    currency,
    reference,
    jsonb_pretty(raw_data) as raw_data,
    created_at
FROM chip_purchases
ORDER BY created_at DESC
LIMIT 1;
```

### Check Payment Created
```sql
SELECT 
    p.id,
    p.order_id,
    p.gateway_payment_id,
    p.amount,
    p.status,
    p.reference,
    p.paid_at,
    p.created_at,
    o.order_number,
    o.total,
    o.status as order_status
FROM payments p
LEFT JOIN orders o ON o.id = p.order_id
ORDER BY p.created_at DESC
LIMIT 1;
```

### Check Order Details
```sql
SELECT 
    o.id,
    o.order_number,
    o.user_id,
    o.total,
    o.status,
    o.created_at,
    COUNT(oi.id) as item_count,
    SUM(oi.quantity) as total_quantity
FROM orders o
LEFT JOIN order_items oi ON oi.order_id = o.id
GROUP BY o.id, o.order_number, o.user_id, o.total, o.status, o.created_at
ORDER BY o.created_at DESC
LIMIT 1;
```

## Log Monitoring

### Watch Logs in Real-Time
```bash
# Terminal 1: Laravel logs
tail -f storage/logs/laravel.log | grep -E "SUCCESS CALLBACK|CHIP|webhook|purchase"

# Terminal 2: Cloudflare tunnel logs
tail -f /Users/saiffil/cloudflared-tunnel.log
```

### Key Log Entries to Watch For

1. **Success Callback Hit**
   ```
   🔴 SUCCESS CALLBACK HIT - EARLY RETURN ACTIVE
   reference: cart_xxxx
   ```

2. **Webhook Received**
   ```
   CHIP request received
   type: webhook
   event_type: purchase.paid
   ```

3. **Signature Verified**
   ```
   CHIP signature verified successfully
   ```

4. **Webhook Queued**
   ```
   CHIP request queued for processing
   ```

5. **Order Creation Started**
   ```
   handlePaymentSuccess invoked
   source: webhook
   ```

6. **Order Created**
   ```
   Order created successfully from cart payment intent
   order_id: xxx
   purchase_id: xxx
   source: webhook
   ```

## Testing Checklist

- [ ] Cloudflare tunnel running (`pgrep cloudflared`)
- [ ] Database clean (no test orders)
- [ ] Early return active in CheckoutController
- [ ] Browser at https://kakkay.test
- [ ] Product added to cart
- [ ] Checkout form submitted
- [ ] Payment completed at CHIP
- [ ] Success page shows pending state
- [ ] Success callback log entry found
- [ ] Webhook received log entry found
- [ ] Webhook signature verified
- [ ] Order created by webhook (not callback)
- [ ] Payment status is 'completed'
- [ ] Order status is 'completed'
- [ ] No duplicate orders on page refresh

## After Testing

### Remove Temporary Code
Once webhook functionality is verified, remove the early return from `CheckoutController`:

```bash
# Edit app/Http/Controllers/CheckoutController.php
# Remove lines ~48-62 (the early return block)
# Keep only: $payload = $this->checkoutService->prepareSuccessView($reference);
```

### Re-test Full Flow
After removing early return:
- Success callback should create order if webhook hasn't yet
- Webhook should skip order creation if callback already created it
- Idempotency works from both directions

## Troubleshooting

### Webhook Not Received
```bash
# Check tunnel is accessible
curl https://local.kakkay.my/webhooks/chip/test_webhook -X POST -H "Content-Type: application/json"

# Check webhook URL in CHIP dashboard
# Should be: https://local.kakkay.my/webhooks/chip/{webhook_id}
```

### Signature Verification Fails
```sql
-- Check if CHIP public key is configured
SELECT value FROM config WHERE key = 'chip.webhooks.public_key';
```

### Queue Not Processing
```bash
# Check queue configuration
php artisan config:cache

# Run queue worker manually
php artisan queue:work --once

# Check failed jobs
php artisan queue:failed
```

## Database Schema Reference

### Tables Used in Webhook Flow

1. **carts** - Cart data with metadata including payment_intent
2. **chip_webhooks** - Raw webhook payloads received
3. **chip_purchases** - Normalized CHIP purchase data
4. **payments** - Payment records linked to orders
5. **orders** - Order records created from cart snapshots
6. **order_items** - Line items for each order
7. **users** - Customer records (created if not exists)
8. **addresses** - Shipping addresses

## Success Criteria

✅ **Webhook Independence**: Order created by webhook alone (not success callback)
✅ **Data Integrity**: Order matches cart snapshot completely
✅ **Idempotency**: No duplicates even with multiple callbacks/webhooks
✅ **Performance**: Queue processing works reliably
✅ **Logging**: Clear audit trail of webhook → order flow
✅ **Status Accuracy**: Payment and order both marked as 'completed'

## Next Steps After Verification

1. Remove early return from CheckoutController
2. Add monitoring/alerting for webhook failures
3. Test edge cases:
   - Network timeout during CHIP payment
   - User closes browser before redirect
   - Multiple rapid webhook deliveries
4. Performance testing with concurrent checkouts
5. Production webhook URL configuration
