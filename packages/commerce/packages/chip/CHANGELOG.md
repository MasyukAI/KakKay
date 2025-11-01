# Changelog

All notable changes to `aiarmada/chip` will be documented in this file.

## [0.1.0] - 2025-11-01

### Added
- Initial release
- Complete CHIP Collect API integration (purchases, refunds, webhooks, clients)
- CHIP Send API integration (payouts, bank accounts, send instructions)
- Fluent purchase builder with method chaining
- Laravel facades for Chip and ChipSend
- Webhook signature verification with RSA public keys
- Data objects for type-safe API responses
- Event system for purchase lifecycle
- Health check command for connectivity testing
- Comprehensive test suite with Pest v4
- Automatic retry logic with exponential backoff
- Request/response logging with sensitive data masking
- Cache support for public keys and payment methods

### Dependencies
- PHP ^8.4
- Laravel ^12.0
- guzzlehttp/guzzle ^7.9
