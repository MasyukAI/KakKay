# Cart Swap Feature

This document describes the new cart swapping functionality that provides a **super simple** way to transfer cart ownership by directly changing the identifier column value.

## Overview

The cart swap feature provides exactly what was requested: a "super stupid and simple solution" that just changes the cart's identifier column value. Instead of creating new carts, transferring content, and deleting old ones, this implementation directly updates the identifier in the storage layer.

### How It Works

For **database storage**, this translates to a simple SQL UPDATE:
```sql
UPDATE cart_storage 
SET identifier = 'new_identifier' 
WHERE identifier = 'old_identifier' AND instance = 'instance_name'
```

For **session/cache storage**, it falls back to the copy/delete approach since we can't directly rename keys.

## Why This Approach is Better

- ✅ **Super Simple**: Just changes one column value
- ✅ **Super Fast**: Single database operation  
- ✅ **Super Effective**: No data copying or merging complexity
- ✅ **Atomic**: Either succeeds completely or fails completely
- ✅ **Memory Efficient**: No temporary data structures needed

## Use Cases

- **Guest to User Login**: When a guest user logs in, transfer their cart ownership to their user account
- **Session Transfer**: Moving cart ownership between different sessions
- **Account Switching**: Transferring carts between different user accounts
- **Cross-device Sync**: Simple ownership transfer for device switching

## API Methods

### Basic Swap Methods

#### `Cart::swap($oldIdentifier, $newIdentifier, $instance = 'default')`

Swaps cart ownership for a specific instance.

```php
// Swap default cart from guest session to user
$success = Cart::swap('guest_session_123', 'user_42', 'default');

// Swap wishlist from one user to another
$success = Cart::swap('user_42', 'user_99', 'wishlist');
```

**Parameters:**
- `$oldIdentifier` (string): Source identifier (e.g., session ID)
- `$newIdentifier` (string): Target identifier (e.g., user ID)  
- `$instance` (string): Cart instance name (default: 'default')

**Returns:** `bool` - True if swap was successful, false if source cart doesn't exist

### CartMigrationService Methods

#### `swapGuestCartToUser($userId, $instance = 'default', $oldSessionId = null)`

Convenience method to swap guest cart to user cart.

```php
$migrationService = new CartMigrationService();

// Swap current session's default cart to user 42
$success = $migrationService->swapGuestCartToUser(42);

// Swap specific session's wishlist to user 42
$success = $migrationService->swapGuestCartToUser(42, 'wishlist', 'specific_session_id');
```

#### `swapAllGuestInstancesToUser($userId, $oldSessionId = null)`

Swap all cart instances from guest to user.

```php
// Swap all instances (default, wishlist, etc.) from current session to user 42
$results = $migrationService->swapAllGuestInstancesToUser(42);

// Results array contains success status for each instance
// ['default' => true, 'wishlist' => true, 'compare' => false]
```

#### `swapAllInstances($oldIdentifier, $newIdentifier)`

Generic method to swap all instances between any identifiers.

```php
// Swap all cart instances from one identifier to another
$results = $migrationService->swapAllInstances('old_identifier', 'new_identifier');
```

## Comparison with Migration

| Feature | Swap | Migration |
|---------|------|-----------|
| **Purpose** | Transfer ownership | Merge contents |
| **Speed** | Fast | Slower |
| **Complexity** | Simple | Complex |
| **Conflict Handling** | None needed | Full conflict resolution |
| **Use Case** | Clean ownership transfer | Merging existing carts |
| **Data Preservation** | Exact copy | May modify quantities |

## When to Use Swap vs Migration

### Use Swap When:
- Guest user logs in and has no existing user cart
- Moving cart between sessions/devices
- Clean ownership transfer is needed
- Performance is critical
- You want to preserve cart exactly as-is

### Use Migration When:
- User already has existing cart items
- You need to merge cart contents
- Conflict resolution is required
- You want to add quantities together

## Example Usage Scenarios

### 1. Guest Login (No Existing User Cart)

```php
// When guest logs in, simply swap ownership
$migrationService = new CartMigrationService();
$success = $migrationService->swapGuestCartToUser(Auth::id());

if ($success) {
    // Cart ownership transferred successfully
    return redirect()->route('cart.index');
}
```

### 2. Multi-Instance Swap

```php
// Transfer all cart types when user logs in
$results = $migrationService->swapAllGuestInstancesToUser(Auth::id());

foreach ($results as $instance => $success) {
    if ($success) {
        Log::info("Swapped {$instance} cart successfully");
    }
}
```

### 3. Cross-Device Transfer

```php
// Move cart from old device session to new device session
$success = Cart::swap('old_device_session', session()->getId(), 'default');
```

## Error Handling

The swap methods return `false` in these cases:
- Source cart doesn't exist
- Source cart is empty (no items and no conditions)
- Storage operation fails

```php
$success = Cart::swap('guest_123', 'user_42', 'default');

if (!$success) {
    // Handle failure - cart might not exist or be empty
    Log::warning('Cart swap failed: source cart not found or empty');
}
```

## Performance Notes

- Swap operations are atomic at the storage level
- Much faster than migration since no merging logic is involved
- Minimal memory usage as data is moved, not copied
- No event dispatching overhead (unlike migration)

## Integration with Existing Code

The swap functionality is designed to complement, not replace, the existing migration system:

```php
// Check if user has existing cart
if ($userHasExistingCart) {
    // Use migration for merging
    $migrationService->migrateGuestCartToUser($userId);
} else {
    // Use swap for simple transfer
    $migrationService->swapGuestCartToUser($userId);
}
```