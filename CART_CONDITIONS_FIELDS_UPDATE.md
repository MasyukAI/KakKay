# Cart Conditions Fields Update

## Overview
Updated the normalized `cart_conditions` table to store all condition properties that exist in the cart package's condition objects. Previously, only basic fields were stored, missing important condition metadata.

## Missing Fields Added

### Database Schema
Added the following columns to `cart_conditions` table:

| Column | Type | Description |
|--------|------|-------------|
| `operator` | `string` | The mathematical operation (+, -, *, /, %) |
| `is_charge` | `boolean` | Whether this condition adds to the total |
| `is_dynamic` | `boolean` | Whether the condition is dynamically calculated |
| `is_discount` | `boolean` | Whether this condition is a discount |
| `is_percentage` | `boolean` | Whether the value is a percentage |
| `parsed_value` | `string` | The calculated/parsed value |
| `rules` | `jsonb` | Additional validation or calculation rules |

### Example Before vs After

**Before:**
```json
{
  "name": "shipping",
  "type": "shipping",
  "target": "subtotal",
  "value": "+990",
  "order": 0,
  "attributes": {
    "method": "standard",
    "description": "shipping"
  }
}
```

**After:**
```json
{
  "name": "shipping",
  "type": "shipping",
  "target": "subtotal",
  "value": "+990",
  "operator": "+",
  "is_charge": true,
  "is_discount": false,
  "is_percentage": false,
  "is_dynamic": false,
  "parsed_value": "990",
  "order": 0,
  "attributes": {
    "method": "standard",
    "description": "shipping"
  },
  "rules": null
}
```

## Files Modified

### 1. Migration (Merged)
- **`packages/masyukai/filament-cart/database/migrations/2025_09_30_000002_create_cart_conditions_table.php`**
  - Updated the base migration to include all condition fields from the start
  - Adds columns: `operator`, `is_charge`, `is_dynamic`, `is_discount`, `is_percentage`, `parsed_value`, `rules`
  - Includes indexes for `is_discount` and `is_percentage` for better query performance
  - **Note:** The separate migration `2025_10_01_092509_add_missing_condition_fields_to_cart_conditions_table.php` was merged into this base migration and deleted

### 2. Model
- **`packages/masyukai/filament-cart/src/Models/CartCondition.php`**
  - Updated `$fillable` array to include new fields
  - Updated `$casts` array with proper type casting

### 3. Listeners (Event Synchronization)
- **`packages/masyukai/filament-cart/src/Listeners/SyncCompleteCart.php`**
  - Updated cart-level and item-level condition sync to use `$condition->toArray()`
  - Ensures all condition properties are stored

- **`packages/masyukai/filament-cart/src/Listeners/SyncCartConditionOnAdd.php`**
  - Updated to extract all condition properties using `toArray()`
  - Passes all new fields to sync method

### 4. Jobs (Queue Support)
- **`packages/masyukai/filament-cart/src/Jobs/SyncCartConditionJob.php`**
  - Added optional parameters for all new fields (backward compatible)
  - Updated sync logic to store new fields

### 5. Filament Resources (Admin Panel)

#### Table Display
- **`packages/masyukai/filament-cart/src/Resources/CartConditionResource/Tables/CartConditionsTable.php`**
  - Added `operator` column with color-coded badges (+, -, *, /, %)
  - Added `is_charge` icon column
  - Added `is_discount` icon column  
  - Added `is_percentage` icon column
  - Added `is_dynamic` icon column
  - All new columns are toggleable (hidden by default)

#### Form Display
- **`packages/masyukai/filament-cart/src/Resources/CartConditionResource/Schemas/CartConditionForm.php`**
  - Added `operator` text input
  - Added `parsed_value` text input
  - Added 4 checkboxes for boolean flags (is_charge, is_discount, is_percentage, is_dynamic)
  - All fields are disabled (read-only resource)

## Benefits

1. **Complete Data Visibility**: All condition properties are now visible in the Filament admin panel
2. **Better Filtering**: Can now filter conditions by operator, discount status, charge status, etc.
3. **Accurate Reporting**: Full condition data available for analytics and reporting
4. **Debugging**: Easier to debug condition application with all fields visible
5. **Data Consistency**: Normalized table now matches the cart package's condition structure

## Testing

Verified with tinker that all fields are correctly stored:

```php
$condition = CartCondition::where('name', 'shipping')->latest()->first();

// Result shows all fields populated correctly:
// operator: "+"
// is_charge: true
// is_discount: false
// is_percentage: false
// is_dynamic: false
// parsed_value: "990"
// rules: null
```

## Migration Notes

- The migration is **backward compatible**
- Existing conditions will have default values for new fields
- No data loss occurs
- Indexes added for performance on commonly filtered fields
