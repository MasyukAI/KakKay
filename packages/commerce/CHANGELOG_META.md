# Changelog

All notable changes to the AIArmada Commerce meta-package will be documented in this file.

The meta-package combines all individual packages into a cohesive ecosystem. For package-specific changes, see:
- [Cart](packages/cart/CHANGELOG.md)
- [CHIP](packages/chip/CHANGELOG.md)
- [Vouchers](packages/vouchers/CHANGELOG.md)
- [J&T Express](packages/jnt/CHANGELOG.md)
- [Stock](packages/stock/CHANGELOG.md)
- [Docs](packages/docs/CHANGELOG.md)
- [Filament Cart](packages/filament-cart/CHANGELOG.md)
- [Filament CHIP](packages/filament-chip/CHANGELOG.md)
- [Filament Vouchers](packages/filament-vouchers/CHANGELOG.md)
- [Support](packages/support/CHANGELOG.md)

---

## [0.1.0] - 2025-11-01

### Added
- **Initial Release** – First public release of AIArmada Commerce ecosystem
- **Meta-package** – Single `composer require aiarmada/commerce` installs entire ecosystem
- **10 Packages** – Complete commerce solution with modular architecture:
  - **Core Packages:**
    - `aiarmada/cart` – Multi-instance shopping cart with dynamic pricing
    - `aiarmada/chip` – CHIP payment gateway integration (Collect & Send)
    - `aiarmada/vouchers` – Flexible voucher and coupon system
    - `aiarmada/jnt` – J&T Express Malaysia shipping integration
    - `aiarmada/stock` – Comprehensive inventory management
    - `aiarmada/docs` – Document generation with PDF support
  - **Filament Admin Plugins:**
    - `aiarmada/filament-cart` – Filament admin for cart management
    - `aiarmada/filament-chip` – Filament admin for CHIP payments
    - `aiarmada/filament-vouchers` – Filament admin for vouchers
  - **Foundation:**
    - `aiarmada/commerce-support` – Shared utilities, exceptions, HTTP clients

### Features Included

#### Shopping Cart (`aiarmada/cart`)
- Multi-instance support (default, wishlist, quote, layaway)
- Dynamic pricing with stackable conditions
- Three storage drivers (session, cache, database)
- Optimistic locking for concurrency safety
- Event-driven architecture
- Guest-to-user cart migration
- Money/currency precision via akaunting/laravel-money
- Comprehensive Pest v4 test suite

#### Payment Processing (`aiarmada/chip`)
- CHIP Collect API integration (purchases, refunds, webhooks)
- CHIP Send API integration (payouts, bank accounts)
- Fluent purchase builder
- Webhook signature verification
- Health check command
- Automatic retry logic with exponential backoff
- Request/response logging with masking
- Cache support for rates and keys

#### Vouchers (`aiarmada/vouchers`)
- Multiple discount types (fixed, percentage, free shipping)
- Usage limits (total and per-user)
- Expiry date management
- Manual and automatic redemption
- Multi-staff/multi-store support
- Cart integration
- Usage analytics
- Event system

#### Shipping (`aiarmada/jnt`)
- J&T Express Malaysia API integration
- Order creation and tracking
- Price calculation by weight/destination
- Service type support (standard, express)
- Address validation
- Shipping label generation
- Webhook support
- Rate limiting

#### Inventory (`aiarmada/stock`)
- UUID-based stock tracking
- Movement recording (in, out, adjustment, reservation)
- Reservation system with automatic expiry
- Multi-location support
- Batch and serial number tracking
- Low stock alerts
- Stock history and audit trail
- Concurrent operation locking

#### Documents (`aiarmada/docs`)
- Invoice generation with templates
- Receipt generation
- Shipping label generation
- PDF rendering via DomPDF
- Customizable Blade templates
- Multi-currency support
- Logo and branding customization
- Template caching

#### Filament Admin Plugins
- **Cart Admin:**
  - Cart, CartItem, CartCondition resources
  - Dashboard widget with statistics
  - Real-time synchronization
  - Bulk operations
- **CHIP Admin:**
  - Purchase, Payment, Client, Webhook resources
  - Send instruction and bank account management
  - Dashboard widget with payment metrics
  - Bulk refund/cancel operations
- **Vouchers Admin:**
  - Voucher and usage resources
  - Manual redemption workflow
  - Dashboard widget with analytics
  - Multi-staff/store support
  - Cart integration (when filament-cart installed)

#### Foundation Utilities (`aiarmada/commerce-support`)
- Exception hierarchy (CommerceException, CommerceApiException, etc.)
- BaseApiClient with Laravel HTTP client
- MoneyHelper with 17 utility methods
- Enum enhancement concerns (HasLabels, HasColors, HasIcons, HasDescriptions)
- Service provider helpers (RegistersSingletonAliases, ValidatesConfiguration)
- Configuration validation utilities

### Technical Stack
- PHP ^8.4
- Laravel ^12.0
- Filament ^4.0
- Livewire ^3.0
- Pest ^4.0 (testing)
- PHPStan level 6 (static analysis)
- Laravel Pint (code formatting)
- Rector (automated refactoring)

### Quality Assurance
- Comprehensive Pest v4 test suites across all packages
- PHPStan level 6 static analysis
- Laravel Pint for consistent code style
- GitHub Actions workflows for CI/CD
- Test coverage tracking
- Monorepo architecture with automated splitting

### Documentation
- Comprehensive README for each package
- CHANGELOG for version tracking
- Code examples and usage patterns
- Configuration guides
- Troubleshooting sections
- API reference documentation
- Migration guides

### Dependencies
- **Production:**
  - akaunting/laravel-money ^6.0
  - guzzlehttp/guzzle ^7.9
  - spatie/laravel-package-tools ^1.92
  - ramsey/uuid ^4.7
  - barryvdh/laravel-dompdf ^3.0
- **Development:**
  - orchestra/testbench ^10.0
  - pestphp/pest ^4.0
  - larastan/larastan ^3.0
  - rector/rector ^2.2
  - symplify/monorepo-builder ^11.0

### Monorepo Features
- Unified version management across all packages
- Automated package splitting to individual repositories
- Shared test suite and quality tools
- Consistent coding standards
- Centralized documentation
- Single issue tracker
- Coordinated releases

---

## Upgrade Guide

### From Individual Packages

If you're currently using individual packages, you can switch to the meta-package:

```bash
# Remove individual packages
composer remove aiarmada/cart aiarmada/chip aiarmada/vouchers ...

# Install meta-package
composer require aiarmada/commerce
```

This won't break your existing code—all package namespaces and APIs remain unchanged.

### Staying with Individual Packages

You can continue using individual packages if you prefer granular control:

```bash
# Update each package separately
composer update aiarmada/cart
composer update aiarmada/chip
# etc.
```

---

## Versioning

AIArmada Commerce follows [Semantic Versioning](https://semver.org/):

- **MAJOR** version for incompatible API changes
- **MINOR** version for backward-compatible functionality
- **PATCH** version for backward-compatible bug fixes

All packages in the monorepo share the same version number for consistency.

---

## Support

- **Documentation:** [https://github.com/aiarmada/commerce](https://github.com/aiarmada/commerce)
- **Issues:** [https://github.com/aiarmada/commerce/issues](https://github.com/aiarmada/commerce/issues)
- **Security:** security@aiarmada.com
- **Email:** info@aiarmada.com
