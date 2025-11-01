# Changelog

All notable changes to `aiarmada/filament-chip` will be documented in this file.

## [0.1.0] - 2025-11-01

### Added
- Initial release
- Filament v4 admin plugin for CHIP payment data
- Purchase resource with table and detailed view
- Payment resource for transaction records
- Client resource for customer management
- Webhook resource for event monitoring
- Company statement resource for financial reports
- Send instruction resource for payouts
- Bank account resource for payout destinations
- Dashboard widget with payment statistics
- Bulk actions (refund, cancel, resend invoice)
- Status filtering and search capabilities
- Date range and amount filtering
- Read-only resources for data exploration
- Comprehensive test suite with Pest v4
- Automatic model synchronization

### Dependencies
- PHP ^8.4
- Laravel ^12.0
- Filament ^4.0
- aiarmada/chip ^0.1
