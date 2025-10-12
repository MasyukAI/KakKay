# AIArmada Commerce Support Package Design

> **Inspired by Filament's Approach**  
> This document analyzes Filament's package structure to guide our own support package design.

## Filament's Package Structure Analysis

### 1. The Support Package Foundation

**filament/support** is the foundational package that:
- Contains **core helper methods and foundation code** for ALL other packages
- Has **no Filament package dependencies** (only external ones)
- Requires only: `spatie/laravel-package-tools`, Livewire, Laravel contracts, and utility libraries
- Provides a **helpers.php** file loaded globally
- Contains the base `SupportServiceProvider`

**Key Dependencies:**
```json
{
  "blade-ui-kit/blade-heroicons": "^2.5",
  "danharrin/livewire-rate-limiting": "^2.0",
  "illuminate/contracts": "^11.28|^12.0",
  "kirschbaum-development/eloquent-power-joins": "^4.0",
  "livewire/livewire": "^3.5",
  "spatie/laravel-package-tools": "^1.9"
}
```

### 2. Package Dependency Pattern

Every Filament package follows this pattern:
```
filament/support (foundation)
    ↓
filament/actions (depends on support)
    ↓
filament/forms (depends on support + actions)
    ↓
filament/tables (depends on support + actions + forms)
    ↓
filament/filament (umbrella - depends on ALL)
```

**Key Observations:**
1. **Support is ALWAYS required** - every package depends on `filament/support`
2. **Self-versioning** - packages use `"self.version"` for internal dependencies
3. **Minimal external dependencies** - each package only adds what it specifically needs
4. **No dev dependencies in composer.json** - testing is handled at monorepo level
5. **Clean separation** - each package has ONE ServiceProvider

### 3. The Umbrella Package Pattern

**filament/filament** is the umbrella package that:
- Requires ALL sub-packages
- Adds specific features (2FA, QR codes) needed only at the full installation level
- Provides global helpers and the main `FilamentServiceProvider`
- Users install this to get the full suite

---

## Our Current Structure vs Filament's Pattern

### What We Have Now

```
aiarmada/commerce (umbrella - similar to filament/filament)
  ├── aiarmada/cart (core domain)
  ├── aiarmada/chip (integration)
  ├── aiarmada/docs (utility)
  ├── aiarmada/filament-cart (UI)
  ├── aiarmada/filament-chip (UI)
  ├── aiarmada/jnt (integration)
  ├── aiarmada/stock (core domain)
  └── aiarmada/vouchers (core domain)
```

**Problems:**
1. ❌ No support package - every package repeats boilerplate
2. ❌ Inconsistent dependencies - some use PackageTools, some don't
3. ❌ Repeated patterns - HTTP clients, config validation, service registration
4. ❌ No shared helpers or base classes

### What We Should Have (Filament-Inspired)

```
aiarmada/commerce-support (NEW - like filament/support)
  ├── Base service provider helpers
  ├── HTTP client utilities (for chip/jnt)
  ├── Config validation helpers
  ├── Shared traits and contracts
  ├── Testing utilities
  └── Helper functions
    ↓
aiarmada/cart (depends on support)
aiarmada/stock (depends on support)
aiarmada/vouchers (depends on support)
aiarmada/docs (depends on support)
    ↓
aiarmada/chip (depends on support)
aiarmada/jnt (depends on support)
    ↓
aiarmada/filament-cart (depends on support + cart)
aiarmada/filament-chip (depends on support + chip)
    ↓
aiarmada/commerce (umbrella - depends on ALL)
```

---

## Support Package Scope (Following Filament's Lead)

### ✅ What Should Be in Support

Based on Filament's approach, our support package should contain:

#### 1. **Service Provider Helpers**
```php
// Like Filament's base provider patterns
trait RegistersSingletonAliases {
    protected function registerSingletonAlias(
        string $abstract, 
        ?string $alias = null, 
        ?Closure $factory = null
    ): void;
}
```

#### 2. **Config Utilities**
```php
// Runtime config validation (JNT/CHIP patterns)
class ConfigValidator {
    public static function requireKeys(string $prefix, array $keys): void;
    public static function validateEnvironment(string $key, array $allowed): void;
}
```

#### 3. **HTTP Client Base**
```php
// Shared by chip/jnt packages
abstract class HttpClient {
    protected function retry(array $config): void;
    protected function log(string $message, array $context): void;
}
```

#### 4. **Shared Contracts/Interfaces**
```php
// Domain interfaces used across packages
interface HasMoney;
interface Conditionable;
interface Synchronizable;
```

#### 5. **Helper Functions**
```php
// Global helpers (like Filament's helpers.php)
function commerce_config(string $key, mixed $default = null);
function format_money(int $cents, string $currency = 'MYR');
```

#### 6. **Testing Support**
```php
// Base test cases for all packages
abstract class CommerceTestCase extends TestCase;
class HttpClientFake;
```

### ❌ What Should NOT Be in Support

Following Filament's minimalist approach:

1. **No Business Logic** - Keep it in domain packages (cart, stock, vouchers)
2. **No UI Components** - Keep in filament-* packages
3. **No Integration Logic** - Keep in chip/jnt packages
4. **No Models/Migrations** - Each package manages its own
5. **No Heavy Dependencies** - Only foundational libraries

---

## Implementation Plan

### Phase 1: Create Support Package Structure

```bash
packages/commerce/packages/support/
├── composer.json
├── src/
│   ├── SupportServiceProvider.php
│   ├── helpers.php
│   ├── Concerns/
│   │   ├── RegistersSingletonAliases.php
│   │   └── ValidatesConfiguration.php
│   ├── Http/
│   │   ├── HttpClient.php
│   │   └── RetryConfig.php
│   ├── Testing/
│   │   ├── CommerceTestCase.php
│   │   └── helpers.php
│   └── Contracts/
│       └── (shared interfaces)
└── tests/
```

### Phase 2: Support Package composer.json

```json
{
    "name": "aiarmada/commerce-support",
    "description": "Core helper methods and foundation code for all AIArmada Commerce packages.",
    "license": "MIT",
    "require": {
        "php": "^8.2",
        "illuminate/contracts": "^12.0",
        "illuminate/support": "^12.0",
        "spatie/laravel-package-tools": "^1.92"
    },
    "suggest": {
        "guzzlehttp/guzzle": "Required for HTTP client utilities (chip/jnt packages)",
        "akaunting/laravel-money": "Required for money utilities (cart/vouchers packages)"
    },
    "autoload": {
        "files": [
            "src/helpers.php"
        ],
        "psr-4": {
            "AIArmada\\CommerceSupport\\": "src"
        }
    },
    "extra": {
        "laravel": {
            "providers": [
                "AIArmada\\CommerceSupport\\SupportServiceProvider"
            ]
        }
    }
}
```

### Phase 3: Update Existing Packages

1. **Add support dependency** to each package:
   ```json
   {
     "require": {
       "aiarmada/commerce-support": "self.version"
     }
   }
   ```

2. **Migrate providers** to use support traits:
   ```php
   use AIArmada\CommerceSupport\Concerns\RegistersSingletonAliases;
   
   class CartServiceProvider extends PackageServiceProvider
   {
       use RegistersSingletonAliases;
   }
   ```

3. **Extract shared code** incrementally

### Phase 4: Update Umbrella Package

```json
{
    "name": "aiarmada/commerce",
    "require": {
        "aiarmada/commerce-support": "self.version",
        "aiarmada/cart": "self.version",
        "aiarmada/chip": "self.version",
        ...
    }
}
```

---

## Key Principles (From Filament)

1. **Support is foundation-only** - no business logic
2. **Every package depends on support** - consistent base
3. **Use self.version** - keeps monorepo versioning clean
4. **Minimal dependencies** - only what's truly shared
5. **Clean separation** - one ServiceProvider per package
6. **No dev dependencies in packages** - handle at monorepo level
7. **Helpers file** - for globally useful functions
8. **Testing support** - shared test utilities

---

## Benefits

✅ **Consistency** - All packages share the same foundation  
✅ **DRY** - No repeated boilerplate code  
✅ **Maintainability** - Update once, fix everywhere  
✅ **Testability** - Shared testing utilities  
✅ **Onboarding** - New packages follow established patterns  
✅ **Aligned with Filament** - Following proven patterns from the ecosystem

---

## Next Steps

1. ✅ Analyze Filament's structure (DONE)
2. ⏳ Create `aiarmada/commerce-support` package skeleton
3. ⏳ Extract common patterns into support
4. ⏳ Update existing packages to use support
5. ⏳ Update umbrella package dependencies
6. ⏳ Test and validate

---

**Decision**: Keep it minimal like Filament. Only extract what's truly shared and foundational.
