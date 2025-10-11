# Package Renaming Summary

## Overview
Renamed packages to have clearer, more descriptive names that better reflect their purpose in the monorepo structure.

## Package Name Changes

### Before → After

1. **Commerce Package**
   - `packages/commerce` → `masyukai/commerce` (was `masyukai/cart`)
   - Path: `packages/commerce/`
   - Purpose: Main commerce package that aggregates all sub-packages

2. **Cart Package**
   - `packages/commerce/packages/cart` → `masyukai/cart` (was `masyukai/cart-core`)
   - Path: `packages/commerce/packages/cart/`
   - Purpose: Core cart functionality

3. **Docs Package**
   - No change: `masyukai/docs`
   - Path: `packages/commerce/packages/docs/`

4. **Stock Package**
   - No change: `masyukai/stock`
   - Path: `packages/commerce/packages/stock/`

5. **Vouchers Package**
   - No change: `masyukai/cart-vouchers`
   - Path: `packages/commerce/packages/vouchers/`

## Rationale

### Why rename `masyukai/cart` to `masyukai/commerce`?
- The main package contains more than just cart functionality (docs, stock, vouchers)
- "Commerce" better describes the full scope of the package
- Allows the core cart functionality to properly use the `masyukai/cart` name

### Why rename `masyukai/cart-core` to `masyukai/cart`?
- More intuitive naming - the core cart package should have the simple `cart` name
- Removes the redundant `-core` suffix
- Aligns with common naming conventions in the Laravel ecosystem

## Updated Dependencies

### Main Application
```json
"require": {
    "masyukai/commerce": "@dev",  // was: "masyukai/cart": "@dev"
    "masyukai/filament-cart": "^1.0"
}
```

### Commerce Package
```json
"require": {
    "masyukai/cart": "@dev",           // NEW: core cart functionality
    "masyukai/docs": "@dev",
    "masyukai/stock": "@dev",
    "masyukai/cart-vouchers": "@dev"
}
```

### Filament Cart Package
```json
"require": {
    "masyukai/commerce": "@dev"  // was: "masyukai/cart": "^1.0"
}
```

### Vouchers Package
```json
"require": {
    "masyukai/cart": "@dev"  // was: "masyukai/cart": "^1.0"
}
```

## File Changes

### Updated Files
1. `packages/commerce/composer.json` - Changed name to `masyukai/commerce`, added cart repository and requirement
2. `packages/commerce/packages/cart/composer.json` - Changed name to `masyukai/cart`
3. `packages/commerce/packages/vouchers/composer.json` - Updated cart dependency to `@dev`, added cart repository
4. `packages/filament-cart/composer.json` - Changed dependency to `masyukai/commerce`, added all sub-package repositories, changed minimum-stability to `dev`
5. `composer.json` (main app) - Changed requirement to `masyukai/commerce`, added cart package repository
6. `MONOREPO_RESTRUCTURING.md` - Updated documentation to reflect new names

## Migration Steps Completed

1. ✅ Renamed commerce package from `masyukai/cart` to `masyukai/commerce`
2. ✅ Renamed cart package from `masyukai/cart-core` to `masyukai/cart`
3. ✅ Added cart package to commerce repositories and requirements
4. ✅ Updated vouchers package to require cart as `@dev` and added repository
5. ✅ Updated filament-cart to require `masyukai/commerce` with all sub-package repositories
6. ✅ Updated main application to require `masyukai/commerce` and added cart repository
7. ✅ Ran `composer update` in all packages
8. ✅ Verified all tests pass:
   - Commerce: 619 passed, 17 skipped
   - Filament Cart: 54 passed
9. ✅ Updated documentation

## Package Structure After Renaming

```
packages/
├── commerce/ (masyukai/commerce) ← renamed from masyukai/cart
│   ├── packages/
│   │   ├── cart/ (masyukai/cart) ← renamed from masyukai/cart-core
│   │   ├── docs/ (masyukai/docs)
│   │   ├── stock/ (masyukai/stock)
│   │   └── vouchers/ (masyukai/cart-vouchers)
│   ├── tests/
│   └── composer.json
├── filament-cart/ (masyukai/filament-cart)
├── filament-chip/ (masyukai/filament-chip)
├── chip/ (masyukai/chip)
└── jnt/ (masyukai/jnt)
```

## Dependency Graph After Renaming

```
Main Application
├── masyukai/commerce ← main commerce package
│   ├── masyukai/cart ← core cart functionality
│   ├── masyukai/docs
│   ├── masyukai/stock
│   └── masyukai/cart-vouchers
│       └── masyukai/cart (dependency)
├── masyukai/filament-cart
│   └── masyukai/commerce
│       └── (all commerce sub-packages)
├── masyukai/chip
├── masyukai/filament-chip
└── masyukai/jnt
```

## Benefits of New Naming

1. **Clearer Purpose**: Package names now clearly indicate what they contain
   - `masyukai/commerce` = full commerce suite
   - `masyukai/cart` = core cart functionality

2. **Better Organization**: The main package name (`commerce`) reflects its role as an aggregator

3. **Improved Discoverability**: Developers can easily understand the package hierarchy

4. **Consistent Naming**: Follows Laravel ecosystem conventions (e.g., `laravel/framework` contains multiple packages)

5. **Reduced Confusion**: Eliminates the overlap between `masyukai/cart` (was the main package) and cart functionality

## Version Constraints

All sub-packages use `@dev` version constraints for local development:
- `"masyukai/cart": "@dev"`
- `"masyukai/docs": "@dev"`
- `"masyukai/stock": "@dev"`
- `"masyukai/cart-vouchers": "@dev"`
- `"masyukai/commerce": "@dev"`

Main application and filament-cart package have `"minimum-stability": "dev"` with `"prefer-stable": true` to allow dev packages while preferring stable versions when available.

## Testing Results

✅ All tests passing after renaming:
- **Commerce Package**: 619 passed, 17 skipped (1753 assertions)
- **Filament Cart Package**: 54 passed (176 assertions)
- **Code Formatting**: All files formatted with Pint

## Future Considerations

When preparing for release:
1. Tag versions for all packages (e.g., `v1.0.0`)
2. Update version constraints from `@dev` to `^1.0`
3. Consider publishing packages to Packagist if they should be publicly available
4. Update `minimum-stability` back to `stable` if desired
