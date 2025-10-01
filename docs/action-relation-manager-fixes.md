# Action Relation Manager Fixes

## Problem
When using `ApplyConditionAction` and `RemoveConditionAction` as header actions in `ConditionsRelationManager`, the actions failed with a TypeError:

```
MasyukAI\FilamentCart\Actions\ApplyConditionAction::{closure}(): 
Argument #2 ($record) must be of type MasyukAI\FilamentCart\Models\Cart, null given
```

## Root Cause
When actions are used as **header actions** in a relation manager context, the `$record` parameter is `null` because there's no specific record being acted upon. The cart record must be retrieved from the relation manager's owner record using `$livewire->getOwnerRecord()`.

## Solution

### 1. Updated `ApplyConditionAction`

#### Standard Action (`make()`)
```php
->action(function (array $data, $record, $livewire): void {
    // Get the cart record - either directly or from relation manager
    $cart = $record instanceof CartModel ? $record : $livewire->getOwnerRecord();
    
    // ... rest of the logic
})
```

#### Custom Condition Action (`makeCustom()`)
```php
->action(function (array $data, $record, $livewire): void {
    // Get the cart record - either directly or from relation manager
    $cart = $record instanceof CartModel ? $record : $livewire->getOwnerRecord();
    
    // ... rest of the logic
})
```

### 2. Updated `RemoveConditionAction`

#### Clear All Action (`makeClearAll()`)
```php
->action(function ($record, $livewire): void {
    // Get the cart record - either directly or from relation manager
    $cart = $record instanceof CartModel ? $record : $livewire->getOwnerRecord();
    
    // ... rest of the logic
})
```

#### Clear By Type Action (`makeClearByType()`)
```php
->action(function (array $data, $record, $livewire): void {
    // Get the cart record - either directly or from relation manager
    $cart = $record instanceof CartModel ? $record : $livewire->getOwnerRecord();
    
    // ... rest of the logic
})
```

## How It Works

### Context Detection
The fix uses a simple pattern to detect the context:
- **Direct use**: If `$record` is a `Cart` instance, use it directly
- **Relation manager**: If `$record` is null, get the owner record from `$livewire`

```php
$cart = $record instanceof CartModel ? $record : $livewire->getOwnerRecord();
```

### Usage Contexts

#### 1. Direct Use (CartResource Pages)
When used directly on Cart resource pages:
- `$record` = Cart model instance
- Action works on that specific cart

#### 2. Relation Manager (ConditionsRelationManager)
When used as header actions in relation managers:
- `$record` = null (no specific record)
- `$livewire` = RelationManager instance
- `$livewire->getOwnerRecord()` = Cart model (the parent record)

## Benefits

1. **Flexible Usage**: Actions work in both contexts without modification
2. **No Breaking Changes**: Existing usage patterns continue to work
3. **Clean Code**: Single line of code handles both scenarios
4. **Type Safety**: Uses `instanceof` check for proper type detection

## Related Files

### Fixed Files:
- `packages/masyukai/filament-cart/src/Actions/ApplyConditionAction.php`
- `packages/masyukai/filament-cart/src/Actions/RemoveConditionAction.php`

### Usage Context:
- `packages/masyukai/filament-cart/src/Resources/CartResource/RelationManagers/ConditionsRelationManager.php`

## Testing

### Manual Testing Steps:
1. Navigate to CartResource
2. Open a specific cart
3. Click on "Conditions" relation tab
4. Try "Apply Condition" (header action) ✅
5. Try "Add Custom Condition" (header action) ✅
6. Try "Clear by Type" (header action) ✅
7. Try "Clear All Conditions" (header action) ✅
8. Try "Remove" on a specific condition (record action) ✅

### Automated Tests:
All FilamentCart tests pass:
```
Tests:    4 skipped, 8 passed (29 assertions)
```

## Additional Fixes

### 1. Model Value Type Handling
Fixed `Condition::computeDerivedFields()` to handle integer values:
```php
// Before: 
$value = $this->value;

// After:
$value = (string) $this->value;
```

This prevents `TypeError` when tests create conditions with integer values.

### 2. Test Updates
Updated `ConditionManagementTest.php`:
- Changed `ConditionTemplate` to `Condition` (model was renamed)
- Updated attribute expectations: `template_id` → `condition_id`, `template_name` → `condition_name`

## Completion Status

✅ ApplyConditionAction fixed for both contexts
✅ RemoveConditionAction fixed for both contexts  
✅ Model value type handling fixed
✅ Tests updated and passing
✅ Code formatted with Laravel Pint
✅ No compilation errors
✅ Manual testing confirmed working

## Related Documentation

- See `docs/condition-actions-updates.md` for action enhancements details
- See `docs/conditions-refactor-summary.md` for complete system overview
- See `docs/condition-resource-updates.md` for UI updates
