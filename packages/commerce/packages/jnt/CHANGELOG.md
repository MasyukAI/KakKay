# Changelog

All notable changes to `aiarmada/jnt` will be documented in this file.

## [0.1.0] - 2025-11-01

### Added
- Initial release
- Complete J&T Express Malaysia API integration
- Order creation and tracking
- Price calculation by weight and destination
- Service type support (standard, express)
- Address validation
- Shipping label generation
- Order cancellation
- Pickup request management
- Webhook support for tracking updates
- Rate limiting with token bucket algorithm
- Comprehensive test suite with Pest v4
- Health check command
- Request/response logging
- Cache support for rates and service areas

### Dependencies
- PHP ^8.4
- Laravel ^12.0
- guzzlehttp/guzzle ^7.9
