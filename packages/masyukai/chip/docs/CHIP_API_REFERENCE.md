# CHIP API Reference Documentation

## Overview

This document provides a comprehensive reference for the CHIP API integration, compiled from the official CHIP documentation to ensure accurate implementation of the Laravel package.

## Base URLs

- **Production:** `https://api.chip-in.asia/api/` (CHIP Send) / `https://gate.chip-in.asia/api/v1/` (CHIP Collect)
- **Staging:** `https://staging-api.chip-in.asia/api/` (CHIP Send) / `https://gate-sandbox.chip-in.asia/api/v1/` (CHIP Collect)

## Authentication

### CHIP Collect
- Uses Bearer token authentication: `Authorization: Bearer <API_KEY>`
- Get API key from: https://portal.chip-in.asia/collect/developers/api-keys
- Get Brand ID from: https://portal.chip-in.asia/collect/developers/brands

### CHIP Send
- Uses Bearer token + signed requests
- Headers required:
  - `Authorization: Bearer <API_KEY>`
  - `epoch: <unix_timestamp>`
  - `checksum: <signed_hash>`

## Data Structures

### Purchase Object (CHIP Collect)

```json
{
  "id": "uuid",
  "type": "string",
  "created_on": 1619740800,
  "updated_on": 1619740800,
  "client": {
    "email": "required@example.com",
    "full_name": "string",
    "phone": "+44 45643564564",
    "street_address": "string",
    "country": "ISO 3166-1 alpha-2",
    "city": "string",
    "zip_code": "string",
    "state": "string",
    "shipping_street_address": "string",
    "shipping_country": "ISO 3166-1 alpha-2", 
    "shipping_city": "string",
    "shipping_zip_code": "string",
    "shipping_state": "string",
    "cc": ["email@example.com"],
    "bcc": ["email@example.com"],
    "legal_name": "string",
    "brand_name": "string",
    "registration_number": "string",
    "tax_number": "string",
    "bank_account": "string",
    "bank_code": "string",
    "personal_code": "string"
  },
  "purchase": {
    "currency": "MYR",
    "products": [
      {
        "name": "required",
        "quantity": 1,
        "price": 1000,
        "discount": 0,
        "tax_percent": 0,
        "category": "string"
      }
    ],
    "total": 1000,
    "language": "en",
    "notes": "string",
    "debt": 0,
    "subtotal_override": null,
    "total_tax_override": null,
    "total_discount_override": null,
    "total_override": null,
    "request_client_details": [],
    "timezone": "Asia/Kuala_Lumpur",
    "due_strict": false,
    "email_message": "string"
  },
  "payment": {
    "is_outgoing": false,
    "payment_type": "purchase",
    "amount": 1000,
    "currency": "MYR",
    "net_amount": 1000,
    "fee_amount": 0,
    "pending_amount": 0,
    "pending_unfreeze_on": null,
    "description": null,
    "paid_on": null,
    "remote_paid_on": null
  },
  "transaction_data": {
    "payment_method": "string",
    "extra": {},
    "country": "MY",
    "attempts": []
  },
  "status": "created",
  "status_history": [],
  "company_id": "uuid",
  "brand_id": "uuid",
  "client_id": null,
  "is_test": true,
  "user_id": null,
  "send_receipt": false,
  "is_recurring_token": false,
  "recurring_token": null,
  "skip_capture": false,
  "force_recurring": false,
  "reference_generated": "string",
  "reference": null,
  "checkout_url": "https://...",
  "invoice_url": "https://...",
  "direct_post_url": "https://...",
  "success_redirect": null,
  "failure_redirect": null,
  "cancel_redirect": null,
  "success_callback": null
}
```

### Payment Object (CHIP Collect)

```json
{
  "is_outgoing": false,
  "payment_type": "purchase",
  "amount": 1000,
  "currency": "MYR", 
  "net_amount": 950,
  "fee_amount": 50,
  "pending_amount": 0,
  "pending_unfreeze_on": null,
  "description": null,
  "paid_on": 1619740800,
  "remote_paid_on": 1619740800
}
```

### Client Object (CHIP Collect)

```json
{
  "id": "uuid",
  "type": "client",
  "created_on": 1619740800,
  "updated_on": 1619740800,
  "email": "required@example.com",
  "full_name": "John Doe",
  "phone": "+60123456789",
  "street_address": "123 Main St",
  "country": "MY",
  "city": "Kuala Lumpur",
  "zip_code": "50000",
  "state": "Selangor",
  "legal_name": "Company Ltd",
  "brand_name": "Brand",
  "registration_number": "123456789",
  "tax_number": "987654321",
  "bank_account": "1234567890",
  "bank_code": "MBBEMYKL"
}
```

### Send Instruction Object (CHIP Send)

```json
{
  "id": 123,
  "bank_account_id": 456,
  "amount": "100.00",
  "email": "recipient@example.com",
  "description": "Payment description",
  "reference": "unique_ref_123",
  "state": "completed",
  "receipt_url": "https://...",
  "slug": "abcd1234",
  "created_at": "2023-07-20T10:41:25.190Z",
  "updated_at": "2023-07-20T10:41:25.302Z"
}
```

### Bank Account Object (CHIP Send)

```json
{
  "id": 123,
  "account_number": "1234567890",
  "bank_code": "MBBEMYKL",
  "name": "Account Holder Name",
  "status": "verified",
  "group_id": null,
  "reference": "unique_ref",
  "is_debiting_account": false,
  "is_crediting_account": false,
  "created_at": "2023-07-20T08:59:10.766Z",
  "updated_at": "2023-07-20T08:59:10.766Z",
  "deleted_at": null,
  "rejection_reason": null
}
```

### Webhook Object (CHIP Collect)

```json
{
  "id": "uuid",
  "type": "webhook",
  "created_on": 1619740800,
  "updated_on": 1619740800,
  "title": "Purchase Events",
  "all_events": false,
  "public_key": "-----BEGIN PUBLIC KEY-----\n...",
  "events": ["purchase.created", "purchase.paid"],
  "callback": "https://your-app.com/webhooks/chip"
}
```

## Status Values

### Purchase Status (CHIP Collect)
- `created` - Purchase created
- `sent` - Invoice sent
- `viewed` - Customer viewed payment form
- `paid` - Successfully paid
- `error` - Payment failed
- `cancelled` - Purchase cancelled
- `overdue` - Past due date
- `expired` - Past due date (strict)
- `blocked` - Blocked by fraud checks
- `hold` - Funds on hold (skip_capture)
- `released` - Funds released
- `pending_*` - Various pending states
- `preauthorized` - Card preauthorized
- `refunded` - Payment refunded

### Send Instruction State (CHIP Send)
- `received` - Instruction received
- `enquiring` - Pending verification
- `executing` - Pending execution
- `reviewing` - Requires attention
- `accepted` - Accepted by provider
- `completed` - Successfully completed
- `rejected` - Instruction rejected
- `deleted` - Instruction deleted

### Bank Account Status (CHIP Send)
- `pending` - Awaiting verification
- `verified` - Valid account
- `rejected` - Invalid account

## Key API Endpoints

### CHIP Collect
- `POST /purchases/` - Create purchase
- `GET /purchases/{id}/` - Retrieve purchase
- `POST /purchases/{id}/cancel/` - Cancel purchase
- `POST /purchases/{id}/capture/` - Capture payment
- `POST /purchases/{id}/charge/` - Charge with token
- `POST /clients/` - Create client
- `POST /webhooks/` - Create webhook

### CHIP Send
- `POST /send/send_instructions` - Create send instruction
- `POST /send/bank_accounts` - Add bank account
- `GET /send/accounts` - List accounts

## Test Data

### Test Card Numbers
- `4444 3333 2222 1111` - Non-3D Secure
- `5555 5555 5555 4444` - 3D Secure
- CVC: `123`
- Expiry: Any future date
- Name: Any Latin name

## Price Format
- All amounts are in the smallest currency unit (cents)
- Example: `1000` = RM 10.00

## Webhook Authentication
- Payloads signed with RSA PKCS#1 v1.5
- Signature in `X-Signature` header
- Public key from `Webhook.public_key` or `GET /public_key/`

## Field Naming Conventions

### Timestamps
- API uses `created_on`/`updated_on` as Unix timestamps
- Some responses may include `created_at`/`updated_at` as ISO strings

### Amount Fields
- `amount` - Base amount in cents
- `net_amount` - Amount after fees
- `fee_amount` - Transaction fee
- `pending_amount` - Pending amount

### Client Fields
- `full_name` - Customer full name
- `personal_code` - ID number
- `street_address` - Billing address
- `shipping_*` - Shipping address fields

This reference should be used to ensure DataObject structures match the actual API responses and test data structures align with expected formats.
