# Vouchers Package Renaming & Decoupling

## Overview
This document details the renaming of the `masyukai/cart-vouchers` package to `masyukai/vouchers` and its decoupling from the cart package to become an independent Laravel voucher/coupon system.

## Changes Made

### Package Renaming
- **Old name**: `masyukai/cart-vouchers`
- **New name**: `masyukai/vouchers`
- **Rationale**: The vouchers package is now a standalone voucher/coupon system that can be used independently of the cart package, making it more flexible and reusable across different contexts.

### Namespace Changes
All PHP namespaces were updated from `MasyukAI\Cart\Vouchers` to `MasyukAI\Vouchers`:

```php
// Before
namespace MasyukAI\Cart\Vouchers\Services;
use MasyukAI\Cart\Vouchers\Models\Voucher;

// After
namespace MasyukAI\Vouchers\Services;
use MasyukAI\Vouchers\Models\Voucher;
```

### Dependency Changes

#### Vouchers Package (packages/commerce/packages/vouchers/composer.json)
**Before**:
```json
{
  "name": "masyukai/cart-vouchers",
  "require": {
    "masyukai/cart": "@dev"
  }
}
```

**After**:
```json
{
  "name": "masyukai/vouchers",
  "require": {
    "php": "^8.2",
    "illuminate/support": "^12.0",
    "illuminate/database": "^12.0"
  },
  "suggest": {
    "masyukai/cart": "Required for cart integration features (VoucherCondition, CartWithVouchers)"
  }
}
```

The cart dependency has been removed from `require` and moved to `suggest`, making it optional. The package can now function independently as a voucher management system.

#### Commerce Package
Updated to require `masyukai/vouchers` instead of `masyukai/cart-vouchers`:

```json
{
  "require": {
    "masyukai/vouchers": "@dev"
  },
  "autoload": {
    "psr-4": {
      "MasyukAI\\Vouchers\\": "packages/vouchers/src"
    }
  }
}
```

## Package Structure After Decoupling

```
masyukai/vouchers (independent package)
├── Core Features (no cart dependency)
│   ├── Voucher management (create, read, update, delete)
│   ├── Voucher validation
│   ├── Usage tracking
│   ├── Events (VoucherApplied, VoucherRemoved)
│   └── Models (Voucher, VoucherUsage)
│
└── Optional Cart Integration (requires masyukai/cart)
    ├── VoucherCondition (extends CartCondition)
    └── CartWithVouchers (extends Cart)
```

## Benefits of Decoupling

1. **Independence**: The vouchers package can now be used in any Laravel application without requiring the cart package
2. **Flexibility**: Applications can use vouchers for purposes beyond shopping carts (e.g., promotional codes, service discounts)
3. **Optional Integration**: Cart integration is still available but is now opt-in rather than mandatory
4. **Better Separation of Concerns**: Each package has a clear, focused responsibility
5. **Reusability**: The vouchers package can be used across different e-commerce implementations

## Migration Guide

### For Existing Applications

If you're already using `masyukai/cart-vouchers`, follow these steps:

1. **Update Composer Dependencies**:
   ```bash
   # In your composer.json, replace:
   "masyukai/cart-vouchers": "@dev"
   # With:
   "masyukai/vouchers": "@dev"
   ```

2. **Update Namespace Imports**:
   ```bash
   # Find and replace in your codebase:
   MasyukAI\Cart\Vouchers → MasyukAI\Vouchers
   ```

3. **Run Composer Update**:
   ```bash
   composer update masyukai/vouchers
   ```

4. **Verify Service Provider Registration**:
   The service provider class has changed from:
   ```php
   MasyukAI\Cart\Vouchers\VoucherServiceProvider
   ```
   To:
   ```php
   MasyukAI\Vouchers\VoucherServiceProvider
   ```
   
   Laravel's package auto-discovery will handle this automatically, but if you manually register providers, update your `config/app.php` or `bootstrap/providers.php`.

5. **Update Facade Aliases** (if manually configured):
   ```php
   // Before
   'Voucher' => MasyukAI\Cart\Vouchers\Facades\Voucher::class,
   
   // After
   'Voucher' => MasyukAI\Vouchers\Facades\Voucher::class,
   ```

### For New Applications

Simply require the package:

```bash
composer require masyukai/vouchers
```

If you need cart integration:
```bash
composer require masyukai/cart masyukai/vouchers
```

## Updated Package Relationships

```
masyukai/commerce (main aggregator)
├── requires masyukai/cart (core cart functionality)
├── requires masyukai/vouchers (voucher system)
├── requires masyukai/docs (document generation)
└── requires masyukai/stock (stock management)

masyukai/vouchers (independent)
├── No hard dependencies on other masyukai packages
└── Suggests masyukai/cart for optional integration features

masyukai/cart (core cart)
├── No dependency on vouchers
└── Vouchers can optionally extend cart through VoucherCondition
```

## Technical Details

### Files Modified
- `packages/commerce/packages/vouchers/composer.json` - Package name, namespace, and dependencies
- `packages/commerce/packages/vouchers/src/**/*.php` - All PHP namespace declarations
- `packages/commerce/composer.json` - Updated package requirement and autoload
- `packages/filament-cart/composer.json` - Transitive dependency (via commerce)
- `packages/commerce/tests/src/TestCase.php` - Service provider class name
- `packages/commerce/tests/src/Vouchers/**/*.php` - Test namespace imports

### Commands Executed
```bash
# Update namespaces in source files
cd packages/commerce/packages/vouchers
find src -name "*.php" -type f -exec sed -i '' 's/namespace MasyukAI\\Cart\\Vouchers/namespace MasyukAI\\Vouchers/g' {} \;
find src -name "*.php" -type f -exec sed -i '' 's/use MasyukAI\\Cart\\Vouchers/use MasyukAI\\Vouchers/g' {} \;

# Update namespaces in test files
cd packages/commerce
find tests -name "*.php" -type f -exec sed -i '' 's/MasyukAI\\Cart\\Vouchers/MasyukAI\\Vouchers/g' {} \;

# Update composer dependencies
cd packages/commerce/packages/vouchers && composer update
cd packages/commerce && composer update
cd packages/filament-cart && composer update
cd /Users/Saiffil/Herd/kakkay && composer update

# Format code
vendor/bin/pint --dirty
```

### Test Results
- **Commerce Package**: 619 passed, 17 skipped (1753 assertions) ✅
- **Filament Cart Package**: 54 passed (176 assertions) ✅
- **Total**: 673 tests passing

## API Stability

All public APIs remain unchanged:

```php
// Voucher management - works the same
Voucher::create([...]);
Voucher::find('CODE123');
Voucher::validate('CODE123', $cart);

// Cart integration - works the same (when masyukai/cart is installed)
$cart->addCondition(new VoucherCondition($voucher));
```

## Future Considerations

The vouchers package can now be extended for:
- Multi-tenant voucher systems
- Subscription-based vouchers
- Geographic or time-based restrictions
- Integration with external coupon providers
- Loyalty program integration
- Gift card management

All without requiring cart-specific functionality.

## Related Documentation
- [Monorepo Structure](MONOREPO_RESTRUCTURING.md)
- [Package Naming](PACKAGE_RENAMING.md)
- [Cart Integration](packages/commerce/packages/vouchers/docs/CART_INTEGRATION_SUMMARY.md)
