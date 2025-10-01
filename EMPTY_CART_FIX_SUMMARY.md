# Cart Empty Cart Creation Fix

## Problem
When visitors accessed the homepage, the application automatically created empty cart records in the database. This happened because:

1. The `brand-header.blade.php` component calls `Cart::getTotalQuantity()` to display the cart badge
2. The `CartCounter` Livewire component calls `CartFacade::getTotalQuantity()` in its `mount()` method
3. This triggers the cart manager to create a cart instance, which fires a `CartCreated` event
4. The `SyncCompleteCart` listener responds to this event and creates a database record for every cart, even empty ones

## Solution
Modified the cart synchronization listeners to only create database records for carts that have actual content (items or conditions):

### 1. Updated `SyncCompleteCart` Listener
- **Before**: Always created database records for any cart event
- **After**: Only creates records for carts with items or conditions
- **Cleanup**: Removes existing database records when carts become empty

### 2. Updated `SyncCartItemOnAdd` Listener
- **Before**: Required existing cart record, logged warnings if not found
- **After**: Creates cart record if needed using `firstOrCreate()`

### 3. Updated `SyncCartConditionOnAdd` Listener
- **Before**: Required existing cart record, logged warnings if not found
- **After**: Creates cart record if needed using `firstOrCreate()`

### 4. Updated `SyncCartItemOnRemove` Listener
- **Before**: Only removed the item record
- **After**: Removes item record and cleans up cart if it becomes empty

### 5. Updated `SyncCartConditionOnRemove` Listener
- **Before**: Only removed the condition record
- **After**: Removes condition record and cleans up cart if it becomes empty

## Result
- ✅ Homepage visits no longer create empty cart records
- ✅ First item addition properly creates cart records
- ✅ Removing all items automatically cleans up cart records
- ✅ Cart with conditions-only are handled correctly
- ✅ All existing functionality remains intact

## Testing
Created comprehensive test suite (`EmptyCartCreationTest`) covering:
- Homepage visits don't create empty carts
- Item addition creates carts
- Item removal cleans up empty carts
- Multi-item workflows
- Condition-only carts

All tests pass and the solution is verified to work correctly.