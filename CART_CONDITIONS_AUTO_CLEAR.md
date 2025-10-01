# Cart Auto-Deletion When Empty

## Overview
When the cart becomes empty (all items removed), the cart record is automatically deleted from the database. The `Cart::clear()` method handles complete cleanup including items, conditions, and the cart record itself through a single database DELETE operation.

## Implementation

### Livewire Cart Component (`app/Livewire/Cart.php`)

Three methods have been updated to delete the cart from storage when it becomes empty:

#### 1. `removeItem()` Method
```php
public function removeItem(string $itemId): void
{
    $item = CartFacade::get($itemId);
    $itemName = $item ? $item->name : 'Item';
    CartFacade::remove($itemId);
    
    // If cart is now empty, delete the cart from storage (this also removes conditions)
    if (CartFacade::isEmpty()) {
        CartFacade::clear();
    }
    
    $this->loadCartItems();
    $this->loadSuggestedProducts();
    $this->dispatch('product-added-to-cart');
    // ... notification
}
```

#### 2. `decrementQuantity()` Method
```php
public function decrementQuantity($itemId)
{
    $item = CartFacade::get($itemId);
    if ($item) {
        $newQuantity = $item->quantity - 1;

        if ($newQuantity <= 0) {
            $this->removeItem($itemId);
            return;
        }

        CartFacade::update($itemId, ['quantity' => ['value' => $newQuantity]]);
        
        // If cart is now empty, delete the cart from storage (this also removes conditions)
        if (CartFacade::isEmpty()) {
            CartFacade::clear();
        }
        
        $this->loadCartItems();
        $this->dispatch('product-added-to-cart');
        // ... notification
    }
}
```

#### 3. `updateQuantity()` Method
```php
public function updateQuantity(string $itemId, int $quantity): void
{
    if ($quantity <= 0) {
        $this->removeItem($itemId);
        return;
    }

    CartFacade::update($itemId, ['quantity' => ['value' => $quantity]]);
    
    // If cart is now empty, delete the cart from storage (this also removes conditions)
    if (CartFacade::isEmpty()) {
        CartFacade::clear();
    }
    
    $this->loadCartItems();
    $this->dispatch('product-added-to-cart');
}
```

## How `Cart::clear()` Works

The `clear()` method is a single operation that:

1. Calls `storage->forget(identifier, instance)` 
2. Executes a DELETE query: `DELETE FROM carts WHERE identifier = ? AND instance = ?`
3. Database foreign key cascades automatically delete related records:
   - All `cart_items` records (via `cart_id` foreign key)
   - All `cart_conditions` records (via `cart_id` foreign key)
4. Dispatches `CartCleared` event

**No need to manually clear conditions** - the database handles everything through cascading deletes.

## Behavior

### When Cart is Deleted from Database
- **All items removed**: When the last item is removed from the cart
- **Quantity reduced to zero**: When item quantity is decremented or updated to 0
- **Manual item removal**: When user clicks "Remove" button
- **Database cleanup**: Cart record is deleted from the `carts` table, along with related records in `cart_items` and `cart_conditions` tables (via foreign key cascades)

### When Cart is Preserved
- **Items remain**: When cart still has one or more items after removal
- **Quantity reduction**: When quantity is decreased but not to zero
- **Adding items**: Adding new items creates a fresh cart if needed

### Process Flow
```
User removes last item
    ↓
Cart becomes empty (items = [])
    ↓
Clear all conditions (conditions = [])
    ↓
Delete cart from database
    ↓
Cart record removed from storage
```

## Conditions Affected
All cart-level conditions are cleared when cart becomes empty:
- **Shipping**: `Cart::addShipping()`
- **Tax**: `Cart::addTax()`
- **Discounts**: `Cart::addDiscount()`
- **Fees**: `Cart::addFee()`
- **Custom conditions**: Any custom `CartCondition` added to cart

## Testing

Test file: `tests/Feature/CartConditionsClearTest.php`

### Test Cases
1. ✅ Clears conditions when cart becomes empty after removing all items
2. ✅ Clears multiple conditions when cart becomes empty
3. ✅ Keeps conditions when items remain in cart after removal
4. ✅ Does not clear conditions if cart is not empty

Run tests:
```bash
php artisan test --filter=CartConditionsClearTest
```

## Example Usage

```php
// User has 1 item with shipping condition
Cart::add('book-1', 'Laravel Book', 5000, 1);
Cart::addShipping('shipping', 990, 'standard');

// Cart exists in database
Cart::isEmpty(); // false
Cart::getConditions(); // collection with 'shipping'

// Remove last item - conditions cleared AND cart deleted
Cart::remove('book-1');

// Cart is completely gone from database
Cart::isEmpty(); // true
Cart::getConditions(); // empty collection
// Database record deleted
```

## Benefits
1. **No orphaned conditions**: Prevents shipping fees on empty carts
2. **Clean database**: No empty cart records cluttering the database
3. **Better performance**: Fewer database queries for empty carts
4. **Clean state**: Cart returns to pristine state when empty
5. **Automatic**: No manual intervention needed
6. **Cascading deletes**: Related `cart_items` and `cart_conditions` records automatically deleted via foreign keys

## Related Files
- `app/Livewire/Cart.php` - Main cart component with condition clearing logic
- `tests/Feature/CartConditionsClearTest.php` - Test coverage
- `packages/masyukai/cart/packages/core/src/Cart.php` - Core cart functionality
- `packages/masyukai/cart/packages/core/src/Traits/ManagesConditions.php` - Condition management

## Notes
- **Order matters**: Conditions are cleared BEFORE cart deletion to ensure proper cleanup
- **Database cleanup**: `Cart::clear()` calls `storage->forget()` which deletes the database record
- **Cascading deletes**: Foreign keys ensure `cart_items` and `cart_conditions` are deleted automatically
- Item-level conditions are removed automatically when items are removed
- The `ensureShippingCondition()` method only adds shipping when cart is NOT empty
- Dynamic conditions from the cart package evaluate rules automatically
- Empty cart = no database record = clean state for next shopping session
