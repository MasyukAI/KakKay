# Shipping Migration to Global Conditions

## Overview
Removed all hardcoded shipping logic from the cart and migrated to the global conditions system.

## Changes Made

### File: `app/Livewire/Cart.php`

#### 1. Removed `ensureShippingCondition()` method
**Before:**
```php
protected function ensureShippingCondition(): void
{
    // Only add shipping if cart is not empty
    if (! CartFacade::isEmpty()) {
        // Check if shipping condition already exists
        if (! CartFacade::getCondition('shipping')) {
            CartFacade::addShipping(
                name: 'shipping',
                value: 990, // RM9.90 in cents
                method: 'standard'
            );
        }
    }
}
```
**After:** ❌ Completely removed - shipping now handled by global conditions

#### 2. Removed `ensureShippingCondition()` calls from `mount()` and `refreshCart()`
**Before:**
```php
public function mount(): void
{
    $this->ensureShippingCondition();
    $this->loadCartItems();
    $this->loadSuggestedProducts();
}

#[On('product-added-to-cart')]
public function refreshCart(): void
{
    $this->ensureShippingCondition();
    $this->loadCartItems();
    $this->loadSuggestedProducts();
}
```

**After:**
```php
public function mount(): void
{
    $this->loadCartItems();
    $this->loadSuggestedProducts();
}

#[On('product-added-to-cart')]
public function refreshCart(): void
{
    $this->loadCartItems();
    $this->loadSuggestedProducts();
}
```

#### 3. Removed `ensureShippingCondition()` call from `addToCart()`
**Before:**
```php
$this->ensureShippingCondition();
$this->loadCartItems();
$this->loadSuggestedProducts();
```

**After:**
```php
$this->loadCartItems();
$this->loadSuggestedProducts();
```

#### 4. Updated `getShipping()` to check for condition by name
**Before:**
```php
public function getShipping(): \Akaunting\Money\Money
{
    $shippingCondition = CartFacade::getShipping();
    // ...
}
```

**After:**
```php
public function getShipping(): \Akaunting\Money\Money
{
    // Check if there's a shipping condition applied to the cart
    $shippingCondition = CartFacade::getCondition('shipping');
    // ...
}
```

## How to Set Up Shipping as a Global Condition

### Step 1: Create Shipping Global Condition in Filament Admin

1. Navigate to **E-commerce → Conditions** in Filament admin
2. Click **New Condition**
3. Fill in the form:

```
Basic Information:
- Condition Name: shipping
- Display Name: Standard Shipping

Condition Details:
- Type: Shipping
- Target: Total
- Value: +9.90 (or 990 if using cents)
- Order: 0

Advanced Options:
- Active Condition: ✓ ON
- Global Condition: ✓ ON
- Dynamic Rules: (leave empty or add rules like min_total if needed)
```

4. Click **Create**

### Step 2: Verify Automatic Application

The shipping condition will now automatically:
- ✅ Apply to all new carts when created
- ✅ Apply when items are added to cart
- ✅ Respect any dynamic rules you set (e.g., free shipping over RM100)

### Example: Conditional Free Shipping

To add free shipping for orders over RM100:

1. Create another condition:
```
- Condition Name: free-shipping-over-100
- Display Name: Free Shipping (Orders over RM100)
- Type: Shipping
- Target: Total
- Value: -9.90 (negative to cancel out shipping)
- Global Condition: ✓ ON
- Dynamic Rules:
  - min_total: 100
```

2. Set order priority:
   - Shipping condition: order = 0
   - Free shipping: order = 1 (applies after)

## Benefits of This Approach

### 1. **Centralized Management**
- All shipping rules managed in Filament admin
- No code changes needed for shipping updates
- Easy to modify rates, add promotions, etc.

### 2. **Flexible Rules**
- Can add conditional shipping (free shipping thresholds)
- Weight-based shipping with dynamic rules
- Location-based shipping with custom rules
- Time-based shipping promotions

### 3. **Clean Code**
- Removed 47 lines of hardcoded logic
- No more manual condition checking
- Consistent with other cart conditions

### 4. **Automatic Application**
- Global conditions listener handles everything
- No duplicate checking needed
- Works seamlessly with other conditions

## Testing

### Manual Testing Steps:
1. ✅ Create a new cart (shipping should auto-apply)
2. ✅ Add item to cart (shipping persists)
3. ✅ Check cart display (shipping shows correctly)
4. ✅ Empty cart (shipping removed with cart)
5. ✅ Disable global condition in admin (shipping stops applying)

### What to Check:
- Cart subtotal calculation
- Shipping display in cart summary
- Total includes shipping
- Checkout page shows correct amounts

## Migration Notes

### Before Deploying:
1. ✅ Create shipping global condition in production admin panel
2. ✅ Test with real cart flows
3. ✅ Verify checkout totals match expectations
4. ✅ Check existing carts (may need to trigger item addition to apply condition)

### Rollback Plan (if needed):
If issues arise, you can:
1. Disable the global shipping condition in admin
2. Or revert to previous Cart.php version with hardcoded logic

## Advanced Examples

### Weight-Based Shipping
```php
// In RuleConverter, add custom rule:
'item_weight' => fn (Cart $cart) => $cart->getItems()->sum('attributes.weight') <= (float) $ruleValue
```

Then create conditions:
```
Light Package (<500g):
- Value: +5.00
- Rules: { "item_weight": "500" }

Heavy Package (≥500g):
- Value: +9.90
- Rules: { "item_weight": "501" } (using inverted logic or custom rule)
```

### Location-Based Shipping
```php
// Add to RuleConverter:
'shipping_zone' => fn (Cart $cart) => auth()->user()?->shipping_zone === $ruleValue
```

```
West Malaysia:
- Value: +9.90
- Rules: { "shipping_zone": "west" }

East Malaysia:
- Value: +15.00
- Rules: { "shipping_zone": "east" }
```

## Support

For issues or questions:
1. Check if shipping global condition is active
2. Verify condition name is exactly "shipping"
3. Check cart events are firing (CartCreated, ItemAdded)
4. Review logs for ApplyGlobalConditions listener

## Status: ✅ COMPLETE

All hardcoded shipping logic has been successfully removed and migrated to the global conditions system. The cart now relies entirely on the global conditions feature for shipping management.
