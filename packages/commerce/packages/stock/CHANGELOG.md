# Changelog

All notable changes to `aiarmada/stock` will be documented in this file.

## [0.1.0] - 2025-11-01

### Added
- Initial release
- Comprehensive stock and inventory management system
- UUID-based stock tracking
- Stock movement recording (in, out, adjustment, reservation, release)
- Reservation system with automatic expiry
- Multi-location inventory support
- Batch and serial number tracking
- Low stock alerts and notifications
- Stock history and audit trail
- Event system for stock operations
- Comprehensive test suite with Pest v4
- Database migrations with proper indexes
- Stock locking for concurrent operations

### Dependencies
- PHP ^8.4
- Laravel ^12.0
- ramsey/uuid ^4.7
