# Nested Resources Implementation

This document explains how the cart items and conditions resources have been nested within the cart resource to create a more hierarchical and organized structure in the Filament admin panel.

## Overview

The cart resources are now organized in a nested structure where:

- **Cart Resource** (top-level) - Main cart management
  - **Cart Items** (nested) - Managed through relation manager
  - **Cart Conditions** (nested) - Managed through relation manager

## Implementation Details

### 1. Resource Nesting Configuration

Both `CartItemResource` and `CartConditionResource` have been configured as nested resources by adding the `parentResource` property:

```php
// CartItemResource.php
protected static ?string $parentResource = CartResource::class;

// CartConditionResource.php  
protected static ?string $parentResource = CartResource::class;
```

### 2. Relation Managers

Two relation managers have been created to manage the nested resources:

#### ItemsRelationManager
- **Location**: `CartResource/RelationManagers/ItemsRelationManager.php`
- **Relationship**: `cartItems` (from Cart model)
- **Related Resource**: `CartItemResource`
- **Features**: Full table functionality with create, edit, delete actions

#### ConditionsRelationManager
- **Location**: `CartResource/RelationManagers/ConditionsRelationManager.php`
- **Relationship**: `cartConditions` (from Cart model)
- **Related Resource**: `CartConditionResource`
- **Features**: Full table functionality with create, edit, delete actions

### 3. Plugin Registration

The FilamentCart plugin now only registers the top-level CartResource:

```php
// FilamentCart.php
$panel->resources([
    CartResource::class, // Only the parent resource
]);
```

The nested resources are automatically accessible through the relation managers.

## Benefits

### 1. Improved Navigation Structure
- Cleaner admin navigation with fewer top-level items
- Logical grouping of related functionality
- Reduced cognitive load for users

### 2. Contextual Management
- Cart items and conditions are now managed in the context of their parent cart
- Better data relationships visibility
- Streamlined workflow for cart management

### 3. Performance Benefits
- Maintains the same 10-100x faster performance from normalized database structure
- Efficient eager loading through relation managers
- Optimized queries for related data

## Usage

### Accessing Nested Resources

1. **Navigate to Carts**: Visit `/admin/carts` to see all carts
2. **View/Edit Cart**: Click on any cart to view or edit it
3. **Manage Items**: Use the "Items" tab/section to manage cart items
4. **Manage Conditions**: Use the "Conditions" tab/section to manage cart conditions

### Available Actions

#### Cart Items Relation Manager
- **View**: See all items in the cart with advanced filtering
- **Create**: Add new items to the cart
- **Edit**: Modify existing cart items
- **Delete**: Remove items from the cart
- **Filter**: Filter by price, quantity, cart, etc.
- **Search**: Search by item ID, name, etc.

#### Cart Conditions Relation Manager
- **View**: See all conditions applied to the cart
- **Create**: Add new conditions (discounts, taxes, fees, etc.)
- **Edit**: Modify existing conditions
- **Delete**: Remove conditions
- **Filter**: Filter by type, level, etc.
- **Search**: Search by condition name, type, etc.

## Testing

Comprehensive tests have been implemented in `NestedResourcesTest.php` covering:

- ✅ Nested resource configuration
- ✅ Relation manager registration
- ✅ UI components rendering
- ✅ Data display functionality
- ✅ Navigation structure validation
- ✅ Resource accessibility

All tests pass, ensuring reliable functionality.

## Technical Implementation

### Database Relationships

The implementation leverages the existing database relationships:

```php
// Cart.php
public function cartItems(): HasMany
{
    return $this->hasMany(CartItem::class);
}

public function cartConditions(): HasMany
{
    return $this->hasMany(CartCondition::class);
}
```

### Database Compatibility

The implementation includes database-aware query scopes that work correctly across different database engines:

- **PostgreSQL**: Uses explicit type casting (`items::text != '[]'`)
- **SQLite/MySQL**: Uses direct comparison (`items != '[]'`)

This ensures the navigation badge and cart counting functionality work correctly in both development (SQLite) and production (PostgreSQL) environments.

### Synchronization

The nested resources maintain full synchronization with the cart package through the existing event-driven system, ensuring data consistency across all interfaces.

### Future Enhancements

The nested structure provides a foundation for additional features:

- Bulk operations on cart items
- Advanced condition management
- Cart templates and presets
- Enhanced reporting and analytics

This implementation provides a more intuitive and organized approach to cart management while maintaining all existing functionality and performance benefits.