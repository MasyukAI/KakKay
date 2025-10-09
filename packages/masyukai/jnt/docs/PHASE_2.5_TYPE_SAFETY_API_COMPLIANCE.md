# Phase 2.5: Type Safety & API Compliance 🎯

**Priority:** 🔴 **CRITICAL - MUST BE IMPLEMENTED BEFORE PHASE 3**  
**Status:** Planned  
**Estimated Duration:** Week 2-3

---

## 🚨 Critical Requirements

This phase is **THE MOST CRITICAL** for ensuring production-ready quality. It focuses on:

1. ✅ **Type Safety** - Correct types for send/receive data
2. ✅ **API Format Compliance** - Match exact API requirements (integer strings, 2-decimal floats, etc.)
3. ✅ **Smart Type Enforcement** - Accept developer-friendly types, send API-compliant formats
4. ✅ **Unit Clarity** - Clear documentation of units (cm/kg/grams)
5. ✅ **Required/Optional Validation** - Proper field validation
6. ✅ **Error/Reason Enums** - Type-safe error handling

---

## 📋 Phase 2.5 Task Breakdown

### Task 1: Type System Audit & Validation 🔍

**Goal:** Verify EVERY field sends/receives correct type per official API docs

#### Subtask 1.1: Audit All Data Classes

**Files to Audit:**
- ✅ `src/Data/AddressData.php`
- ✅ `src/Data/ItemData.php`
- ✅ `src/Data/PackageInfoData.php`
- ✅ `src/Data/OrderData.php`
- ✅ `src/Data/TrackingData.php`
- ✅ `src/Data/TrackingDetailData.php`
- ⏳ `src/Data/PrintWaybillData.php` (Phase 2)

**What to Check:**
1. **Property Type Declarations**
   ```php
   // ❌ BAD - Loses type information
   public readonly string $weight;
   
   // ✅ GOOD - Accepts int|float|string, enforces transformation
   public readonly int|float|string $weight;
   ```

2. **API Format Requirements** (from official docs)
   - `String(1-999)` = INTEGER sent as string → Accept `int|string`, send as `(string)(int)$value`
   - `String(0.01-999.99)` = FLOAT with 2dp → Accept `float|string`, send as `number_format($value, 2, '.', '')`
   - `String(255)` = TEXT string → Accept `string`, send as-is
   - `String(Y/N)` = BOOLEAN flag → Create enum or accept `bool`, send as 'Y'/'N'

3. **Units Must Be Crystal Clear**
   ```php
   // ❌ BAD - Ambiguous
   public readonly float $weight;
   
   // ✅ GOOD - Unit in property name
   public readonly float $weightInGrams;    // Or $itemWeightGrams
   public readonly float $weightInKg;       // Or $packageWeightKg
   ```

#### Subtask 1.2: Document Current Type Issues

Create comprehensive audit document listing:
- Property name
- Current type declaration
- API requirement (from docs)
- What we accept (developer input)
- What we send (API format)
- Required vs Optional
- Default value (if optional)

**Example Format:**

| Property | Current Type | API Requirement | Developer Input | API Output | Required | Unit |
|----------|--------------|-----------------|-----------------|------------|----------|------|
| `itemWeightGrams` | `float\|string` | String(1-9999) integer | `int\|float\|string` | `(string)(int)$value` | ✅ Yes | grams |
| `packageWeightKg` | `float\|string` | String(0.01-999.99) 2dp | `float\|string` | `number_format(2)` | ✅ Yes | kg |
| `quantity` | `int\|string` | String(1-999) integer | `int\|string` | `(string)(int)$value` | ✅ Yes | pieces |
| `insuranceValue` | `float\|string` | String(0.01-999.99) 2dp | `float\|string` | `number_format(2)` | ❌ No | MYR |

---

### Task 2: Smart Type System Implementation 🧠

**Goal:** Package accepts developer-friendly types, handles conversion automatically

#### Subtask 2.1: Create Type Transformation Helpers

**New File:** `src/Support/TypeTransformer.php`

```php
<?php

declare(strict_types=1);

namespace MasyukAI\Jnt\Support;

/**
 * Handles type transformations between developer-friendly types and API requirements
 */
class TypeTransformer
{
    /**
     * Convert to integer string (for quantities, counts, etc.)
     * 
     * API Format: String(1-999) - integer values sent as strings
     * 
     * @param int|float|string $value
     * @return string Integer formatted as string
     * 
     * @example
     * toIntegerString(5) → "5"
     * toIntegerString(5.7) → "5"
     * toIntegerString("5") → "5"
     */
    public static function toIntegerString(int|float|string $value): string
    {
        return (string)(int)$value;
    }

    /**
     * Convert to 2-decimal float string (for money, weights in kg, etc.)
     * 
     * API Format: String(0.01-999.99) - floats with exactly 2 decimal places
     * 
     * @param float|int|string $value
     * @return string Float formatted as string with 2 decimal places
     * 
     * @example
     * toDecimalString(5) → "5.00"
     * toDecimalString(5.1) → "5.10"
     * toDecimalString(5.456) → "5.46"
     * toDecimalString("5") → "5.00"
     */
    public static function toDecimalString(float|int|string $value, int $decimals = 2): string
    {
        return number_format((float)$value, $decimals, '.', '');
    }

    /**
     * Convert boolean to Y/N string
     * 
     * API Format: String(Y/N) - boolean flags sent as Y or N
     * 
     * @param bool|string $value
     * @return string 'Y' or 'N'
     * 
     * @example
     * toBooleanString(true) → "Y"
     * toBooleanString(false) → "N"
     * toBooleanString("Y") → "Y"
     */
    public static function toBooleanString(bool|string $value): string
    {
        if (is_string($value)) {
            return strtoupper($value) === 'Y' ? 'Y' : 'N';
        }
        
        return $value ? 'Y' : 'N';
    }

    /**
     * Convert Y/N string to boolean
     * 
     * @param string|bool $value
     * @return bool
     * 
     * @example
     * fromBooleanString('Y') → true
     * fromBooleanString('N') → false
     * fromBooleanString(true) → true
     */
    public static function fromBooleanString(string|bool $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        
        return strtoupper($value) === 'Y';
    }

    /**
     * Validate integer range
     * 
     * @param int|float|string $value
     * @param int $min
     * @param int $max
     * @return bool
     */
    public static function isValidIntegerRange(int|float|string $value, int $min, int $max): bool
    {
        $intValue = (int)$value;
        return $intValue >= $min && $intValue <= $max;
    }

    /**
     * Validate decimal range
     * 
     * @param float|int|string $value
     * @param float $min
     * @param float $max
     * @return bool
     */
    public static function isValidDecimalRange(float|int|string $value, float $min, float $max): bool
    {
        $floatValue = (float)$value;
        return $floatValue >= $min && $floatValue <= $max;
    }

    /**
     * Validate string length
     * 
     * @param string $value
     * @param int $maxLength
     * @return bool
     */
    public static function isValidStringLength(string $value, int $maxLength): bool
    {
        return strlen($value) <= $maxLength;
    }
}
```

#### Subtask 2.2: Update All Data Classes to Use TypeTransformer

**Example: ItemData.php**

```php
<?php

declare(strict_types=1);

namespace MasyukAI\Jnt\Data;

use MasyukAI\Jnt\Support\TypeTransformer;

/**
 * Represents an item in the shipment
 */
readonly class ItemData
{
    /**
     * @param string $itemName Item description/name (Required, max 200 chars)
     * @param int|string $quantity Number of items (Required, 1-999, integer)
     * @param float|string $itemWeightGrams Weight per item in GRAMS (Required, 1-9999, integer)
     * @param float|string $unitPriceMyr Unit price in MYR (Required, 0.01-999.99, 2 decimals)
     * @param string|null $itemUrl Product URL (Optional, max 500 chars)
     */
    public function __construct(
        public string $itemName,
        public int|string $quantity,
        public float|string $itemWeightGrams,
        public float|string $unitPriceMyr,
        public ?string $itemUrl = null,
    ) {}

    /**
     * Convert to API array format
     * 
     * CRITICAL TRANSFORMATIONS:
     * - quantity: int → string (integer format)
     * - itemWeightGrams: float → string (integer format, API expects grams as integer)
     * - unitPriceMyr: float → string (2 decimal format, API expects money with 2dp)
     */
    public function toApiArray(): array
    {
        $data = [
            'itemname' => $this->itemName,
            'number' => TypeTransformer::toIntegerString($this->quantity),
            'itemweight' => TypeTransformer::toIntegerString($this->itemWeightGrams), // Grams as integer
            'itemprice' => TypeTransformer::toDecimalString($this->unitPriceMyr, 2),   // MYR with 2dp
        ];

        if ($this->itemUrl !== null) {
            $data['itemurl'] = $this->itemUrl;
        }

        return $data;
    }

    /**
     * Create from API response
     */
    public static function fromApiArray(array $data): self
    {
        return new self(
            itemName: $data['itemname'] ?? '',
            quantity: $data['number'] ?? 1,
            itemWeightGrams: $data['itemweight'] ?? 0,
            unitPriceMyr: $data['itemprice'] ?? 0.0,
            itemUrl: $data['itemurl'] ?? null,
        );
    }
}
```

---

### Task 3: Unit Documentation & Validation 📏

**Goal:** Make units crystal clear to prevent developer confusion

**Approach:** Keep property names clean (`weight`, `length`, `price`) but be **VERY CLEAR** in documentation and use smart transformers.

#### Subtask 3.1: Smart TypeTransformer with Context-Aware Methods

**Update:** `src/Support/TypeTransformer.php`

```php
<?php

declare(strict_types=1);

namespace MasyukAI\Jnt\Support;

/**
 * Handles type transformations between developer-friendly types and API requirements
 * 
 * Uses context-aware methods to handle different unit requirements:
 * - Item weights: GRAMS (integer)
 * - Package weights: KILOGRAMS (2 decimals)
 * - Dimensions: CENTIMETERS (2 decimals)
 * - Money: MALAYSIAN RINGGIT (2 decimals)
 */
class TypeTransformer
{
    /**
     * Convert to integer string (for quantities, counts, etc.)
     * 
     * API Format: String(1-999) - integer values sent as strings
     * 
     * @param int|float|string $value
     * @return string Integer formatted as string
     * 
     * @example
     * toIntegerString(5) → "5"
     * toIntegerString(5.7) → "5"
     * toIntegerString("5") → "5"
     */
    public static function toIntegerString(int|float|string $value): string
    {
        return (string)(int)$value;
    }

    /**
     * Convert to N-decimal float string
     * 
     * API Format: String with exact decimal places
     * 
     * @param float|int|string $value
     * @param int $decimals Number of decimal places
     * @return string Float formatted as string with N decimal places
     * 
     * @example
     * toDecimalString(5, 2) → "5.00"
     * toDecimalString(5.1, 2) → "5.10"
     * toDecimalString(5.456, 2) → "5.46"
     */
    public static function toDecimalString(float|int|string $value, int $decimals = 2): string
    {
        return number_format((float)$value, $decimals, '.', '');
    }

    /**
     * Transform item weight (GRAMS → integer string)
     * 
     * Items are measured in GRAMS and sent as INTEGER strings
     * 
     * @param float|int|string $grams Weight in grams (1-9999)
     * @return string Weight as integer string
     * 
     * @example
     * forItemWeight(500) → "500"
     * forItemWeight(500.5) → "500"
     */
    public static function forItemWeight(float|int|string $grams): string
    {
        return self::toIntegerString($grams);
    }

    /**
     * Transform package weight (KILOGRAMS → 2 decimal string)
     * 
     * Packages are measured in KILOGRAMS and sent with 2 DECIMALS
     * 
     * @param float|int|string $kg Weight in kilograms (0.01-999.99)
     * @return string Weight as 2-decimal string
     * 
     * @example
     * forPackageWeight(5) → "5.00"
     * forPackageWeight(5.5) → "5.50"
     * forPackageWeight(5.456) → "5.46"
     */
    public static function forPackageWeight(float|int|string $kg): string
    {
        return self::toDecimalString($kg, 2);
    }

    /**
     * Transform dimension (CENTIMETERS → 2 decimal string)
     * 
     * Dimensions are measured in CENTIMETERS and sent with 2 DECIMALS
     * 
     * @param float|int|string $cm Dimension in centimeters (0.01-999.99)
     * @return string Dimension as 2-decimal string
     * 
     * @example
     * forDimension(25) → "25.00"
     * forDimension(25.5) → "25.50"
     */
    public static function forDimension(float|int|string $cm): string
    {
        return self::toDecimalString($cm, 2);
    }

    /**
     * Transform money (MALAYSIAN RINGGIT → 2 decimal string)
     * 
     * Money is in MALAYSIAN RINGGIT (MYR) and sent with 2 DECIMALS
     * 
     * @param float|int|string $myr Amount in MYR (0.01-999999.99)
     * @return string Money as 2-decimal string
     * 
     * @example
     * forMoney(19.9) → "19.90"
     * forMoney(150) → "150.00"
     */
    public static function forMoney(float|int|string $myr): string
    {
        return self::toDecimalString($myr, 2);
    }

    /**
     * Convert boolean to Y/N string
     * 
     * API Format: String(Y/N) - boolean flags sent as Y or N
     * 
     * @param bool|string $value
     * @return string 'Y' or 'N'
     */
    public static function toBooleanString(bool|string $value): string
    {
        if (is_string($value)) {
            return strtoupper($value) === 'Y' ? 'Y' : 'N';
        }
        
        return $value ? 'Y' : 'N';
    }

    /**
     * Convert Y/N string to boolean
     * 
     * @param string|bool $value
     * @return bool
     */
    public static function fromBooleanString(string|bool $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        
        return strtoupper($value) === 'Y';
    }

    /**
     * Validate integer range
     * 
     * @param int|float|string $value
     * @param int $min
     * @param int $max
     * @return bool
     */
    public static function isValidIntegerRange(int|float|string $value, int $min, int $max): bool
    {
        $intValue = (int)$value;
        return $intValue >= $min && $intValue <= $max;
    }

    /**
     * Validate decimal range
     * 
     * @param float|int|string $value
     * @param float $min
     * @param float $max
     * @return bool
     */
    public static function isValidDecimalRange(float|int|string $value, float $min, float $max): bool
    {
        $floatValue = (float)$value;
        return $floatValue >= $min && $floatValue <= $max;
    }

    /**
     * Validate string length
     * 
     * @param string $value
     * @param int $maxLength
     * @return bool
     */
    public static function isValidStringLength(string $value, int $maxLength): bool
    {
        return strlen($value) <= $maxLength;
    }
}
```

#### Subtask 3.2: Update Data Classes with Clean Property Names

**Example: ItemData.php**

```php
<?php

declare(strict_types=1);

namespace MasyukAI\Jnt\Data;

use MasyukAI\Jnt\Support\TypeTransformer;

/**
 * Represents an item in the shipment
 */
readonly class ItemData
{
    /**
     * @param string $itemName Item description/name (Required, max 200 chars)
     * @param int|string $quantity Number of items (Required, 1-999 pieces)
     * @param float|string $weight Weight per item in GRAMS (Required, 1-9999 grams, sent as integer)
     * @param float|string $price Unit price in MYR (Required, 0.01-999.99 MYR, sent with 2 decimals)
     * @param string|null $itemUrl Product URL (Optional, max 500 chars)
     */
    public function __construct(
        public string $itemName,
        public int|string $quantity,
        public float|string $weight,       // GRAMS - integer format
        public float|string $price,        // MYR - 2 decimal format
        public ?string $itemUrl = null,
    ) {}

    /**
     * Convert to API array format
     * 
     * CRITICAL TRANSFORMATIONS:
     * - quantity: any numeric → string (integer format)
     * - weight: any numeric IN GRAMS → string (integer format, API expects grams as integer)
     * - price: any numeric IN MYR → string (2 decimal format, API expects money with 2dp)
     */
    public function toApiArray(): array
    {
        $data = [
            'itemname' => $this->itemName,
            'number' => TypeTransformer::toIntegerString($this->quantity),
            'itemweight' => TypeTransformer::forItemWeight($this->weight),  // Smart: grams → integer string
            'itemprice' => TypeTransformer::forMoney($this->price),         // Smart: MYR → 2dp string
        ];

        if ($this->itemUrl !== null) {
            $data['itemurl'] = $this->itemUrl;
        }

        return $data;
    }

    /**
     * Create from API response
     */
    public static function fromApiArray(array $data): self
    {
        return new self(
            itemName: $data['itemname'] ?? '',
            quantity: $data['number'] ?? 1,
            weight: $data['itemweight'] ?? 0,  // Returns grams
            price: $data['itemprice'] ?? 0.0,  // Returns MYR
            itemUrl: $data['itemurl'] ?? null,
        );
    }
}
```

**Example: PackageInfoData.php**

```php
<?php

declare(strict_types=1);

namespace MasyukAI\Jnt\Data;

use MasyukAI\Jnt\Support\TypeTransformer;

/**
 * Represents package information
 */
readonly class PackageInfoData
{
    /**
     * @param int|string $quantity Number of packages (Required, 1-999 pieces)
     * @param float|string $weight Total weight in KILOGRAMS (Required, 0.01-999.99 kg, sent with 2 decimals)
     * @param float|string $length Length in CENTIMETERS (Required, 0.01-999.99 cm, sent with 2 decimals)
     * @param float|string $width Width in CENTIMETERS (Required, 0.01-999.99 cm, sent with 2 decimals)
     * @param float|string $height Height in CENTIMETERS (Required, 0.01-999.99 cm, sent with 2 decimals)
     */
    public function __construct(
        public int|string $quantity,
        public float|string $weight,      // KILOGRAMS - 2dp format
        public float|string $length,      // CENTIMETERS - 2dp format
        public float|string $width,       // CENTIMETERS - 2dp format
        public float|string $height,      // CENTIMETERS - 2dp format
    ) {}

    /**
     * Convert to API array format
     * 
     * CRITICAL TRANSFORMATIONS:
     * - quantity: any numeric → string (integer format)
     * - weight: any numeric IN KILOGRAMS → string (2 decimal format)
     * - dimensions: any numeric IN CENTIMETERS → string (2 decimal format)
     */
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

    /**
     * Create from API response
     */
    public static function fromApiArray(array $data): self
    {
        return new self(
            quantity: $data['number'] ?? 1,
            weight: $data['weight'] ?? 0.0,    // Returns kilograms
            length: $data['length'] ?? 0.0,    // Returns centimeters
            width: $data['width'] ?? 0.0,      // Returns centimeters
            height: $data['height'] ?? 0.0,    // Returns centimeters
        );
    }
}
```

#### Subtask 3.3: Create Comprehensive Unit Reference Documentation

**New File:** `docs/UNITS_REFERENCE.md`

```markdown
# Units Reference - J&T Express API

## 🎯 Quick Reference Table

| Property | Data Class | Unit | API Format | Example Input | Example Output |
|----------|-----------|------|------------|---------------|----------------|
| `weight` | ItemData | **GRAMS** | Integer string | `500` or `500.5` | `"500"` |
| `weight` | PackageInfoData | **KILOGRAMS** | 2-decimal string | `5.5` or `5` | `"5.50"` |
| `length/width/height` | PackageInfoData | **CENTIMETERS** | 2-decimal string | `25.5` or `25` | `"25.50"` |
| `price` | ItemData | **MYR** | 2-decimal string | `19.9` or `19.90` | `"19.90"` |
| `quantity` | All | **PIECES** | Integer string | `5` or `5.0` | `"5"` |

## Weight Units ⚖️

### Item Weight (ItemData)
- **Unit:** GRAMS (integer values)
- **Property:** `weight`
- **API Field:** `itemweight`
- **API Format:** String(1-9999) - integer only
- **Transformer:** `TypeTransformer::forItemWeight()`
- **Range:** 1-9999 grams

**Examples:**
```php
// All these inputs work
$item = new ItemData(
    weight: 500,        // int → "500"
    weight: 500.7,      // float → "500" (truncated)
    weight: "500",      // string → "500"
);

// API receives: "500" (integer string)
```

### Package Weight (PackageInfoData)
- **Unit:** KILOGRAMS (2 decimal precision)
- **Property:** `weight`
- **API Field:** `weight`
- **API Format:** String(0.01-999.99) - 2 decimal places
- **Transformer:** `TypeTransformer::forPackageWeight()`
- **Range:** 0.01-999.99 kg

**Examples:**
```php
// All these inputs work
$package = new PackageInfoData(
    weight: 5,          // int → "5.00"
    weight: 5.5,        // float → "5.50"
    weight: 5.456,      // float → "5.46" (rounded)
    weight: "5.5",      // string → "5.50"
);

// API receives: "5.50" (2-decimal string)
```

**⚠️ CRITICAL:** Items use GRAMS (integer), Packages use KILOGRAMS (2dp)

## Dimension Units 📦

### Package Dimensions (PackageInfoData)
- **Unit:** CENTIMETERS (2 decimal precision)
- **Properties:** `length`, `width`, `height`
- **API Fields:** `length`, `width`, `height`
- **API Format:** String(0.01-999.99) - 2 decimal places
- **Transformer:** `TypeTransformer::forDimension()`
- **Range:** 0.01-999.99 cm

**Examples:**
```php
$package = new PackageInfoData(
    length: 25,         // int → "25.00"
    width: 20.5,        // float → "20.50"
    height: 15.75,      // float → "15.75"
);

// API receives: "25.00", "20.50", "15.75"
```

## Currency Units 💰

### Monetary Values (ItemData)
- **Unit:** MALAYSIAN RINGGIT (MYR) (2 decimal precision)
- **Property:** `price`
- **API Field:** `itemprice`
- **API Format:** String(0.01-999999.99) - 2 decimal places
- **Transformer:** `TypeTransformer::forMoney()`
- **Range:** 0.01-999999.99 MYR

**Examples:**
```php
$item = new ItemData(
    price: 150,         // int → "150.00"
    price: 150.5,       // float → "150.50"
    price: 19.9,        // float → "19.90"
    price: "150.50",    // string → "150.50"
);

// API receives: "150.50" (2-decimal string)
```

## Quantity Units 🔢

### Counts (All Data Classes)
- **Unit:** PIECES (integer values)
- **Property:** `quantity`
- **API Field:** `number`
- **API Format:** String(1-999) - integer only
- **Transformer:** `TypeTransformer::toIntegerString()`
- **Range:** 1-999 pieces

**Examples:**
```php
$item = new ItemData(
    quantity: 5,        // int → "5"
    quantity: 5.7,      // float → "5" (truncated)
    quantity: "5",      // string → "5"
);

// API receives: "5" (integer string)
```

## 🚨 Common Mistakes

### ❌ WRONG: Using wrong units
```php
// DON'T pass kilograms for item weight!
$item = new ItemData(
    weight: 0.5,  // ❌ This is 0.5 grams, not 0.5 kg!
);
```

### ✅ CORRECT: Use grams for items
```php
// DO pass grams for item weight
$item = new ItemData(
    weight: 500,  // ✅ 500 grams
);
```

### ❌ WRONG: Manual string formatting
```php
// DON'T format strings manually
$package = new PackageInfoData(
    weight: "5.5",  // Works, but not necessary
);
```

### ✅ CORRECT: Let transformer handle it
```php
// DO pass raw numbers
$package = new PackageInfoData(
    weight: 5.5,  // ✅ Transformer handles formatting
);
```

## 🔄 Transformation Pipeline

```
Developer Input → Type Transformer → API Format

Example: Item Weight
500 (int) → forItemWeight() → "500" (integer string)

Example: Package Weight  
5.5 (float) → forPackageWeight() → "5.50" (2-decimal string)

Example: Dimension
25 (int) → forDimension() → "25.00" (2-decimal string)

Example: Money
19.9 (float) → forMoney() → "19.90" (2-decimal string)
```

## 📝 Configuration (Future Enhancement)

```php
// config/jnt.php
return [
    'units' => [
        'item_weight' => 'grams',
        'package_weight' => 'kilograms',
        'dimensions' => 'centimeters',
        'currency' => 'MYR',
    ],
    
    'display' => [
        'weight_decimals' => 0,      // Item weight (grams)
        'package_decimals' => 2,     // Package weight (kg)
        'dimension_decimals' => 2,   // Dimensions (cm)
        'money_decimals' => 2,       // Money (MYR)
    ],
];
```
```

#### Subtask 3.4: Add Unit Validation in Data Classes

```php
/**
 * Validate item weight is in acceptable range (1-9999 grams)
 * 
 * @throws \InvalidArgumentException If weight is out of range
 */
private function validateItemWeight(float|string $weightGrams): void
{
    if (!TypeTransformer::isValidIntegerRange($weightGrams, 1, 9999)) {
        throw new \InvalidArgumentException(
            "Item weight must be between 1-9999 grams. Got: {$weightGrams}g"
        );
    }
}

/**
 * Validate package weight is in acceptable range (0.01-999.99 kg)
 * 
 * @throws \InvalidArgumentException If weight is out of range
 */
{
    if (!TypeTransformer::isValidDecimalRange($weightKg, 0.01, 999.99)) {
        throw new \InvalidArgumentException(
            "Package weight must be between 0.01-999.99 kg. Got: {$weightKg}kg"
        );
    }
}
```

---

### Task 4: Required vs Optional Field Validation ✅

**Goal:** Enforce required fields at runtime, provide sensible defaults for optional fields

#### Subtask 4.1: Create Field Validation Map

**New File:** `docs/FIELD_REQUIREMENTS.md`

Table showing EVERY field with Required/Optional status per official API docs.

#### Subtask 4.2: Implement Validation in OrderBuilder

```php
public function build(): array
{
    // Validate all required fields
    $this->validateRequiredFields();
    $this->validateFieldFormats();
    $this->validateFieldRanges();
    
    // Build payload...
}

private function validateRequiredFields(): void
{
    $required = [
        'orderId' => $this->orderId,
        'sender' => $this->sender,
        'receiver' => $this->receiver,
        'items' => $this->items,
        'packageInfo' => $this->packageInfo,
    ];
    
    foreach ($required as $field => $value) {
        if ($value === null || (is_array($value) && empty($value))) {
            throw new \InvalidArgumentException(
                "Required field '{$field}' is missing. " .
                "Consult docs/FIELD_REQUIREMENTS.md for required fields."
            );
        }
    }
}

private function validateFieldFormats(): void
{
    // Validate phone numbers (10-15 digits)
    if (!preg_match('/^\d{10,15}$/', $this->sender->phone)) {
        throw new \InvalidArgumentException(
            "Sender phone must be 10-15 digits. Got: {$this->sender->phone}"
        );
    }
    
    // Validate postal codes (5 digits for Malaysia)
    if (!preg_match('/^\d{5}$/', $this->sender->postCode)) {
        throw new \InvalidArgumentException(
            "Sender postal code must be 5 digits. Got: {$this->sender->postCode}"
        );
    }
    
    // ... more format validations
}
```

---

### Task 5: Error Code & Reason Enums 🚨

**Goal:** Type-safe error handling with descriptive enums

#### Subtask 5.1: Create Error Code Enum

**New File:** `src/Enums/ErrorCode.php`

```php
<?php

declare(strict_types=1);

namespace MasyukAI\Jnt\Enums;

/**
 * J&T Express API error codes
 * 
 * Based on official API documentation
 */
enum ErrorCode: string
{
    // Authentication Errors (1xxx)
    case INVALID_SIGNATURE = '1001';
    case MISSING_TIMESTAMP = '1002';
    case EXPIRED_TIMESTAMP = '1003';
    case INVALID_API_ACCOUNT = '1004';
    case INSUFFICIENT_PERMISSIONS = '1005';

    // Validation Errors (2xxx)
    case MISSING_REQUIRED_FIELD = '2001';
    case INVALID_FIELD_FORMAT = '2002';
    case INVALID_FIELD_LENGTH = '2003';
    case INVALID_FIELD_RANGE = '2004';
    case DUPLICATE_ORDER_ID = '2005';
    case INVALID_POSTAL_CODE = '2006';
    case INVALID_PHONE_NUMBER = '2007';
    case INVALID_WEIGHT = '2008';
    case INVALID_DIMENSIONS = '2009';

    // Business Logic Errors (3xxx)
    case ORDER_NOT_FOUND = '3001';
    case ORDER_ALREADY_CANCELLED = '3002';
    case ORDER_CANNOT_BE_CANCELLED = '3003';
    case INSUFFICIENT_BALANCE = '3004';
    case SERVICE_NOT_AVAILABLE = '3005';
    case DELIVERY_AREA_NOT_COVERED = '3006';

    // System Errors (9xxx)
    case INTERNAL_SERVER_ERROR = '9001';
    case SERVICE_UNAVAILABLE = '9002';
    case TIMEOUT = '9003';
    case RATE_LIMIT_EXCEEDED = '9004';

    /**
     * Get human-readable error message
     */
    public function getMessage(): string
    {
        return match ($this) {
            // Authentication
            self::INVALID_SIGNATURE => 'Invalid signature. Check your private key and signature generation.',
            self::MISSING_TIMESTAMP => 'Missing timestamp in request headers.',
            self::EXPIRED_TIMESTAMP => 'Request timestamp has expired. Check server time synchronization.',
            self::INVALID_API_ACCOUNT => 'Invalid API account. Check your apiAccount credential.',
            self::INSUFFICIENT_PERMISSIONS => 'API account does not have permission for this operation.',

            // Validation
            self::MISSING_REQUIRED_FIELD => 'Required field is missing from request.',
            self::INVALID_FIELD_FORMAT => 'Field format is invalid. Check field type and format.',
            self::INVALID_FIELD_LENGTH => 'Field length exceeds maximum allowed length.',
            self::INVALID_FIELD_RANGE => 'Field value is outside acceptable range.',
            self::DUPLICATE_ORDER_ID => 'Order ID already exists. Use a unique order ID.',
            self::INVALID_POSTAL_CODE => 'Invalid postal code format. Must be 5 digits for Malaysia.',
            self::INVALID_PHONE_NUMBER => 'Invalid phone number format. Must be 10-15 digits.',
            self::INVALID_WEIGHT => 'Weight is outside acceptable range.',
            self::INVALID_DIMENSIONS => 'Dimensions are outside acceptable range.',

            // Business Logic
            self::ORDER_NOT_FOUND => 'Order not found with the provided order ID or tracking number.',
            self::ORDER_ALREADY_CANCELLED => 'Order has already been cancelled.',
            self::ORDER_CANNOT_BE_CANCELLED => 'Order cannot be cancelled in its current status.',
            self::INSUFFICIENT_BALANCE => 'Insufficient account balance to create shipment.',
            self::SERVICE_NOT_AVAILABLE => 'Requested service is not available for this route.',
            self::DELIVERY_AREA_NOT_COVERED => 'Delivery area is not covered by J&T Express.',

            // System
            self::INTERNAL_SERVER_ERROR => 'Internal server error. Contact J&T Express support.',
            self::SERVICE_UNAVAILABLE => 'Service is temporarily unavailable. Try again later.',
            self::TIMEOUT => 'Request timed out. Check network connection and try again.',
            self::RATE_LIMIT_EXCEEDED => 'Rate limit exceeded. Reduce request frequency.',
        };
    }

    /**
     * Check if error is retryable
     */
    public function isRetryable(): bool
    {
        return in_array($this, [
            self::SERVICE_UNAVAILABLE,
            self::TIMEOUT,
            self::RATE_LIMIT_EXCEEDED,
        ], true);
    }

    /**
     * Check if error is due to client mistake
     */
    public function isClientError(): bool
    {
        return str_starts_with($this->value, '2') || str_starts_with($this->value, '3');
    }

    /**
     * Check if error is due to server issue
     */
    public function isServerError(): bool
    {
        return str_starts_with($this->value, '9');
    }

    /**
     * Get error category
     */
    public function getCategory(): string
    {
        return match (true) {
            str_starts_with($this->value, '1') => 'Authentication',
            str_starts_with($this->value, '2') => 'Validation',
            str_starts_with($this->value, '3') => 'Business Logic',
            str_starts_with($this->value, '9') => 'System',
            default => 'Unknown',
        };
    }

    /**
     * Create from string value
     */
    public static function fromValue(string $value): ?self
    {
        return self::tryFrom($value);
    }
}
```

#### Subtask 5.2: Create Cancellation Reason Enum

**New File:** `src/Enums/CancellationReason.php`

```php
<?php

declare(strict_types=1);

namespace MasyukAI\Jnt\Enums;

/**
 * Predefined cancellation reasons for orders
 * 
 * Based on common business scenarios and J&T Express guidelines
 */
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

    /**
     * Get description of the cancellation reason
     */
    public function getDescription(): string
    {
        return match ($this) {
            self::CUSTOMER_REQUESTED => 'Customer changed their mind and requested order cancellation',
            self::WRONG_ADDRESS => 'Incorrect or incomplete delivery address provided',
            self::WRONG_ITEM => 'Customer ordered wrong item or changed product preference',
            self::DUPLICATE_ORDER => 'Order was created multiple times by mistake',
            self::PAYMENT_FAILED => 'Payment transaction failed or was declined',
            self::PRICE_CHANGED => 'Product price changed after order was placed',
            self::OUT_OF_STOCK => 'Ordered item is no longer available in stock',
            self::DELIVERY_DELAY => 'Expected delivery time exceeds customer requirements',
            self::CUSTOMER_UNREACHABLE => 'Unable to contact customer for order confirmation',
            self::BUSINESS_CLOSURE => 'Merchant business closed or suspended',
            self::SYSTEM_ERROR => 'Technical error in order processing',
            self::OTHER => 'Reason not listed in predefined categories',
        };
    }

    /**
     * Check if reason requires customer contact
     */
    public function requiresCustomerContact(): bool
    {
        return in_array($this, [
            self::CUSTOMER_REQUESTED,
            self::WRONG_ADDRESS,
            self::WRONG_ITEM,
            self::CUSTOMER_UNREACHABLE,
        ], true);
    }

    /**
     * Check if reason is merchant responsibility
     */
    public function isMerchantResponsibility(): bool
    {
        return in_array($this, [
            self::WRONG_ITEM,
            self::PRICE_CHANGED,
            self::OUT_OF_STOCK,
            self::BUSINESS_CLOSURE,
            self::SYSTEM_ERROR,
        ], true);
    }

    /**
     * Create custom reason
     */
    public static function custom(string $reason): string
    {
        return $reason;
    }
}
```

#### Subtask 5.3: Update cancelOrder() to Accept Enum

```php
/**
 * Cancel an order
 * 
 * @param string $orderId Your internal order ID
 * @param CancellationReason|string $reason Reason for cancellation (Required, max 300 chars)
 * @param string|null $trackingNumber J&T tracking number (Optional)
 * @return array Response data
 * 
 * @throws JntExpressException
 */
public function cancelOrder(
    string $orderId,
    CancellationReason|string $reason,
    ?string $trackingNumber = null
): array {
    // Convert enum to string if needed
    $reasonString = $reason instanceof CancellationReason 
        ? $reason->value 
        : $reason;
    
    // Validate reason length
    if (strlen($reasonString) > 300) {
        throw new \InvalidArgumentException(
            'Cancellation reason cannot exceed 300 characters. Got: ' . strlen($reasonString)
        );
    }
    
    $payload = [
        'customerCode' => $this->customerCode,
        'password' => $this->password,
        'txlogisticId' => $orderId,
        'reason' => $reasonString,
    ];
    
    if ($trackingNumber !== null) {
        $payload['billCode'] = $trackingNumber;
    }
    
    $response = $this->getClient()->post('/api/order/cancelOrder', $payload);
    
    return $response['data'];
}
```

---

## 📊 Phase 2.5 Success Metrics

### Type Safety Metrics
- ✅ 100% of fields have correct type declarations
- ✅ 100% of fields have proper API format transformations
- ✅ 100% of numeric fields validated against API ranges
- ✅ 100% of string fields validated against API max lengths

### Documentation Metrics
- ✅ Every field documents its unit in PHPDoc (grams/kg/cm/MYR)
- ✅ Every field documents required vs optional
- ✅ Every field documents API format (integer string, 2dp float, etc.)
- ✅ Comprehensive examples for all transformations
- ✅ Context-aware transformer methods (forItemWeight, forPackageWeight, forDimension, forMoney)

### Error Handling Metrics
- ✅ Type-safe error code enum created
- ✅ Type-safe cancellation reason enum created
- ✅ All error codes mapped to human messages
- ✅ Error categories and retry logic implemented

---

## 🎯 Expected Impact

### Before Phase 2.5
- Developer confusion about units (grams vs kg?)
- Type mismatches causing API errors
- Manual string formatting required
- Magic strings for errors and reasons
- Unclear required vs optional fields

### After Phase 2.5
- ✅ **Crystal clear units** - Documented in PHPDoc and comprehensive docs
- ✅ **Clean property names** - `weight`, `length`, `price` (not `weightKg`, `lengthCm`)
- ✅ **Context-aware transformers** - `forItemWeight()`, `forPackageWeight()`, `forDimension()`, `forMoney()`
- ✅ **Automatic type transformation** - Accept ints/floats, send correct strings
- ✅ **Type safety** - Enums for errors and reasons
- ✅ **Validation** - Runtime checks for required fields and ranges
- ✅ **Developer-friendly** - IDE autocomplete for all enums

---

## 📝 Deliverables

### New Files (6)
1. ✅ `src/Support/TypeTransformer.php` - Type transformation utilities
2. ✅ `src/Enums/ErrorCode.php` - Complete error code enum (~40+ codes)
3. ✅ `src/Enums/CancellationReason.php` - Cancellation reason enum (12 values)
4. ✅ `docs/UNITS_REFERENCE.md` - Comprehensive unit documentation
5. ✅ `docs/FIELD_REQUIREMENTS.md` - Required vs optional field map
6. ✅ `docs/TYPE_TRANSFORMATION_GUIDE.md` - Developer guide for type handling

### Updated Files (8+)
1. ✅ All Data classes (6 files) - Updated with TypeTransformer
2. ✅ `src/Services/JntExpressService.php` - Updated cancelOrder()
3. ✅ `src/Builders/OrderBuilder.php` - Added validation methods
4. ✅ Update all relevant documentation with unit clarity

### Tests (15+)
1. ✅ Unit tests for TypeTransformer (10 tests)
2. ✅ Unit tests for ErrorCode enum (5 tests)
3. ✅ Unit tests for CancellationReason enum (3 tests)
4. ✅ Integration tests for type transformations (5 tests)
5. ✅ Validation tests for all Data classes (10+ tests)

---

## 🚀 Implementation Order

**Week 2:**
1. Day 1-2: Create TypeTransformer with tests
2. Day 3-4: Audit and update all Data classes
3. Day 5: Create unit documentation (UNITS_REFERENCE.md)

**Week 3:**
1. Day 1-2: Create ErrorCode and CancellationReason enums with tests
2. Day 3: Add validation to OrderBuilder
3. Day 4: Create FIELD_REQUIREMENTS.md
4. Day 5: Final testing and documentation

---

## ✅ Definition of Done

Phase 2.5 is complete when:
- ✅ Every field has correct type declaration
- ✅ Every field has proper API transformation
- ✅ Every unit is clearly documented
- ✅ Every required field is validated
- ✅ Error codes are type-safe enums
- ✅ Cancellation reasons are type-safe enums
- ✅ All tests passing (100% coverage for new code)
- ✅ Documentation complete with examples
- ✅ No ambiguity in API requirements

---

## 🎉 Why Phase 2.5 is CRITICAL

This phase prevents:
- ❌ API errors due to wrong number formatting (e.g., "5" instead of "5.00")
- ❌ Weight confusion (sending kg when grams expected)
- ❌ Type casting errors in production
- ❌ Unclear validation errors
- ❌ Magic strings for errors
- ❌ Developer confusion about requirements

This phase ensures:
- ✅ **Production-ready quality**
- ✅ **Type safety at runtime**
- ✅ **Clear developer experience**
- ✅ **Correct API compliance**
- ✅ **Maintainable codebase**

**This must be implemented before webhook system (Phase 3) to ensure all incoming data is properly typed!**

---

**Next:** After Phase 2.5, proceed to Phase 3 (Webhook System) with confidence that all data types are correct.
