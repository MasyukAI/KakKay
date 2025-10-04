# CHIP API Complete Reference Documentation# CHIP API Reference Documentation



**Last Updated:** October 4, 2025  ## Overview

**Official Documentation:** https://docs.chip-in.asia/

This document provides a comprehensive reference for the CHIP API integration, compiled from the official CHIP documentation to ensure accurate implementation of the Laravel package.

This comprehensive reference provides complete API specifications for both CHIP Collect (payment gateway) and CHIP Send (money transfer) APIs, compiled directly from official CHIP documentation.

## Base URLs

---

- **Production:** `https://api.chip-in.asia/api/` (CHIP Send) / `https://gate.chip-in.asia/api/v1/` (CHIP Collect)

## Table of Contents- **Staging:** `https://staging-api.chip-in.asia/api/` (CHIP Send) / `https://gate-sandbox.chip-in.asia/api/v1/` (CHIP Collect)



- [Quick Links](#quick-links)## Authentication

- [Base URLs & Authentication](#base-urls--authentication)

- [CHIP Collect API](#chip-collect-api)### CHIP Collect

- [CHIP Send API](#chip-send-api)- Uses Bearer token authentication: `Authorization: Bearer <API_KEY>`

- [Data Structures](#data-structures)- Get API key from: https://portal.chip-in.asia/collect/developers/api-keys

- [Webhook Events](#webhook-events)- Get Brand ID from: https://portal.chip-in.asia/collect/developers/brands

- [Status & State Values](#status--state-values)

- [Testing](#testing)### CHIP Send

- Uses Bearer token + signed requests

---- Headers required:

  - `Authorization: Bearer <API_KEY>`

## Quick Links  - `epoch: <unix_timestamp>`

  - `checksum: <signed_hash>`

### Official Documentation

- **Main Documentation:** https://docs.chip-in.asia/## Data Structures

- **Getting Started:** https://docs.chip-in.asia/getting-started

- **API Reference:** https://docs.chip-in.asia/api-reference### Purchase Object (CHIP Collect)



### CHIP Collect (Payment Gateway)```json

- **API Overview:** https://docs.chip-in.asia/chip-collect/api-reference{

- **Purchases:** https://docs.chip-in.asia/chip-collect/api-reference/purchases  "id": "uuid",

- **Clients:** https://docs.chip-in.asia/chip-collect/api-reference/clients  "type": "string",

- **Webhooks:** https://docs.chip-in.asia/chip-collect/api-reference/webhooks  "created_on": 1619740800,

- **Payment Methods:** https://docs.chip-in.asia/chip-collect/api-reference/payment-methods  "updated_on": 1619740800,

- **Recurring Tokens:** https://docs.chip-in.asia/chip-collect/api-reference/recurring-tokens  "client": {

- **Company Statements:** https://docs.chip-in.asia/chip-collect/api-reference/company-statements    "email": "required@example.com",

    "full_name": "string",

### CHIP Send (Money Transfer)    "phone": "+44 45643564564",

- **API Overview:** https://docs.chip-in.asia/chip-send/api-reference    "street_address": "string",

- **Send Instructions:** https://docs.chip-in.asia/chip-send/api-reference/send-instructions    "country": "ISO 3166-1 alpha-2",

- **Bank Accounts:** https://docs.chip-in.asia/chip-send/api-reference/bank-accounts    "city": "string",

- **Send Limits:** https://docs.chip-in.asia/chip-send/api-reference/send-limits    "zip_code": "string",

- **Groups:** https://docs.chip-in.asia/chip-send/api-reference/groups    "state": "string",

- **Accounts:** https://docs.chip-in.asia/chip-send/api-reference/accounts    "shipping_street_address": "string",

- **Webhooks:** https://docs.chip-in.asia/chip-send/api-reference/webhooks    "shipping_country": "ISO 3166-1 alpha-2", 

    "shipping_city": "string",

### Guides & Tutorials    "shipping_zip_code": "string",

- **Pre-authorization Guide:** https://docs.chip-in.asia/chip-collect/guides/pre-authorization    "shipping_state": "string",

- **Subscription Payments:** https://docs.chip-in.asia/chip-collect/guides/subscription-payments    "cc": ["email@example.com"],

- **Direct Post Integration:** https://docs.chip-in.asia/chip-collect/guides/direct-post    "bcc": ["email@example.com"],

- **Webhook Security:** https://docs.chip-in.asia/chip-collect/guides/webhooks    "legal_name": "string",

    "brand_name": "string",

---    "registration_number": "string",

    "tax_number": "string",

## Base URLs & Authentication    "bank_account": "string",

    "bank_code": "string",

### CHIP Collect    "personal_code": "string"

  },

**Base URL (Production & Sandbox):**  "purchase": {

```    "currency": "MYR",

https://gate.chip-in.asia/api/v1/    "products": [

```      {

        "name": "required",

**Note:** CHIP Collect uses the same production URL for both sandbox and production environments. The environment is determined by the API key used.        "quantity": 1,

        "price": 1000,

**Authentication:**        "discount": 0,

```http        "tax_percent": 0,

Authorization: Bearer <API_KEY>        "category": "string"

Content-Type: application/json      }

Accept: application/json    ],

```    "total": 1000,

    "language": "en",

**Get API Keys:**    "notes": "string",

- Portal: https://portal.chip-in.asia/collect/developers/api-keys    "debt": 0,

- Brand IDs: https://portal.chip-in.asia/collect/developers/brands    "subtotal_override": null,

    "total_tax_override": null,

**Documentation:**    "total_discount_override": null,

- Authentication: https://docs.chip-in.asia/chip-collect/api-reference#authentication    "total_override": null,

    "request_client_details": [],

---    "timezone": "Asia/Kuala_Lumpur",

    "due_strict": false,

### CHIP Send    "email_message": "string"

  },

**Base URLs:**  "payment": {

- **Production:** `https://api.chip-in.asia/api/`    "is_outgoing": false,

- **Staging/Sandbox:** `https://staging-api.chip-in.asia/api/`    "payment_type": "purchase",

    "amount": 1000,

**Authentication:**    "currency": "MYR",

```http    "net_amount": 1000,

Authorization: Bearer <API_KEY>    "fee_amount": 0,

epoch: <unix_timestamp>    "pending_amount": 0,

checksum: <hmac_sha256_signature>    "pending_unfreeze_on": null,

Content-Type: application/json    "description": null,

```    "paid_on": null,

    "remote_paid_on": null

**Checksum Generation:**  },

```php  "transaction_data": {

$epoch = time();    "payment_method": "string",

$checksum = hash_hmac('sha256', (string)$epoch, $apiSecret);    "extra": {},

```    "country": "MY",

    "attempts": []

**Documentation:**  },

- Authentication: https://docs.chip-in.asia/chip-send/api-reference#authentication  "status": "created",

- HMAC Signature: https://docs.chip-in.asia/chip-send/api-reference#request-signing  "status_history": [],

  "company_id": "uuid",

---  "brand_id": "uuid",

  "client_id": null,

## CHIP Collect API  "is_test": true,

  "user_id": null,

### Purchases  "send_receipt": false,

  "is_recurring_token": false,

**Official Docs:** https://docs.chip-in.asia/chip-collect/api-reference/purchases  "recurring_token": null,

  "skip_capture": false,

#### Create Purchase  "force_recurring": false,

```http  "reference_generated": "string",

POST /purchases/  "reference": null,

```  "checkout_url": "https://...",

**Documentation:** https://docs.chip-in.asia/chip-collect/api-reference/purchases/create  "invoice_url": "https://...",

  "direct_post_url": "https://...",

**Required Fields:**  "success_redirect": null,

- `purchase` (object) - Purchase details with `products` array  "failure_redirect": null,

- `brand_id` (uuid) - Brand identifier  "cancel_redirect": null,

  "success_callback": null

**Optional Fields:**}

- `client` (object) - Client/customer details```

- `client_id` (uuid) - Existing client reference

- `success_redirect` (string) - Success URL### Payment Object (CHIP Collect)

- `failure_redirect` (string) - Failure URL

- `cancel_redirect` (string) - Cancel URL```json

- `success_callback` (string) - Webhook URL{

- `reference` (string) - Merchant reference  "is_outgoing": false,

- `send_receipt` (boolean) - Email receipt to customer  "payment_type": "purchase",

- `skip_capture` (boolean) - Pre-authorization mode  "amount": 1000,

- `force_recurring` (boolean) - Create recurring token  "currency": "MYR", 

- `due` (integer) - Due date timestamp  "net_amount": 950,

  "fee_amount": 50,

**Example Request:**  "pending_amount": 0,

```json  "pending_unfreeze_on": null,

{  "description": null,

  "brand_id": "550e8400-e29b-41d4-a716-446655440000",  "paid_on": 1619740800,

  "purchase": {  "remote_paid_on": 1619740800

    "currency": "MYR",}

    "products": [```

      {

        "name": "Premium Subscription",### Client Object (CHIP Collect)

        "quantity": 1,

        "price": 9900```json

      }{

    ]  "id": "uuid",

  },  "type": "client",

  "client": {  "created_on": 1619740800,

    "email": "customer@example.com",  "updated_on": 1619740800,

    "phone": "+60123456789"  "email": "required@example.com",

  },  "full_name": "John Doe",

  "success_redirect": "https://yoursite.com/success"  "phone": "+60123456789",

}  "street_address": "123 Main St",

```  "country": "MY",

  "city": "Kuala Lumpur",

**Response:** Purchase object with `checkout_url`  "zip_code": "50000",

  "state": "Selangor",

---  "legal_name": "Company Ltd",

  "brand_name": "Brand",

#### Get Purchase  "registration_number": "123456789",

```http  "tax_number": "987654321",

GET /purchases/{id}/  "bank_account": "1234567890",

```  "bank_code": "MBBEMYKL"

**Documentation:** https://docs.chip-in.asia/chip-collect/api-reference/purchases/retrieve}

```

**Response:** Complete Purchase object

### Send Instruction Object (CHIP Send)

---

```json

#### Cancel Purchase{

```http  "id": 123,

POST /purchases/{id}/cancel/  "bank_account_id": 456,

```  "amount": "100.00",

**Documentation:** https://docs.chip-in.asia/chip-collect/api-reference/purchases/cancel  "email": "recipient@example.com",

  "description": "Payment description",

**Status Requirement:** Purchase must be in `created`, `sent`, or `viewed` status  "reference": "unique_ref_123",

  "state": "completed",

---  "receipt_url": "https://...",

  "slug": "abcd1234",

#### Capture Payment (Pre-auth)  "created_at": "2023-07-20T10:41:25.190Z",

```http  "updated_at": "2023-07-20T10:41:25.302Z"

POST /purchases/{id}/capture/}

``````

**Documentation:** https://docs.chip-in.asia/chip-collect/api-reference/purchases/capture

### Bank Account Object (CHIP Send)

**Request Body:**

```json```json

{{

  "amount": 5000  // Optional: partial capture amount in cents  "id": 123,

}  "account_number": "1234567890",

```  "bank_code": "MBBEMYKL",

  "name": "Account Holder Name",

**Requirement:** Purchase must be in `hold` status (created with `skip_capture: true`)  "status": "verified",

  "group_id": null,

---  "reference": "unique_ref",

  "is_debiting_account": false,

#### Release Payment (Pre-auth)  "is_crediting_account": false,

```http  "created_at": "2023-07-20T08:59:10.766Z",

POST /purchases/{id}/release/  "updated_at": "2023-07-20T08:59:10.766Z",

```  "deleted_at": null,

**Documentation:** https://docs.chip-in.asia/chip-collect/api-reference/purchases/release  "rejection_reason": null

}

**Requirement:** Purchase must be in `hold` status```



---### Webhook Object (CHIP Collect)



#### Charge Recurring Token```json

```http{

POST /purchases/{id}/charge/  "id": "uuid",

```  "type": "webhook",

**Documentation:** https://docs.chip-in.asia/chip-collect/api-reference/purchases/charge  "created_on": 1619740800,

  "updated_on": 1619740800,

**Request Body:**  "title": "Purchase Events",

```json  "all_events": false,

{  "public_key": "-----BEGIN PUBLIC KEY-----\n...",

  "recurring_token": "token_value",  "events": ["purchase.created", "purchase.paid"],

  "amount": 5000  // Optional: different amount  "callback": "https://your-app.com/webhooks/chip"

}}

``````



---## Status Values



#### Refund Purchase### Purchase Status (CHIP Collect)

```http- `created` - Purchase created

POST /purchases/{id}/refund/- `sent` - Invoice sent

```- `viewed` - Customer viewed payment form

**Documentation:** https://docs.chip-in.asia/chip-collect/api-reference/purchases/refund- `paid` - Successfully paid

- `error` - Payment failed

**Request Body:**- `cancelled` - Purchase cancelled

```json- `overdue` - Past due date

{- `expired` - Past due date (strict)

  "amount": 5000  // Optional: partial refund amount- `blocked` - Blocked by fraud checks

}- `hold` - Funds on hold (skip_capture)

```- `released` - Funds released

- `pending_*` - Various pending states

**Requirement:** Purchase must be `paid` and within refund window- `preauthorized` - Card preauthorized

- `refunded` - Payment refunded

---

### Send Instruction State (CHIP Send)

#### Delete Recurring Token- `received` - Instruction received

```http- `enquiring` - Pending verification

DELETE /purchases/{id}/recurring_token/- `executing` - Pending execution

```- `reviewing` - Requires attention

**Documentation:** https://docs.chip-in.asia/chip-collect/api-reference/recurring-tokens/delete- `accepted` - Accepted by provider

- `completed` - Successfully completed

---- `rejected` - Instruction rejected

- `deleted` - Instruction deleted

#### Mark as Paid (Manual)

```http### Bank Account Status (CHIP Send)

POST /purchases/{id}/mark_as_paid/- `pending` - Awaiting verification

```- `verified` - Valid account

**Documentation:** https://docs.chip-in.asia/chip-collect/api-reference/purchases/mark-as-paid- `rejected` - Invalid account



---## Key API Endpoints



#### Resend Invoice Email### CHIP Collect

```http- `POST /purchases/` - Create purchase

POST /purchases/{id}/resend_invoice/- `GET /purchases/{id}/` - Retrieve purchase

```- `POST /purchases/{id}/cancel/` - Cancel purchase

**Documentation:** https://docs.chip-in.asia/chip-collect/api-reference/purchases/resend-invoice- `POST /purchases/{id}/capture/` - Capture payment

- `POST /purchases/{id}/charge/` - Charge with token

---- `POST /clients/` - Create client

- `POST /webhooks/` - Create webhook

### Clients

### CHIP Send

**Official Docs:** https://docs.chip-in.asia/chip-collect/api-reference/clients- `POST /send/send_instructions` - Create send instruction

- `POST /send/bank_accounts` - Add bank account

#### Create Client- `GET /send/accounts` - List accounts

```http

POST /clients/## Test Data

```

**Documentation:** https://docs.chip-in.asia/chip-collect/api-reference/clients/create### Test Card Numbers

- `4444 3333 2222 1111` - Non-3D Secure

**Required Field:**- `5555 5555 5555 4444` - 3D Secure

- `email` (string) - Valid email address- CVC: `123`

- Expiry: Any future date

**Optional Fields:**- Name: Any Latin name

- `full_name` (string)

- `phone` (string)## Price Format

- `street_address` (string)- All amounts are in the smallest currency unit (cents)

- `country` (string) - ISO 3166-1 alpha-2- Example: `1000` = RM 10.00

- `city` (string)

- `zip_code` (string)## Webhook Authentication

- `state` (string)- Payloads signed with RSA PKCS#1 v1.5

- `legal_name` (string) - Business name- Signature in `X-Signature` header

- `brand_name` (string)- Public key from `Webhook.public_key` or `GET /public_key/`

- `registration_number` (string)

- `tax_number` (string)## Field Naming Conventions

- `bank_account` (string)

- `bank_code` (string)### Timestamps

- API uses `created_on`/`updated_on` as Unix timestamps

---- Some responses may include `created_at`/`updated_at` as ISO strings



#### Get Client### Amount Fields

```http- `amount` - Base amount in cents

GET /clients/{id}/- `net_amount` - Amount after fees

```- `fee_amount` - Transaction fee

**Documentation:** https://docs.chip-in.asia/chip-collect/api-reference/clients/retrieve- `pending_amount` - Pending amount



---### Client Fields

- `full_name` - Customer full name

#### Update Client- `personal_code` - ID number

```http- `street_address` - Billing address

PUT /clients/{id}/- `shipping_*` - Shipping address fields

```

**Documentation:** https://docs.chip-in.asia/chip-collect/api-reference/clients/updateThis reference should be used to ensure DataObject structures match the actual API responses and test data structures align with expected formats.


---

#### Partial Update Client
```http
PATCH /clients/{id}/
```
**Documentation:** https://docs.chip-in.asia/chip-collect/api-reference/clients/update

---

#### Delete Client
```http
DELETE /clients/{id}/
```
**Documentation:** https://docs.chip-in.asia/chip-collect/api-reference/clients/delete

---

#### List Clients
```http
GET /clients/
```
**Documentation:** https://docs.chip-in.asia/chip-collect/api-reference/clients/list

**Query Parameters:**
- `email` (string) - Filter by email
- `page` (integer) - Pagination
- `page_size` (integer) - Results per page

---

#### List Client Recurring Tokens
```http
GET /clients/{id}/recurring_tokens/
```
**Documentation:** https://docs.chip-in.asia/chip-collect/api-reference/recurring-tokens/list

---

#### Get Client Recurring Token
```http
GET /clients/{id}/recurring_tokens/{token}/
```
**Documentation:** https://docs.chip-in.asia/chip-collect/api-reference/recurring-tokens/retrieve

---

#### Delete Client Recurring Token
```http
DELETE /clients/{id}/recurring_tokens/{token}/
```
**Documentation:** https://docs.chip-in.asia/chip-collect/api-reference/recurring-tokens/delete

---

### Webhooks

**Official Docs:** https://docs.chip-in.asia/chip-collect/api-reference/webhooks

#### Create Webhook
```http
POST /webhooks/
```
**Documentation:** https://docs.chip-in.asia/chip-collect/api-reference/webhooks/create

**Required Fields:**
- `callback` (string) - Webhook URL
- `events` (array) - Event types OR `all_events: true`

**Example:**
```json
{
  "callback": "https://yoursite.com/webhooks/chip",
  "events": ["purchase.created", "purchase.paid", "purchase.refunded"]
}
```

**OR for all events:**
```json
{
  "callback": "https://yoursite.com/webhooks/chip",
  "all_events": true
}
```

---

#### Get Webhook
```http
GET /webhooks/{id}/
```
**Documentation:** https://docs.chip-in.asia/chip-collect/api-reference/webhooks/retrieve

---

#### Update Webhook
```http
PUT /webhooks/{id}/
```
**Documentation:** https://docs.chip-in.asia/chip-collect/api-reference/webhooks/update

---

#### Delete Webhook
```http
DELETE /webhooks/{id}/
```
**Documentation:** https://docs.chip-in.asia/chip-collect/api-reference/webhooks/delete

---

#### List Webhooks
```http
GET /webhooks/
```
**Documentation:** https://docs.chip-in.asia/chip-collect/api-reference/webhooks/list

---

### Payment Methods

**Official Docs:** https://docs.chip-in.asia/chip-collect/api-reference/payment-methods

#### Get Available Payment Methods
```http
GET /payment_methods/
```
**Documentation:** https://docs.chip-in.asia/chip-collect/api-reference/payment-methods/list

**Query Parameters:**
- `currency` (string) - Filter by currency (MYR, USD, etc.)
- `country` (string) - Filter by country code

**Response:** Array of available payment method objects

---

### Public Key

#### Get Public Key for Webhook Verification
```http
GET /public_key/
```
**Documentation:** https://docs.chip-in.asia/chip-collect/guides/webhooks#signature-verification

**Response:**
```json
{
  "public_key": "-----BEGIN PUBLIC KEY-----\n...\n-----END PUBLIC KEY-----"
}
```

---

### Account Information

#### Get Account Balance
```http
GET /account/balance/
```
**Documentation:** https://docs.chip-in.asia/chip-collect/api-reference/account

**Response:**
```json
{
  "balance": 125000,
  "currency": "MYR",
  "available": 100000,
  "pending": 25000
}
```

---

#### Get Account Turnover
```http
GET /account/turnover/
```
**Documentation:** https://docs.chip-in.asia/chip-collect/api-reference/account

**Query Parameters:**
- `from` (integer) - Start timestamp
- `to` (integer) - End timestamp

---

### Company Statements

**Official Docs:** https://docs.chip-in.asia/chip-collect/api-reference/company-statements

#### List Company Statements
```http
GET /company_statements/
```
**Documentation:** https://docs.chip-in.asia/chip-collect/api-reference/company-statements/list

---

#### Get Company Statement
```http
GET /company_statements/{id}/
```
**Documentation:** https://docs.chip-in.asia/chip-collect/api-reference/company-statements/retrieve

---

#### Cancel Company Statement
```http
POST /company_statements/{id}/cancel/
```
**Documentation:** https://docs.chip-in.asia/chip-collect/api-reference/company-statements/cancel

---

## CHIP Send API

### Send Instructions

**Official Docs:** https://docs.chip-in.asia/chip-send/api-reference/send-instructions

#### Create Send Instruction
```http
POST /send/send_instructions
```
**Documentation:** https://docs.chip-in.asia/chip-send/api-reference/send-instructions/create

**Required Fields:**
- `bank_account_id` (integer) - Recipient bank account ID
- `amount` (string) - Amount as decimal string (e.g., "100.00")
- `email` (string) - Recipient email for notifications
- `description` (string) - Payment description
- `reference` (string) - Unique merchant reference

**Example:**
```json
{
  "bank_account_id": 123,
  "amount": "500.00",
  "email": "recipient@example.com",
  "description": "Payment for invoice #12345",
  "reference": "INV-12345"
}
```

**Response:** SendInstruction object

---

#### Get Send Instruction
```http
GET /send/send_instructions/{id}
```
**Documentation:** https://docs.chip-in.asia/chip-send/api-reference/send-instructions/retrieve

---

#### List Send Instructions
```http
GET /send/send_instructions
```
**Documentation:** https://docs.chip-in.asia/chip-send/api-reference/send-instructions/list

**Query Parameters:**
- `state` (string) - Filter by state
- `reference` (string) - Filter by reference
- `page` (integer)
- `page_size` (integer)

---

#### Delete Send Instruction
```http
DELETE /send/send_instructions/{id}
```
**Documentation:** https://docs.chip-in.asia/chip-send/api-reference/send-instructions/delete

**Requirement:** Can only delete instructions in certain states (not completed/executing)

---

#### Resend Send Instruction Webhook
```http
POST /send/send_instructions/{id}/resend_webhook
```
**Documentation:** https://docs.chip-in.asia/chip-send/api-reference/send-instructions/resend-webhook

---

### Bank Accounts

**Official Docs:** https://docs.chip-in.asia/chip-send/api-reference/bank-accounts

#### Create Bank Account
```http
POST /send/bank_accounts
```
**Documentation:** https://docs.chip-in.asia/chip-send/api-reference/bank-accounts/create

**Required Fields:**
- `account_number` (string) - Bank account number
- `bank_code` (string) - Bank SWIFT/BIC code (e.g., "MBBEMYKL")
- `name` (string) - Account holder name (1-65535 characters)

**Optional Fields:**
- `reference` (string) - Merchant reference

**Example:**
```json
{
  "account_number": "157380111111",
  "bank_code": "MBBEMYKL",
  "name": "Ahmad Pintu",
  "reference": "customer-001"
}
```

**Response:** BankAccount object with status `pending` (requires verification)

---

#### Get Bank Account
```http
GET /send/bank_accounts/{id}
```
**Documentation:** https://docs.chip-in.asia/chip-send/api-reference/bank-accounts/retrieve

---

#### List Bank Accounts
```http
GET /send/bank_accounts
```
**Documentation:** https://docs.chip-in.asia/chip-send/api-reference/bank-accounts/list

**Query Parameters:**
- `status` (string) - Filter by status (pending, verified, rejected)
- `group_id` (integer) - Filter by group
- `page` (integer)
- `page_size` (integer)

---

#### Delete Bank Account
```http
DELETE /send/bank_accounts/{id}
```
**Documentation:** https://docs.chip-in.asia/chip-send/api-reference/bank-accounts/delete

---

#### Resend Bank Account Webhook
```http
POST /send/bank_accounts/{id}/resend_webhook
```
**Documentation:** https://docs.chip-in.asia/chip-send/api-reference/bank-accounts/resend-webhook

---

### Send Limits

**Official Docs:** https://docs.chip-in.asia/chip-send/api-reference/send-limits

#### Create Send Limit
```http
POST /send/send_limits
```
**Documentation:** https://docs.chip-in.asia/chip-send/api-reference/send-limits/create

---

#### Get Send Limit
```http
GET /send/send_limits/{id}
```
**Documentation:** https://docs.chip-in.asia/chip-send/api-reference/send-limits/retrieve

---

#### List Send Limits
```http
GET /send/send_limits
```
**Documentation:** https://docs.chip-in.asia/chip-send/api-reference/send-limits/list

---

#### Resend Send Limit Approval Requests
```http
POST /send/send_limits/{id}/resend_approval_requests
```
**Documentation:** https://docs.chip-in.asia/chip-send/api-reference/send-limits/resend-approval

---

### Groups

**Official Docs:** https://docs.chip-in.asia/chip-send/api-reference/groups

#### Create Group
```http
POST /send/groups
```
**Documentation:** https://docs.chip-in.asia/chip-send/api-reference/groups/create

---

#### Get Group
```http
GET /send/groups/{id}
```
**Documentation:** https://docs.chip-in.asia/chip-send/api-reference/groups/retrieve

---

#### Update Group
```http
PUT /send/groups/{id}
```
**Documentation:** https://docs.chip-in.asia/chip-send/api-reference/groups/update

---

#### Delete Group
```http
DELETE /send/groups/{id}
```
**Documentation:** https://docs.chip-in.asia/chip-send/api-reference/groups/delete

---

#### List Groups
```http
GET /send/groups
```
**Documentation:** https://docs.chip-in.asia/chip-send/api-reference/groups/list

---

### Accounts

**Official Docs:** https://docs.chip-in.asia/chip-send/api-reference/accounts

#### List Accounts
```http
GET /send/accounts
```
**Documentation:** https://docs.chip-in.asia/chip-send/api-reference/accounts/list

**Response:** Array of available CHIP Send accounts

---

### Webhooks (CHIP Send)

**Official Docs:** https://docs.chip-in.asia/chip-send/api-reference/webhooks

#### Create Send Webhook
```http
POST /send/webhooks
```
**Documentation:** https://docs.chip-in.asia/chip-send/api-reference/webhooks/create

---

#### Get Send Webhook
```http
GET /send/webhooks/{id}
```
**Documentation:** https://docs.chip-in.asia/chip-send/api-reference/webhooks/retrieve

---

#### Update Send Webhook
```http
PUT /send/webhooks/{id}
```
**Documentation:** https://docs.chip-in.asia/chip-send/api-reference/webhooks/update

---

#### Delete Send Webhook
```http
DELETE /send/webhooks/{id}
```
**Documentation:** https://docs.chip-in.asia/chip-send/api-reference/webhooks/delete

---

#### List Send Webhooks
```http
GET /send/webhooks
```
**Documentation:** https://docs.chip-in.asia/chip-send/api-reference/webhooks/list

---

## Data Structures

### Purchase Object (CHIP Collect)

**Documentation:** https://docs.chip-in.asia/chip-collect/api-reference/purchases#purchase-object

```json
{
  "id": "uuid",
  "type": "purchase",
  "created_on": 1619740800,
  "updated_on": 1619740800,
  "viewed_on": 1619740800,
  "status": "paid",
  "client": {},
  "purchase": {
    "currency": "MYR",
    "products": [],
    "total": 9900
  },
  "payment": {},
  "transaction_data": {},
  "checkout_url": "https://..."
}
```

**See full structure at:** https://docs.chip-in.asia/chip-collect/api-reference/purchases#purchase-object

---

### Product Object

**Documentation:** https://docs.chip-in.asia/chip-collect/api-reference/purchases#product-object

```json
{
  "name": "Premium Subscription",
  "quantity": 1,
  "price": 9900,
  "discount": 0,
  "tax_percent": 6.0,
  "category": "digital_goods"
}
```

---

### SendInstruction Object (CHIP Send)

**Documentation:** https://docs.chip-in.asia/chip-send/api-reference/send-instructions#send-instruction-object

```json
{
  "id": 123,
  "bank_account_id": 456,
  "amount": "500.00",
  "email": "recipient@example.com",
  "description": "Payment description",
  "reference": "INV-12345",
  "state": "completed",
  "created_at": "2023-07-20T10:41:25Z"
}
```

---

### BankAccount Object (CHIP Send)

**Documentation:** https://docs.chip-in.asia/chip-send/api-reference/bank-accounts#bank-account-object

```json
{
  "id": 84,
  "account_number": "157380111111",
  "bank_code": "MBBEMYKL",
  "name": "Ahmad Pintu",
  "status": "verified",
  "created_at": "2023-07-20T08:59:10Z"
}
```

---

## Webhook Events

### CHIP Collect Events

**Documentation:** https://docs.chip-in.asia/chip-collect/guides/webhooks#event-types

**Purchase Events:**
- `purchase.created`
- `purchase.paid`
- `purchase.refunded`
- `purchase.cancelled`
- And more...

### CHIP Send Events

**Documentation:** https://docs.chip-in.asia/chip-send/api-reference/webhooks#event-types

**Send Instruction Events:**
- `send_instruction.completed`
- `send_instruction.rejected`

**Bank Account Events:**
- `bank_account.verified`
- `bank_account.rejected`

---

## Status & State Values

### Purchase Status (CHIP Collect)

**Documentation:** https://docs.chip-in.asia/chip-collect/api-reference/purchases#status-values

- `created` - Awaiting payment
- `paid` - Successfully paid ✅
- `cancelled` - Cancelled
- `refunded` - Refunded
- And more...

### Send Instruction States (CHIP Send)

**Documentation:** https://docs.chip-in.asia/chip-send/api-reference/send-instructions#state-values

- `received` - Received
- `executing` - Processing
- `completed` - Completed ✅
- `rejected` - Rejected ❌

---

## Testing

### Test Environment

**Documentation:** https://docs.chip-in.asia/getting-started/testing

**CHIP Collect:** Same URL, use test API keys  
**CHIP Send:** Use staging environment

### Test Cards

**Documentation:** https://docs.chip-in.asia/getting-started/testing#test-cards

```
Card: 4444 3333 2222 1111
CVC: 123
Expiry: Any future date
```

---

## Additional Resources

- **Documentation:** https://docs.chip-in.asia/
- **API Status:** https://status.chip-in.asia/
- **Support:** support@chip-in.asia
- **Portal:** https://portal.chip-in.asia/

---

**Last Updated:** October 4, 2025  
**For latest updates:** https://docs.chip-in.asia/
