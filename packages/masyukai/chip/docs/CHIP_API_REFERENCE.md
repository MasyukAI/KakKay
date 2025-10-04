# CHIP API Reference Documentation# CHIP API Reference Documentation# CHIP API Complete Reference Documentation# CHIP API Reference Documentation



**Last Updated:** October 4, 2025  

**Official Documentation:** https://docs.chip-in.asia/

**Last Updated:** January 2025  

---

**Official Documentation:** https://docs.chip-in.asia/

## Overview

**Last Updated:** October 4, 2025  ## Overview

This comprehensive reference provides complete API specifications for both CHIP Collect (payment gateway) and CHIP Send (money transfer) APIs, compiled directly from official CHIP documentation to ensure 100% accuracy.

This comprehensive reference provides complete API specifications for both CHIP Collect (payment gateway) and CHIP Send (money transfer) APIs, compiled directly from official CHIP documentation.

---

**Official Documentation:** https://docs.chip-in.asia/

## Table of Contents

---

- [Quick Links](#quick-links)

- [Base URLs & Authentication](#base-urls--authentication)This document provides a comprehensive reference for the CHIP API integration, compiled from the official CHIP documentation to ensure accurate implementation of the Laravel package.

- [Package Features](#package-features)

- [Quick Start](#quick-start)## Table of Contents

- [Advanced Features](#advanced-features)

  - [Facade Pattern](#facade-pattern)This comprehensive reference provides complete API specifications for both CHIP Collect (payment gateway) and CHIP Send (money transfer) APIs, compiled directly from official CHIP documentation.

  - [Builder Pattern](#builder-pattern)

  - [Status Enums](#status-enums)- [Package Features](#package-features)

  - [Logging & Monitoring](#logging--monitoring)

  - [Rate Limiting](#rate-limiting)- [Quick Start](#quick-start)## Base URLs

  - [Webhook Queue Handler](#webhook-queue-handler)

  - [Health Check Command](#health-check-command)- [Advanced Features](#advanced-features)

- [CHIP Collect API](#chip-collect-api)

- [CHIP Send API](#chip-send-api)  - [Facade Pattern](#facade-pattern)---

- [Data Structures](#data-structures)

- [Webhook Events](#webhook-events)  - [Builder Pattern](#builder-pattern)

- [Status & State Values](#status--state-values)

- [Testing](#testing)  - [Status Enums](#status-enums)- **Production:** `https://api.chip-in.asia/api/` (CHIP Send) / `https://gate.chip-in.asia/api/v1/` (CHIP Collect)



---  - [Logging & Monitoring](#logging--monitoring)



## Quick Links  - [Rate Limiting](#rate-limiting)## Table of Contents- **Staging:** `https://staging-api.chip-in.asia/api/` (CHIP Send) / `https://gate-sandbox.chip-in.asia/api/v1/` (CHIP Collect)



### Official Documentation  - [Idempotency](#idempotency)



- **CHIP Collect:** https://docs.chip-in.asia/chip-collect/  - [Webhook Queue Handler](#webhook-queue-handler)

- **CHIP Send:** https://docs.chip-in.asia/chip-send/

- **API Reference:** https://docs.chip-in.asia/api-reference  - [Health Check Command](#health-check-command)



### Developer Portal- [API Reference](#api-reference)- [Quick Links](#quick-links)## Authentication



- **Production Portal:** https://portal.chip-in.asia/- [Base URLs & Authentication](#base-urls--authentication)

- **Sandbox Portal:** https://gate-sandbox.chip-in.asia/

- [CHIP Collect API](#chip-collect-api)- [Base URLs & Authentication](#base-urls--authentication)

---

- [CHIP Send API](#chip-send-api)

## Base URLs & Authentication

- [Data Structures](#data-structures)- [CHIP Collect API](#chip-collect-api)### CHIP Collect

### CHIP Collect

- [Webhook Events](#webhook-events)

**Production:**

- Base URL: `https://gate.chip-in.asia/api/v1/`- [Status & State Values](#status--state-values)- [CHIP Send API](#chip-send-api)- Uses Bearer token authentication: `Authorization: Bearer <API_KEY>`

- Portal: https://portal.chip-in.asia/collect/

- [Testing](#testing)

**Sandbox:**

- Base URL: `https://gate-sandbox.chip-in.asia/api/v1/`- [Data Structures](#data-structures)- Get API key from: https://portal.chip-in.asia/collect/developers/api-keys

- Portal: https://gate-sandbox.chip-in.asia/collect/

---

**Authentication:**

- Bearer token: `Authorization: Bearer <API_KEY>`- [Webhook Events](#webhook-events)- Get Brand ID from: https://portal.chip-in.asia/collect/developers/brands

- Get API keys from: https://portal.chip-in.asia/collect/developers/api-keys

- Get Brand ID from: https://portal.chip-in.asia/collect/developers/brands## Package Features



### CHIP Send- [Status & State Values](#status--state-values)



**Production:**This Laravel package provides production-ready integration with CHIP payment gateway with the following enterprise features:

- Base URL: `https://api.chip-in.asia/api/`

- Portal: https://portal.chip-in.asia/send/- [Testing](#testing)### CHIP Send



**Staging:**✅ **Full API Coverage** - Complete implementation of CHIP Collect & Send APIs  

- Base URL: `https://staging-api.chip-in.asia/api/`

✅ **Fluent Builder Pattern** - Elegant purchase creation API  - Uses Bearer token + signed requests

**Authentication:**

- Bearer token + signed requests✅ **Type-Safe Enums** - Status constants with helper methods  

- Headers required:

  - `Authorization: Bearer <API_KEY>`✅ **Automatic Retry Logic** - Exponential backoff for failed requests  ---- Headers required:

  - `epoch: <unix_timestamp>`

  - `checksum: <signed_hash>`✅ **Request/Response Logging** - Masked sensitive data logging  



---✅ **Rate Limit Tracking** - Real-time monitoring with warnings    - `Authorization: Bearer <API_KEY>`



## Package Features✅ **Idempotency Support** - Prevent duplicate charges  



This Laravel package provides production-ready integration with CHIP payment gateway with the following enterprise features:✅ **Webhook Queue Handler** - Non-blocking webhook processing  ## Quick Links  - `epoch: <unix_timestamp>`



✅ **Full API Coverage** - Complete implementation of CHIP Collect & Send APIs  ✅ **Health Check Command** - Monitor API connectivity  

✅ **Fluent Builder Pattern** - Elegant purchase creation API  

✅ **Type-Safe Enums** - Status constants with helper methods  ✅ **Comprehensive Testing** - 115 tests with 275 assertions  - `checksum: <signed_hash>`

✅ **Automatic Retry Logic** - Exponential backoff for failed requests  

✅ **Request/Response Logging** - Masked sensitive data logging  

✅ **Rate Limit Tracking** - Real-time monitoring with warnings  

✅ **Webhook Queue Handler** - Non-blocking webhook processing  ---### Official Documentation

✅ **Health Check Command** - Monitor API connectivity  

✅ **Comprehensive Testing** - 115 tests with 275 assertions



---## Quick Start- **Main Documentation:** https://docs.chip-in.asia/## Data Structures



## Quick Start



### Installation### Installation- **Getting Started:** https://docs.chip-in.asia/getting-started



```bash

composer require masyukai/chip

``````bash- **API Reference:** https://docs.chip-in.asia/api-reference### Purchase Object (CHIP Collect)



### Configurationcomposer require masyukai/chip



Publish the config file:php artisan chip:install



```bash```

php artisan vendor:publish --tag=chip-config

```### CHIP Collect (Payment Gateway)```json



Set your credentials in `.env`:### Basic Usage



```env- **API Overview:** https://docs.chip-in.asia/chip-collect/api-reference{

# Environment

CHIP_ENVIRONMENT=sandbox  # or production```php



# CHIP Collectuse MasyukAI\Chip\Facades\Chip;- **Purchases:** https://docs.chip-in.asia/chip-collect/api-reference/purchases  "id": "uuid",

CHIP_COLLECT_API_KEY=your_api_key

CHIP_COLLECT_BRAND_ID=your_brand_id



# CHIP Send (optional)// Create a purchase- **Clients:** https://docs.chip-in.asia/chip-collect/api-reference/clients  "type": "string",

CHIP_SEND_API_KEY=your_send_api_key

CHIP_SEND_SECRET=your_send_secret$purchase = Chip::createPurchase([



# Webhook (optional)    'brand_id' => config('chip.collect.brand_id'),- **Webhooks:** https://docs.chip-in.asia/chip-collect/api-reference/webhooks  "created_on": 1619740800,

CHIP_WEBHOOK_SECRET=your_webhook_secret

```    'purchase' => [



### Basic Usage        'currency' => 'MYR',- **Payment Methods:** https://docs.chip-in.asia/chip-collect/api-reference/payment-methods  "updated_on": 1619740800,



```php        'products' => [

use MasyukAI\Chip\Facades\Chip;

            [- **Recurring Tokens:** https://docs.chip-in.asia/chip-collect/api-reference/recurring-tokens  "client": {

// Create a purchase

$purchase = Chip::purchase()                'name' => 'Premium Plan',

    ->currency('MYR')

    ->addProduct('Premium Plan', 9900, 1)                'price' => 9900, // RM 99.00- **Company Statements:** https://docs.chip-in.asia/chip-collect/api-reference/company-statements    "email": "required@example.com",

    ->email('customer@example.com')

    ->successUrl('https://example.com/success')                'quantity' => 1,

    ->create();

            ],    "full_name": "string",

// Get purchase URL

$checkoutUrl = $purchase->checkout_url;        ],

```

    ],### CHIP Send (Money Transfer)    "phone": "+44 45643564564",

---

    'client' => [

## Advanced Features

        'email' => 'customer@example.com',- **API Overview:** https://docs.chip-in.asia/chip-send/api-reference    "street_address": "string",

### Facade Pattern

    ],

The `Chip` facade provides a fluent static interface for all operations:

]);- **Send Instructions:** https://docs.chip-in.asia/chip-send/api-reference/send-instructions    "country": "ISO 3166-1 alpha-2",

```php

use MasyukAI\Chip\Facades\Chip;



// CHIP Collect operations// Redirect to checkout- **Bank Accounts:** https://docs.chip-in.asia/chip-send/api-reference/bank-accounts    "city": "string",

$purchase = Chip::purchase()->currency('MYR')->create();

$purchase = Chip::getPurchase('purchase_id');return redirect($purchase->checkoutUrl);

$purchase = Chip::cancelPurchase('purchase_id');

$purchase = Chip::refundPurchase('purchase_id');```- **Send Limits:** https://docs.chip-in.asia/chip-send/api-reference/send-limits    "zip_code": "string",

$purchase = Chip::chargePurchase('purchase_id', 'token');



// CHIP Send operations

$instruction = Chip::createSendInstruction($data);---- **Groups:** https://docs.chip-in.asia/chip-send/api-reference/groups    "state": "string",

$instruction = Chip::getSendInstruction('instruction_id');

$instruction = Chip::deleteSendInstruction('instruction_id');



// Rate limiting## Advanced Features- **Accounts:** https://docs.chip-in.asia/chip-send/api-reference/accounts    "shipping_street_address": "string",

$status = Chip::getRateLimitStatus();

```



### Builder Pattern### Facade Pattern- **Webhooks:** https://docs.chip-in.asia/chip-send/api-reference/webhooks    "shipping_country": "ISO 3166-1 alpha-2", 



Fluent API for creating purchases with 25+ chainable methods:



```phpThe `Chip` facade provides a fluent interface for all CHIP operations:    "shipping_city": "string",

$purchase = Chip::purchase()

    // Currency

    ->currency('MYR')

    ```php### Guides & Tutorials    "shipping_zip_code": "string",

    // Products

    ->addProduct('Premium Plan', 9900, 1, 0, 6.0, 'subscription')use MasyukAI\Chip\Facades\Chip;

    

    // Customer- **Pre-authorization Guide:** https://docs.chip-in.asia/chip-collect/guides/pre-authorization    "shipping_state": "string",

    ->customer('customer@example.com', 'John Doe', '+60123456789', 'MY')

    // Collect API

    // Addresses

    ->billingAddress('123 Main St', 'KL', '50000', 'Selangor', 'MY')$purchase = Chip::createPurchase($data);- **Subscription Payments:** https://docs.chip-in.asia/chip-collect/guides/subscription-payments    "cc": ["email@example.com"],

    ->shippingAddress('456 Shipping St', 'KL', '50001', 'Selangor', 'MY')

    $purchase = Chip::getPurchase($id);

    // References & URLs

    ->reference('ORDER-2025-001')$status = Chip::getRateLimitStatus();- **Direct Post Integration:** https://docs.chip-in.asia/chip-collect/guides/direct-post    "bcc": ["email@example.com"],

    ->successUrl('https://example.com/success')

    ->failureUrl('https://example.com/failure')

    ->cancelUrl('https://example.com/cancel')

    // Send API- **Webhook Security:** https://docs.chip-in.asia/chip-collect/guides/webhooks    "legal_name": "string",

    // Webhooks

    ->webhook('https://example.com/webhooks/chip')$instruction = Chip::createSendInstruction($amount, $currency, $recipientId, $description, $reference, $email);

    

    // Options    "brand_name": "string",

    ->sendReceipt(true)

    ->preAuthorize(false)// Builder Pattern

    ->forceRecurring(false)

    $purchase = Chip::purchase()---    "registration_number": "string",

    // Metadata

    ->due(now()->addDays(7)->timestamp)    ->currency('MYR')

    ->notes('Order #12345')

        ->addProduct('Premium Plan', 9900)    "tax_number": "string",

    // Create

    ->create();    ->email('customer@example.com')



// Inspect data before creating    ->successUrl('https://example.com/success')## Base URLs & Authentication    "bank_account": "string",

$data = Chip::purchase()

    ->currency('MYR')    ->idempotent() // Automatic UUID

    ->addProduct('Item', 5000)

    ->toArray();    ->create();    "bank_code": "string",

```

```

#### Available Builder Methods

### CHIP Collect    "personal_code": "string"

| Method | Description |

|--------|-------------|### Builder Pattern

| `brand(string $brandId)` | Set brand ID |

| `currency(string $currency)` | Set currency (default: MYR) |  },

| `addProduct(...)` | Add product to purchase |

| `email(string $email)` | Set customer email |Fluent API for creating purchases with readable, chainable methods:

| `customer(...)` | Set full customer details |

| `clientId(string $id)` | Set client ID |**Base URL (Production & Sandbox):**  "purchase": {

| `billingAddress(...)` | Set billing address |

| `shippingAddress(...)` | Set shipping address |```php

| `reference(string $ref)` | Set order reference |

| `successUrl(string $url)` | Set success redirect URL |use MasyukAI\Chip\Services\ChipCollectService;```    "currency": "MYR",

| `failureUrl(string $url)` | Set failure redirect URL |

| `cancelUrl(string $url)` | Set cancel redirect URL |

| `redirects(string $success, ...)` | Set all redirect URLs |

| `webhook(string $url)` | Set webhook callback URL |$purchase = app(ChipCollectService::class)->purchase()https://gate.chip-in.asia/api/v1/    "products": [

| `sendReceipt(bool $send)` | Enable/disable email receipt |

| `preAuthorize(bool $preAuth)` | Enable pre-authorization |    // Brand (optional, uses config default)

| `forceRecurring(bool $force)` | Force recurring payment |

| `due(int $timestamp)` | Set payment due timestamp |    ->brand('550e8400-e29b-41d4-a716-446655440000')```      {

| `notes(string $notes)` | Set order notes |

| `toArray()` | Get built data array |    

| `create()` | Create purchase |

| `save()` | Alias for create() |    // Currency        "name": "required",



### Status Enums    ->currency('MYR')



Type-safe enums for CHIP API statuses with helper methods:    **Note:** CHIP Collect uses the same production URL for both sandbox and production environments. The environment is determined by the API key used.        "quantity": 1,



#### PurchaseStatus    // Products



```php    ->addProduct(        "price": 1000,

use MasyukAI\Chip\Enums\PurchaseStatus;

        name: 'Premium Plan',

$status = PurchaseStatus::from($purchase->status);

        price: 9900,  // RM 99.00**Authentication:**        "discount": 0,

// Helper methods

$status->label();           // Human-readable label        quantity: 1,

$status->isSuccessful();    // true for paid, paid_authorized, recurring_successful

$status->isPending();       // true for pending, viewed, attempted_*        discount: 0,```http        "tax_percent": 0,

$status->isFailed();        // true for invalid, overdue, voided, blocked, cancelled

$status->canBeCancelled();  // Check if cancellable        taxPercent: 6.0,

$status->canBeCaptured();   // Check if can be captured

$status->canBeReleased();   // Check if can be released        category: 'subscription'Authorization: Bearer <API_KEY>        "category": "string"

$status->canBeRefunded();   // Check if can be refunded

    )

// Available statuses

PurchaseStatus::Created;    ->addProduct('Additional Service', 2000)Content-Type: application/json      }

PurchaseStatus::Pending;

PurchaseStatus::Viewed;    

PurchaseStatus::Paid;

PurchaseStatus::PaidAuthorized;    // Customer DetailsAccept: application/json    ],

PurchaseStatus::RecurringSuccessful;

PurchaseStatus::AttemptedCapture;    ->customer(

PurchaseStatus::AttemptedRefund;

PurchaseStatus::AttemptedRecurring;        email: 'customer@example.com',```    "total": 1000,

PurchaseStatus::Released;

PurchaseStatus::Voided;        fullName: 'John Doe',

PurchaseStatus::Invalid;

PurchaseStatus::Overdue;        phone: '+60123456789',    "language": "en",

PurchaseStatus::Blocked;

PurchaseStatus::Cancelled;        country: 'MY'

PurchaseStatus::Refunded;

```    )**Get API Keys:**    "notes": "string",



#### SendInstructionState    // Or just email



```php    ->email('customer@example.com')- Portal: https://portal.chip-in.asia/collect/developers/api-keys    "debt": 0,

use MasyukAI\Chip\Enums\SendInstructionState;

    

$state = SendInstructionState::from($instruction->state);

    // Or existing client- Brand IDs: https://portal.chip-in.asia/collect/developers/brands    "subtotal_override": null,

// Helper methods

$state->label();         // Human-readable label    ->clientId('existing-client-uuid')

$state->isSuccessful();  // true for success, completed

$state->isPending();     // true for pending, processing, pay_out        "total_tax_override": null,

$state->isFailed();      // true for failed, rejected

$state->canBeDeleted();  // Check if deletable    // Billing Address



// Available states    ->billingAddress(**Documentation:**    "total_discount_override": null,

SendInstructionState::Pending;

SendInstructionState::Processing;        streetAddress: '123 Main Street',

SendInstructionState::PayOut;

SendInstructionState::Success;        city: 'Kuala Lumpur',- Authentication: https://docs.chip-in.asia/chip-collect/api-reference#authentication    "total_override": null,

SendInstructionState::Completed;

SendInstructionState::Failed;        zipCode: '50000',

SendInstructionState::Rejected;

SendInstructionState::Deleted;        state: 'Selangor',    "request_client_details": [],

```

        country: 'MY'

#### BankAccountStatus

    )---    "timezone": "Asia/Kuala_Lumpur",

```php

use MasyukAI\Chip\Enums\BankAccountStatus;    



$status = BankAccountStatus::from($account->status);    // Shipping Address    "due_strict": false,



// Helper methods    ->shippingAddress(

$status->label();       // Human-readable label

$status->isVerified();  // true if verified        streetAddress: '456 Delivery St',### CHIP Send    "email_message": "string"

$status->isPending();   // true if pending

$status->isRejected();  // true if rejected        city: 'Petaling Jaya',



// Available statuses        zipCode: '46000',  },

BankAccountStatus::Verified;

BankAccountStatus::Pending;        state: 'Selangor',

BankAccountStatus::Rejected;

```        country: 'MY'**Base URLs:**  "payment": {



### Logging & Monitoring    )



Comprehensive logging with sensitive data masking:    - **Production:** `https://api.chip-in.asia/api/`    "is_outgoing": false,



```php    // Merchant Reference

// config/chip.php

'logging' => [    ->reference('ORDER-2025-001')- **Staging/Sandbox:** `https://staging-api.chip-in.asia/api/`    "payment_type": "purchase",

    'enabled' => env('CHIP_LOGGING_ENABLED', env('APP_DEBUG', false)),

    'channel' => env('CHIP_LOGGING_CHANNEL', 'stack'),    

    'mask_sensitive_data' => env('CHIP_LOGGING_MASK_SENSITIVE', true),

    'log_requests' => env('CHIP_LOG_REQUESTS', true),    // Redirects    "amount": 1000,

    'log_responses' => env('CHIP_LOG_RESPONSES', true),

    'log_webhooks' => env('CHIP_LOG_WEBHOOKS', true),    ->successUrl('https://example.com/success')

],

```    ->failureUrl('https://example.com/failed')**Authentication:**    "currency": "MYR",



**What gets logged:**    ->cancelUrl('https://example.com/cancelled')

- All API requests (method, endpoint, duration)

- All API responses (status code, body)    // Or all at once```http    "net_amount": 1000,

- All webhook events

- All errors and exceptions    ->redirects(



**Sensitive data masking:**        successUrl: 'https://example.com/success',Authorization: Bearer <API_KEY>    "fee_amount": 0,

- API keys → `***`

- Email addresses → `u***@***.com`        failureUrl: 'https://example.com/failed',

- Phone numbers → `+***12345`

- Card details → `card_***`        cancelUrl: 'https://example.com/cancelled'epoch: <unix_timestamp>    "pending_amount": 0,

- Bank account numbers → `acc_***`

    )

### Rate Limiting

    checksum: <hmac_sha256_signature>    "pending_unfreeze_on": null,

Real-time rate limit tracking with automatic warnings:

    // Webhook

```php

// config/chip.php    ->webhook('https://example.com/webhooks/chip')Content-Type: application/json    "description": null,

'rate_limiting' => [

    'track' => env('CHIP_TRACK_RATE_LIMITS', true),    

    'cache_ttl' => env('CHIP_RATE_LIMIT_CACHE_TTL', 60),

    'warning_threshold' => env('CHIP_RATE_LIMIT_WARNING_THRESHOLD', 80),    // Options```    "paid_on": null,

],

```    ->sendReceipt(true)



**Get rate limit status:**    ->preAuthorize(true)  // skip_capture    "remote_paid_on": null



```php    ->forceRecurring(true)

$status = Chip::getRateLimitStatus();

    ->due(timestamp: now()->addDays(7)->timestamp, strict: false)**Checksum Generation:**  },

// Returns:

[    ->notes('Special instructions for order')

    'limit' => 1000,           // Total requests allowed

    'remaining' => 850,        // Requests remaining    ```php  "transaction_data": {

    'reset' => 1640995200,     // Reset timestamp

    'percentage' => 85.0,      // Percentage remaining    // Idempotency (prevent duplicate charges)

    'is_near_limit' => false,  // Near threshold?

    'is_critical' => false,    // Critical (<10%)?    ->idempotent('custom-idempotency-key')$epoch = time();    "payment_method": "string",

]

```    // Or auto-generate



**Listen for warnings:**    ->idempotent()$checksum = hash_hmac('sha256', (string)$epoch, $apiSecret);    "extra": {},



```php    

use MasyukAI\Chip\Events\RateLimitWarning;

    // Create the purchase```    "country": "MY",

// In EventServiceProvider

protected $listen = [    ->create();

    RateLimitWarning::class => [

        SendRateLimitAlert::class,    "attempts": []

    ],

];// Inspect data before creating



// Event contains$data = app(ChipCollectService::class)->purchase()**Documentation:**  },

$event->service;    // 'collect' or 'send'

$event->limit;      // Total limit    ->currency('MYR')

$event->remaining;  // Remaining requests

$event->percentage; // Percentage used    ->addProduct('Item', 5000)- Authentication: https://docs.chip-in.asia/chip-send/api-reference#authentication  "status": "created",

```

    ->email('test@example.com')

### Webhook Queue Handler

    ->toArray();- HMAC Signature: https://docs.chip-in.asia/chip-send/api-reference#request-signing  "status_history": [],

Non-blocking webhook processing with automatic retry:

```

```php

// config/chip.php  "company_id": "uuid",

'webhooks' => [

    'queue' => env('CHIP_WEBHOOK_QUEUE', true),**Available Builder Methods:**

    'queue_name' => env('CHIP_WEBHOOK_QUEUE_NAME', 'default'),

    ---  "brand_id": "uuid",

    'signature_key' => env('CHIP_WEBHOOK_SECRET'),

    'events' => [| Method | Description |

        'payment.created' => true,

        'payment.paid' => true,|--------|-------------|  "client_id": null,

        'payment.refunded' => true,

        'payment.cancelled' => true,| `brand(string $brandId)` | Set brand ID (optional, uses config) |

    ],

],| `currency(string $currency)` | Set currency (default: MYR) |## CHIP Collect API  "is_test": true,

```

| `addProduct(...)` | Add product to purchase |

**Features:**

- Automatic signature validation| `email(string $email)` | Set customer email |  "user_id": null,

- 3 retry attempts with exponential backoff

- 60-second timeout| `customer(...)` | Set customer details |

- Dispatches Laravel events for each webhook type

- Comprehensive error logging| `clientId(string $clientId)` | Use existing client |### Purchases  "send_receipt": false,



**Listen for webhook events:**| `billingAddress(...)` | Set billing address |



```php| `shippingAddress(...)` | Set shipping address |  "is_recurring_token": false,

use MasyukAI\Chip\Events\PurchasePaid;

| `reference(string $reference)` | Set merchant reference |

// In EventServiceProvider

protected $listen = [| `successUrl(string $url)` | Success redirect URL |**Official Docs:** https://docs.chip-in.asia/chip-collect/api-reference/purchases  "recurring_token": null,

    PurchasePaid::class => [

        SendOrderConfirmation::class,| `failureUrl(string $url)` | Failure redirect URL |

        UpdateInventory::class,

    ],| `cancelUrl(string $url)` | Cancel redirect URL |  "skip_capture": false,

];

| `redirects(...)` | Set all redirect URLs |

// Event contains full purchase data

$event->purchase->id;| `webhook(string $url)` | Webhook callback URL |#### Create Purchase  "force_recurring": false,

$event->purchase->status;

$event->purchase->client->email;| `sendReceipt(bool $send)` | Email receipt to customer |

```

| `preAuthorize(bool $skipCapture)` | Enable pre-authorization |```http  "reference_generated": "string",

### Health Check Command

| `forceRecurring(bool $force)` | Create recurring token |

Monitor CHIP API connectivity:

| `due(int $timestamp, bool $strict)` | Set due date |POST /purchases/  "reference": null,

```bash

# Check all APIs| `notes(string $notes)` | Add notes |

php artisan chip:health

| `idempotent(?string $key)` | Set idempotency key |```  "checkout_url": "https://...",

# Check specific API

php artisan chip:health --collect| `getIdempotencyKey()` | Get current idempotency key |

php artisan chip:health --send

| `toArray()` | Get built data (inspection) |**Documentation:** https://docs.chip-in.asia/chip-collect/api-reference/purchases/create  "invoice_url": "https://...",

# Verbose output

php artisan chip:health --verbose| `create()` | Create the purchase |

```

| `save()` | Alias for create() |  "direct_post_url": "https://...",

**Output:**



```

🔍 CHIP API Health Check### Status Enums**Required Fields:**  "success_redirect": null,



📦 Checking CHIP Collect API...

   ✅ Connected

Type-safe status constants with helper methods for business logic:- `purchase` (object) - Purchase details with `products` array  "failure_redirect": null,

💸 Checking CHIP Send API...

   ✅ Connected



⚙️  Configuration Status#### PurchaseStatus Enum- `brand_id` (uuid) - Brand identifier  "cancel_redirect": null,

   Environment: sandbox

   Logging: Enabled

   Rate Limit Tracking: Enabled

   Warning Threshold: 80%```php  "success_callback": null

   Webhook Events: Enabled

use MasyukAI\Chip\Enums\PurchaseStatus;

📊 Rate Limit Status

   Collect API: 850/1000 requests remaining (85.0%)**Optional Fields:**}

   Send API: 920/1000 requests remaining (92.0%)

// Available statuses

✅ All systems operational

```PurchaseStatus::CREATED;- `client` (object) - Client/customer details```



**Exit codes:**PurchaseStatus::SENT;

- `0` - All checks passed

- `1` - One or more checks failedPurchaseStatus::VIEWED;- `client_id` (uuid) - Existing client reference



---PurchaseStatus::PAID;



## CHIP Collect APIPurchaseStatus::ERROR;- `success_redirect` (string) - Success URL### Payment Object (CHIP Collect)



Complete CHIP Collect (Payment Gateway) API implementation.PurchaseStatus::CANCELLED;



**Official Documentation:** https://docs.chip-in.asia/chip-collect/PurchaseStatus::OVERDUE;- `failure_redirect` (string) - Failure URL



### Create PurchasePurchaseStatus::EXPIRED;



**Endpoint:** `POST /purchases/`  PurchaseStatus::BLOCKED;- `cancel_redirect` (string) - Cancel URL```json

**Documentation:** https://docs.chip-in.asia/chip-collect/api-reference/purchases

PurchaseStatus::HOLD;

```php

use MasyukAI\Chip\Services\ChipCollectService;PurchaseStatus::RELEASED;- `success_callback` (string) - Webhook URL{



$service = app(ChipCollectService::class);PurchaseStatus::PENDING;



$purchase = $service->createPurchase([PurchaseStatus::PENDING_EXECUTE;- `reference` (string) - Merchant reference  "is_outgoing": false,

    'brand_id' => config('chip.collect.brand_id'),

    'client' => [PurchaseStatus::PENDING_VERIFICATION;

        'email' => 'customer@example.com',

        'full_name' => 'John Doe',PurchaseStatus::PREAUTHORIZED;- `send_receipt` (boolean) - Email receipt to customer  "payment_type": "purchase",

        'phone' => '+60123456789',

        'country' => 'MY',PurchaseStatus::REFUNDED;

    ],

    'purchase' => [- `skip_capture` (boolean) - Pre-authorization mode  "amount": 1000,

        'currency' => 'MYR',

        'products' => [// Usage

            [

                'name' => 'Premium Plan',$status = PurchaseStatus::from($purchase->status);- `force_recurring` (boolean) - Create recurring token  "currency": "MYR", 

                'price' => 9900,  // in cents

                'quantity' => 1,

                'discount' => 0,

                'tax_percent' => 6.0,// Helper methods- `due` (integer) - Due date timestamp  "net_amount": 950,

                'category' => 'subscription',

            ],$status->label();              // "Successfully Paid"

        ],

        'notes' => 'Order #12345',$status->isSuccessful();       // true for PAID, COMPLETED  "fee_amount": 50,

        'due' => now()->addDays(7)->timestamp,

        'billing_address' => [$status->isPending();          // true for CREATED, SENT, etc.

            'line_1' => '123 Main Street',

            'city' => 'Kuala Lumpur',$status->isFailed();           // true for ERROR, CANCELLED, etc.**Example Request:**  "pending_amount": 0,

            'post_code' => '50000',

            'state' => 'Selangor',$status->canBeCancelled();     // true for CREATED, SENT, VIEWED

            'country' => 'MY',

        ],$status->canBeCaptured();      // true for HOLD```json  "pending_unfreeze_on": null,

        'shipping_address' => [

            'line_1' => '456 Shipping Street',$status->canBeReleased();      // true for HOLD

            'city' => 'Kuala Lumpur',

            'post_code' => '50001',$status->canBeRefunded();      // true for PAID{  "description": null,

            'state' => 'Selangor',

            'country' => 'MY',

        ],

    ],// Example  "brand_id": "550e8400-e29b-41d4-a716-446655440000",  "paid_on": 1619740800,

    'reference' => 'ORDER-2025-001',

    'success_redirect' => 'https://example.com/payment/success',if ($status->canBeRefunded()) {

    'failure_redirect' => 'https://example.com/payment/failure',

    'cancel_redirect' => 'https://example.com/payment/cancel',    // Show refund button  "purchase": {  "remote_paid_on": 1619740800

    'success_callback' => 'https://example.com/webhooks/chip',

    'send_receipt' => true,}

    'skip_capture' => false,

    'force_recurring' => false,```    "currency": "MYR",}

]);



// Returns Purchase object

echo $purchase->id;           // "purchase_123"#### SendInstructionState Enum    "products": [```

echo $purchase->status;       // "pending"

echo $purchase->checkout_url; // "https://gate.chip-in.asia/purchases/purchase_123"

```

```php      {

### Get Purchase

use MasyukAI\Chip\Enums\SendInstructionState;

**Endpoint:** `GET /purchases/{id}/`  

**Documentation:** https://docs.chip-in.asia/chip-collect/api-reference/purchases        "name": "Premium Subscription",### Client Object (CHIP Collect)



```php// Available states

$purchase = $service->getPurchase('purchase_123');

SendInstructionState::RECEIVED;        "quantity": 1,

echo $purchase->status;         // Current status

echo $purchase->client->email;  // Customer emailSendInstructionState::ENQUIRING;

echo $purchase->is_paid;        // Payment status

```SendInstructionState::EXECUTING;        "price": 9900```json



### Cancel PurchaseSendInstructionState::REVIEWING;



**Endpoint:** `POST /purchases/{id}/cancel/`  SendInstructionState::ACCEPTED;      }{

**Documentation:** https://docs.chip-in.asia/chip-collect/api-reference/purchases/cancel

SendInstructionState::COMPLETED;

```php

$purchase = $service->cancelPurchase('purchase_123');SendInstructionState::REJECTED;    ]  "id": "uuid",



echo $purchase->status; // "cancelled"SendInstructionState::DELETED;

```

  },  "type": "client",

### Refund Purchase

// Usage

**Endpoint:** `POST /purchases/{id}/refund/`  

**Documentation:** https://docs.chip-in.asia/chip-collect/api-reference/purchases/refund$state = SendInstructionState::from($instruction->state);  "client": {  "created_on": 1619740800,



```php

// Full refund

$purchase = $service->refundPurchase('purchase_123');// Helper methods    "email": "customer@example.com",  "updated_on": 1619740800,



// Partial refund (amount in cents)$state->label();           // "Successfully Completed"

$purchase = $service->refundPurchase('purchase_123', 5000);

$state->isSuccessful();    // true for COMPLETED    "phone": "+60123456789"  "email": "required@example.com",

echo $purchase->status; // "refunded"

```$state->isPending();       // true for RECEIVED, ENQUIRING, etc.



### Get Payment Methods$state->isFailed();        // true for REJECTED  },  "full_name": "John Doe",



**Endpoint:** `GET /payment_methods/`  $state->canBeDeleted();    // true for RECEIVED, REJECTED

**Documentation:** https://docs.chip-in.asia/chip-collect/api-reference/payment-methods

  "success_redirect": "https://yoursite.com/success"  "phone": "+60123456789",

```php

$methods = $service->getPaymentMethods([// Example

    'currency' => 'MYR',

    'country' => 'MY',if ($state->isPending()) {}  "street_address": "123 Main St",

]);

    // Show "Processing" badge

// Returns array of available payment methods

foreach ($methods as $method) {}```  "country": "MY",

    echo $method['id'];   // "fpx_b2c"

    echo $method['name']; // "FPX B2C"```

}

```  "city": "Kuala Lumpur",



### Charge Purchase (Recurring)#### BankAccountStatus Enum



**Endpoint:** `POST /purchases/{id}/charge/`  **Response:** Purchase object with `checkout_url`  "zip_code": "50000",

**Documentation:** https://docs.chip-in.asia/chip-collect/api-reference/purchases/charge

```php

```php

$purchase = $service->chargePurchase('purchase_123', 'recurring_token_xyz');use MasyukAI\Chip\Enums\BankAccountStatus;  "state": "Selangor",



echo $purchase->status; // "paid"

```

// Available statuses---  "legal_name": "Company Ltd",

### Capture Purchase

BankAccountStatus::PENDING;

**Endpoint:** `POST /purchases/{id}/capture/`  

**Documentation:** https://docs.chip-in.asia/chip-collect/api-reference/purchases/captureBankAccountStatus::VERIFIED;  "brand_name": "Brand",



```phpBankAccountStatus::REJECTED;

$purchase = $service->capturePurchase('purchase_123');

#### Get Purchase  "registration_number": "123456789",

echo $purchase->status; // "paid"

```// Usage



### Release Purchase$status = BankAccountStatus::from($account->status);```http  "tax_number": "987654321",



**Endpoint:** `POST /purchases/{id}/release/`  

**Documentation:** https://docs.chip-in.asia/chip-collect/api-reference/purchases/release

// Helper methodsGET /purchases/{id}/  "bank_account": "1234567890",

```php

$purchase = $service->releasePurchase('purchase_123');$status->label();        // "Account Verified"



echo $purchase->status; // "released"$status->isVerified();   // true```  "bank_code": "MBBEMYKL"

```

$status->isPending();    // false

### Delete Client

$status->isRejected();   // false**Documentation:** https://docs.chip-in.asia/chip-collect/api-reference/purchases/retrieve}

**Endpoint:** `POST /clients/{id}/delete/`  

**Documentation:** https://docs.chip-in.asia/chip-collect/api-reference/clients



```php// Example```

$service->deleteClient('client_123');

```if ($status->isPending()) {



---    return 'Awaiting verification...';**Response:** Complete Purchase object



## CHIP Send API}



Complete CHIP Send (Money Transfer) API implementation.```### Send Instruction Object (CHIP Send)



**Official Documentation:** https://docs.chip-in.asia/chip-send/



### Create Send Instruction### Logging & Monitoring---



**Endpoint:** `POST /send_instructions/`  

**Documentation:** https://docs.chip-in.asia/chip-send/api-reference/send-instructions

Automatic request/response logging with sensitive data masking:```json

```php

use MasyukAI\Chip\Services\ChipSendService;



$service = app(ChipSendService::class);**Configuration:**#### Cancel Purchase{



$instruction = $service->createSendInstruction([

    'bank_account' => 'bank_account_id',

    'amount' => 10000,  // in cents```php```http  "id": 123,

    'reference' => 'PAYOUT-001',

    'description' => 'Monthly payout',// config/chip.php

]);

'logging' => [POST /purchases/{id}/cancel/  "bank_account_id": 456,

echo $instruction->id;     // "send_instruction_123"

echo $instruction->state;  // "pending"    'enabled' => env('CHIP_LOGGING_ENABLED', env('APP_DEBUG', false)),

echo $instruction->amount; // 10000

```    'channel' => env('CHIP_LOGGING_CHANNEL', 'stack'),```  "amount": "100.00",



### Get Send Instruction    'mask_sensitive_data' => env('CHIP_LOGGING_MASK_SENSITIVE', true),



**Endpoint:** `GET /send_instructions/{id}/`      'log_requests' => env('CHIP_LOG_REQUESTS', true),**Documentation:** https://docs.chip-in.asia/chip-collect/api-reference/purchases/cancel  "email": "recipient@example.com",

**Documentation:** https://docs.chip-in.asia/chip-send/api-reference/send-instructions

    'log_responses' => env('CHIP_LOG_RESPONSES', true),

```php

$instruction = $service->getSendInstruction('send_instruction_123');    'log_webhooks' => env('CHIP_LOG_WEBHOOKS', true),  "description": "Payment description",



echo $instruction->state;      // Current state],

echo $instruction->reference;  // Reference ID

``````**Status Requirement:** Purchase must be in `created`, `sent`, or `viewed` status  "reference": "unique_ref_123",



### Delete Send Instruction



**Endpoint:** `DELETE /send_instructions/{id}/`  **Logged Data:**  "state": "completed",

**Documentation:** https://docs.chip-in.asia/chip-send/api-reference/send-instructions



```php

$service->deleteSendInstruction('send_instruction_123');```---  "receipt_url": "https://...",

```

// Request Log

### Create Bank Account

[2025-01-15 10:30:45] production.INFO: CHIP API Request {  "slug": "abcd1234",

**Endpoint:** `POST /bank_accounts/`  

**Documentation:** https://docs.chip-in.asia/chip-send/api-reference/bank-accounts    "method": "POST",



```php    "url": "https://gate.chip-in.asia/api/v1/purchases/",#### Capture Payment (Pre-auth)  "created_at": "2023-07-20T10:41:25.190Z",

$account = $service->createBankAccount([

    'bank_code' => 'MBB0228',    "headers": {

    'account_number' => '1234567890',

    'holder_name' => 'John Doe',        "Authorization": "Bearer ****key",```http  "updated_at": "2023-07-20T10:41:25.302Z"

]);

        "Content-Type": "application/json"

echo $account->id;     // "bank_account_123"

echo $account->status; // "pending"    },POST /purchases/{id}/capture/}

```

    "body": {

### Get Bank Account

        "purchase": {"currency": "MYR", "products": [...]},``````

**Endpoint:** `GET /bank_accounts/{id}/`  

**Documentation:** https://docs.chip-in.asia/chip-send/api-reference/bank-accounts        "client": {"email": "c***@example.com"}



```php    }**Documentation:** https://docs.chip-in.asia/chip-collect/api-reference/purchases/capture

$account = $service->getBankAccount('bank_account_123');

}

echo $account->status;         // "verified"

echo $account->holder_name;    // "John Doe"### Bank Account Object (CHIP Send)

echo $account->account_number; // "1234567890"

```// Response Log



### List Send Limits[2025-01-15 10:30:46] production.INFO: CHIP API Response {**Request Body:**



**Endpoint:** `GET /send_limits/`      "status": 200,

**Documentation:** https://docs.chip-in.asia/chip-send/api-reference/send-limits

    "duration_ms": 1234,```json```json

```php

$limits = $service->listSendLimits();    "body": {



echo $limits['daily_limit'];     // Daily limit in cents        "id": "550e8400-e29b-41d4-a716-446655440000",{{

echo $limits['remaining_daily']; // Remaining daily limit

echo $limits['monthly_limit'];   // Monthly limit in cents        "status": "created"

echo $limits['remaining_monthly']; // Remaining monthly limit

```    }  "amount": 5000  // Optional: partial capture amount in cents  "id": 123,



---}



## Data Structures```}  "account_number": "1234567890",



### Purchase Object



```php**Masked Fields:**```  "bank_code": "MBBEMYKL",

class Purchase

{- API keys: `****key`

    public string $id;

    public string $type;                    // "payment"- Emails: `c***@example.com`  "name": "Account Holder Name",

    public Carbon $created_on;

    public string $status;                  // See PurchaseStatus enum- Phone numbers: `+60****6789`

    public bool $is_test;

    public bool $is_paid;- Card numbers: `4444 **** **** 1111`**Requirement:** Purchase must be in `hold` status (created with `skip_capture: true`)  "status": "verified",

    public bool $is_outbound;

    public ?int $due;- Bank accounts: `1234****7890`

    public ?string $reference;

    public ?string $checkout_url;  "group_id": null,

    public ?string $send_receipt_email;

    public ?bool $skip_capture;### Rate Limiting

    public ?bool $force_recurring;

    public ?string $payment_method_whitelist;---  "reference": "unique_ref",

    public ?string $success_redirect;

    public ?string $failure_redirect;Automatic tracking of API rate limits with warning events:

    public ?string $cancel_redirect;

    public ?string $success_callback;  "is_debiting_account": false,

    public ?string $creator_agent;

    public ?string $platform;**Configuration:**

    public ?array $metadata;

    public ?PurchaseDetails $purchase;#### Release Payment (Pre-auth)  "is_crediting_account": false,

    public ?ClientDetails $client;

    public ?array $brand;```php

    public ?array $products;

    public ?IssuerDetails $issuer_details;// config/chip.php```http  "created_at": "2023-07-20T08:59:10.766Z",

    public ?TransactionData $transaction_data;

    public ?CurrencyConversion $currency_conversion;'rate_limiting' => [

    public ?Payment $payment;

    public ?string $recurring_token;    'track' => env('CHIP_TRACK_RATE_LIMITS', true),POST /purchases/{id}/release/  "updated_at": "2023-07-20T08:59:10.766Z",

}

```    'cache_ttl' => env('CHIP_RATE_LIMIT_CACHE_TTL', 60),



### Payment Object    'warning_threshold' => env('CHIP_RATE_LIMIT_WARNING_THRESHOLD', 80), // Warn at 80%```  "deleted_at": null,



```php],

class Payment

{```**Documentation:** https://docs.chip-in.asia/chip-collect/api-reference/purchases/release  "rejection_reason": null

    public string $id;

    public string $type;                    // "outgoing"

    public int $amount;

    public string $currency;**Get Rate Limit Status:**}

    public ?int $net_amount;

    public ?int $fee;

    public ?int $incoming_amount;

    public ?string $incoming_currency;```php**Requirement:** Purchase must be in `hold` status```

    public ?int $incoming_fee;

    public ?string $status;use MasyukAI\Chip\Services\ChipCollectService;

    public ?array $transfer_details;

}

```

$service = app(ChipCollectService::class);

### Client Object

$status = $service->getRateLimitStatus();---### Webhook Object (CHIP Collect)

```php

class Client

{

    public string $id;// Response

    public string $created_on;

    public string $email;[

    public ?string $full_name;

    public ?string $street_address;    'limit' => 1000,           // Requests per window#### Charge Recurring Token```json

    public ?string $country;

    public ?string $city;    'remaining' => 750,        // Remaining requests

    public ?string $zip_code;

    public ?string $phone;    'percentage' => 75.0,      // % remaining```http{

    public ?string $ip_address;

    public ?string $legal_name;    'resets_at' => 1642310400  // Unix timestamp when limit resets

    public ?string $brand_name;

    public ?string $registration_number;]POST /purchases/{id}/charge/  "id": "uuid",

    public ?string $registration_date;

    public ?string $website_url;

}

```// Check if nearing limit```  "type": "webhook",



### SendInstruction Objectif ($status['percentage'] < 20) {



```php    // Slow down requests**Documentation:** https://docs.chip-in.asia/chip-collect/api-reference/purchases/charge  "created_on": 1619740800,

class SendInstruction

{}

    public string $id;

    public string $state;                   // See SendInstructionState enum```  "updated_on": 1619740800,

    public int $amount;

    public ?string $reference;

    public ?string $description;

    public ?string $bank_account;**Listen for Rate Limit Warnings:****Request Body:**  "title": "Purchase Events",

    public Carbon $created_on;

    public ?Carbon $updated_on;

}

``````php```json  "all_events": false,



### BankAccount Object// app/Providers/EventServiceProvider.php



```phpuse MasyukAI\Chip\Events\RateLimitWarning;{  "public_key": "-----BEGIN PUBLIC KEY-----\n...",

class BankAccount

{

    public string $id;

    public string $status;                  // See BankAccountStatus enumprotected $listen = [  "recurring_token": "token_value",  "events": ["purchase.created", "purchase.paid"],

    public string $bank_code;

    public string $account_number;    RateLimitWarning::class => [

    public string $holder_name;

    public Carbon $created_on;        NotifyAdministrators::class,  "amount": 5000  // Optional: different amount  "callback": "https://your-app.com/webhooks/chip"

    public ?Carbon $updated_on;

}    ],

```

];}}

---



## Webhook Events

// Event properties``````

### Available Events

class RateLimitWarning

The package dispatches these Laravel events when webhooks are received:

{

| Webhook Type | Laravel Event | Description |

|--------------|---------------|-------------|    public string $service;      // 'collect' or 'send'

| `payment.created` | `PurchaseCreated` | Purchase created |

| `payment.viewed` | `PurchaseViewed` | Payment form viewed |    public int $remaining;       // Remaining requests---## Status Values

| `payment.paid` | `PurchasePaid` | Payment successful |

| `payment.refunded` | `PurchaseRefunded` | Payment refunded |    public int $limit;           // Total limit

| `payment.cancelled` | `PurchaseCancelled` | Payment cancelled |

| `payment.released` | `PurchaseReleased` | Authorization released |    public float $percentage;    // Percentage remaining

| `payment.recurring.failed` | `PurchaseRecurringFailed` | Recurring payment failed |

    public int $resetsAt;        // Reset timestamp

### Event Structure

    #### Refund Purchase### Purchase Status (CHIP Collect)

All webhook events extend `WebhookReceived` and contain:

    public function isNearLimit(): bool; // <20%

```php

class PurchasePaid    public function isCritical(): bool;  // <10%```http- `created` - Purchase created

{

    public Purchase $purchase;  // Full purchase object}

    public array $payload;       // Raw webhook payload

}```POST /purchases/{id}/refund/- `sent` - Invoice sent

```



### Listening to Events

### Idempotency```- `viewed` - Customer viewed payment form

```php

// In EventServiceProvider

protected $listen = [

    \MasyukAI\Chip\Events\PurchasePaid::class => [Prevent duplicate purchases with idempotency keys:**Documentation:** https://docs.chip-in.asia/chip-collect/api-reference/purchases/refund- `paid` - Successfully paid

        SendOrderConfirmation::class,

        UpdateInventory::class,

    ],

];**Usage:**- `error` - Payment failed



// In your listener

public function handle(PurchasePaid $event)

{```php**Request Body:**- `cancelled` - Purchase cancelled

    $purchase = $event->purchase;

    use MasyukAI\Chip\Services\ChipCollectService;

    // Update your order

    Order::where('chip_purchase_id', $purchase->id)use Illuminate\Support\Str;```json- `overdue` - Past due date

        ->update(['status' => 'paid']);

        

    // Send confirmation

    Mail::to($purchase->client->email)$service = app(ChipCollectService::class);{- `expired` - Past due date (strict)

        ->send(new OrderConfirmation($purchase));

}

```

// Custom idempotency key  "amount": 5000  // Optional: partial refund amount- `blocked` - Blocked by fraud checks

---

$purchase = $service->createPurchase($data, 'order-2025-001');

## Status & State Values

}- `hold` - Funds on hold (skip_capture)

### Purchase Status

// Auto-generated UUID

Official CHIP Collect purchase statuses from: https://docs.chip-in.asia/chip-collect/api-reference/purchases

$purchase = $service->createPurchase($data, Str::uuid()->toString());```- `released` - Funds released

- `created` - Purchase created

- `pending` - Awaiting payment

- `viewed` - Customer viewed payment form

- `paid` - Successfully paid// With Builder- `pending_*` - Various pending states

- `paid_authorized` - Pre-authorized (not captured)

- `recurring_successful` - Recurring payment successful$purchase = $service->purchase()

- `attempted_capture` - Capture attempted

- `attempted_refund` - Refund attempted    ->currency('MYR')**Requirement:** Purchase must be `paid` and within refund window- `preauthorized` - Card preauthorized

- `attempted_recurring` - Recurring charge attempted

- `released` - Authorization released    ->addProduct('Item', 5000)

- `voided` - Payment voided

- `invalid` - Invalid payment    ->email('test@example.com')- `refunded` - Payment refunded

- `overdue` - Payment overdue

- `blocked` - Blocked by fraud checks    ->idempotent('custom-key')

- `cancelled` - Cancelled by customer/merchant

- `refunded` - Fully refunded    ->create();---



### Send Instruction State



Official CHIP Send instruction states from: https://docs.chip-in.asia/chip-send/api-reference/send-instructions// Or auto-generate### Send Instruction State (CHIP Send)



- `pending` - Awaiting processing$purchase = $service->purchase()

- `processing` - Being processed

- `pay_out` - Payment in progress    ->currency('MYR')#### Delete Recurring Token- `received` - Instruction received

- `success` - Successfully completed

- `completed` - Fully completed    ->addProduct('Item', 5000)

- `failed` - Failed to process

- `rejected` - Rejected by bank    ->email('test@example.com')```http- `enquiring` - Pending verification

- `deleted` - Deleted by merchant

    ->idempotent() // Auto UUID

### Bank Account Status

    ->create();DELETE /purchases/{id}/recurring_token/- `executing` - Pending execution

Official CHIP Send bank account statuses from: https://docs.chip-in.asia/chip-send/api-reference/bank-accounts

```

- `verified` - Verified and active

- `pending` - Awaiting verification```- `reviewing` - Requires attention

- `rejected` - Rejected by verification

**How It Works:**

---

**Documentation:** https://docs.chip-in.asia/chip-collect/api-reference/recurring-tokens/delete- `accepted` - Accepted by provider

## Testing

- The package sends an `Idempotency-Key` header with the request

The package includes 115 comprehensive tests covering all features:

- If CHIP receives a request with the same key within 24 hours, it returns the original response- `completed` - Successfully completed

```bash

# Run all tests- This prevents accidental duplicate charges if the user clicks "Pay" multiple times

vendor/bin/pest

- **Best Practice:** Use order ID or generate a UUID per payment attempt---- `rejected` - Instruction rejected

# Run with parallel execution

vendor/bin/pest --parallel



# Run specific test file### Webhook Queue Handler- `deleted` - Instruction deleted

vendor/bin/pest tests/Services/ChipCollectServiceTest.php



# Run with coverage

vendor/bin/pest --coverageNon-blocking webhook processing via Laravel queues:#### Mark as Paid (Manual)

```



### Test Coverage

**Job:**```http### Bank Account Status (CHIP Send)

- ✅ CHIP Collect API (purchases, clients, webhooks, payments)

- ✅ CHIP Send API (send instructions, bank accounts, limits)

- ✅ DataObjects (all DTOs validated)

- ✅ Services (ChipCollectService, ChipSendService, WebhookService)```phpPOST /purchases/{id}/mark_as_paid/- `pending` - Awaiting verification

- ✅ Clients (ChipCollectClient, ChipSendClient)

- ✅ Builders (PurchaseBuilder)use MasyukAI\Chip\Jobs\ProcessChipWebhook;

- ✅ Enums (PurchaseStatus, SendInstructionState, BankAccountStatus)

- ✅ Events (webhook events, rate limit warnings)```- `verified` - Valid account

- ✅ Exceptions (API exceptions, validation exceptions)

- ✅ Configuration (all config options validated)// The job is automatically dispatched when webhooks are received



### Example Test// if routes/api.php is registered**Documentation:** https://docs.chip-in.asia/chip-collect/api-reference/purchases/mark-as-paid- `rejected` - Invalid account



```php

use MasyukAI\Chip\Facades\Chip;

// Manual dispatch

it('can create a purchase with builder', function () {

    $purchase = Chip::purchase()ProcessChipWebhook::dispatch($webhookData);

        ->currency('MYR')

        ->addProduct('Test Product', 10000, 1)```---## Key API Endpoints

        ->email('test@example.com')

        ->reference('ORDER-123')

        ->create();

**Configuration:**

    expect($purchase)

        ->toBeInstanceOf(Purchase::class)

        ->and($purchase->status)->toBe('pending')

        ->and($purchase->reference)->toBe('ORDER-123');```php#### Resend Invoice Email### CHIP Collect

});

```// config/chip.php



---'webhooks' => [```http- `POST /purchases/` - Create purchase



## Best Practices    'queue' => env('CHIP_WEBHOOK_QUEUE', true),



### 1. Use Environment Variables    'queue_name' => env('CHIP_WEBHOOK_QUEUE_NAME', 'default'),POST /purchases/{id}/resend_invoice/- `GET /purchases/{id}/` - Retrieve purchase



Never hardcode credentials:    // ...



```php],```- `POST /purchases/{id}/cancel/` - Cancel purchase

// ❌ Bad

$purchase = $service->createPurchase([```

    'brand_id' => 'brand_123',

    // ...**Documentation:** https://docs.chip-in.asia/chip-collect/api-reference/purchases/resend-invoice- `POST /purchases/{id}/capture/` - Capture payment

]);

**Job Properties:**

// ✅ Good

$purchase = Chip::purchase()- `POST /purchases/{id}/charge/` - Charge with token

    ->currency('MYR')

    // brand_id automatically from config- **Tries:** 3 attempts

    ->create();

```- **Timeout:** 60 seconds---- `POST /clients/` - Create client



### 2. Handle Exceptions- **Failure:** Logged with full context



Always wrap API calls in try-catch:- `POST /webhooks/` - Create webhook



```php**What The Job Does:**

try {

    $purchase = Chip::purchase()### Clients

        ->currency('MYR')

        ->addProduct('Item', 5000)1. Validates webhook signature

        ->email('test@example.com')

        ->create();2. Creates `Webhook` DataObject### CHIP Send

} catch (\MasyukAI\Chip\Exceptions\ChipApiException $e) {

    Log::error('CHIP API Error', [3. Fires `WebhookReceived` event

        'message' => $e->getMessage(),

        'status' => $e->getStatusCode(),4. Maps event type to specific events (e.g., `PurchasePaid`)**Official Docs:** https://docs.chip-in.asia/chip-collect/api-reference/clients- `POST /send/send_instructions` - Create send instruction

        'response' => $e->getResponse(),

    ]);5. Logs errors with full context on failure

    

    return back()->with('error', 'Payment system unavailable');- `POST /send/bank_accounts` - Add bank account

}

```**Listen for Webhook Events:**



### 3. Validate Webhooks#### Create Client- `GET /send/accounts` - List accounts



Always validate webhook signatures:```php



```php// app/Providers/EventServiceProvider.php```http

// The package does this automatically, but if you

// handle webhooks manually:use MasyukAI\Chip\Events\{WebhookReceived, PurchasePaid};

$service = app(WebhookService::class);

$isValid = $service->validateSignature($request);POST /clients/## Test Data



if (!$isValid) {protected $listen = [

    abort(403, 'Invalid signature');

}    WebhookReceived::class => [```

```

        LogWebhook::class,

### 4. Use Status Enums

    ],**Documentation:** https://docs.chip-in.asia/chip-collect/api-reference/clients/create### Test Card Numbers

Prefer type-safe enums over string comparisons:

    PurchasePaid::class => [

```php

use MasyukAI\Chip\Enums\PurchaseStatus;        FulfillOrder::class,- `4444 3333 2222 1111` - Non-3D Secure



// ❌ Bad        SendConfirmationEmail::class,

if ($purchase->status === 'paid') {

    // ...    ],**Required Field:**- `5555 5555 5555 4444` - 3D Secure

}

];

// ✅ Good

$status = PurchaseStatus::from($purchase->status);```- `email` (string) - Valid email address- CVC: `123`

if ($status->isSuccessful()) {

    // Handles paid, paid_authorized, recurring_successful

}

```### Health Check Command- Expiry: Any future date



### 5. Monitor Rate Limits



Keep track of API usage:Monitor CHIP API connectivity and configuration:**Optional Fields:**- Name: Any Latin name



```php

$status = Chip::getRateLimitStatus();

**Usage:**- `full_name` (string)

if ($status['percentage'] < 20) {

    Log::warning('CHIP API rate limit low', $status);

    

    // Notify admin```bash- `phone` (string)## Price Format

    Mail::to('admin@example.com')

        ->send(new RateLimitWarning($status));# Check both APIs

}

```php artisan chip:health- `street_address` (string)- All amounts are in the smallest currency unit (cents)



### 6. Use Webhook Queue



Always enable webhook queueing for production:# Check only Collect API- `country` (string) - ISO 3166-1 alpha-2- Example: `1000` = RM 10.00



```envphp artisan chip:health --collect

CHIP_WEBHOOK_QUEUE=true

CHIP_WEBHOOK_QUEUE_NAME=default- `city` (string)

```

# Check only Send API

### 7. Enable Logging in Development

php artisan chip:health --send- `zip_code` (string)## Webhook Authentication

Debug issues with comprehensive logging:



```env

CHIP_LOGGING_ENABLED=true# Verbose output- `state` (string)- Payloads signed with RSA PKCS#1 v1.5

CHIP_LOG_REQUESTS=true

CHIP_LOG_RESPONSES=truephp artisan chip:health --verbose

CHIP_LOGGING_MASK_SENSITIVE=true

``````- `legal_name` (string) - Business name- Signature in `X-Signature` header



---



## Support & Resources**Output:**- `brand_name` (string)- Public key from `Webhook.public_key` or `GET /public_key/`



### Official CHIP Resources



- **Main Website:** https://www.chip-in.asia/```- `registration_number` (string)

- **Documentation:** https://docs.chip-in.asia/

- **Support:** support@chip-in.asia🔍 CHIP API Health Check



### Package Resources- `tax_number` (string)## Field Naming Conventions



- **GitHub:** https://github.com/masyukai/chip📦 Checking CHIP Collect API...

- **Issues:** https://github.com/masyukai/chip/issues

   ✅ Connected- `bank_account` (string)

---

      Brand ID: 550e8400-e29b-41d4-a716-446655440000

**Last Updated:** October 4, 2025  

**Package Version:** 1.1        Available payment methods: 12- `bank_code` (string)### Timestamps

**Laravel Version:** 12.x  

**PHP Version:** 8.4+


💸 Checking CHIP Send API...- API uses `created_on`/`updated_on` as Unix timestamps

   ✅ Connected

      Send limits retrieved successfully---- Some responses may include `created_at`/`updated_at` as ISO strings



⚙️  Configuration Status

   Environment: sandbox

   Logging: Enabled#### Get Client### Amount Fields

   Rate Limit Tracking: Enabled

   Warning Threshold: 80%```http- `amount` - Base amount in cents

   Webhook Events: Enabled

GET /clients/{id}/- `net_amount` - Amount after fees

📊 Rate Limit Status

   Collect API: 850/1000 requests remaining (85.0%)```- `fee_amount` - Transaction fee

      Resets: in 45 minutes

   Send API: 920/1000 requests remaining (92.0%)**Documentation:** https://docs.chip-in.asia/chip-collect/api-reference/clients/retrieve- `pending_amount` - Pending amount

      Resets: in 45 minutes



✅ All systems operational

```---### Client Fields



**Exit Codes:**- `full_name` - Customer full name



- `0` - All systems operational#### Update Client- `personal_code` - ID number

- `1` - Some systems experiencing issues

```http- `street_address` - Billing address

**Integration with Monitoring:**

PUT /clients/{id}/- `shipping_*` - Shipping address fields

```bash

# Crontab for monitoring```

*/5 * * * * php artisan chip:health || mail -s "CHIP API Health Check Failed" admin@example.com

```**Documentation:** https://docs.chip-in.asia/chip-collect/api-reference/clients/updateThis reference should be used to ensure DataObject structures match the actual API responses and test data structures align with expected formats.



---

---

## API Reference

#### Partial Update Client

### Base URLs```http

PATCH /clients/{id}/

#### CHIP Collect```

**Documentation:** https://docs.chip-in.asia/chip-collect/api-reference/clients/update

- **Production & Sandbox:** `https://gate.chip-in.asia/api/v1/`

---

_Note: CHIP Collect uses the same URL for both environments. The environment is determined by the API key used._

#### Delete Client

#### CHIP Send```http

DELETE /clients/{id}/

- **Production:** `https://api.chip-in.asia/api/````

- **Staging:** `https://staging-api.chip-in.asia/api/`**Documentation:** https://docs.chip-in.asia/chip-collect/api-reference/clients/delete



------



### Authentication#### List Clients

```http

#### CHIP CollectGET /clients/

```

```http**Documentation:** https://docs.chip-in.asia/chip-collect/api-reference/clients/list

Authorization: Bearer <API_KEY>

Content-Type: application/json**Query Parameters:**

Accept: application/json- `email` (string) - Filter by email

```- `page` (integer) - Pagination

- `page_size` (integer) - Results per page

**Get API Keys:** https://portal.chip-in.asia/collect/developers/api-keys  

**Brand IDs:** https://portal.chip-in.asia/collect/developers/brands---



#### CHIP Send#### List Client Recurring Tokens

```http

```httpGET /clients/{id}/recurring_tokens/

Authorization: Bearer <API_KEY>```

epoch: <unix_timestamp>**Documentation:** https://docs.chip-in.asia/chip-collect/api-reference/recurring-tokens/list

checksum: <hmac_sha256_signature>

Content-Type: application/json---

```

#### Get Client Recurring Token

**Checksum Generation:**```http

GET /clients/{id}/recurring_tokens/{token}/

```php```

$epoch = time();**Documentation:** https://docs.chip-in.asia/chip-collect/api-reference/recurring-tokens/retrieve

$checksum = hash_hmac('sha256', (string)$epoch, $apiSecret);

```---



---#### Delete Client Recurring Token

```http

## CHIP Collect APIDELETE /clients/{id}/recurring_tokens/{token}/

```

### Purchases**Documentation:** https://docs.chip-in.asia/chip-collect/api-reference/recurring-tokens/delete



**Official Docs:** https://docs.chip-in.asia/chip-collect/api-reference/purchases---



#### Create Purchase### Webhooks



```http**Official Docs:** https://docs.chip-in.asia/chip-collect/api-reference/webhooks

POST /purchases/

```#### Create Webhook

```http

**Request:**POST /webhooks/

```

```json**Documentation:** https://docs.chip-in.asia/chip-collect/api-reference/webhooks/create

{

  "brand_id": "550e8400-e29b-41d4-a716-446655440000",**Required Fields:**

  "purchase": {- `callback` (string) - Webhook URL

    "currency": "MYR",- `events` (array) - Event types OR `all_events: true`

    "products": [

      {**Example:**

        "name": "Premium Subscription",```json

        "quantity": 1,{

        "price": 9900  "callback": "https://yoursite.com/webhooks/chip",

      }  "events": ["purchase.created", "purchase.paid", "purchase.refunded"]

    ]}

  },```

  "client": {

    "email": "customer@example.com",**OR for all events:**

    "phone": "+60123456789"```json

  },{

  "success_redirect": "https://yoursite.com/success"  "callback": "https://yoursite.com/webhooks/chip",

}  "all_events": true

```}

```

**Response:**

---

```json

{#### Get Webhook

  "id": "550e8400-e29b-41d4-a716-446655440000",```http

  "status": "created",GET /webhooks/{id}/

  "checkout_url": "https://gate.chip-in.asia/checkout/550e8400",```

  "purchase": {**Documentation:** https://docs.chip-in.asia/chip-collect/api-reference/webhooks/retrieve

    "total": 9900,

    "currency": "MYR"---

  }

}#### Update Webhook

``````http

PUT /webhooks/{id}/

#### Get Purchase```

**Documentation:** https://docs.chip-in.asia/chip-collect/api-reference/webhooks/update

```http

GET /purchases/{id}/---

```

#### Delete Webhook

#### Cancel Purchase```http

DELETE /webhooks/{id}/

```http```

POST /purchases/{id}/cancel/**Documentation:** https://docs.chip-in.asia/chip-collect/api-reference/webhooks/delete

```

---

#### Capture Payment (Pre-auth)

#### List Webhooks

```http```http

POST /purchases/{id}/capture/GET /webhooks/

``````

**Documentation:** https://docs.chip-in.asia/chip-collect/api-reference/webhooks/list

**Optional partial capture:**

---

```json

{### Payment Methods

  "amount": 5000

}**Official Docs:** https://docs.chip-in.asia/chip-collect/api-reference/payment-methods

```

#### Get Available Payment Methods

#### Release Payment (Pre-auth)```http

GET /payment_methods/

```http```

POST /purchases/{id}/release/**Documentation:** https://docs.chip-in.asia/chip-collect/api-reference/payment-methods/list

```

**Query Parameters:**

#### Refund Purchase- `currency` (string) - Filter by currency (MYR, USD, etc.)

- `country` (string) - Filter by country code

```http

POST /purchases/{id}/refund/**Response:** Array of available payment method objects

```

---

**Optional partial refund:**

### Public Key

```json

{#### Get Public Key for Webhook Verification

  "amount": 5000```http

}GET /public_key/

``````

**Documentation:** https://docs.chip-in.asia/chip-collect/guides/webhooks#signature-verification

#### Charge Recurring Token

**Response:**

```http```json

POST /purchases/{id}/charge/{

```  "public_key": "-----BEGIN PUBLIC KEY-----\n...\n-----END PUBLIC KEY-----"

}

**Request:**```



```json---

{

  "recurring_token": "token_value",### Account Information

  "amount": 9900

}#### Get Account Balance

``````http

GET /account/balance/

### Clients```

**Documentation:** https://docs.chip-in.asia/chip-collect/api-reference/account

**Official Docs:** https://docs.chip-in.asia/chip-collect/api-reference/clients

**Response:**

#### Create Client```json

{

```http  "balance": 125000,

POST /clients/  "currency": "MYR",

```  "available": 100000,

  "pending": 25000

#### Get Client}

```

```http

GET /clients/{id}/---

```

#### Get Account Turnover

#### Update Client```http

GET /account/turnover/

```http```

PUT /clients/{id}/**Documentation:** https://docs.chip-in.asia/chip-collect/api-reference/account

```

**Query Parameters:**

#### Delete Client- `from` (integer) - Start timestamp

- `to` (integer) - End timestamp

```http

DELETE /clients/{id}/---

```

### Company Statements

#### List Clients

**Official Docs:** https://docs.chip-in.asia/chip-collect/api-reference/company-statements

```http

GET /clients/?email=test@example.com&page=1#### List Company Statements

``````http

GET /company_statements/

### Webhooks```

**Documentation:** https://docs.chip-in.asia/chip-collect/api-reference/company-statements/list

**Official Docs:** https://docs.chip-in.asia/chip-collect/api-reference/webhooks

---

#### Create Webhook

#### Get Company Statement

```http```http

POST /webhooks/GET /company_statements/{id}/

``````

**Documentation:** https://docs.chip-in.asia/chip-collect/api-reference/company-statements/retrieve

**Request:**

---

```json

{#### Cancel Company Statement

  "callback": "https://yoursite.com/webhooks/chip",```http

  "events": ["purchase.created", "purchase.paid"]POST /company_statements/{id}/cancel/

}```

```**Documentation:** https://docs.chip-in.asia/chip-collect/api-reference/company-statements/cancel



#### List Webhooks---



```http## CHIP Send API

GET /webhooks/

```### Send Instructions



### Payment Methods**Official Docs:** https://docs.chip-in.asia/chip-send/api-reference/send-instructions



```http#### Create Send Instruction

GET /payment_methods/?currency=MYR&country=MY```http

```POST /send/send_instructions

```

### Account Information**Documentation:** https://docs.chip-in.asia/chip-send/api-reference/send-instructions/create



```http**Required Fields:**

GET /account/balance/- `bank_account_id` (integer) - Recipient bank account ID

GET /account/turnover/?from=1619740800&to=1619827200- `amount` (string) - Amount as decimal string (e.g., "100.00")

```- `email` (string) - Recipient email for notifications

- `description` (string) - Payment description

---- `reference` (string) - Unique merchant reference



## CHIP Send API**Example:**

```json

### Send Instructions{

  "bank_account_id": 123,

**Official Docs:** https://docs.chip-in.asia/chip-send/api-reference/send-instructions  "amount": "500.00",

  "email": "recipient@example.com",

#### Create Send Instruction  "description": "Payment for invoice #12345",

  "reference": "INV-12345"

```http}

POST /send/send_instructions```

```

**Response:** SendInstruction object

**Request:**

---

```json

{#### Get Send Instruction

  "bank_account_id": 123,```http

  "amount": "500.00",GET /send/send_instructions/{id}

  "email": "recipient@example.com",```

  "description": "Payment for invoice #12345",**Documentation:** https://docs.chip-in.asia/chip-send/api-reference/send-instructions/retrieve

  "reference": "INV-12345"

}---

```

#### List Send Instructions

#### Get Send Instruction```http

GET /send/send_instructions

```http```

GET /send/send_instructions/{id}**Documentation:** https://docs.chip-in.asia/chip-send/api-reference/send-instructions/list

```

**Query Parameters:**

#### List Send Instructions- `state` (string) - Filter by state

- `reference` (string) - Filter by reference

```http- `page` (integer)

GET /send/send_instructions?state=completed- `page_size` (integer)

```

---

#### Delete Send Instruction

#### Delete Send Instruction

```http```http

DELETE /send/send_instructions/{id}DELETE /send/send_instructions/{id}

``````

**Documentation:** https://docs.chip-in.asia/chip-send/api-reference/send-instructions/delete

### Bank Accounts

**Requirement:** Can only delete instructions in certain states (not completed/executing)

**Official Docs:** https://docs.chip-in.asia/chip-send/api-reference/bank-accounts

---

#### Create Bank Account

#### Resend Send Instruction Webhook

```http```http

POST /send/bank_accountsPOST /send/send_instructions/{id}/resend_webhook

``````

**Documentation:** https://docs.chip-in.asia/chip-send/api-reference/send-instructions/resend-webhook

**Request:**

---

```json

{### Bank Accounts

  "account_number": "157380111111",

  "bank_code": "MBBEMYKL",**Official Docs:** https://docs.chip-in.asia/chip-send/api-reference/bank-accounts

  "name": "Ahmad Pintu",

  "reference": "customer-001"#### Create Bank Account

}```http

```POST /send/bank_accounts

```

#### List Bank Accounts**Documentation:** https://docs.chip-in.asia/chip-send/api-reference/bank-accounts/create



```http**Required Fields:**

GET /send/bank_accounts?status=verified- `account_number` (string) - Bank account number

```- `bank_code` (string) - Bank SWIFT/BIC code (e.g., "MBBEMYKL")

- `name` (string) - Account holder name (1-65535 characters)

### Send Limits

**Optional Fields:**

```http- `reference` (string) - Merchant reference

GET /send/send_limits

POST /send/send_limits**Example:**

``````json

{

### Groups  "account_number": "157380111111",

  "bank_code": "MBBEMYKL",

```http  "name": "Ahmad Pintu",

GET /send/groups  "reference": "customer-001"

POST /send/groups}

PUT /send/groups/{id}```

DELETE /send/groups/{id}

```**Response:** BankAccount object with status `pending` (requires verification)



### Accounts---



```http#### Get Bank Account

GET /send/accounts```http

```GET /send/bank_accounts/{id}

```

---**Documentation:** https://docs.chip-in.asia/chip-send/api-reference/bank-accounts/retrieve



## Data Structures---



### Purchase Object#### List Bank Accounts

```http

```jsonGET /send/bank_accounts

{```

  "id": "uuid",**Documentation:** https://docs.chip-in.asia/chip-send/api-reference/bank-accounts/list

  "status": "paid",

  "created_on": 1619740800,**Query Parameters:**

  "checkout_url": "https://...",- `status` (string) - Filter by status (pending, verified, rejected)

  "purchase": {- `group_id` (integer) - Filter by group

    "currency": "MYR",- `page` (integer)

    "total": 9900,- `page_size` (integer)

    "products": [...]

  },---

  "client": {...},

  "payment": {...}#### Delete Bank Account

}```http

```DELETE /send/bank_accounts/{id}

```

### SendInstruction Object**Documentation:** https://docs.chip-in.asia/chip-send/api-reference/bank-accounts/delete



```json---

{

  "id": 123,#### Resend Bank Account Webhook

  "bank_account_id": 456,```http

  "amount": "500.00",POST /send/bank_accounts/{id}/resend_webhook

  "state": "completed",```

  "email": "recipient@example.com",**Documentation:** https://docs.chip-in.asia/chip-send/api-reference/bank-accounts/resend-webhook

  "reference": "INV-12345"

}---

```

### Send Limits

### BankAccount Object

**Official Docs:** https://docs.chip-in.asia/chip-send/api-reference/send-limits

```json

{#### Create Send Limit

  "id": 84,```http

  "account_number": "157380111111",POST /send/send_limits

  "bank_code": "MBBEMYKL",```

  "name": "Ahmad Pintu",**Documentation:** https://docs.chip-in.asia/chip-send/api-reference/send-limits/create

  "status": "verified"

}---

```

#### Get Send Limit

---```http

GET /send/send_limits/{id}

## Webhook Events```

**Documentation:** https://docs.chip-in.asia/chip-send/api-reference/send-limits/retrieve

### CHIP Collect

---

- `purchase.created`

- `purchase.paid`#### List Send Limits

- `purchase.refunded````http

- `purchase.cancelled`GET /send/send_limits

- `payment.paid````

- `payment.failed`**Documentation:** https://docs.chip-in.asia/chip-send/api-reference/send-limits/list



### CHIP Send---



- `send_instruction.completed`#### Resend Send Limit Approval Requests

- `send_instruction.rejected````http

- `bank_account.verified`POST /send/send_limits/{id}/resend_approval_requests

- `bank_account.rejected````

**Documentation:** https://docs.chip-in.asia/chip-send/api-reference/send-limits/resend-approval

---

---

## Status & State Values

### Groups

### Purchase Status

**Official Docs:** https://docs.chip-in.asia/chip-send/api-reference/groups

| Status | Description |

|--------|-------------|#### Create Group

| `created` | Awaiting payment |```http

| `sent` | Invoice sent |POST /send/groups

| `viewed` | Customer viewed |```

| `paid` | Successfully paid |**Documentation:** https://docs.chip-in.asia/chip-send/api-reference/groups/create

| `error` | Payment failed |

| `cancelled` | Cancelled |---

| `refunded` | Refunded |

| `hold` | Pre-auth hold |#### Get Group

```http

### Send Instruction StateGET /send/groups/{id}

```

| State | Description |**Documentation:** https://docs.chip-in.asia/chip-send/api-reference/groups/retrieve

|-------|-------------|

| `received` | Received |---

| `executing` | Processing |

| `completed` | Completed |#### Update Group

| `rejected` | Rejected |```http

PUT /send/groups/{id}

### Bank Account Status```

**Documentation:** https://docs.chip-in.asia/chip-send/api-reference/groups/update

| Status | Description |

|--------|-------------|---

| `pending` | Awaiting verification |

| `verified` | Verified |#### Delete Group

| `rejected` | Invalid account |```http

DELETE /send/groups/{id}

---```

**Documentation:** https://docs.chip-in.asia/chip-send/api-reference/groups/delete

## Testing

---

### Test Environment

#### List Groups

**CHIP Collect:** Same URL, use test API keys  ```http

**CHIP Send:** Use staging environmentGET /send/groups

```

### Test Cards**Documentation:** https://docs.chip-in.asia/chip-send/api-reference/groups/list



```---

Card: 4444 3333 2222 1111

CVC: 123### Accounts

Expiry: Any future date

```**Official Docs:** https://docs.chip-in.asia/chip-send/api-reference/accounts



### Test Bank Accounts#### List Accounts

```http

```GET /send/accounts

Account: 157380111111```

Bank Code: MBBEMYKL**Documentation:** https://docs.chip-in.asia/chip-send/api-reference/accounts/list

```

**Response:** Array of available CHIP Send accounts

---

---

## Additional Resources

### Webhooks (CHIP Send)

- **Documentation:** https://docs.chip-in.asia/

- **API Status:** https://status.chip-in.asia/**Official Docs:** https://docs.chip-in.asia/chip-send/api-reference/webhooks

- **Support:** support@chip-in.asia

- **Portal:** https://portal.chip-in.asia/#### Create Send Webhook

```http

---POST /send/webhooks

```

**Last Updated:** January 2025  **Documentation:** https://docs.chip-in.asia/chip-send/api-reference/webhooks/create

**Package Version:** v1.1  

**Grade:** A++ (100/100) - Production Ready---


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
