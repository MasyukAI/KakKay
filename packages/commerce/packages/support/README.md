# AIArmada Commerce Support

Core helper methods and foundation code for all AIArmada Commerce packages.

## Purpose

This package provides shared utilities, traits, and base classes used across all AIArmada Commerce packages.

## What's Included

### Service Provider Helpers

**`RegistersSingletonAliases` Trait**
- Simplifies registering services as singletons with optional aliases
- Reduces boilerplate in package service providers

```php
use AIArmada\CommerceSupport\Concerns\RegistersSingletonAliases;

class MyServiceProvider extends PackageServiceProvider
{
    use RegistersSingletonAliases;
    
    public function packageRegistered(): void
    {
        $this->registerSingletonAlias(
            CartService::class,
            'cart'
        );
    }
}
```

**`ValidatesConfiguration` Trait**
- Runtime configuration validation helpers
- Used by integration packages (chip, jnt)

```php
use AIArmada\CommerceSupport\Concerns\ValidatesConfiguration;

class MyServiceProvider extends PackageServiceProvider
{
    use ValidatesConfiguration;
    
    public function boot(): void
    {
        $this->requireConfigKeys('chip', ['api_key', 'brand_id']);
        $this->validateConfigEnum('chip.environment', ['sandbox', 'production']);
    }
}
```

### HTTP Client Base

**`BaseHttpClient` Abstract Class**
- Shared HTTP client with retry logic and logging
- Used by chip and jnt packages
- Configurable retry attempts and delays
- Automatic logging of requests/responses

```php
use AIArmada\CommerceSupport\Http\BaseHttpClient;

class MyApiClient extends BaseHttpClient
{
    protected function shouldLog(): bool
    {
        return config('mypackage.logging.enabled', false);
    }
}
```

### Helper Functions

**`commerce_config()`**
- Convenient config accessor
- Consistent interface across packages

```php
$storageDriver = commerce_config('cart.storage', 'session');
```

## Installation

This package is automatically required by all AIArmada Commerce packages. You don't need to install it directly.

## Package Structure

```
packages/support/
├── composer.json
├── README.md
├── src/
│   ├── SupportServiceProvider.php
│   ├── helpers.php
│   ├── Concerns/
│   │   ├── RegistersSingletonAliases.php
│   │   └── ValidatesConfiguration.php
│   ├── Http/
│   │   └── BaseHttpClient.php
│   ├── Contracts/
│   │   └── (shared interfaces)
│   └── Testing/
│       └── (test utilities)
└── tests/
    └── (unit tests)
```

## Design Philosophy

1. **Foundation Only** - No business logic, only shared utilities
2. **Minimal Dependencies** - Only Laravel contracts, support, and package tools
3. **Zero UI Dependencies** - Can be used independently
4. **Clean Separation** - Each feature in its own namespace
5. **Well Documented** - Clear examples and use cases

## Dependencies

- PHP ^8.2
- Laravel ^12.0 (illuminate/contracts, illuminate/support)
- spatie/laravel-package-tools ^1.92

### Suggested Dependencies

- guzzlehttp/guzzle (for HTTP client utilities)
- akaunting/laravel-money (for money utilities)

## Usage in Packages

All AIArmada Commerce packages depend on this support package:

```json
{
    "require": {
        "aiarmada/commerce-support": "self.version"
    }
}
```

## Contributing

This package is part of the AIArmada Commerce monorepo. When adding utilities:

1. ✅ **DO** - Add truly shared code used by multiple packages
2. ✅ **DO** - Keep dependencies minimal
3. ✅ **DO** - Document with examples
4. ❌ **DON'T** - Add business logic (belongs in domain packages)
5. ❌ **DON'T** - Add UI components (belongs in filament-* packages)
6. ❌ **DON'T** - Add heavy dependencies

## License

MIT
