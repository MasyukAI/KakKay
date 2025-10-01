# Admin Panel Clear Cart - Comprehensive Clearing

**Date:** October 1, 2025  
**Status:** ✅ Complete

## Overview

Updated the "Clear Cart" functionality in the Filament admin panel to properly clear all cart data while preserving the cart record for manual administration. This includes clearing the main cart columns AND deleting related normalized records from `cart_items` and `cart_conditions` tables.

## Problem

The initial implementation of the admin panel's "Clear Cart" action:
1. ❌ Only cleared some columns (`items`, `conditions`) but missed `metadata`
2. ❌ Didn't increment the `version` column
3. ❌ Left orphaned records in `cart_items` and `cart_conditions` tables

## Solution

Updated all "Clear Cart" actions to:
1. ✅ Clear ALL cart data columns: `items`, `conditions`, `metadata`
2. ✅ Delete related normalized records from `cart_items` table
3. ✅ Delete related normalized records from `cart_conditions` table  
4. ✅ Increment the `version` column
5. ✅ Reset calculated fields: `items_count`, `total_quantity`, `subtotal`, `total`
6. ✅ Keep the cart record in the database for manual management

## Database Tables Involved

### 1. `carts` Table (Main Cart Record)
```sql
- id (uuid)
- identifier (string)
- instance (string)
- items (jsonb) ← Cleared to []
- conditions (jsonb) ← Cleared to []
- metadata (jsonb) ← Cleared to []
- version (integer) ← Incremented
- items_count (calculated) ← Reset to 0
- total_quantity (calculated) ← Reset to 0
- subtotal (calculated) ← Reset to 0
- total (calculated) ← Reset to 0
- timestamps
```

### 2. `cart_items` Table (Normalized Items)
```sql
- id (uuid)
- cart_id (foreign key to carts) ← Records deleted via cart_id
- item_id (string)
- name, price, quantity, subtotal
- attributes, conditions
- associated_model
- timestamps
```

### 3. `cart_conditions` Table (Normalized Conditions)
```sql
- id (uuid)
- cart_id (foreign key to carts) ← Records deleted via cart_id
- cart_item_id (nullable foreign key to cart_items)
- name, type, target, value, order
- attributes
- item_id (nullable)
- timestamps
```

## Implementation Details

### Files Updated

1. **`EditCart.php`** - Single cart clear action
2. **`CartsTable.php`** - Individual row clear action
3. **`CartsTable.php`** - Bulk clear selected carts action

### Code Pattern

```php
// Delete normalized cart_items records
DB::table('cart_items')->where('cart_id', $record->id)->delete();

// Delete normalized cart_conditions records
DB::table('cart_conditions')->where('cart_id', $record->id)->delete();

// Clear cart data and increment version
$record->update([
    'items' => [],
    'conditions' => [],
    'metadata' => [],
    'items_count' => 0,
    'total_quantity' => 0,
    'subtotal' => 0,
    'total' => 0,
    'version' => $record->version + 1,
]);
```

### Bulk Clear Optimization

For bulk operations, we optimize by:
1. Collecting all cart IDs
2. Deleting related records in batch using `whereIn()`
3. Updating each cart individually to increment version correctly

```php
$cartIds = $records->pluck('id')->toArray();

// Delete normalized records for all selected carts in batch
DB::table('cart_items')->whereIn('cart_id', $cartIds)->delete();
DB::table('cart_conditions')->whereIn('cart_id', $cartIds)->delete();

// Update each cart individually
$records->each(function ($record) {
    $record->update([
        // ... updates with version increment
    ]);
});
```

## Benefits

1. **Data Integrity** - No orphaned records in normalized tables
2. **Version Tracking** - Version increments properly for optimistic locking
3. **Complete Clearing** - All cart data including metadata is cleared
4. **Admin Flexibility** - Cart record preserved for manual item/condition addition
5. **Performance** - Bulk operations optimized with batch deletes

## Use Cases

### 1. Single Cart Clear (Edit Page)
Admin is editing a cart and wants to clear it to start fresh:
- Redirects back to edit page
- Cart is empty and ready for manual additions

### 2. Single Cart Clear (Table Row)
Admin clears a cart from the table view:
- Stays on the table page
- Cart shows as empty in the listing

### 3. Bulk Clear Selected
Admin selects multiple carts and clears them all:
- All selected carts cleared in batch
- Optimized with batch deletes
- Each cart version incremented individually

## Database Cascade Note

The foreign key constraints on `cart_items` and `cart_conditions` have `onDelete('cascade')`, so technically when a cart is deleted, related records are automatically removed. However, we explicitly delete them in the clear action because:

1. **Clarity** - Makes the operation explicit and clear in the code
2. **Control** - We have full control over the clearing process
3. **Audit** - Can add logging or events if needed in the future
4. **Independence** - Doesn't rely on database cascade behavior

## Testing Checklist

- [ ] Clear single cart from edit page
- [ ] Verify cart record remains in database
- [ ] Verify `items`, `conditions`, `metadata` are empty arrays
- [ ] Verify `version` incremented
- [ ] Verify all `cart_items` records deleted
- [ ] Verify all `cart_conditions` records deleted
- [ ] Clear cart from table row action
- [ ] Bulk clear multiple carts
- [ ] Verify optimized batch deletes for bulk operation
- [ ] Add items manually after clearing
- [ ] Add conditions manually after clearing

## Conclusion

The admin panel "Clear Cart" functionality now performs a comprehensive clearing operation that:
- Clears all cart data in the main `carts` table
- Deletes related normalized records from `cart_items` and `cart_conditions` tables
- Increments the version for proper optimistic locking
- Preserves the cart record for manual administration

This ensures complete data integrity and proper cleanup while maintaining the flexibility for admins to manually manage cart contents through the Filament UI.
