# Changelog

All notable changes to `aiarmada/vouchers` will be documented in this file.

## [0.1.0] - 2025-11-01

### Added
- Initial release
- Flexible voucher and coupon system with multiple redemption types
- Support for fixed discount, percentage discount, and free shipping vouchers
- Usage limits (per voucher and per user)
- Expiry date management
- Manual and automatic redemption workflows
- Cart integration via aiarmada/cart
- Multi-staff support with ownership resolution
- Multi-store support for marketplace scenarios
- Voucher validation with detailed error messages
- Usage history tracking
- Event system for voucher lifecycle
- Comprehensive test suite with Pest v4
- Database migrations for vouchers and redemptions

### Dependencies
- PHP ^8.4
- Laravel ^12.0
- aiarmada/cart ^0.1
