# Changelog

All notable changes to `aiarmada/cart` will be documented in this file.

## [0.1.0] - 2025-11-01

### Added
- Initial release
- Flexible shopping cart system with multiple storage drivers (session, cache, database)
- Support for cart instances (default, wishlist, quote)
- Cart items with quantity, price, and custom attributes
- Cart conditions (discounts, fees, taxes) with target-based application
- Dynamic conditions with automatic rule evaluation
- Money integration via akaunting/laravel-money
- Event system for cart operations
- Comprehensive test suite with Pest v4
- Database storage with optimistic locking for concurrency
- Cart metadata support
- Bulk operations (add multiple items, apply multiple conditions)

### Dependencies
- PHP ^8.4
- Laravel ^12.0
- akaunting/laravel-money ^6.0
