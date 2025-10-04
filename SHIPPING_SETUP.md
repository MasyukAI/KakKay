# Quick Setup Guide: Shipping Global Condition

## ✅ Changes Complete

All hardcoded shipping logic has been removed from `app/Livewire/Cart.php`. The cart now relies entirely on **global conditions** for shipping.

## 🚀 Next Steps

### 1. Create the Shipping Global Condition

Go to your Filament admin panel and create a new condition:

**URL:** `/admin/conditions/create`

**Fill in the form:**

```
Basic Information:
├─ Condition Name: shipping
└─ Display Name: Standard Shipping

Condition Details:
├─ Type: Shipping
├─ Target: Total
├─ Value: +9.90
└─ Order: 0

Advanced Options:
├─ ✓ Active Condition: ON
├─ ✓ Global Condition: ON
└─ Dynamic Rules: (leave empty)
```

**Click "Create"**

### 2. Test Your Cart

1. Add a product to cart
2. View cart page
3. Verify shipping (RM9.90) appears in the order summary
4. Proceed to checkout
5. Confirm total is correct

### 3. Alternative: Create via Tinker

If you prefer command line:

```bash
php artisan tinker
```

```php
MasyukAI\FilamentCart\Models\Condition::create([
    'name' => 'shipping',
    'display_name' => 'Standard Shipping',
    'type' => 'shipping',
    'target' => 'total',
    'value' => '+9.90',
    'order' => 0,
    'attributes' => [],
    'is_active' => true,
    'is_global' => true,
    'rules' => [],
]);
```

## 📊 What Changed

### Before (Hardcoded):
```php
// In Cart.php - REMOVED
protected function ensureShippingCondition(): void
{
    if (! CartFacade::isEmpty()) {
        if (! CartFacade::getCondition('shipping')) {
            CartFacade::addShipping(
                name: 'shipping',
                value: 990,
                method: 'standard'
            );
        }
    }
}
```

### After (Global Condition):
```php
// Now handled automatically by ApplyGlobalConditions listener
// No code needed in Cart.php!
```

## 🎯 Benefits

✅ **No code changes needed** for shipping updates  
✅ **Manage in admin panel** - update rates anytime  
✅ **Add rules easily** - free shipping thresholds, etc.  
✅ **Cleaner codebase** - 47 lines of code removed  
✅ **Consistent** - shipping works like other conditions  

## 🔧 Advanced: Add Free Shipping Rule

To add free shipping for orders over RM100:

```
Condition Name: free-shipping-100
Display Name: Free Shipping
Type: Shipping
Target: Total
Value: -9.90 (negative cancels out shipping)
Order: 1 (applies after base shipping)
Global Condition: ✓ ON
Rules:
  └─ min_total: 100
```

## ⚠️ Important

- Condition name **must be "shipping"** for display to work
- Value should be **+9.90** (with plus sign) or just **9.90**
- Make sure **Global Condition** toggle is ON
- Make sure **Active Condition** toggle is ON

## 🧪 Testing

The cart will:
1. ✅ Show RM0.00 shipping if no global shipping condition exists
2. ✅ Show shipping cost if global shipping condition exists
3. ✅ Auto-apply shipping when items are added
4. ✅ Remove shipping when cart is emptied

## 📝 Documentation

Full details available in:
- `docs/shipping-global-condition-migration.md`
- `docs/global-conditions-implementation.md`

## 🆘 Troubleshooting

**Shipping not showing?**
1. Check condition is created with name "shipping"
2. Verify "Global Condition" is ON
3. Verify "Active Condition" is ON
4. Clear browser cache and refresh

**Wrong amount?**
1. Check the value field (should be +9.90 or 9.90)
2. Ensure there aren't multiple shipping conditions
3. Check condition order/priority

## Status: ✅ READY TO DEPLOY

Once you create the shipping global condition in admin, everything will work automatically!
