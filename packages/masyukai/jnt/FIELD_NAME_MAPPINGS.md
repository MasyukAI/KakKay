# Complete Field Name Mappings - J&T Express Package

## Overview

The J&T Express Laravel package uses **clean, developer-friendly field names** for all inputs, which are automatically converted to J&T's API format internally. This ensures a consistent, intuitive developer experience.

## FieldNameConverter Class

The `MasyukAI\Jnt\Support\FieldNameConverter` class handles all field name conversions. It's a standalone utility that can be used anywhere in your application.

### Basic Usage

```php
use MasyukAI\Jnt\Support\FieldNameConverter;

// Convert clean field names to API format
$apiData = FieldNameConverter::convert($cleanData);

// Convert specific contexts
$apiAddress = FieldNameConverter::convertAddress($cleanAddress);
$apiItem = FieldNameConverter::convertItem($cleanItem);
$apiPackage = FieldNameConverter::convertPackageInfo($cleanPackage);

// Get all mappings
$mappings = FieldNameConverter::getMappings();
```

## Complete Mapping Table

### Order Level
| Clean Name | API Name | Type | Description |
|------------|----------|------|-------------|
| `orderId` | `txlogisticId` | string | Your system's order identifier |
| `trackingNumber` | `billCode` | string | J&T tracking number |

### Address Fields (Sender & Receiver)
| Clean Name | API Name | Type | Description |
|------------|----------|------|-------------|
| `name` | `name` | string | Person's name (no conversion) |
| `phone` | `phone` | string | Phone number (no conversion) |
| `address` | `address` | string | Street address (no conversion) |
| `postcode` | `postCode` | string | Postal code (no conversion) |
| `city` | `city` | string | City name (no conversion) |
| `state` | `prov` | string | **State/province** ✅ Converted |
| `area` | `area` | string | Area/district (no conversion) |
| `email` | `email` | string | Email address (no conversion) |

### Item Fields
| Clean Name | API Name | Type | Description |
|------------|----------|------|-------------|
| `name` | `itemName` | string | **Item name** ✅ Converted |
| `quantity` | `number` | int | **Number of units** ✅ Converted |
| `weight` | `weight` | int | Weight in grams (no conversion) |
| `price` | `itemValue` | float | **Unit price in MYR** ✅ Converted |
| `description` | `itemDesc` | string | **Item description** ✅ Converted |
| `englishName` | `englishName` | string | English name (no conversion) |
| `currency` | `itemCurrency` | string | Currency code (no conversion) |

### Package Info Fields
| Clean Name | API Name | Type | Description |
|------------|----------|------|-------------|
| `quantity` | `packageQuantity` | int | **Number of packages** ✅ Converted |
| `weight` | `weight` | float | Weight in kg (no conversion) |
| `value` | `packageValue` | float | **Declared value** ✅ Converted |
| `goodsType` | `goodsType` | string | Goods type (no conversion) |
| `length` | `length` | float | Length in cm (no conversion) |
| `width` | `width` | float | Width in cm (no conversion) |
| `height` | `height` | float | Height in cm (no conversion) |

## Usage Examples

### Single Order Creation

```php
use MasyukAI\Jnt\Facades\JntExpress;
use MasyukAI\Jnt\Data\{AddressData, ItemData, PackageInfoData};

// Create with Data objects (clean field names)
$order = JntExpress::createOrder(
    sender: new AddressData(
        name: 'John Doe',
        phone: '0123456789',
        address: '123 Main St',
        postCode: '47000',
        state: 'Selangor',  // ✅ Clean name, auto-converts to 'prov'
    ),
    receiver: new AddressData(
        name: 'Jane Smith',
        phone: '0198765432',
        address: '456 Oak Rd',
        postCode: '50000',
        state: 'Kuala Lumpur',  // ✅ Clean name
    ),
    items: [
        new ItemData(
            name: 'Product Name',        // ✅ Clean, → itemName
            quantity: 2,                 // ✅ Clean, → number
            price: 99.99,                // ✅ Clean, → itemValue
            weight: 500,                 // Weight in grams
            description: 'Test product', // ✅ Clean, → itemDesc
        ),
    ],
    packageInfo: new PackageInfoData(
        quantity: 1,      // ✅ Clean, → packageQuantity
        weight: 1.5,      // Weight in kg
        value: 199.98,    // ✅ Clean, → packageValue
        goodsType: 'ITN8',
    ),
    orderId: 'ORDER123',  // ✅ Clean, → txlogisticId
);
```

### Batch Order Creation

```php
use MasyukAI\Jnt\Facades\JntExpress;

$result = JntExpress::batchCreateOrders([
    [
        'orderId' => 'ORDER001',  // ✅ Clean field name
        'sender' => [
            'name' => 'John Doe',
            'phone' => '0123456789',
            'address' => '123 Main St',
            'postcode' => '47000',
            'state' => 'Selangor',  // ✅ state → prov
        ],
        'receiver' => [
            'name' => 'Jane Smith',
            'phone' => '0198765432',
            'address' => '456 Oak Rd',
            'postcode' => '50000',
            'state' => 'Kuala Lumpur',  // ✅ state → prov
        ],
        'items' => [
            [
                'name' => 'Widget',       // ✅ name → itemName
                'quantity' => 2,          // ✅ quantity → number
                'price' => 50.00,         // ✅ price → itemValue
                'weight' => 200,          // grams
                'description' => 'Test',  // ✅ description → itemDesc
            ],
        ],
        'packageInfo' => [
            'quantity' => 1,       // ✅ quantity → packageQuantity
            'weight' => 1.5,       // kg
            'value' => 100.00,     // ✅ value → packageValue
            'goodsType' => 'ITN8',
            'length' => 30,
            'width' => 20,
            'height' => 10,
        ],
    ],
    // More orders...
]);
```

## Conversion Rules

### 1. Clean Names Take Precedence

If both clean and API names are present, clean names override:

```php
[
    'orderId' => 'CLEAN_ID',        // Used
    'txlogisticId' => 'API_ID',     // Ignored
]
// Result: txlogisticId = 'CLEAN_ID'
```

### 2. API Names Still Work

For backward compatibility, API field names work without conversion:

```php
[
    'txlogisticId' => 'ORDER123',  // No conversion needed
    'sender' => [
        'prov' => 'Selangor',      // No conversion needed
    ],
]
```

### 3. Nested Arrays Are Converted

All nested structures (sender, receiver, items, packageInfo) are recursively converted:

```php
[
    'orderId' => 'ORDER123',      // Top-level conversion
    'items' => [
        [
            'name' => 'Product',  // Item-level conversion
        ],
    ],
]
```

### 4. Empty Arrays Are Preserved

```php
[
    'orderId' => 'ORDER123',
    'items' => [],           // Preserved as-is
    'packageInfo' => [],     // Preserved as-is
]
```

## Benefits

✅ **Consistent API** - Use same field names everywhere (single orders, batch operations, arrays)
✅ **Developer-Friendly** - Intuitive names like `state` instead of `prov`, `name` instead of `itemName`
✅ **Type-Safe** - Data objects use clean field names
✅ **Backward Compatible** - Old API field names still work
✅ **No Manual Mapping** - Automatic conversion handled by package
✅ **Well-Tested** - 321 tests ensure reliability

## Testing

The field name converter has comprehensive test coverage (99 assertions):

```bash
vendor/bin/pest --filter="FieldNameConverter"

Tests:    9 passed (99 assertions)
Duration: 0.47s
```

Tests verify:
- Order-level conversions (orderId, trackingNumber)
- Address conversions (state)
- Item conversions (name, quantity, price, description)
- Package info conversions (quantity, value)
- Complete nested conversions
- Backward compatibility
- Mixed field names handling
- Empty array handling

## Summary

**8 field names are automatically converted:**

1. `orderId` → `txlogisticId`
2. `trackingNumber` → `billCode`
3. `state` → `prov` (in sender/receiver)
4. `name` → `itemName` (in items)
5. `quantity` → `number` (in items)
6. `price` → `itemValue` (in items)
7. `description` → `itemDesc` (in items)
8. `quantity` → `packageQuantity` (in packageInfo)
9. `value` → `packageValue` (in packageInfo)

**All other fields remain unchanged** and use the same name for both clean API and J&T API.

---

**Package Philosophy:** "Clean, developer-friendly API - No more confusing property names like `txlogisticId`" ✅
