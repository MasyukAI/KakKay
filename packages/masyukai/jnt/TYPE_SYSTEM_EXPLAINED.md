# Smart Type System - Understanding J&T API Types

## The Problem with "String" Everything

J&T's API documentation says **everything** is a "String", but the **ranges and examples** reveal the TRUE semantic types:

```
❌ Misleading: "String"
✅ Reality: Integer, Float with 2 decimals, or actual String
```

## Our Smart Type Strategy

We accept **developer-friendly types** (int/float) and **transform correctly** to J&T's string format with proper validation and decimal formatting.

---

## Type Mappings

### PackageInfoData

| Property | API Says | Actually Is | We Accept | We Transform To |
|----------|----------|-------------|-----------|-----------------|
| `quantity` | String(1-999) | **Integer** (parcel count) | `int\|string` | `"1"` to `"999"` |
| `weight` | String(0.01-999.99) | **Float 2dp** (kg) | `float\|string` | `"0.01"` to `"999.99"` |
| `declaredValue` | String(3) | **Float 2dp** (MYR) | `float\|string` | `"0.00"` to `"999.99"` |
| `length` | String(0.01-999.99) | **Float 2dp** (cm) | `float\|string\|null` | `"0.01"` to `"999.99"` |
| `width` | String(0.01-999.99) | **Float 2dp** (cm) | `float\|string\|null` | `"0.01"` to `"999.99"` |
| `height` | String(0.01-999.99) | **Float 2dp** (cm) | `float\|string\|null` | `"0.01"` to `"999.99"` |
| `goodsType` | String(4) | **Enum** (ITN2/ITN8) | `GoodsType\|string` | `"ITN2"` or `"ITN8"` |

### ItemData

| Property | API Says | Actually Is | We Accept | We Transform To |
|----------|----------|-------------|-----------|-----------------|
| `quantity` | String(1-9999999) | **Integer** (item count) | `int\|string` | `"1"` to `"9999999"` |
| `weight` | String(1-999999) | **Integer** (grams!) | `float\|string` | `"1"` to `"999999"` |
| `unitPrice` | String(0.01-9999999.99) | **Float 2dp** (MYR) | `float\|string` | `"0.01"` to `"9999999.99"` |

---

## How It Works

### 1. Developer-Friendly Input ✅

Developers can use **natural types**:

```php
$item = new ItemData(
    itemName: 'Basketball',
    quantity: 2,              // ✅ Integer - natural and clean
    weight: 10,               // ✅ Integer grams
    unitPrice: 50.00          // ✅ Float with decimals
);

$package = new PackageInfoData(
    quantity: 1,              // ✅ Integer
    weight: 10.5,             // ✅ Float kg
    declaredValue: 100,       // ✅ Integer or Float
    goodsType: GoodsType::PACKAGE,  // ✅ Type-safe enum
    length: 30.5,             // ✅ Float cm
    width: 20,                // ✅ Integer cm (converted to float)
    height: 15.25             // ✅ Float cm
);
```

### 2. Smart Transformation to API Format 🎯

Our `toApiArray()` methods **intelligently format** each type:

```php
// PackageInfoData::toApiArray()
[
    'packageQuantity' => (string) (int) $this->quantity,                    // "1"
    'weight' => number_format((float) $this->weight, 2, '.', ''),          // "10.50"
    'packageValue' => number_format((float) $this->declaredValue, 2, '.', ''), // "100.00"
    'length' => number_format((float) $this->length, 2, '.', ''),          // "30.50"
    'width' => number_format((float) $this->width, 2, '.', ''),            // "20.00"
    'height' => number_format((float) $this->height, 2, '.', ''),          // "15.25"
]

// ItemData::toApiArray()
[
    'number' => (string) (int) $this->quantity,                            // "2"
    'weight' => (string) (int) $this->weight,                              // "10"
    'itemValue' => number_format((float) $this->unitPrice, 2, '.', ''),   // "50.00"
]
```

### 3. Proper Decimal Formatting 🔢

**Critical:** J&T expects **exactly 2 decimal places** for monetary values and dimensions:

```php
❌ Bad:  "10"      → Missing decimals
❌ Bad:  "10.5"    → Only 1 decimal
❌ Bad:  "10.999"  → Too many decimals
✅ Good: "10.00"   → Exactly 2 decimals
✅ Good: "10.50"   → Exactly 2 decimals
✅ Good: "10.99"   → Exactly 2 decimals
```

Our `number_format((float) $value, 2, '.', '')` ensures this!

### 4. Type Safety from API Responses 📥

When receiving data from J&T, we cast to proper PHP types:

```php
// fromApiArray() converts strings back to proper types
quantity: (int) $data['packageQuantity'],     // "1" → 1
weight: (float) $data['weight'],              // "10.50" → 10.5
unitPrice: (float) $data['itemValue'],        // "50.00" → 50.0
```

---

## Benefits

### ✅ Developer Experience
- Use **natural types** (int, float) instead of strings
- **Type hints** in IDE
- **No manual string conversion** needed

### ✅ Data Integrity
- **Automatic decimal formatting** (2 places for money/dimensions)
- **Integer enforcement** for quantities
- **Range validation** (future: can add validation layer)

### ✅ API Compliance
- **Always sends correct string format** to J&T
- **Proper decimal places** (0.00)
- **No precision issues**

---

## Examples

### Creating an Order

```php
use MasyukAI\Jnt\Facades\JntExpress;
use MasyukAI\Jnt\Enums\{ExpressType, GoodsType};

$order = JntExpress::createOrderBuilder()
    ->orderId('ORD-2024-001')
    ->expressType(ExpressType::DOMESTIC)
    ->sender($sender)
    ->receiver($receiver)
    ->addItem(new ItemData(
        itemName: 'Smartphone',
        quantity: 1,           // Integer → "1"
        weight: 200,           // Integer grams → "200"
        unitPrice: 1299.90     // Float → "1299.90"
    ))
    ->addItem(new ItemData(
        itemName: 'Phone Case',
        quantity: 2,           // Integer → "2"
        weight: 50,            // Integer grams → "50"
        unitPrice: 29.99       // Float → "29.99"
    ))
    ->packageInfo(new PackageInfoData(
        quantity: 1,           // Integer → "1"
        weight: 0.5,           // Float kg → "0.50"
        declaredValue: 1359.88, // Float → "1359.88"
        goodsType: GoodsType::PACKAGE,
        length: 25.5,          // Float cm → "25.50"
        width: 15.0,           // Float cm → "15.00"
        height: 8.5            // Float cm → "8.50"
    ))
    ->build();

// API receives:
[
    'items' => [
        [
            'itemName' => 'Smartphone',
            'number' => '1',              // ✅ String
            'weight' => '200',            // ✅ String (grams)
            'itemValue' => '1299.90',     // ✅ String with 2 decimals
        ],
        [
            'itemName' => 'Phone Case',
            'number' => '2',              // ✅ String
            'weight' => '50',             // ✅ String (grams)
            'itemValue' => '29.99',       // ✅ String with 2 decimals
        ],
    ],
    'packageInfo' => [
        'packageQuantity' => '1',         // ✅ String
        'weight' => '0.50',               // ✅ String with 2 decimals (kg)
        'packageValue' => '1359.88',      // ✅ String with 2 decimals
        'goodsType' => 'ITN8',            // ✅ Enum value
        'length' => '25.50',              // ✅ String with 2 decimals (cm)
        'width' => '15.00',               // ✅ String with 2 decimals (cm)
        'height' => '8.50',               // ✅ String with 2 decimals (cm)
    ],
]
```

### Reading Order Data

```php
$order = JntExpress::getOrder('ORD-2024-001');

// Properties are properly typed PHP values:
$order->orderId;              // string: "ORD-2024-001"
$order->trackingNumber;       // string: "JT123456789"
$order->chargeableWeight;     // string: "0.50" (API returns string)

// Package info with proper types:
$package = $order->packageInfo;
$package->quantity;           // int: 1
$package->weight;             // float: 0.5
$package->declaredValue;      // float: 1359.88
$package->length;             // float: 25.5
```

---

## Important Notes

### Item Weight vs Package Weight

**Different units and formats!**

- **Item weight:** Integer (grams) → `'weight' => '200'`
- **Package weight:** Float with 2dp (kg) → `'weight' => '0.50'`

```php
// Item weight (grams)
new ItemData(weight: 200)  // Sent as "200" (grams)

// Package weight (kg)
new PackageInfoData(weight: 0.2)  // Sent as "0.20" (kg)
```

### Currency Values

All monetary values use **2 decimal places**:

```php
unitPrice: 29.99    → "29.99"
unitPrice: 30       → "30.00"  ✅ Adds .00
unitPrice: 29.999   → "30.00"  ✅ Rounds to 2dp
declaredValue: 1500 → "1500.00" ✅ Adds .00
```

### Dimensions

All dimensions (length/width/height) are in **centimeters** with **2 decimal places**:

```php
length: 25.5   → "25.50"
width: 15      → "15.00"  ✅ Adds .00
height: 8.127  → "8.13"   ✅ Rounds to 2dp
```

---

## Future Enhancements

### Validation Layer (Coming Soon)

```php
class PackageInfoData
{
    public function __construct(
        public readonly int|string $quantity,
        // ... other properties
    ) {
        $this->validate();
    }
    
    private function validate(): void
    {
        $qty = (int) $this->quantity;
        if ($qty < 1 || $qty > 999) {
            throw new ValidationException('Quantity must be between 1 and 999');
        }
        
        $weight = (float) $this->weight;
        if ($weight < 0.01 || $weight > 999.99) {
            throw new ValidationException('Weight must be between 0.01 and 999.99 kg');
        }
        
        // ... more validations
    }
}
```

### Value Objects (Considering)

```php
class Weight
{
    public function __construct(
        private readonly float $kilograms
    ) {
        if ($kilograms < 0.01 || $kilograms > 999.99) {
            throw new InvalidArgumentException('Weight must be between 0.01 and 999.99 kg');
        }
    }
    
    public function toApiString(): string
    {
        return number_format($this->kilograms, 2, '.', '');
    }
    
    public static function fromGrams(int $grams): self
    {
        return new self($grams / 1000);
    }
}

// Usage:
new PackageInfoData(
    weight: Weight::fromGrams(500),  // 0.5 kg
    // ...
);
```

---

## Comparison: Before vs After

### Before (Unsafe Strings) ❌

```php
new ItemData(
    quantity: '2',        // ❌ String - could be "2.5" or "abc"
    weight: '10',         // ❌ String - no format validation
    unitPrice: '50.00'    // ❌ String - developer must format manually
);

// Sent to API:
'number' => '2',          // ❌ What if developer passed "2.5"?
'weight' => '10',         // ❌ What if developer passed "10.5"?
'itemValue' => '50.00'    // ❌ What if developer passed "50"?
```

### After (Smart Types) ✅

```php
new ItemData(
    quantity: 2,          // ✅ Integer - type safe
    weight: 10,           // ✅ Integer - clear it's grams
    unitPrice: 50.00      // ✅ Float - natural representation
);

// Sent to API:
'number' => '2',          // ✅ Guaranteed integer string
'weight' => '10',         // ✅ Guaranteed integer string
'itemValue' => '50.00'    // ✅ Automatically formatted to 2dp
```

---

## Summary

Our smart type system provides:

✅ **Developer-friendly API** - use natural PHP types  
✅ **Automatic formatting** - proper decimals and string conversion  
✅ **Type safety** - IDE support and runtime type checking  
✅ **API compliance** - always sends correct format to J&T  
✅ **Future-proof** - easy to add validation layer

This is **far superior** to blindly using strings everywhere! 🎯
