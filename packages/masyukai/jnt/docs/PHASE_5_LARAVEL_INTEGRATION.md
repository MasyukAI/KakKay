# Phase 5: Laravel Integration & Polish
## Combined Implementation Plan (Options A + D)

**Status:** 🚧 IN PROGRESS  
**Started:** January 8, 2025  
**Estimated Duration:** 6-8 hours  
**Target Completion:** ~95% package completion

---

## Overview

This phase combines Laravel-specific integration features (Option A) with optional enhancements (Option D) to create a polished, production-ready package that feels native to Laravel applications.

---

## Part 1: Optional Enhancements (Option D)

### Enhancement 1: Expand Exception Hierarchy

**Goal:** Create specific exception classes for different error scenarios

**Files to Create:**
1. `src/Exceptions/JntApiException.php` - API-specific errors
2. `src/Exceptions/JntValidationException.php` - Validation errors
3. `src/Exceptions/JntNetworkException.php` - Network/connection errors
4. `src/Exceptions/JntConfigurationException.php` - Configuration errors
5. `src/Exceptions/JntSignatureException.php` - Webhook signature errors

**Exception Hierarchy:**
```
Exception
└── JntException (base)
    ├── JntApiException (API errors)
    │   ├── OrderCreationException
    │   ├── OrderCancellationException
    │   └── TrackingException
    ├── JntValidationException (validation errors)
    ├── JntNetworkException (network errors)
    ├── JntConfigurationException (config errors)
    └── JntSignatureException (webhook signature errors)
```

**Implementation Details:**

**JntApiException:**
```php
class JntApiException extends JntException
{
    public function __construct(
        string $message,
        public readonly ?string $errorCode = null,
        public readonly mixed $apiResponse = null,
        public readonly ?string $endpoint = null,
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $errorCode, $apiResponse, $code, $previous);
    }

    public static function orderCreationFailed(string $reason, mixed $response = null): self
    public static function orderCancellationFailed(string $orderId, string $reason, mixed $response = null): self
    public static function trackingFailed(string $orderId, mixed $response = null): self
    public static function invalidApiResponse(string $endpoint, mixed $response): self
}
```

**JntValidationException:**
```php
class JntValidationException extends JntException
{
    public function __construct(
        string $message,
        public readonly array $errors = [],
        public readonly ?string $field = null,
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, null, $errors, $code, $previous);
    }

    public static function fieldValidationFailed(string $field, string $reason, array $errors = []): self
    public static function requiredFieldMissing(string $field): self
    public static function invalidFieldValue(string $field, mixed $value, string $expected): self
}
```

**JntNetworkException:**
```php
class JntNetworkException extends JntException
{
    public function __construct(
        string $message,
        public readonly ?string $endpoint = null,
        public readonly ?int $httpStatus = null,
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, null, null, $code, $previous);
    }

    public static function connectionFailed(string $endpoint, \Throwable $previous): self
    public static function timeout(string $endpoint, int $seconds): self
    public static function serverError(string $endpoint, int $httpStatus, mixed $response = null): self
}
```

**JntConfigurationException:**
```php
class JntConfigurationException extends JntException
{
    public function __construct(
        string $message,
        public readonly ?string $configKey = null,
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, null, null, $code, $previous);
    }

    public static function missingApiKey(): self
    public static function invalidApiKey(): self
    public static function missingPrivateKey(): self
    public static function invalidPrivateKey(): self
}
```

**JntSignatureException:**
```php
class JntSignatureException extends JntException
{
    public function __construct(
        string $message,
        public readonly ?string $expectedSignature = null,
        public readonly ?string $actualSignature = null,
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, null, null, $code, $previous);
    }

    public static function verificationFailed(string $expected, string $actual): self
    public static function missingSignature(): self
    public static function invalidPublicKey(): self
}
```

**Tests to Create:**
- `tests/Unit/Exceptions/JntApiExceptionTest.php`
- `tests/Unit/Exceptions/JntValidationExceptionTest.php`
- `tests/Unit/Exceptions/JntNetworkExceptionTest.php`
- `tests/Unit/Exceptions/JntConfigurationExceptionTest.php`
- `tests/Unit/Exceptions/JntSignatureExceptionTest.php`

**Expected Test Count:** ~30 new tests (6 per exception class)

---

### Enhancement 2: Create PrintWaybillData DTO

**Goal:** Parse `printOrder()` response into structured DTO

**File to Create:**
- `src/Data/PrintWaybillData.php`

**Implementation:**
```php
declare(strict_types=1);

namespace MasyukAI\Jnt\Data;

use MasyukAI\Jnt\Contracts\DataContract;

final readonly class PrintWaybillData implements DataContract
{
    public function __construct(
        public string $orderId,
        public ?string $trackingNumber,
        public ?string $base64Content,
        public ?string $urlContent,
        public bool $isMultiParcel,
        public ?string $templateName = null,
    ) {}

    public static function fromApiArray(array $data): self
    {
        return new self(
            orderId: $data['txlogisticId'] ?? $data['orderId'],
            trackingNumber: $data['billCode'] ?? null,
            base64Content: $data['base64EncodeContent'] ?? null,
            urlContent: $data['urlContent'] ?? null,
            isMultiParcel: isset($data['urlContent']) && !isset($data['base64EncodeContent']),
            templateName: $data['templateName'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'orderId' => $this->orderId,
            'trackingNumber' => $this->trackingNumber,
            'base64Content' => $this->base64Content,
            'urlContent' => $this->urlContent,
            'isMultiParcel' => $this->isMultiParcel,
            'templateName' => $this->templateName,
        ];
    }

    public function hasBase64Content(): bool
    {
        return $this->base64Content !== null;
    }

    public function hasUrlContent(): bool
    {
        return $this->urlContent !== null;
    }

    public function savePdf(string $path): bool
    {
        if (!$this->hasBase64Content()) {
            return false;
        }

        $pdfContent = base64_decode($this->base64Content);
        return file_put_contents($path, $pdfContent) !== false;
    }

    public function getPdfContent(): ?string
    {
        if (!$this->hasBase64Content()) {
            return null;
        }

        return base64_decode($this->base64Content);
    }
}
```

**Update JntExpressService:**
```php
// Update printOrder() to return PrintWaybillData
public function printOrder(
    string $orderId,
    ?string $trackingNumber = null,
    ?string $templateName = null
): PrintWaybillData
{
    // ... existing code ...
    
    return PrintWaybillData::fromApiArray([
        'txlogisticId' => $orderId,
        'billCode' => $trackingNumber,
        'base64EncodeContent' => $response['base64EncodeContent'] ?? null,
        'urlContent' => $response['urlContent'] ?? null,
        'templateName' => $templateName,
    ]);
}
```

**Tests to Create:**
- `tests/Unit/Data/PrintWaybillDataTest.php` (~15 tests)
- Update `tests/Feature/JntExpressServiceTest.php` (printOrder tests)

**Expected Test Count:** ~15 new tests

---

### Enhancement 3: Complete TrackingDetailData (Optional)

**Goal:** Add 17 missing optional fields to TrackingDetailData

**File to Update:**
- `src/Data/TrackingDetailData.php`

**Missing Fields to Add:**
```php
public ?string $expressType,      // Express service type
public ?string $goods,            // Goods description
public ?int $itemQty,             // Item quantity
public ?float $weight,            // Package weight
public ?string $operatorCode,     // Operator code
public ?string $operatorName,     // Operator name
public ?string $operatorSite,     // Operator site
public ?string $prePhone,         // Previous contact phone
public ?string $preMobile,        // Previous contact mobile
public ?string $scanType,         // Scan type
public ?string $problem,          // Problem description
public ?string $problemType,      // Problem type code
public ?string $receiver,         // Receiver name
public ?string $receiverPhone,    // Receiver phone
public ?string $receiverMobile,   // Receiver mobile
public ?string $receiverAddress,  // Receiver address
public ?string $receiverCity,     // Receiver city
```

**Note:** This is marked as OPTIONAL because current implementation covers the most important fields. Only implement if needed for specific use cases.

**Expected Test Count:** ~10 new tests (if implemented)

---

## Part 2: Laravel Integration Features (Option A)

### Feature 1: Artisan Commands

**Commands to Create:**

#### 1. `jnt:order:create` - Create Order via CLI
```bash
php artisan jnt:order:create \
    --sender-name="John Doe" \
    --sender-phone="0123456789" \
    --receiver-name="Jane Smith" \
    --receiver-phone="0198765432" \
    --receiver-address="123 Main St" \
    --item-name="Product" \
    --item-qty=1 \
    --item-price=50.00
```

**File:** `src/Console/Commands/CreateOrderCommand.php`

**Features:**
- Interactive prompts for all required fields
- Validation before submission
- Pretty output with order details
- Option to save response to file
- Support for multiple items

#### 2. `jnt:order:track` - Track Order via CLI
```bash
php artisan jnt:order:track ORDER-123
php artisan jnt:order:track --tracking-number=JT987654321
```

**File:** `src/Console/Commands/TrackOrderCommand.php`

**Features:**
- Track by order ID or tracking number
- Pretty table output of tracking history
- Status highlighting (delivered = green, problem = red)
- Option to watch for updates (--watch)
- Export to JSON/CSV

#### 3. `jnt:order:cancel` - Cancel Order via CLI
```bash
php artisan jnt:order:cancel ORDER-123 \
    --reason="OUT_OF_STOCK" \
    --tracking-number=JT987654321
```

**File:** `src/Console/Commands/CancelOrderCommand.php`

**Features:**
- Interactive reason selection from CancellationReason enum
- Confirmation prompt
- Pretty output with cancellation details

#### 4. `jnt:order:print` - Print Waybill via CLI
```bash
php artisan jnt:order:print ORDER-123 \
    --tracking-number=JT987654321 \
    --output=waybill.pdf
```

**File:** `src/Console/Commands/PrintOrderCommand.php`

**Features:**
- Save PDF to specified path
- Open PDF automatically (--open flag)
- Support for custom templates

#### 5. `jnt:webhook:test` - Test Webhook Endpoint
```bash
php artisan jnt:webhook:test \
    --order-id=ORDER-123 \
    --tracking-number=JT987654321 \
    --status=DELIVERED
```

**File:** `src/Console/Commands/TestWebhookCommand.php`

**Features:**
- Generate test webhook payload
- Send to local webhook endpoint
- Validate signature
- Pretty output of response

#### 6. `jnt:config:check` - Validate Configuration
```bash
php artisan jnt:config:check
```

**File:** `src/Console/Commands/ConfigCheckCommand.php`

**Features:**
- Check all required config values
- Validate API credentials
- Test API connectivity
- Check webhook signature verification
- Pretty status table

**Service Provider Update:**
```php
// src/JntServiceProvider.php
public function boot(): void
{
    if ($this->app->runningInConsole()) {
        $this->commands([
            \MasyukAI\Jnt\Console\Commands\CreateOrderCommand::class,
            \MasyukAI\Jnt\Console\Commands\TrackOrderCommand::class,
            \MasyukAI\Jnt\Console\Commands\CancelOrderCommand::class,
            \MasyukAI\Jnt\Console\Commands\PrintOrderCommand::class,
            \MasyukAI\Jnt\Console\Commands\TestWebhookCommand::class,
            \MasyukAI\Jnt\Console\Commands\ConfigCheckCommand::class,
        ]);
    }
}
```

**Expected Test Count:** ~40 new tests (~6-8 per command)

---

### Feature 2: Laravel Events & Listeners

**Goal:** Emit Laravel events for important operations

**Events to Create:**

1. **`OrderCreatedEvent`** - Fired when order is created
```php
namespace MasyukAI\Jnt\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use MasyukAI\Jnt\Data\OrderData;

class OrderCreatedEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly OrderData $order,
        public readonly array $rawResponse,
    ) {}
}
```

2. **`OrderCancelledEvent`** - Fired when order is cancelled
```php
class OrderCancelledEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly string $orderId,
        public readonly string $reason,
        public readonly ?string $trackingNumber = null,
    ) {}
}
```

3. **`TrackingUpdatedEvent`** - Fired when tracking is queried
```php
class TrackingUpdatedEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly string $orderId,
        public readonly array $trackingDetails,
        public readonly ?string $latestStatus = null,
    ) {}
}
```

4. **`WaybillPrintedEvent`** - Fired when waybill is printed
```php
class WaybillPrintedEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly string $orderId,
        public readonly ?string $trackingNumber = null,
        public readonly bool $isMultiParcel = false,
    ) {}
}
```

**Update JntExpressService to dispatch events:**
```php
public function createOrder(array $orderData): OrderData
{
    $response = $this->client->post('order/addOrder', $orderData);
    $order = OrderData::fromApiArray($response['data']);
    
    // Dispatch event
    event(new OrderCreatedEvent($order, $response));
    
    return $order;
}
```

**Example Listener:**
```php
namespace App\Listeners;

use MasyukAI\Jnt\Events\OrderCreatedEvent;
use Illuminate\Support\Facades\Log;

class LogOrderCreated
{
    public function handle(OrderCreatedEvent $event): void
    {
        Log::info('J&T Order Created', [
            'order_id' => $event->order->orderId,
            'tracking_number' => $event->order->trackingNumber,
        ]);
    }
}
```

**Expected Test Count:** ~15 new tests (~3-4 per event)

---

### Feature 3: Laravel Notifications

**Goal:** Create notification classes for common scenarios

**Notifications to Create:**

1. **`OrderShippedNotification`**
```php
namespace MasyukAI\Jnt\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class OrderShippedNotification extends Notification
{
    public function __construct(
        public readonly string $orderId,
        public readonly string $trackingNumber,
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Order Has Been Shipped')
            ->line("Your order {$this->orderId} has been shipped.")
            ->line("Tracking Number: {$this->trackingNumber}")
            ->action('Track Your Order', url("/track/{$this->trackingNumber}"))
            ->line('Thank you for your order!');
    }

    public function toArray($notifiable): array
    {
        return [
            'order_id' => $this->orderId,
            'tracking_number' => $this->trackingNumber,
            'type' => 'order_shipped',
        ];
    }
}
```

2. **`OrderDeliveredNotification`**
3. **`OrderProblemNotification`** - For problem statuses

**Expected Test Count:** ~10 new tests

---

### Feature 4: Improved Service Provider

**Enhancements:**

1. **Configuration Publishing:**
```php
public function boot(): void
{
    $this->publishes([
        __DIR__.'/../config/jnt.php' => config_path('jnt.php'),
    ], 'jnt-config');
}
```

2. **Migration Publishing (for webhook logs):**
```php
public function boot(): void
{
    $this->publishes([
        __DIR__.'/../database/migrations/' => database_path('migrations'),
    ], 'jnt-migrations');
}
```

3. **View Publishing (for email notifications):**
```php
public function boot(): void
{
    $this->loadViewsFrom(__DIR__.'/../resources/views', 'jnt');
    
    $this->publishes([
        __DIR__.'/../resources/views' => resource_path('views/vendor/jnt'),
    ], 'jnt-views');
}
```

4. **Route Publishing (for webhook endpoint):**
```php
public function boot(): void
{
    $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
}
```

**Expected Test Count:** ~8 new tests

---

### Feature 5: Facades Enhancement

**Current Facades:**
- `Jnt` facade (main service)

**Enhancements:**
1. Add IDE helper methods
2. Add convenience methods
3. Improve documentation

**Example:**
```php
namespace MasyukAI\Jnt\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \MasyukAI\Jnt\Data\OrderData createOrder(array $orderData)
 * @method static \MasyukAI\Jnt\Data\OrderData queryOrder(string $orderId, ?string $trackingNumber = null)
 * @method static array getTracking(string $orderId, ?string $trackingNumber = null)
 * @method static array cancelOrder(string $orderId, \MasyukAI\Jnt\Enums\CancellationReason|string $reason, ?string $trackingNumber = null)
 * @method static \MasyukAI\Jnt\Data\PrintWaybillData printOrder(string $orderId, ?string $trackingNumber = null, ?string $templateName = null)
 * 
 * @see \MasyukAI\Jnt\Services\JntExpressService
 */
class Jnt extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'jnt';
    }
}
```

---

## Implementation Order

### Week 1: Core Enhancements (Days 1-2)
1. ✅ Expand exception hierarchy (all 5 exception classes)
2. ✅ Create PrintWaybillData DTO
3. ✅ Write tests for exceptions and DTO

### Week 1: Laravel Integration (Days 3-4)
4. ✅ Create all 6 Artisan commands
5. ✅ Write tests for commands
6. ✅ Update service provider

### Week 1: Events & Notifications (Day 5)
7. ✅ Create event classes
8. ✅ Update service to dispatch events
9. ✅ Create notification classes
10. ✅ Write tests

### Week 1: Polish (Day 6)
11. ✅ Enhance facades with IDE helpers
12. ✅ Improve service provider
13. ✅ Run full test suite
14. ✅ Format with Pint
15. ✅ Update documentation

---

## Success Criteria

### Code Quality
- [ ] All new code follows Laravel conventions
- [ ] PHPStan level 6 compliance maintained
- [ ] All files formatted with Pint
- [ ] Comprehensive PHPDoc blocks

### Testing
- [ ] All new features have tests
- [ ] Test coverage: ~220+ tests (currently 176)
- [ ] 100% test pass rate
- [ ] Integration tests for commands

### Documentation
- [ ] All commands documented
- [ ] Event/listener examples provided
- [ ] Notification usage examples
- [ ] Updated README with new features

### Developer Experience
- [ ] Intuitive command syntax
- [ ] Helpful error messages
- [ ] Clear event names
- [ ] Easy-to-use notifications

---

## Expected Deliverables

### New Files (~30 files)

**Exceptions (5):**
- JntApiException.php
- JntValidationException.php
- JntNetworkException.php
- JntConfigurationException.php
- JntSignatureException.php

**Commands (6):**
- CreateOrderCommand.php
- TrackOrderCommand.php
- CancelOrderCommand.php
- PrintOrderCommand.php
- TestWebhookCommand.php
- ConfigCheckCommand.php

**Events (4):**
- OrderCreatedEvent.php
- OrderCancelledEvent.php
- TrackingUpdatedEvent.php
- WaybillPrintedEvent.php

**Notifications (3):**
- OrderShippedNotification.php
- OrderDeliveredNotification.php
- OrderProblemNotification.php

**Data (1):**
- PrintWaybillData.php

**Tests (~15 test files):**
- Exception tests (5 files)
- Command tests (6 files)
- Event tests (1 file)
- Notification tests (1 file)
- DTO tests (1 file)

### Updated Files (~5 files)
- JntExpressService.php (add event dispatching)
- JntServiceProvider.php (register commands, views, etc.)
- Jnt facade (IDE helpers)
- README.md (document new features)
- COMPLETE_API_GAP_ANALYSIS.md (update progress)

---

## Estimated Timeline

**Total Time:** 6-8 hours over 5-6 days

**Breakdown:**
- Exception hierarchy: 2 hours
- PrintWaybillData DTO: 1 hour
- Artisan commands: 3 hours
- Events & notifications: 1.5 hours
- Service provider enhancements: 0.5 hours
- Testing: 2 hours
- Documentation: 1 hour

---

## Testing Strategy

### Unit Tests
- Test all exception factory methods
- Test PrintWaybillData parsing
- Test event data structures
- Test notification content

### Feature Tests
- Test each Artisan command
- Test event dispatching
- Test notification delivery

### Integration Tests
- Test complete command workflows
- Test event listener integration

---

## After Phase 5

**Package Completion:** ~95%

**What Will Be Complete:**
- ✅ All API endpoints
- ✅ Complete exception hierarchy
- ✅ Full Laravel integration
- ✅ Artisan commands
- ✅ Event system
- ✅ Notification system
- ✅ Comprehensive testing

**Remaining (Phase 6):**
- Integration tests with J&T sandbox
- Performance optimization
- Complete API reference docs
- Migration guide
- Production deployment guide

---

**Ready to begin Phase 5?**

Let me know if you'd like to adjust priorities or start with a specific section!
