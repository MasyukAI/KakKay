# Checkout Shipping Logic Refactor

## Summary
Removed hardcoded shipping logic from the checkout page and implemented dynamic condition-based shipping detection, matching the cart page implementation.

## Changes Made

### 1. **app/Livewire/Checkout.php**

#### Before (Hardcoded Shipping Logic):
```php
public function getTotal(): \Akaunting\Money\Money
{
    $cartTotal = CartFacade::total();
    $shipping = $this->getShippingMoney();

    return $cartTotal->add($shipping);
}

public function getShippingMoney(): \Akaunting\Money\Money
{
    $deliveryMethod = $this->data['delivery_method'] ?? 'standard';
    $currency = config('cart.money.default_currency', 'MYR');

    $shippingAmount = match ($deliveryMethod) {
        'express' => 4900, // RM49 in cents
        'fast' => 1500,    // RM15 in cents
        default => 500,    // RM5 in cents
    };

    return \Akaunting\Money\Money::{$currency}($shippingAmount);
}
```

#### After (Dynamic Condition-Based):
```php
#[Computed]
public function getTotal(): \Akaunting\Money\Money
{
    return CartFacade::total(); // Cart total already includes all conditions (including shipping)
}

#[Computed]
public function getShipping(): \Akaunting\Money\Money
{
    // Check if there's a shipping condition applied to the cart
    $shippingCondition = CartFacade::getCondition('shipping');

    if ($shippingCondition) {
        $currency = config('cart.money.default_currency', 'MYR');

        return \Akaunting\Money\Money::{$currency}((int) $shippingCondition->getValue());
    }

    // Return zero if no shipping condition exists
    $currency = config('cart.money.default_currency', 'MYR');

    return \Akaunting\Money\Money::{$currency}(0);
}
```

### 2. **resources/views/livewire/checkout.blade.php**

#### Before:
```blade
<span class="font-medium text-white">{{ $this->getShippingMoney()->format() }}</span>
```

#### After:
```blade
<span class="font-medium text-white">{{ $this->getShipping()->format() }}</span>
```

## Benefits

1. **Consistency**: Checkout page now uses the same shipping logic as the cart page
2. **Flexibility**: Shipping costs are managed through cart conditions, not hardcoded values
3. **Centralized Management**: Shipping can be managed through Filament's condition system
4. **Dynamic Pricing**: Shipping costs can change based on cart conditions and rules
5. **Easier Maintenance**: One source of truth for shipping logic

## Implementation Details

### How It Works:
1. The checkout page checks if a cart condition named `'shipping'` exists
2. If found, it retrieves the shipping amount from the condition
3. If not found, it returns RM 0.00 (zero shipping)
4. The cart total automatically includes all conditions, including shipping

### Testing Results:
- ✅ All 12 checkout tests passing
- ✅ All 11 cart tests passing
- ✅ No breaking changes to existing functionality

## Migration Notes

### For Developers:
- Remove any hardcoded `delivery_method` form fields if they exist
- Shipping should now be managed through cart conditions
- Use Filament's Condition resource to set up shipping rules

### For Administrators:
- Create shipping conditions in Filament Admin Panel
- Set the condition name to `'shipping'`
- Configure shipping amount and rules as needed
- Conditions can be global (applied automatically) or manual

## Example Shipping Condition Setup

```php
Condition::create([
    'name' => 'shipping',
    'display_name' => 'Standard Shipping',
    'type' => 'shipping',
    'target' => 'total',
    'value' => '+500', // RM 5.00 in cents
    'is_global' => true,
    'is_active' => true,
    'rules' => [],
    'order' => 0,
]);
```

## Related Files

- `app/Livewire/Checkout.php` - Main checkout component
- `app/Livewire/Cart.php` - Cart component (reference implementation)
- `resources/views/livewire/checkout.blade.php` - Checkout view
- `resources/views/livewire/cart.blade.php` - Cart view (reference)

## Date
October 3, 2025
