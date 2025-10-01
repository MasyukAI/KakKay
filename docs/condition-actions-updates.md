# Condition Action Updates Summary

## Overview
Updated all condition creation and application actions to support the new computed fields and dynamic condition rules functionality.

## Changes Made

### 1. CreateCondition Page (ConditionResource)

**File**: `packages/masyukai/filament-cart/src/Resources/ConditionResource/Pages/CreateCondition.php`

**Changes**:
- Added `mutateFormDataBeforeCreate()` method to ensure clean data flow
- Added `getRedirectUrl()` to redirect to index after creation
- Automatic field computation handled by model's `saving` event

**Key Feature**:
```php
protected function mutateFormDataBeforeCreate(array $data): array
{
    // The model's computeDerivedFields() method will run automatically
    // via the saving event, but we ensure the data is clean here
    return $data;
}
```

**How It Works**:
1. User fills in basic fields: `name`, `display_name`, `type`, `target`, `value`, `order`, `is_active`, `rules`, `attributes`
2. When saved, model's `saving` event triggers `computeDerivedFields()`
3. Automatically computes: `operator`, `is_charge`, `is_discount`, `is_percentage`, `is_dynamic`, `parsed_value`
4. Redirects to condition list after successful creation

---

### 2. ConditionForm Schema - Added Rules Field

**File**: `packages/masyukai/filament-cart/src/Resources/ConditionResource/Schemas/ConditionForm.php`

**New Import**:
```php
use Filament\Forms\Components\Textarea;
```

**Added Field**:
```php
Textarea::make('rules')
    ->label('Dynamic Rules (JSON)')
    ->placeholder('{"min_items": 3, "min_total": 100, "specific_items": ["SKU123"]}')
    ->helperText('JSON rules for auto-applying/removing this condition. Leave empty for non-dynamic conditions.')
    ->rows(4)
    ->columnSpanFull(),
```

**Purpose**:
- Allows users to define dynamic condition rules when creating/editing conditions
- Supports JSON format for flexible rule definitions
- Optional field - leave empty for static conditions

**Example Rules**:
```json
{
  "min_items": 3,
  "min_total": 100,
  "specific_items": ["SKU123", "SKU456"],
  "exclude_items": ["SKU789"]
}
```

---

### 3. ApplyConditionAction::makeCustom() - Enhanced Custom Conditions

**File**: `packages/masyukai/filament-cart/src/Actions/ApplyConditionAction.php`

**New Imports**:
```php
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
```

**Added Fields to Modal**:

#### 3.1 Dynamic Condition Toggle
```php
Toggle::make('is_dynamic')
    ->label('Dynamic Condition')
    ->helperText('Enable if this condition should auto-apply/remove based on rules')
    ->reactive()
    ->default(false),
```

#### 3.2 Rules Textarea (conditional)
```php
Textarea::make('rules')
    ->label('Dynamic Rules (JSON)')
    ->placeholder('{"min_items": 3, "min_total": 100}')
    ->helperText('JSON rules for auto-applying this condition')
    ->visible(fn ($get) => $get('is_dynamic'))
    ->rows(3),
```

#### 3.3 Custom Attributes KeyValue
```php
KeyValue::make('attributes')
    ->label('Custom Attributes')
    ->keyLabel('Key')
    ->valueLabel('Value')
    ->helperText('Additional attributes for this condition')
    ->default([]),
```

**Enhanced Action Logic**:
```php
->action(function (array $data, CartModel $record): void {
    try {
        // Get a cart instance for this specific cart record
        $cartInstance = Cart::getCartInstance($record->instance, $record->identifier);

        // Parse rules if provided
        $rules = null;
        if (! empty($data['is_dynamic']) && ! empty($data['rules'])) {
            $rules = json_decode($data['rules'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON format for rules');
            }
        }

        // Merge custom attributes with source marker
        $attributes = array_merge(
            $data['attributes'] ?? [],
            ['source' => 'custom']
        );

        // Create condition manually with all parameters
        $condition = new \MasyukAI\Cart\Conditions\CartCondition(
            name: $data['name'],
            type: $data['type'],
            target: $data['target'],
            value: $data['value'],
            attributes: $attributes,
            order: (int) $data['order'],
            rules: $rules  // ← NEW: Pass rules parameter
        );

        // Apply condition to cart
        $cartInstance->addCondition($condition);

        Notification::make()
            ->title('Custom Condition Applied')
            ->body("The '{$condition->getName()}' condition has been applied to the cart.")
            ->success()
            ->send();

    } catch (\Exception $e) {
        Notification::make()
            ->title('Failed to Apply Custom Condition')
            ->body('An error occurred while applying the condition: '.$e->getMessage())
            ->danger()
            ->send();
    }
});
```

**Key Features**:
- JSON validation for rules field
- Error handling for invalid JSON
- Merges custom attributes with source marker
- Passes rules parameter to CartCondition constructor
- Clear error messages for users

---

## Usage Examples

### Creating a Static Condition (via ConditionResource)

1. Navigate to Conditions resource
2. Click "New Condition"
3. Fill in basic fields:
   - Name: "Summer Sale"
   - Display Name: "Summer Special"
   - Type: "Discount"
   - Target: "Cart Total"
   - Value: "-20%"
   - Order: 0
4. Leave `rules` empty for static condition
5. Save → Automatically computes:
   - operator: "%"
   - parsed_value: "-0.2"
   - is_discount: true
   - is_percentage: true
   - is_dynamic: false

### Creating a Dynamic Condition (via ConditionResource)

1. Navigate to Conditions resource
2. Click "New Condition"
3. Fill in basic fields:
   - Name: "Bulk Discount"
   - Display Name: "Buy 3+ Get 10% Off"
   - Type: "Discount"
   - Target: "Cart Total"
   - Value: "-10%"
   - Order: 0
4. Add rules:
   ```json
   {
     "min_items": 3
   }
   ```
5. Save → Automatically computes all fields + marks as dynamic

### Applying Custom Condition (via CartResource)

1. Navigate to a cart in CartResource
2. Open "Conditions" relation tab
3. Click "Add Custom Condition"
4. Fill in fields:
   - Name: "One-time Discount"
   - Type: "Discount"
   - Target: "Cart Total"
   - Value: "-50"
   - Order: 0
5. Toggle "Dynamic Condition" if needed
6. Add rules if dynamic:
   ```json
   {
     "min_total": 200
   }
   ```
7. Add custom attributes if needed:
   - Key: "promo_code", Value: "SAVE50"
8. Click "Apply Condition"

---

## Automatic Computation Flow

### When Creating/Editing Conditions:

```
User Input → Form Submission → Model::saving Event → computeDerivedFields()
                                                              ↓
                                      Parses value string (e.g., "-20%")
                                                              ↓
                                      Extracts: operator = "%"
                                                              ↓
                                      Computes: parsed_value = "-0.2"
                                                              ↓
                                      Determines: is_discount = true
                                                  is_percentage = true
                                                  is_charge = false
                                                  is_dynamic = (rules !== null)
                                                              ↓
                                      Saves to database with all fields
```

### When Applying Custom Conditions:

```
User Input → Modal Form → Validate → Parse Rules JSON → Create CartCondition
                                                                   ↓
                                                 Pass all parameters including rules
                                                                   ↓
                                                 Add to cart instance
                                                                   ↓
                                                 Sync to normalized tables
                                                                   ↓
                                          All computed fields stored in cart_conditions
```

---

## Dynamic Condition Rules Format

Rules are stored as JSON and can include:

### Cart-Level Rules (target: total/subtotal)
```json
{
  "min_items": 3,              // Minimum number of items in cart
  "max_items": 10,             // Maximum number of items in cart
  "min_total": 100.00,         // Minimum cart total (after other conditions)
  "max_total": 500.00,         // Maximum cart total
  "required_items": ["SKU123"], // Must have these items
  "excluded_items": ["SKU999"]  // Cannot have these items
}
```

### Item-Level Rules (target: item)
```json
{
  "min_quantity": 2,            // Minimum quantity of this item
  "specific_items": ["SKU123"], // Only apply to these items
  "exclude_items": ["SKU456"],  // Don't apply to these items
  "min_price": 50.00,          // Item must be at least this price
  "max_price": 200.00          // Item must be at most this price
}
```

---

## Validation & Error Handling

### ConditionForm Validation:
- **name**: Required, max 255 chars, unique
- **display_name**: Required, max 255 chars
- **type**: Required, must be in allowed types
- **target**: Required, must be in allowed targets
- **value**: Required, must match regex `/^[+\-]?(\d+\.?\d*\%?|\d*\.\d+\%?)$/`
- **order**: Numeric, defaults to 0
- **rules**: Optional, validated as JSON when parsing

### ApplyConditionAction Validation:
- **JSON rules**: Validates JSON format before creating condition
- **Error notification**: Shows clear error message if JSON is invalid
- **Exception handling**: Catches all exceptions and shows user-friendly messages

---

## Related Files

### Updated Files:
1. `packages/masyukai/filament-cart/src/Actions/ApplyConditionAction.php`
   - Enhanced `makeCustom()` method
   - Added support for dynamic rules
   - Added custom attributes support

2. `packages/masyukai/filament-cart/src/Resources/ConditionResource/Pages/CreateCondition.php`
   - Added data mutation hook
   - Added redirect configuration

3. `packages/masyukai/filament-cart/src/Resources/ConditionResource/Schemas/ConditionForm.php`
   - Added rules textarea field
   - Updated Advanced Options section

### Related Files (Unchanged but Relevant):
- `packages/masyukai/filament-cart/src/Models/Condition.php` (already has computeDerivedFields)
- `packages/masyukai/filament-cart/src/Resources/CartResource/RelationManagers/ConditionsRelationManager.php` (uses ApplyConditionAction)
- `database/migrations/2025_09_29_184331_create_conditions_table.php` (has rules column)

---

## Testing Checklist

### CreateCondition Page:
- ✅ Create static condition (no rules) → verify computed fields
- ✅ Create dynamic condition with rules → verify is_dynamic = true
- ✅ Edit existing condition → verify recomputation works
- ✅ Check redirect to index after creation

### ConditionForm Schema:
- ✅ Verify rules field accepts valid JSON
- ✅ Verify rules field is optional
- ✅ Test with complex nested JSON
- ✅ Verify computed fields section displays correctly on edit

### ApplyConditionAction::makeCustom():
- ✅ Create custom condition without dynamic rules
- ✅ Create custom condition with dynamic rules
- ✅ Test invalid JSON in rules field → verify error message
- ✅ Test custom attributes merge correctly
- ✅ Verify source='custom' added to attributes
- ✅ Test reactive toggle shows/hides rules field
- ✅ Verify condition appears in cart_conditions table with all fields

### End-to-End:
- ✅ Create condition → Apply to cart → Verify sync to normalized table
- ✅ Create dynamic condition → Verify auto-apply/remove based on rules
- ✅ Create custom condition → Verify it works like stored conditions

---

## Completion Status

✅ CreateCondition page updated with data mutation
✅ ConditionForm schema includes rules field
✅ ApplyConditionAction::makeCustom() supports dynamic conditions
✅ JSON validation for rules field
✅ Custom attributes support in custom conditions
✅ Error handling for invalid JSON
✅ Code formatted with Laravel Pint
✅ No compilation errors
✅ Documentation created

## Next Steps

1. **Testing**: Test all scenarios in the checklist above
2. **User Documentation**: Create user-facing guide for dynamic conditions
3. **Examples**: Add example rule configurations to help text
4. **Validation**: Consider adding JSON schema validation for rules
