# Changelog

All notable changes to `aiarmada/commerce-support` will be documented in this file.

## [0.1.0] - 2025-11-01

### Added
- Initial release
- Foundation package for all AIArmada Commerce packages
- `RegistersSingletonAliases` trait for service provider helpers
- `ValidatesConfiguration` trait for runtime config validation
- Exception hierarchy:
  - `CommerceException` base exception with error context
  - `CommerceApiException` for API integration errors
  - `CommerceValidationException` for validation failures
  - `CommerceConfigurationException` for config errors
- `BaseApiClient` abstract HTTP client with:
  - Laravel HTTP client integration
  - Automatic retry logic with exponential backoff
  - Request/response logging with sensitive data masking
  - Authentication hooks
  - Timeout configuration
- `MoneyHelper` utility with 17 static methods:
  - `make()`, `sanitizePrice()`, `formatForDisplay()`
  - `fromCents()`, `toCents()`, `parseAmount()`
  - `zero()`, `equals()`, `sum()`, `percentage()`
  - Currency operations and validation
- Enum enhancement concerns:
  - `HasLabels` for human-readable labels
  - `HasColors` for Filament badge colors
  - `HasIcons` for Heroicon integration
  - `HasDescriptions` for tooltips and help text
- `commerce_config()` helper function
- Comprehensive test suite with Pest v4

### Dependencies
- PHP ^8.4
- Laravel ^12.0
- spatie/laravel-package-tools ^1.92
- akaunting/laravel-money ^6.0 (suggested)
- guzzlehttp/guzzle ^7.9 (suggested)
