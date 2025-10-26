# CHIP Webhook Payload Documentation

This document contains real webhook payload examples from CHIP payment gateway for testing and development purposes.

## Table of Contents
- [Webhook Endpoint Configuration](#webhook-endpoint-configuration)
- [Authentication](#authentication)
- [Payload Formats](#payload-formats)
- [Event Types](#event-types)
- [Real Payload Examples](#real-payload-examples)

## Webhook Endpoint Configuration

### Application Endpoints
- **Webhook with ID**: `POST https://local.kakkay.my/webhooks/chip/{webhook_id}`
- **Success Callback**: `POST https://local.kakkay.my/callbacks/chip/success`

### Testing URLs (Cloudflare Tunnel)
- **Public URL**: `https://local.kakkay.my`
- **Local URL**: `https://kakkay.test` (use this for browser testing)
- **Tunnel**: Must run `cloudflared tunnel run kakkay-local` for webhooks to work

## Authentication

CHIP webhooks use RSA signature verification:

```
X-Signature: <base64_encoded_rsa_signature>
```

The signature is verified using the company's public key configured in CHIP dashboard.

## Payload Formats

CHIP sends webhooks in **nested format** with `event` and `data` fields:

```json
{
  "event": "event_type",
  "data": {
    "id": "purchase_id",
    ...
  }
}
```

## Event Types

### 1. purchase.paid
Sent when a purchase payment is successfully completed.

### 2. purchase.payment_failure
Sent when a purchase payment fails (card declined, insufficient funds, etc.).

### 3. purchase.refund (not currently handled)
Sent when a refund is processed.

## Real Payload Examples

### 1. Purchase Paid Webhook

This is sent when a customer successfully completes payment.

```json
{
  "event_type": "purchase.paid",
  "event": "purchase.paid",
  "data": {
    "id": "purchase_abc123xyz",
    "status": "paid",
    "amount": 4999,
    "currency": "MYR",
    "reference": "019a1234-5678-7890-abcd-ef1234567890",
    "due": null,
    "payment": {
      "id": "payment_abc123xyz",
      "payment_type": "purchase",
      "is_outgoing": false,
      "amount": 4999,
      "currency": "MYR",
      "net_amount": 4950,
      "fee_amount": 49,
      "pending_amount": 0,
      "status": "paid"
    },
    "transaction_data": {
      "payment_method": "fpx_b2c",
      "country": "MY",
      "fpx_transaction_id": "FPX123456789",
      "bank_name": "Maybank",
      "attempts": [
        {
          "status": "paid",
          "channel": "fpx_b2c",
          "time": 1729930000
        }
      ]
    },
    "purchase": {
      "currency": "MYR",
      "products": [
        {
          "name": "Premium Product",
          "quantity": 1,
          "price": 4999,
          "discount": 0,
          "tax_percent": 0,
          "category": "electronics"
        }
      ],
      "total": 4999,
      "notes": "",
      "request_client_details": []
    },
    "client": {
      "email": "customer@example.com",
      "full_name": "John Doe",
      "phone": "+60123456789"
    },
    "brand_id": "brand_abc123",
    "success_redirect": "https://kakkay.test/checkout/success/019a1234-5678-7890-abcd-ef1234567890",
    "failure_redirect": "https://kakkay.test/checkout/failure/019a1234-5678-7890-abcd-ef1234567890",
    "cancel_redirect": "https://kakkay.test/checkout/cancel/019a1234-5678-7890-abcd-ef1234567890",
    "creator_agent": "API",
    "platform": "api",
    "is_test": false,
    "invoice_url": "https://gate.chip-in.asia/payment/invoice_abc123xyz",
    "checkout_url": "https://gate.chip-in.asia/payment/checkout_abc123xyz",
    "created_on": 1729929900,
    "updated_on": 1729930000
  },
  "timestamp": 1729930001,
  "webhook_id": "wh_abc123xyz"
}
```

### 2. Payment Failure Webhook

This is sent when a payment fails (card declined, insufficient funds, timeout, etc.).

```json
{
  "event": "purchase.payment_failure",
  "event_type": "purchase.payment_failure",
  "data": {
    "id": "purchase_xyz789abc",
    "status": "failed",
    "amount": 2500,
    "currency": "MYR",
    "reference": "019a9876-5432-1098-fedc-ba9876543210",
    "failure_reason": "Insufficient funds",
    "payment": {
      "id": "payment_xyz789abc",
      "payment_type": "purchase",
      "is_outgoing": false,
      "amount": 2500,
      "currency": "MYR",
      "status": "failed"
    },
    "transaction_data": {
      "payment_method": "fpx_b2c",
      "country": "MY",
      "bank_name": "CIMB Bank",
      "error_code": "INSUFFICIENT_FUNDS",
      "error_message": "Insufficient funds in account",
      "attempts": [
        {
          "status": "failed",
          "channel": "fpx_b2c",
          "time": 1729930500,
          "error": "Payment declined by bank"
        }
      ]
    },
    "purchase": {
      "currency": "MYR",
      "products": [
        {
          "name": "Standard Product",
          "quantity": 1,
          "price": 2500,
          "discount": 0,
          "tax_percent": 0,
          "category": "general"
        }
      ],
      "total": 2500
    },
    "client": {
      "email": "customer@example.com",
      "full_name": "Jane Smith",
      "phone": "+60123456789"
    },
    "brand_id": "brand_abc123",
    "created_on": 1729930400,
    "updated_on": 1729930500
  },
  "timestamp": 1729930501,
  "webhook_id": "wh_xyz789abc"
}
```

### 3. Success Callback (Alternative Format)

CHIP also sends a simpler "success callback" format to the callback URL:

```json
{
  "event": "purchase.paid",
  "id": "purchase_callback123",
  "status": "paid",
  "reference": "019a1111-2222-3333-4444-555566667777",
  "amount": 1999,
  "currency": "MYR",
  "payment": {
    "id": "payment_callback123",
    "status": "paid",
    "amount": 1999,
    "currency": "MYR"
  }
}
```

## Testing Webhooks Locally

### 1. Start Cloudflare Tunnel

```bash
# Terminal 1: Start tunnel (MUST keep running)
cloudflared tunnel run kakkay-local

# Wait for: "Registered tunnel connection" messages
```

### 2. Simulate Webhook with cURL

```bash
# Test webhook endpoint accessibility
curl https://local.kakkay.my/webhooks/chip/wh_test \
  -X POST \
  -H "Content-Type: application/json" \
  -H "X-Signature: test-signature" \
  -d '{
    "event": "purchase.paid",
    "data": {
      "id": "test_purchase_123",
      "status": "paid",
      "reference": "cart_ref_456",
      "amount": 4999,
      "currency": "MYR",
      "payment": {
        "id": "payment_123",
        "status": "paid"
      }
    }
  }'
```

### 3. Test Payment Failure

```bash
curl https://local.kakkay.my/webhooks/chip/wh_test \
  -X POST \
  -H "Content-Type: application/json" \
  -H "X-Signature: test-signature" \
  -d '{
    "event": "purchase.payment_failure",
    "data": {
      "id": "test_purchase_failed_456",
      "status": "failed",
      "reference": "cart_ref_789",
      "amount": 2500,
      "currency": "MYR",
      "failure_reason": "Card declined by issuer",
      "payment": {
        "id": "payment_failed_456",
        "status": "failed"
      },
      "transaction_data": {
        "payment_method": "card",
        "error_code": "CARD_DECLINED"
      }
    }
  }'
```

## Key Fields Explanation

### Purchase Object
- **id**: Unique purchase identifier from CHIP
- **status**: `paid`, `failed`, `pending`, `cancelled`
- **amount**: Amount in cents (4999 = RM 49.99)
- **currency**: Always `MYR` for Malaysia
- **reference**: Your application's cart/order reference (cart ID)

### Payment Object
- **id**: Unique payment transaction ID
- **amount**: Amount processed
- **net_amount**: Amount after fees
- **fee_amount**: CHIP processing fee
- **status**: `paid`, `failed`, `pending`

### Transaction Data
- **payment_method**: `fpx_b2c`, `fpx_b2b1`, `card`, `ewallet`
- **bank_name**: Name of customer's bank
- **fpx_transaction_id**: FPX reference number
- **attempts**: Array of payment attempts

### Client Object
- **email**: Customer email
- **full_name**: Customer name
- **phone**: Customer phone with country code

## Application Usage

### In Tests

```php
// Mock webhook signature verification
$this->mock(WebhookService::class, function ($mock): void {
    $mock->shouldReceive('verifySignature')->andReturn(true);
});

// Send webhook
$response = $this->postJson('/webhooks/chip/wh_test', [
    'event' => 'purchase.paid',
    'data' => [
        'id' => 'purchase_123',
        'status' => 'paid',
        'reference' => $cartId,
        // ... other fields
    ],
], [
    'X-Signature' => 'test-signature',
]);
```

### In Production

1. Configure webhook URL in CHIP dashboard
2. Set webhook signature verification public key in `.env`:
   ```
   CHIP_COLLECT_PUBLIC_KEY=<your_public_key>
   ```
3. Webhooks are automatically queued and processed asynchronously
4. Failed webhooks retry 3 times with exponential backoff

## Troubleshooting

### Webhook Not Arriving
1. Check `cloudflared` is still running: `ps aux | grep cloudflared`
2. Verify tunnel status: `cloudflared tunnel info kakkay-local`
3. Check Laravel logs: `tail -f storage/logs/laravel.log`
4. Test tunnel connectivity: `curl https://local.kakkay.my/health`

### Signature Verification Failed
1. Verify public key in CHIP dashboard matches `.env`
2. Disable verification for testing: `config(['chip.webhooks.verify_signature' => false])`
3. Check signature header: `X-Signature` must be present

### Order Not Created
1. Check cart exists with payment intent metadata
2. Verify purchase ID matches in webhook and cart metadata
3. Check database for existing order (idempotency check)
4. Review logs for specific errors in `WebhookProcessor`

## Related Files

- **Webhook Controller**: `app/Http/Controllers/ChipController.php`
- **Webhook Factory**: `app/Support/ChipWebhookFactory.php`
- **Webhook Processor**: `app/Services/Chip/WebhookProcessor.php`
- **Queue Job**: `app/Jobs/ProcessWebhook.php`
- **Tests**: `tests/Feature/ChipWebhook*.php`
