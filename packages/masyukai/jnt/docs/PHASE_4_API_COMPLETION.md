# Phase 4: API Endpoint Completion

> **Priority:** 🟡 HIGH  
> **Status:** 🚀 Starting  
> **Started:** Current Session

---

## 🎯 Phase 4 Objectives

Complete all missing J&T Express API endpoints and data structures to achieve 100% API coverage.

### Success Criteria
- ✅ Fix `cancelOrder()` to include required `reason` parameter
- ✅ Implement `printOrder()` / `printWaybill()` endpoint
- ✅ Create `PrintWaybillData` DTO
- ✅ Add missing `ExpressType` enum values
- ✅ Implement comprehensive error handling
- ✅ Complete test coverage for all new endpoints
- ✅ Update documentation

### Expected Outcome
- 100% API endpoint coverage (6/6 endpoints)
- 100% enum completeness
- Production-ready error handling
- Comprehensive test suite

---

## 📋 Implementation Checklist

### Step 1: Fix cancelOrder() Method ⏳
**Current Issue:** Missing required `reason` parameter per J&T API spec

**Current Signature:**
```php
public function cancelOrder(string $orderId, ?string $trackingNumber = null): array
```

**Required Signature:**
```php
public function cancelOrder(
    string $orderId, 
    string $reason,           // ← NEW: Required, max 300 chars
    ?string $trackingNumber = null
): CancelOrderData
```

**Tasks:**
- [ ] Update `cancelOrder()` method signature
- [ ] Add `reason` parameter validation (required, max 300 chars)
- [ ] Create `CancelOrderData` DTO for response
- [ ] Update all existing tests
- [ ] Add tests for reason validation
- [ ] Update documentation

**API Specification:**
- Parameter: `reason` - String(300), Required
- Purpose: Cancellation reason for tracking/audit
- Example: "Customer requested cancellation", "Wrong address", etc.

---

### Step 2: Implement printOrder() Endpoint ⏳

**Purpose:** Generate AWB/waybill labels for printing

**API Details:**
- **Endpoint:** POST /api/order/printOrder
- **Method Name:** `printOrder()` or `printWaybill()`
- **Purpose:** Get waybill PDF for printing labels

**Parameters:**
```php
public function printOrder(
    string $orderId,              // txlogisticId - Required
    ?string $trackingNumber = null, // billCode - Optional
    ?string $templateName = null    // templateName - Optional (for custom templates)
): PrintWaybillData
```

**Response Structure:**
```php
readonly class PrintWaybillData
{
    public string $orderId;                    // txlogisticId
    public string $trackingNumber;             // billCode
    public ?string $base64EncodeContent;       // Base64 PDF (single parcel)
    public ?string $urlContent;                // URL to PDF (multi-parcel)
}
```

**Important Notes:**
- **Single Parcel:** Returns `base64EncodeContent` (Base64-encoded PDF)
- **Multi-Parcel:** Returns `urlContent` (URL to PDF) instead
- **Template:** Optional custom template name for special formats

**Tasks:**
- [ ] Create `PrintWaybillData` DTO
- [ ] Implement `printOrder()` method in `JntExpressService`
- [ ] Add parameter validation
- [ ] Handle both single/multi-parcel responses
- [ ] Create builder method (optional)
- [ ] Write comprehensive tests (unit + feature)
- [ ] Add usage examples to documentation

---

### Step 3: Complete ExpressType Enum ⏳

**Current State:** 3/5 values (60% complete)

**Existing:**
```php
enum ExpressType: string
{
    case DOMESTIC = 'EZ';   // Standard domestic
    case NEXT_DAY = 'EX';   // Express next day
    case FRESH = 'FD';      // Fresh delivery
}
```

**Missing Values:**
```php
case DOOR_TO_DOOR = 'DO';   // ❌ Door-to-door service
case SAME_DAY = 'JS';       // ❌ Same-day delivery
```

**Tasks:**
- [ ] Add `DOOR_TO_DOOR = 'DO'` case
- [ ] Add `SAME_DAY = 'JS'` case
- [ ] Update tests to cover new enum values
- [ ] Update documentation with descriptions
- [ ] Add usage examples for each type

**Usage Examples to Document:**
```php
// Door-to-door service
ExpressType::DOOR_TO_DOOR // 'DO'

// Same-day delivery (for urgent shipments)
ExpressType::SAME_DAY // 'JS'
```

---

### Step 4: Enhance Error Handling ⏳

**Current State:** Basic error handling only  
**Target:** Comprehensive, Laravel-idiomatic error handling

**Create Custom Exception Classes:**

```php
namespace MasyukAI\Jnt\Exceptions;

// Base exception
class JntException extends \Exception {}

// API-specific exceptions
class JntApiException extends JntException {
    public function __construct(
        string $message,
        public readonly int $apiCode,
        public readonly ?array $apiResponse = null,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
    }
}

class JntAuthenticationException extends JntException {}
class JntValidationException extends JntException {}
class JntRateLimitException extends JntException {}
class JntNetworkException extends JntException {}
```

**Error Response Handling:**
```php
// J&T API error format:
{
    "code": "0",  // "0" = failure
    "msg": "error message",
    "data": null
}
```

**Service Method Pattern:**
```php
public function createOrder(OrderData $order): CreateOrderData
{
    try {
        $response = $this->client->post('/order/addOrder', [
            'json' => $order->toApiArray(),
        ]);
        
        $data = json_decode($response->getBody(), true);
        
        // Check J&T API response code
        if ($data['code'] !== '1') {
            throw new JntApiException(
                message: $data['msg'] ?? 'Unknown error',
                apiCode: (int) $data['code'],
                apiResponse: $data
            );
        }
        
        return CreateOrderData::fromApiArray($data['data']);
        
    } catch (\GuzzleException $e) {
        throw new JntNetworkException(
            "Network error: {$e->getMessage()}",
            previous: $e
        );
    }
}
```

**Tasks:**
- [ ] Create exception classes hierarchy
- [ ] Update all service methods with proper error handling
- [ ] Add error logging
- [ ] Create error handling tests
- [ ] Document error types and handling strategies
- [ ] Add retry logic for transient errors

---

### Step 5: Optional - Complete TrackingDetailData 🔵

**Current:** 16/33 fields (~50% complete)  
**Priority:** Lower (most fields are rarely used)

**Missing Fields:**
```php
public readonly ?string $timeZone;                    // GMT offset
public readonly ?string $otp;                         // Delivery OTP
public readonly ?string $secondLevelTypeCode;         // Secondary classification
public readonly ?string $wcTraceFlag;                 // WC trace flag
public readonly ?string $postCode;                    // Postal code
public readonly ?string $paymentStatus;               // Payment status
public readonly ?string $paymentMethod;               // Payment method
public readonly ?string $nextStopName;                // Next stop
public readonly ?string $remark;                      // Remarks
public readonly ?string $nextNetworkProvinceName;     // Next province
public readonly ?string $nextNetworkCityName;         // Next city
public readonly ?string $nextNetworkAreaName;         // Next area
public readonly ?string $problemType;                 // Problem type
public readonly ?string $signUrl;                     // Signature URL
public readonly ?string $electronicSignaturePicUrl;   // E-signature
public readonly ?int $scanNetworkId;                  // Network ID
public readonly ?string $scanNetworkCountray;         // Country (typo in API)
```

**Tasks (Optional):**
- [ ] Add missing fields to `TrackingDetailData`
- [ ] Update `fromApiArray()` to map new fields
- [ ] Update tests
- [ ] Document new fields

**Note:** This step is optional and can be deferred. The current 16 fields cover the most common use cases.

---

## 🏗️ Implementation Order

### Priority 1 (Critical - Must Have)
1. ✅ Step 1: Fix `cancelOrder()` - Required by API spec
2. ✅ Step 2: Implement `printOrder()` - Core functionality
3. ✅ Step 3: Complete `ExpressType` enum - API compliance

### Priority 2 (Important - Should Have)
4. ✅ Step 4: Enhance error handling - Production readiness

### Priority 3 (Optional - Nice to Have)
5. 🔵 Step 5: Complete `TrackingDetailData` - Can be deferred

---

## 📊 Expected Deliverables

### New Files to Create:
1. `src/Data/PrintWaybillData.php` - DTO for waybill printing response
2. `src/Data/CancelOrderData.php` - DTO for cancel order response
3. `src/Exceptions/JntException.php` - Base exception
4. `src/Exceptions/JntApiException.php` - API error exception
5. `src/Exceptions/JntAuthenticationException.php` - Auth errors
6. `src/Exceptions/JntValidationException.php` - Validation errors
7. `src/Exceptions/JntRateLimitException.php` - Rate limit errors
8. `src/Exceptions/JntNetworkException.php` - Network errors
9. `tests/Unit/Data/PrintWaybillDataTest.php` - Unit tests
10. `tests/Unit/Data/CancelOrderDataTest.php` - Unit tests
11. `tests/Feature/PrintOrderTest.php` - Feature tests
12. `tests/Feature/CancelOrderTest.php` - Updated tests
13. `tests/Unit/Exceptions/ExceptionHandlingTest.php` - Exception tests

### Files to Update:
1. `src/Services/JntExpressService.php` - Add `printOrder()`, fix `cancelOrder()`, add error handling
2. `src/Enums/ExpressType.php` - Add missing enum values
3. `src/Data/TrackingDetailData.php` - Optional: add missing fields
4. `tests/Feature/JntExpressServiceTest.php` - Update existing tests
5. `README.md` - Document new features
6. `docs/API_REFERENCE.md` - Update API documentation

---

## 🧪 Testing Strategy

### Unit Tests
- `PrintWaybillData::fromApiArray()` with various scenarios
- `CancelOrderData::fromApiArray()` with various scenarios
- Exception class construction and properties
- Enum value mapping

### Feature Tests
- `printOrder()` with single parcel (base64 response)
- `printOrder()` with multi-parcel (URL response)
- `printOrder()` with custom template
- `cancelOrder()` with reason parameter
- Error handling for all scenarios:
  - Invalid credentials
  - Network failures
  - API errors (code !== '1')
  - Invalid responses

### Integration Tests (with J&T sandbox)
- Create order → Print waybill → Verify PDF
- Create order → Cancel with reason → Verify cancellation
- Test all ExpressType values

---

## 📈 Expected Coverage Improvements

| Category | Before | After | Improvement |
|----------|--------|-------|-------------|
| **API Endpoints** | 3.5/6 (58%) | 6/6 (100%) | +42% ✅ |
| **Enums** | 3.6/5 (72%) | 5/5 (100%) | +28% ✅ |
| **Error Handling** | 0/5 (0%) | 5/5 (100%) | +100% ✅ |
| **Data Classes** | 5/7 (71%) | 7/7 (100%) | +29% ✅ |
| **OVERALL Package** | ~60% | ~85% | +25% 🚀 |

---

## 📚 Documentation Requirements

### Usage Examples:

**Print Waybill:**
```php
use MasyukAI\Jnt\Facades\JntExpress;

// Single parcel - get Base64 PDF
$waybill = JntExpress::printOrder(
    orderId: 'ORDER-123',
    trackingNumber: 'JNTMY12345678'
);

if ($waybill->base64EncodeContent) {
    // Single parcel - decode and save/display PDF
    $pdfContent = base64_decode($waybill->base64EncodeContent);
    file_put_contents('label.pdf', $pdfContent);
}

// Multi-parcel - get URL
if ($waybill->urlContent) {
    // Multi-parcel - download from URL
    $pdfUrl = $waybill->urlContent;
}

// With custom template
$waybill = JntExpress::printOrder(
    orderId: 'ORDER-123',
    trackingNumber: 'JNTMY12345678',
    templateName: 'CUSTOM_TEMPLATE'
);
```

**Cancel Order with Reason:**
```php
$result = JntExpress::cancelOrder(
    orderId: 'ORDER-123',
    reason: 'Customer requested cancellation',
    trackingNumber: 'JNTMY12345678'
);

if ($result->success) {
    echo "Order cancelled successfully";
}
```

**Error Handling:**
```php
use MasyukAI\Jnt\Exceptions\{JntApiException, JntNetworkException};

try {
    $order = JntExpress::createOrder($orderData);
} catch (JntApiException $e) {
    // J&T API returned error
    Log::error('J&T API error', [
        'code' => $e->apiCode,
        'message' => $e->getMessage(),
        'response' => $e->apiResponse,
    ]);
} catch (JntNetworkException $e) {
    // Network/connection error
    Log::error('Network error', [
        'message' => $e->getMessage(),
    ]);
}
```

---

## ✅ Definition of Done

Phase 4 is complete when:

- ✅ All 6 J&T API endpoints implemented and working
- ✅ `cancelOrder()` includes required `reason` parameter
- ✅ `printOrder()` handles both single/multi-parcel scenarios
- ✅ `ExpressType` enum has all 5 documented values
- ✅ Comprehensive exception hierarchy created
- ✅ All service methods have proper error handling
- ✅ All new features have unit + feature tests
- ✅ All tests passing (100% pass rate)
- ✅ Code formatted with Pint
- ✅ PHPStan level 6 passes
- ✅ Documentation updated with examples
- ✅ README updated with new features

---

## 🚀 Success Metrics

After Phase 4 completion:
- **API Coverage:** 100% (6/6 endpoints)
- **Enum Completeness:** 100% (5/5 enums)
- **Error Handling:** Production-ready exception hierarchy
- **Package Completeness:** ~85% (up from ~60%)
- **Test Coverage:** Comprehensive coverage of all features

---

## 📅 Estimated Timeline

- **Step 1 (cancelOrder fix):** 30 minutes
- **Step 2 (printOrder implementation):** 2 hours
- **Step 3 (ExpressType completion):** 15 minutes
- **Step 4 (Error handling):** 1.5 hours
- **Step 5 (TrackingDetailData - optional):** 1 hour

**Total Estimated Time:** 4-5 hours for Priority 1-2 items

---

**Ready to begin Phase 4!** Let's achieve 100% API endpoint coverage and production-ready error handling. 🚀
