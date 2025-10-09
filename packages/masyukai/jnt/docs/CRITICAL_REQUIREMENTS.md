# Critical Requirements for Production-Ready Quality

> **Priority:** 🔴 HIGHEST  
> **Phase:** 2.5 - Type Safety & API Compliance  
> **Status:** Must be implemented before Phase 3 (Webhooks)

---

## 🎯 The 5 Critical Requirements

### 1. 🔢 Type Safety - Send/Receive Correct Format

**Problem:**
J&T API documentation declares most fields as "String" but **ranges reveal true semantic types**:
- `String(1-999)` = **INTEGER** sent as string
- `String(0.01-999.99)` = **FLOAT with 2 decimals** sent as string
- `String(255)` = **TEXT** string
- `String(Y/N)` = **BOOLEAN** flag as Y/N

**Current Issue:**
```php
// ❌ BAD - Loses type information, developer must format manually
public readonly string $weight;

// When sending to API:
'weight' => $this->weight,  // If developer passes 5.5, API gets "5.5" instead of "5.50"
```

**Required Solution:**
```php
// ✅ GOOD - Accept developer-friendly types, transform automatically
public readonly int|float|string $weightKg;

// In toApiArray():
'weight' => TypeTransformer::toDecimalString($this->weightKg, 2),  // 5.5 → "5.50"
```

**Impact:**
- ✅ Prevents API validation errors
- ✅ Developer doesn't need to know API format details
- ✅ Type safety enforced at compile time
- ✅ Automatic transformation to correct API format

---

### 2. 🧠 Smart Type Enforcement

**Package Must Be Smart:**
1. **Accept** developer-friendly types (int, float, bool)
2. **Validate** ranges and formats
3. **Transform** to exact API requirements
4. **Send** correctly formatted strings

**Examples:**

**Integer Strings (quantities, counts):**
```php
// Developer input: int|string
$item = new ItemData(
    itemName: 'Product',
    quantity: 5,              // Developer passes int
    itemWeightGrams: 500,     // Developer passes int
    unitPriceMyr: 19.90,      // Developer passes float
);

// API output:
[
    'number' => '5',          // → string (integer format)
    'itemweight' => '500',    // → string (integer format)
    'itemprice' => '19.90',   // → string (2 decimal format)
]
```

**Decimal Strings (money, weights in kg, dimensions):**
```php
// Developer input: float|int|string
$package = new PackageInfoData(
    quantity: 1,
    weightKg: 5.5,           // Developer passes float
    lengthCm: 25,            // Developer passes int
    widthCm: 20.5,           // Developer passes float
    heightCm: 15,            // Developer passes int
);

// API output:
[
    'number' => '1',         // → string (integer format)
    'weight' => '5.50',      // → string (2 decimal format)
    'length' => '25.00',     // → string (2 decimal format)
    'width' => '20.50',      // → string (2 decimal format)
    'height' => '15.00',     // → string (2 decimal format)
]
```

**Boolean Strings (flags):**
```php
// Developer input: bool|string
$order = new OrderData(
    isInsured: true,         // Developer passes bool
);

// API output:
[
    'isinsured' => 'Y',      // → 'Y' or 'N'
]
```

**Implementation Tool:**
```php
class TypeTransformer
{
    // Accept: int|float|string → Send: "123"
    public static function toIntegerString(int|float|string $value): string
    {
        return (string)(int)$value;
    }
    
    // Accept: float|int|string → Send: "123.45"
    public static function toDecimalString(float|int|string $value, int $decimals = 2): string
    {
        return number_format((float)$value, $decimals, '.', '');
    }
    
    // Accept: bool|string → Send: "Y" or "N"
    public static function toBooleanString(bool|string $value): string
    {
        if (is_string($value)) {
            return strtoupper($value) === 'Y' ? 'Y' : 'N';
        }
        return $value ? 'Y' : 'N';
    }
}
```

---

### 3. 📏 Unit Clarity - Crystal Clear to Developers

**Problem:**
Ambiguous units cause confusion - developers don't know if weight is in grams or kg:
```php
// ❌ BAD - What unit? Grams? Kilograms? Pounds?
public readonly float $weight;
public readonly float $itemWeight;
public readonly float $packageWeight;
```

**Solution:**
Keep property names clean, but be **VERY CLEAR** in documentation and use smart config + transformer:

```php
// ✅ GOOD - Clean names, clear documentation
readonly class ItemData
{
    /**
     * @param string $itemName Item name/description (max 200 chars)
     * @param int|string $quantity Number of items (1-999 pieces)
     * @param float|string $weight Weight per item in GRAMS (1-9999 grams, sent as integer)
     * @param float|string $price Unit price in MYR (0.01-999.99, sent with 2 decimals)
     * @param string|null $itemUrl Product URL (optional, max 500 chars)
     */
    public function __construct(
        public string $itemName,
        public int|string $quantity,
        public float|string $weight,      // GRAMS - see docs
        public float|string $price,       // MYR - see docs
        public ?string $itemUrl = null,
    ) {}
    
    public function toApiArray(): array
    {
        return [
            'itemname' => $this->itemName,
            'number' => TypeTransformer::toIntegerString($this->quantity),
            'itemweight' => TypeTransformer::forItemWeight($this->weight),  // Smart: grams → integer string
            'itemprice' => TypeTransformer::forMoney($this->price),         // Smart: MYR → 2dp string
            'itemurl' => $this->itemUrl,
        ];
    }
}

readonly class PackageInfoData
{
    /**
     * @param int|string $quantity Number of packages (1-999 pieces)
     * @param float|string $weight Total weight in KILOGRAMS (0.01-999.99 kg, sent with 2 decimals)
     * @param float|string $length Length in CENTIMETERS (0.01-999.99 cm, sent with 2 decimals)
     * @param float|string $width Width in CENTIMETERS (0.01-999.99 cm, sent with 2 decimals)
     * @param float|string $height Height in CENTIMETERS (0.01-999.99 cm, sent with 2 decimals)
     */
    public function __construct(
        public int|string $quantity,
        public float|string $weight,      // KILOGRAMS - see docs
        public float|string $length,      // CENTIMETERS - see docs
        public float|string $width,       // CENTIMETERS - see docs
        public float|string $height,      // CENTIMETERS - see docs
    ) {}
    
    public function toApiArray(): array
    {
        return [
            'number' => TypeTransformer::toIntegerString($this->quantity),
            'weight' => TypeTransformer::forPackageWeight($this->weight),   // Smart: kg → 2dp string
            'length' => TypeTransformer::forDimension($this->length),       // Smart: cm → 2dp string
            'width' => TypeTransformer::forDimension($this->width),         // Smart: cm → 2dp string
            'height' => TypeTransformer::forDimension($this->height),       // Smart: cm → 2dp string
        ];
    }
}
```

**Smart TypeTransformer with Context-Aware Methods:**
```php
class TypeTransformer
{
    // Generic transformers
    public static function toIntegerString(int|float|string $value): string
    {
        return (string)(int)$value;
    }
    
    public static function toDecimalString(float|int|string $value, int $decimals = 2): string
    {
        return number_format((float)$value, $decimals, '.', '');
    }
    
    // Context-specific transformers (self-documenting)
    public static function forItemWeight(float|string $value): string
    {
        // Items: weight in GRAMS, sent as INTEGER string
        return self::toIntegerString($value);
    }
    
    public static function forPackageWeight(float|string $value): string
    {
        // Packages: weight in KILOGRAMS, sent as 2 DECIMAL string
        return self::toDecimalString($value, 2);
    }
    
    public static function forDimension(float|string $value): string
    {
        // Dimensions: in CENTIMETERS, sent as 2 DECIMAL string
        return self::toDecimalString($value, 2);
    }
    
    public static function forMoney(float|string $value): string
    {
        // Money: in MALAYSIAN RINGGIT, sent as 2 DECIMAL string
        return self::toDecimalString($value, 2);
    }
}
```

**Configuration for Unit Display (Future Enhancement):**
```php
// config/jnt.php
return [
    'units' => [
        'item_weight' => 'grams',        // Display unit for item weight
        'package_weight' => 'kilograms', // Display unit for package weight
        'dimensions' => 'centimeters',   // Display unit for dimensions
        'currency' => 'MYR',             // Currency code
    ],
    
    'display' => [
        'weight_decimals' => 0,          // Item weight display (grams = no decimals)
        'package_decimals' => 2,         // Package weight display (kg = 2 decimals)
        'dimension_decimals' => 2,       // Dimensions display (cm = 2 decimals)
        'money_decimals' => 2,           // Money display (MYR = 2 decimals)
    ],
];
```

**Critical API Rules (DOCUMENTED CLEARLY):**
- **Item weights** → Input: grams (any numeric) → API: `String(1-9999)` integer
- **Package weights** → Input: kilograms (any numeric) → API: `String(0.01-999.99)` 2dp
- **Dimensions** → Input: centimeters (any numeric) → API: `String(0.01-999.99)` 2dp
- **Money** → Input: MYR (any numeric) → API: `String(0.01-999999.99)` 2dp

**Documentation Required:**
Create `docs/UNITS_REFERENCE.md` with:
- **Crystal clear table** showing property → unit → API format
- **Code examples** for every field type
- **Common mistakes** section (e.g., "Don't pass kg for item weight!")
- **Visual guide** showing the transformation pipeline

---

### 4. ✅ Required vs Optional Fields

**Problem:**
No runtime validation of required fields causes API errors:
```php
// ❌ BAD - No validation, nullable everywhere
public function __construct(
    public ?string $orderId = null,
    public ?AddressData $sender = null,
    public ?array $items = null,
) {}
```

**Solution:**
Enforce required fields at runtime:
```php
// ✅ GOOD - Required fields not nullable
public function __construct(
    public string $orderId,              // REQUIRED
    public AddressData $sender,          // REQUIRED
    public AddressData $receiver,        // REQUIRED
    public array $items,                 // REQUIRED (must have items)
    public PackageInfoData $packageInfo, // REQUIRED
    public ?string $remark = null,       // OPTIONAL
    public ?float $insuranceValueMyr = null, // OPTIONAL
) {}
```

**Validation in OrderBuilder:**
```php
public function build(): array
{
    // 1. Validate required fields are present
    $this->validateRequiredFields();
    
    // 2. Validate field formats (phone: 10-15 digits, postal: 5 digits, etc.)
    $this->validateFieldFormats();
    
    // 3. Validate field ranges (weight: 0.01-999.99, quantity: 1-999, etc.)
    $this->validateFieldRanges();
    
    // 4. Validate string lengths (name: max 200, address: max 200, etc.)
    $this->validateFieldLengths();
    
    return $payload;
}

private function validateRequiredFields(): void
{
    if (empty($this->orderId)) {
        throw new \InvalidArgumentException(
            'orderId is required. Cannot create order without order ID.'
        );
    }
    
    if ($this->sender === null) {
        throw new \InvalidArgumentException(
            'sender is required. Cannot create order without sender address.'
        );
    }
    
    if (empty($this->items)) {
        throw new \InvalidArgumentException(
            'items is required. Cannot create order without at least one item.'
        );
    }
    
    // ... more validations
}

private function validateFieldFormats(): void
{
    // Phone number: 10-15 digits
    if (!preg_match('/^\d{10,15}$/', $this->sender->phone)) {
        throw new \InvalidArgumentException(
            "Sender phone must be 10-15 digits. Got: {$this->sender->phone}"
        );
    }
    
    // Postal code: 5 digits (Malaysia)
    if (!preg_match('/^\d{5}$/', $this->sender->postCode)) {
        throw new \InvalidArgumentException(
            "Sender postal code must be 5 digits. Got: {$this->sender->postCode}"
        );
    }
}

private function validateFieldRanges(): void
{
    // Package weight: 0.01-999.99 kg
    if (!TypeTransformer::isValidDecimalRange($this->packageInfo->weightKg, 0.01, 999.99)) {
        throw new \InvalidArgumentException(
            "Package weight must be 0.01-999.99 kg. Got: {$this->packageInfo->weightKg}kg"
        );
    }
    
    // Item quantity: 1-999 pieces
    foreach ($this->items as $item) {
        if (!TypeTransformer::isValidIntegerRange($item->quantity, 1, 999)) {
            throw new \InvalidArgumentException(
                "Item quantity must be 1-999. Got: {$item->quantity}"
            );
        }
    }
}
```

**Documentation Required:**
Create `docs/FIELD_REQUIREMENTS.md` with complete table:

| Field | Required | Type | Range/Length | Default | Unit |
|-------|----------|------|--------------|---------|------|
| orderId | ✅ Yes | String | Max 50 chars | - | - |
| sender | ✅ Yes | Object | - | - | - |
| sender.name | ✅ Yes | String | Max 200 chars | - | - |
| sender.phone | ✅ Yes | String | 10-15 digits | - | - |
| packageInfo.weightKg | ✅ Yes | Float | 0.01-999.99 | - | kg |
| insuranceValueMyr | ❌ No | Float | 0.01-999999.99 | null | MYR |
| remark | ❌ No | String | Max 200 chars | null | - |

---

### 5. 🚨 Error Code & Reason Enums

**Problem:**
Magic strings everywhere:
```php
// ❌ BAD - Magic strings, no IDE support, typo-prone
if ($response['code'] === '145003030') {
    throw new Exception('Signature failed');
}

$service->cancelOrder($orderId, 'Customer changed mind');  // Any random string
```

**Solution:**
Type-safe enums with descriptions:

**ErrorCode Enum:**
```php
enum ErrorCode: string
{
    // Authentication Errors (1xxx)
    case INVALID_SIGNATURE = '145003030';
    case INVALID_API_ACCOUNT = '145003010';
    case MISSING_TIMESTAMP = '145003053';
    
    // Validation Errors (2xxx)
    case MISSING_REQUIRED_FIELD = '999001010';
    case INVALID_FIELD_FORMAT = '145003050';
    case INVALID_POSTAL_CODE = '2006';
    case INVALID_WEIGHT = '2008';
    
    // Business Logic Errors (3xxx)
    case ORDER_NOT_FOUND = '999001030';
    case ORDER_ALREADY_CANCELLED = '3002';
    case ORDER_CANNOT_BE_CANCELLED = '999002010';
    case INSUFFICIENT_BALANCE = '3004';
    
    // System Errors (9xxx)
    case INTERNAL_SERVER_ERROR = '9001';
    case SERVICE_UNAVAILABLE = '9002';
    case TIMEOUT = '9003';
    
    public function getMessage(): string
    {
        return match ($this) {
            self::INVALID_SIGNATURE => 'Invalid signature. Check your private key.',
            self::ORDER_NOT_FOUND => 'Order not found with provided ID.',
            // ... all error messages
        };
    }
    
    public function isRetryable(): bool
    {
        return in_array($this, [
            self::SERVICE_UNAVAILABLE,
            self::TIMEOUT,
        ], true);
    }
    
    public function isClientError(): bool
    {
        return str_starts_with($this->value, '2') || str_starts_with($this->value, '3');
    }
}
```

**CancellationReason Enum:**
```php
enum CancellationReason: string
{
    case CUSTOMER_REQUESTED = 'Customer requested cancellation';
    case WRONG_ADDRESS = 'Wrong delivery address';
    case WRONG_ITEM = 'Wrong item ordered';
    case DUPLICATE_ORDER = 'Duplicate order';
    case PAYMENT_FAILED = 'Payment failed';
    case PRICE_CHANGED = 'Price changed';
    case OUT_OF_STOCK = 'Item out of stock';
    case DELIVERY_DELAY = 'Expected delivery delay';
    case CUSTOMER_UNREACHABLE = 'Customer unreachable';
    case BUSINESS_CLOSURE = 'Business closure';
    case SYSTEM_ERROR = 'System error';
    case OTHER = 'Other reason';
    
    public function getDescription(): string
    {
        return match ($this) {
            self::CUSTOMER_REQUESTED => 'Customer changed their mind',
            self::OUT_OF_STOCK => 'Product no longer available',
            // ... all descriptions
        };
    }
    
    public function requiresCustomerContact(): bool
    {
        return in_array($this, [
            self::CUSTOMER_REQUESTED,
            self::WRONG_ADDRESS,
            self::CUSTOMER_UNREACHABLE,
        ], true);
    }
}
```

**Usage:**
```php
// ✅ GOOD - Type-safe, IDE autocomplete, no typos
use MasyukAI\Jnt\Enums\CancellationReason;

$service->cancelOrder(
    orderId: 'ORD-123',
    reason: CancellationReason::OUT_OF_STOCK,  // Type-safe enum
    trackingNumber: 'JMX123456',
);

// Or custom reason
$service->cancelOrder(
    orderId: 'ORD-123',
    reason: CancellationReason::custom('Special request from management'),
);

// Error handling
try {
    $order = $service->createOrder($builder);
} catch (JntExpressException $e) {
    $errorCode = ErrorCode::fromValue($e->getCode());
    
    if ($errorCode?->isRetryable()) {
        // Retry logic
    }
    
    Log::error('J&T API Error', [
        'code' => $errorCode?->value,
        'message' => $errorCode?->getMessage(),
        'category' => $errorCode?->getCategory(),
    ]);
}
```

---

## 📊 Summary of Critical Requirements

| # | Requirement | Why Critical | Impact if Missing |
|---|-------------|--------------|-------------------|
| 1 | **Type Safety** | API rejects wrong formats | Production errors, failed orders |
| 2 | **Smart Enforcement** | Developer experience | Manual formatting required, error-prone |
| 3 | **Unit Clarity** | Prevent weight/dimension confusion | Wrong calculations, rejected shipments |
| 4 | **Required Validation** | Catch errors before API call | API errors, wasted API calls |
| 5 | **Error/Reason Enums** | Type-safe error handling | Magic strings, no IDE support, typos |

---

## 🎯 Implementation Checklist

### Phase 2.5 Must Complete:

- [ ] **Create TypeTransformer class** with methods:
  - [ ] `toIntegerString()` - Convert to integer string
  - [ ] `toDecimalString()` - Convert to 2dp float string
  - [ ] `toBooleanString()` - Convert to Y/N
  - [ ] `isValidIntegerRange()` - Validate integer ranges
  - [ ] `isValidDecimalRange()` - Validate decimal ranges
  - [ ] `isValidStringLength()` - Validate string lengths

- [ ] **Update all Data classes** (6 files):
  - [ ] Add unit suffixes to property names (weightKg, weightGrams, lengthCm, priceMyr)
  - [ ] Use TypeTransformer in toApiArray()
  - [ ] Accept developer-friendly types (int|float|string)
  - [ ] Add property-level validation

- [ ] **Create ErrorCode enum**:
  - [ ] ~40+ error codes from API docs
  - [ ] getMessage() method
  - [ ] isRetryable() method
  - [ ] isClientError() / isServerError() methods
  - [ ] getCategory() method

- [ ] **Create CancellationReason enum**:
  - [ ] 12 predefined reasons
  - [ ] getDescription() method
  - [ ] Helper methods (requiresCustomerContact, isMerchantResponsibility)
  - [ ] custom() method for custom reasons

- [ ] **Update cancelOrder() signature**:
  - [ ] Accept CancellationReason|string
  - [ ] Add validation for reason length (max 300 chars)

- [ ] **Add validation to OrderBuilder**:
  - [ ] validateRequiredFields()
  - [ ] validateFieldFormats()
  - [ ] validateFieldRanges()
  - [ ] validateFieldLengths()

- [ ] **Create documentation**:
  - [ ] `docs/UNITS_REFERENCE.md` - Complete unit reference
  - [ ] `docs/FIELD_REQUIREMENTS.md` - Required vs optional table
  - [ ] `docs/TYPE_TRANSFORMATION_GUIDE.md` - Developer guide

- [ ] **Write comprehensive tests**:
  - [ ] TypeTransformer unit tests (10 tests)
  - [ ] ErrorCode enum tests (5 tests)
  - [ ] CancellationReason enum tests (3 tests)
  - [ ] Data class validation tests (10+ tests)
  - [ ] Integration tests for type transformations

---

## ✅ Definition of Done

Phase 2.5 complete when:

✅ Every field has correct type declaration (int|float|string, not just string)  
✅ Every field has proper API transformation using TypeTransformer  
✅ Every unit is in property name (weightKg, weightGrams, lengthCm, priceMyr)  
✅ Every required field is validated at runtime  
✅ Every optional field has sensible default or null  
✅ Error codes are type-safe enum with ~40+ codes  
✅ Cancellation reasons are type-safe enum with 12+ values  
✅ All tests passing (100% coverage for new code)  
✅ Documentation complete with examples  
✅ Zero ambiguity in units or requirements  

---

## 🚀 After Phase 2.5

With Phase 2.5 complete, the package will have:

✅ **Production-ready quality** - Correct types prevent API errors  
✅ **Type safety at runtime** - Catch errors before API call  
✅ **Clear developer experience** - Property names include units  
✅ **Smart type handling** - Accept ints/floats, send correct strings  
✅ **Proper validation** - Required fields enforced, ranges checked  
✅ **Type-safe enums** - No magic strings for errors/reasons  

**Then** we can confidently proceed to Phase 3 (Webhooks) knowing all data types are correct!

---

**See:** `docs/PHASE_2.5_TYPE_SAFETY_API_COMPLIANCE.md` for complete implementation details.
