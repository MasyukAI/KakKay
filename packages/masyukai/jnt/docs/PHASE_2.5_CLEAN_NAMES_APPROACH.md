# Phase 2.5 Implementation Approach - Clean Property Names

**Date:** October 9, 2025  
**Decision:** Use clean property names (`weight`, `length`, `price`) with context-aware transformers

---

## 🎯 Key Decision

**User Feedback:**
> "i dont want this. this is just want type setup. just use weight, length and price but be very clear in the docs. if we wanna be smart, we could setup config and transformer"

**Decision Made:**
✅ Use **clean property names** without unit suffixes  
✅ Be **VERY CLEAR in documentation** (PHPDoc + comprehensive docs)  
✅ Use **context-aware transformer methods** that know the unit context  
✅ Optional **config for future** unit display/conversion features  

---

## 📝 Property Naming Convention

### ❌ REJECTED Approach (Unit Suffixes)
```php
public readonly float $itemWeightGrams;
public readonly float $packageWeightKg;
public readonly float $lengthCm;
public readonly float $unitPriceMyr;
```

**Why Rejected:**
- Verbose property names
- Not standard Laravel/PHP convention
- Unit changes would require property renames

### ✅ APPROVED Approach (Clean Names + Documentation)
```php
/**
 * @param float|string $weight Weight per item in GRAMS (1-9999 grams, sent as integer)
 * @param float|string $price Unit price in MYR (0.01-999.99, sent with 2 decimals)
 */
public function __construct(
    public string $itemName,
    public int|string $quantity,
    public float|string $weight,      // GRAMS - see PHPDoc
    public float|string $price,       // MYR - see PHPDoc
    public ?string $itemUrl = null,
) {}
```

**Why Approved:**
- Clean, standard property names
- Unit documented in PHPDoc (IDE shows it)
- Comprehensive docs explain everything
- Context-aware transformers handle unit logic

---

## 🧠 Smart Context-Aware Transformers

Instead of generic methods, use **context-specific methods** that know the unit:

```php
class TypeTransformer
{
    // ❌ OLD: Generic (requires developer to know unit)
    public static function toIntegerString(int|float|string $value): string
    {
        return (string)(int)$value;
    }
    
    public static function toDecimalString(float|int|string $value, int $decimals = 2): string
    {
        return number_format((float)$value, $decimals, '.', '');
    }
    
    // ✅ NEW: Context-aware (self-documenting, knows unit context)
    
    /**
     * Transform item weight (GRAMS → integer string)
     * Items are measured in GRAMS and sent as INTEGER strings
     */
    public static function forItemWeight(float|int|string $grams): string
    {
        return self::toIntegerString($grams);
    }
    
    /**
     * Transform package weight (KILOGRAMS → 2 decimal string)
     * Packages are measured in KILOGRAMS and sent with 2 DECIMALS
     */
    public static function forPackageWeight(float|int|string $kg): string
    {
        return self::toDecimalString($kg, 2);
    }
    
    /**
     * Transform dimension (CENTIMETERS → 2 decimal string)
     * Dimensions are measured in CENTIMETERS and sent with 2 DECIMALS
     */
    public static function forDimension(float|int|string $cm): string
    {
        return self::toDecimalString($cm, 2);
    }
    
    /**
     * Transform money (MALAYSIAN RINGGIT → 2 decimal string)
     * Money is in MALAYSIAN RINGGIT (MYR) and sent with 2 DECIMALS
     */
    public static function forMoney(float|int|string $myr): string
    {
        return self::toDecimalString($myr, 2);
    }
}
```

**Benefits:**
1. **Self-documenting** - Method name tells you the unit context
2. **Type-safe** - IDE autocomplete shows available transformers
3. **Centralized** - Unit logic in one place
4. **Flexible** - Easy to add new contexts (e.g., `forWeight('lb')` for pounds)

---

## 📚 Documentation Strategy

### 1. PHPDoc Comments (IDE Support)
```php
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
        public float|string $weight,
        public float|string $price,
        public ?string $itemUrl = null,
    ) {}
}
```

### 2. Comprehensive Reference Documentation
**File:** `docs/UNITS_REFERENCE.md`

Contains:
- Quick reference table (property → class → unit → API format)
- Detailed explanation for each unit type
- Code examples showing input → transformation → output
- Common mistakes section
- Transformation pipeline visualization

### 3. Inline Method Documentation
```php
public function toApiArray(): array
{
    return [
        'itemname' => $this->itemName,
        'number' => TypeTransformer::toIntegerString($this->quantity),
        'itemweight' => TypeTransformer::forItemWeight($this->weight),  // Smart: grams → integer string
        'itemprice' => TypeTransformer::forMoney($this->price),         // Smart: MYR → 2dp string
    ];
}
```

---

## ⚙️ Optional Configuration (Future Enhancement)

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
        'weight_decimals' => 0,      // Item weight display (grams)
        'package_decimals' => 2,     // Package weight display (kg)
        'dimension_decimals' => 2,   // Dimensions display (cm)
        'money_decimals' => 2,       // Money display (MYR)
    ],
    
    // Future: Support unit conversion
    'conversions' => [
        'enabled' => false,
        'from_unit' => 'pounds',     // Convert from pounds
        'to_unit' => 'grams',        // To grams (API requirement)
    ],
];
```

**Potential Future Features:**
- Display weights in different units (lb, oz, kg, g)
- Automatic unit conversion (e.g., pounds → grams before API call)
- Localized currency display
- Custom formatting for different locales

---

## 📊 Comparison: Before vs After

### Property Declaration

**Before (Unit Suffixes):**
```php
public readonly float $itemWeightGrams;
public readonly float $packageWeightKg;
public readonly float $lengthCm;
public readonly float $unitPriceMyr;
```

**After (Clean Names):**
```php
public readonly float $weight;  // Unit in PHPDoc
public readonly float $length;  // Unit in PHPDoc
public readonly float $price;   // Unit in PHPDoc
```

### Transformation

**Before (Generic Methods):**
```php
'itemweight' => TypeTransformer::toIntegerString($this->itemWeightGrams),
'weight' => TypeTransformer::toDecimalString($this->packageWeightKg, 2),
'length' => TypeTransformer::toDecimalString($this->lengthCm, 2),
'itemprice' => TypeTransformer::toDecimalString($this->unitPriceMyr, 2),
```

**After (Context-Aware Methods):**
```php
'itemweight' => TypeTransformer::forItemWeight($this->weight),     // Self-documenting
'weight' => TypeTransformer::forPackageWeight($this->weight),      // Self-documenting
'length' => TypeTransformer::forDimension($this->length),          // Self-documenting
'itemprice' => TypeTransformer::forMoney($this->price),            // Self-documenting
```

---

## ✅ Benefits of This Approach

### 1. Clean, Standard Naming
- ✅ Follows Laravel/PHP conventions
- ✅ Properties are concise (`weight` not `itemWeightGrams`)
- ✅ Easy to read and understand

### 2. Self-Documenting Code
- ✅ Transformer method names explain the context
- ✅ `forItemWeight()` is clearer than `toIntegerString()`
- ✅ Developer knows unit from method name

### 3. IDE Support
- ✅ PHPDoc shows unit in hover tooltip
- ✅ Autocomplete suggests context-aware transformers
- ✅ Type hints guide correct usage

### 4. Flexibility
- ✅ Can add new contexts easily (`forWeightLb()`, `forWeightOz()`)
- ✅ Can support multiple unit systems
- ✅ Configuration can override defaults

### 5. Maintainability
- ✅ Unit logic centralized in TypeTransformer
- ✅ Property renames not needed if unit changes
- ✅ Easy to update transformation logic

---

## 🎯 Implementation Checklist

### TypeTransformer Updates
- [x] Keep generic methods (`toIntegerString`, `toDecimalString`)
- [x] Add context-aware methods:
  - [x] `forItemWeight()` - GRAMS → integer string
  - [x] `forPackageWeight()` - KILOGRAMS → 2dp string
  - [x] `forDimension()` - CENTIMETERS → 2dp string
  - [x] `forMoney()` - MYR → 2dp string

### Data Class Updates
- [ ] ItemData: Use `weight` and `price` (not `weightGrams`, `priceMyr`)
- [ ] PackageInfoData: Use `weight`, `length`, `width`, `height`
- [ ] AddressData: No changes needed
- [ ] OrderData: No changes needed
- [ ] TrackingData: No changes needed
- [ ] TrackingDetailData: No changes needed

### Documentation Updates
- [x] `docs/UNITS_REFERENCE.md` - Quick reference table with clean names
- [ ] `docs/CRITICAL_REQUIREMENTS.md` - Update examples
- [x] `docs/PHASE_2.5_TYPE_SAFETY_API_COMPLIANCE.md` - Update approach
- [ ] PHPDoc in all Data classes - Clear unit documentation

### Testing
- [ ] Unit tests for context-aware transformers
- [ ] Integration tests showing input → output
- [ ] Documentation examples that work as tests

---

## 🚀 Next Steps

1. **Review this approach** - Ensure alignment with your vision
2. **Update TypeTransformer** - Add context-aware methods
3. **Update Data classes** - Clean property names with PHPDoc
4. **Create UNITS_REFERENCE.md** - Comprehensive unit documentation
5. **Write tests** - Verify transformations work correctly

---

**Status:** ✅ Approach approved, ready for implementation

**Key Takeaway:** Clean property names + excellent documentation + context-aware transformers = Best developer experience! 🎉
