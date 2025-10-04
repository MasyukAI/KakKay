# Global Conditions Implementation Plan

## Overview
Add the ability to tag conditions as "global" so they are automatically applied to all carts. This feature allows administrators to set up site-wide promotions, taxes, or fees that apply automatically without manual application.

## Implementation Strategy

### 1. Database Schema Changes

**Add new column to `conditions` table:**
```php
// Migration: add_is_global_to_conditions_table
Schema::table('conditions', function (Blueprint $table) {
    $table->boolean('is_global')->default(false)->after('is_active');
});
```

**Fields to add:**
- `is_global` (boolean, default: false) - Marks if condition applies to all carts

### 2. Model Updates

**File: `packages/masyukai/filament-cart/src/Models/Condition.php`**

Changes needed:
1. Add `is_global` to `$fillable` array
2. Add `is_global` to `$casts` array (as boolean)
3. Add scope `scopeGlobal()` to query global conditions
4. Add method `isGlobal()` to check if condition is global

```php
// In $fillable array
'is_global',

// In $casts array
'is_global' => 'boolean',

// New scope
public function scopeGlobal(Builder $query): void
{
    $query->where('is_global', true)
          ->where('is_active', true);
}

// New method
public function isGlobal(): bool
{
    return $this->is_global;
}
```

### 3. Form Updates

**File: `packages/masyukai/filament-cart/src/Resources/ConditionResource/Schemas/ConditionForm.php`**

Add toggle in the "Advanced Options" section:

```php
Toggle::make('is_global')
    ->label('Global Condition')
    ->default(false)
    ->helperText('Apply this condition automatically to all carts')
    ->hint('Global conditions are applied when items are added to cart')
    ->hintIcon('heroicon-m-information-circle')
    ->live()
    ->afterStateUpdated(function ($state, Forms\Set $set) {
        if ($state) {
            // When global is enabled, suggest making it active
            $set('is_active', true);
        }
    }),
```

**Placement:** After `is_active` toggle, before `rules` KeyValue field.

### 4. Event Listener - Core Implementation

**Create new listener: `packages/masyukai/filament-cart/src/Listeners/ApplyGlobalConditions.php`**

This listener will:
- Hook into `CartCreated` and `ItemAdded` events
- Query all active global conditions
- Apply them to the cart if they don't already exist
- Respect dynamic rules (only apply if rules pass)

```php
<?php

namespace MasyukAI\FilamentCart\Listeners;

use Illuminate\Support\Facades\Log;
use MasyukAI\Cart\Events\CartCreated;
use MasyukAI\Cart\Events\ItemAdded;
use MasyukAI\FilamentCart\Models\Condition;

class ApplyGlobalConditions
{
    /**
     * Handle the cart event (CartCreated or ItemAdded).
     */
    public function handle(CartCreated|ItemAdded $event): void
    {
        $cart = $event->cart;
        
        // Get all active global conditions
        $globalConditions = Condition::query()
            ->global()
            ->orderBy('order')
            ->get();

        foreach ($globalConditions as $conditionRecord) {
            try {
                // Create CartCondition instance
                $cartCondition = $conditionRecord->createCondition();
                
                // Check if condition already exists in cart
                $existingCondition = $cart->getCondition($cartCondition->getName());
                if ($existingCondition) {
                    continue; // Skip if already applied
                }
                
                // For dynamic conditions, check if rules pass
                if ($conditionRecord->isDynamic()) {
                    if (!$cartCondition->shouldApply($cart)) {
                        continue; // Skip if rules don't pass
                    }
                }
                
                // Apply the condition to the cart
                $cart->addCondition($cartCondition);
                
                Log::info('Global condition applied', [
                    'condition' => $conditionRecord->name,
                    'cart_identifier' => $cart->getIdentifier(),
                ]);
                
            } catch (\Exception $e) {
                Log::error('Failed to apply global condition', [
                    'condition' => $conditionRecord->name,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
```

### 5. Service Provider Registration

**File: `packages/masyukai/filament-cart/src/FilamentCartServiceProvider.php`**

Update `registerEventListeners()` method:

```php
use MasyukAI\FilamentCart\Listeners\ApplyGlobalConditions;

protected function registerEventListeners(): void
{
    // ... existing listeners ...

    // Global conditions - apply automatically on cart creation and item addition
    Event::listen(CartCreated::class, ApplyGlobalConditions::class);
    Event::listen(ItemAdded::class, ApplyGlobalConditions::class);
}
```

### 6. Configuration Option

**File: `packages/masyukai/filament-cart/config/filament-cart.php`**

Add configuration to enable/disable global conditions:

```php
/*
|--------------------------------------------------------------------------
| Enable Global Conditions
|--------------------------------------------------------------------------
|
| When enabled, conditions marked as "global" will be automatically applied
| to all carts when they are created or when items are added.
|
*/
'enable_global_conditions' => env('FILAMENT_CART_ENABLE_GLOBAL_CONDITIONS', true),
```

Update listener registration to respect this config:

```php
protected function registerEventListeners(): void
{
    // ... existing code ...

    // Global conditions
    if (config('filament-cart.enable_global_conditions', true)) {
        Event::listen(CartCreated::class, ApplyGlobalConditions::class);
        Event::listen(ItemAdded::class, ApplyGlobalConditions::class);
    }
}
```

## Hook Points Analysis

### Available Events to Hook Into:

1. ✅ **CartCreated** - Fired when cart is first created
   - **Best for**: Applying initial global conditions
   - **When**: First item added or cart explicitly created

2. ✅ **ItemAdded** - Fired when item is added to cart
   - **Best for**: Re-checking dynamic conditions that depend on items
   - **When**: Every time an item is added or quantity increased
   - **Note**: May trigger multiple times, need to check if condition already exists

3. ❌ **CartUpdated** - Too broad, fired on many operations
4. ❌ **ItemUpdated** - Not needed, quantity changes trigger ItemAdded

### Recommended Hook Strategy:

**Primary Hook: CartCreated**
- Apply all active global conditions when cart is created
- Best performance, runs once per cart lifecycle

**Secondary Hook: ItemAdded**
- Re-evaluate dynamic global conditions
- Allows rules like "min_items: 3" to work correctly
- Skip non-dynamic conditions if already applied

## Edge Cases & Considerations

### 1. Dynamic Rules
- If a global condition has rules, only apply if rules pass
- Re-check on ItemAdded to handle rules like "min_items"
- Remove condition if rules no longer pass (need removal logic)

### 2. Duplicate Prevention
- Always check if condition already exists before applying
- Use condition name as unique identifier

### 3. Order of Application
- Apply conditions in their defined `order` field
- Lower order = applied first

### 4. User-Added vs Global
- If user manually removes a global condition, should it re-apply?
- **Recommendation**: Track removed conditions in cart metadata
- **Alternative**: Let it re-apply (simpler, but may annoy users)

### 5. Performance
- Query global conditions once and cache
- Consider Redis cache for high-traffic sites

### 6. Conflict Resolution
- What if global discount conflicts with user coupon?
- **Recommendation**: Let condition order handle it
- Document that lower order = higher priority

## Testing Plan

### Unit Tests
1. Test Condition model `isGlobal()` method
2. Test `scopeGlobal()` returns only active global conditions
3. Test ApplyGlobalConditions listener logic

### Feature Tests
1. Test global condition applied on cart creation
2. Test dynamic global condition evaluated on item addition
3. Test global condition not duplicated if already exists
4. Test non-global conditions not auto-applied
5. Test inactive global conditions not applied
6. Test global conditions respect order field

### Integration Tests
1. Test cart with items + global tax + global shipping
2. Test dynamic global condition (e.g., "free shipping over $100")
3. Test user removes global condition (behavior depends on implementation choice)

## Migration Path

### For Existing Installations:
1. Run migration to add `is_global` column
2. All existing conditions default to `is_global = false`
3. Admin can enable global on desired conditions via UI
4. No data loss or breaking changes

## UI/UX Considerations

### ConditionResource Table
- Add "Global" badge column
- Filter by global conditions
- Bulk action to enable/disable global

### Form
- Clear explanation of what "global" means
- Warning if making dynamic+global (explain evaluation)
- Hint about performance impact

### User-Facing Cart
- Show which conditions are global (optional)
- Allow/disallow user removal of global conditions (configurable)

## Benefits

1. ✅ **Automated Tax**: Set up tax once, applies to all carts
2. ✅ **Site-Wide Promotions**: "10% off everything" without code
3. ✅ **Standard Fees**: Handling fee, payment processing fee
4. ✅ **Shipping Rules**: "Free shipping over $50" auto-applies
5. ✅ **Flexible**: Can be dynamic (rule-based) or static
6. ✅ **No Code Required**: Admins can manage via UI

## Potential Issues & Solutions

### Issue 1: Performance on high-traffic sites
**Solution**: Cache global conditions, clear on condition update

### Issue 2: User experience - can't remove global condition
**Solution**: Make configurable, or track in metadata

### Issue 3: Order of application matters
**Solution**: Document clearly, provide visual order editor

## Summary

This implementation provides a clean, event-driven approach to automatically applying conditions to all carts. It:
- Uses existing event infrastructure
- Respects dynamic rules
- Prevents duplicates
- Is configurable and testable
- Requires minimal changes to existing code
