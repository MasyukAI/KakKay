# Changelog

All notable changes to the Commerce monorepo will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.0.0] - 2025-11-06

### Initial Release

#### Cart Package
- Multi-instance cart support (cart, wishlist, quote, etc.)
- Session, cache, and database storage drivers
- Optimistic locking for concurrency safety
- Dynamic pricing conditions (discounts, taxes, fees)
- Currency handling via Akaunting Money
- Comprehensive event system
- Identifier migration (guest → authenticated user)
- Laravel Octane compatibility

#### Stock Package
- Inventory tracking trait
- Stock validation
- Low stock alerts
- Stock reservation system

#### Vouchers Package
- Discount voucher management
- Usage tracking and limits
- Expiration handling
- Integration with cart conditions

#### CHIP Package
- CHIP payment gateway integration
- Purchase creation and management
- Webhook handling with signature verification
- Client management
- Payment method retrieval
- Recurring tokens support

#### J&T Express Package
- J&T Express shipping integration
- Order creation and tracking
- Webhook handling for tracking updates
- Delivery notifications

#### Filament Packages
- Admin panels for cart management (filament-cart)
- Admin panels for CHIP payment management (filament-chip)
- Resource management with Filament v4

## Package Versions

Individual package versions are tracked in their respective CHANGELOG files:
- [packages/cart/CHANGELOG.md](packages/cart/CHANGELOG.md)
- [packages/chip/CHANGELOG.md](packages/chip/CHANGELOG.md)
- [packages/jnt/CHANGELOG.md](packages/jnt/CHANGELOG.md)
- [packages/stock/CHANGELOG.md](packages/stock/CHANGELOG.md)
- [packages/vouchers/CHANGELOG.md](packages/vouchers/CHANGELOG.md)
- [packages/filament-cart/CHANGELOG.md](packages/filament-cart/CHANGELOG.md)
- [packages/filament-chip/CHANGELOG.md](packages/filament-chip/CHANGELOG.md)
- [packages/docs/CHANGELOG.md](packages/docs/CHANGELOG.md)

[Unreleased]: https://github.com/masyukai/kakkay/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/masyukai/kakkay/releases/tag/v1.0.0
