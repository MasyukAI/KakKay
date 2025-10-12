# Filament Monorepo Deep Analysis

## Executive Summary
This document provides a factual, source-code-based analysis of Filament's monorepo structure compared to Commerce, with actionable refactoring recommendations.

---

## 1. Repository Structure

### Filament Structure (Actual)
```
filament/
├── Root Configuration (Centralized)
│   ├── composer.json          # Main orchestrator
│   ├── phpstan.neon.dist      # Level 6, analyzes all packages/*
│   ├── pint.json              # Basic Laravel preset
│   ├── pint-strict-imports.json # Stricter with fully_qualified_strict_types
│   ├── rector.php             # Covers packages/, tests/, docs-assets/
│   ├── phpunit.xml.dist       # Centralized test config
│   ├── testbench.yaml         # Orchestra Testbench config
│   └── monorepo-builder.php   # Release automation
│
├── packages/
│   ├── Core Packages (Each with composer.json only)
│   │   ├── actions/
│   │   ├── forms/
│   │   ├── infolists/
│   │   ├── notifications/
│   │   ├── panels/
│   │   ├── schemas/
│   │   ├── support/           # Foundation package
│   │   ├── tables/
│   │   └── widgets/
│   │
│   └── Plugin Packages (Each with composer.json only)
│       ├── spark-billing-provider/
│       ├── spatie-laravel-google-fonts-plugin/
│       ├── spatie-laravel-media-library-plugin/
│       ├── spatie-laravel-settings-plugin/
│       ├── spatie-laravel-tags-plugin/
│       └── upgrade/            # CLI tool with bin/
│
├── tests/
│   ├── Pest.php               # Pest config with custom expects
│   ├── helpers.php            # Shared test helpers (livewire())
│   ├── database/              # Shared migrations/factories
│   ├── resources/             # Test resources
│   └── src/                   # Test classes
│
├── .github/workflows/
│   ├── phpstan.yml            # Matrix: PHP 8.2-8.4, Laravel 11-12
│   ├── tests.yml              # Matrix: PHP 8.2-8.4, Laravel 11-12
│   ├── fix-code-style.yml     # Auto-fixes: rector + pint + prettier
│   ├── monorepo-split.yml     # Splits to individual repos on tag
│   ├── release.yml
│   ├── npm-build.yml
│   ├── docs-screenshots.yml
│   ├── translation-update.yml
│   ├── manage-issue.yml
│   └── check-pr-maintainer-access.yml
│
├── docs/                      # Comprehensive documentation
│   ├── 01-introduction/
│   ├── 03-resources/
│   ├── 06-navigation/
│   ├── 07-users/
│   ├── 08-styling/
│   ├── 09-advanced/
│   ├── 10-testing/
│   ├── 11-plugins/
│   └── 12-components/
│
├── docs-assets/               # Documentation app
│   └── app/
│
├── bin/
│   ├── build.js
│   ├── setup-playground.sh
│   └── translation-tool.php
│
└── bootstrap/
    └── app.php                # Laravel app bootstrap
```

### Commerce Structure (Current)
```
commerce/
├── Root Configuration (Centralized)
│   ├── composer.json          # Main orchestrator
│   ├── phpstan.neon           # Level 6, only 4 packages
│   ├── pint.json              # Very strict custom rules
│   ├── rector.php             # Only cart/src and tests
│   └── phpunit.xml
│
├── packages/
│   ├── cart/                  # NO tooling configs
│   │   └── composer.json (minimal)
│   ├── docs/                  # NO tooling configs
│   ├── stock/                 # NO tooling configs
│   ├── vouchers/              # NO tooling configs
│   │
│   └── Packages with INDIVIDUAL configs (anti-pattern)
│       ├── chip/
│       │   ├── composer.json
│       │   ├── phpstan.neon   # Duplicate config
│       │   ├── pint.json      # Duplicate config
│       │   ├── phpunit.xml    # Duplicate config
│       │   ├── rector.php     # Duplicate config
│       │   └── vendor/        # ❌ Should not exist
│       │
│       ├── filament-cart/     # Same duplication
│       ├── filament-chip/     # Same duplication
│       └── jnt/               # Same duplication
│
├── tests/                     # Shared tests
│   ├── Pest.php
│   ├── database/
│   └── src/
│
└── .github/                   # ❌ MISSING - No CI/CD
```

---

## 2. Key Differences Analysis

### A. Tooling Configuration

| Aspect | Filament | Commerce | Impact |
|--------|----------|----------|--------|
| **PHPStan** | Single root `phpstan.neon.dist` analyzing all `packages/*` | Root + 4 individual configs in chip/jnt/filament-* | Inconsistency, maintenance overhead |
| **Pint** | Two root configs: basic + strict-imports | Root + 4 individual configs | Style inconsistency across packages |
| **Rector** | Single root covering all packages | Root (only cart) + 4 individual | Incomplete refactoring coverage |
| **PHPUnit** | Single root config | Root + 4 individual | Test environment inconsistency |
| **Package vendor/** | Never exists | Exists in 4 packages | Bloat, confusion, split issues |

**Verdict**: Filament uses **centralized tooling** for all packages. Commerce has **mixed approach** causing:
- Configuration drift
- Maintenance burden (5 places to update PHPStan rules)
- Inconsistent code quality across packages

---

### B. Package Structure

#### Filament Packages (Actual Content)
```
packages/forms/
├── composer.json              # ✅ ONLY this
├── src/                       # Source code
├── docs/                      # Package-specific docs
├── resources/                 # Views, translations
├── stubs/                     # Stubs for users
├── dist/                      # Built assets
└── .stubs.php                 # IDE helper

# NO pint.json, phpstan.neon, rector.php, phpunit.xml, vendor/
```

#### Commerce Packages (Current)
```
packages/chip/
├── composer.json              # ✅ Good
├── phpstan.neon               # ❌ Should be in root
├── pint.json                  # ❌ Should be in root
├── rector.php                 # ❌ Should be in root
├── phpunit.xml                # ❌ Should be in root
├── vendor/                    # ❌ Should NEVER exist
├── composer.lock              # ❌ Should NEVER exist
├── src/
├── config/
├── database/
└── docs/
```

**Verdict**: Filament packages are **clean and minimal**. Commerce packages have **tooling pollution** and vendor directories.

---

### C. Autoloading Strategy

#### Filament (Root composer.json)
```json
"autoload": {
    "files": [
        "packages/panels/src/global_helpers.php",
        "packages/panels/src/helpers.php",
        "packages/support/src/helpers.php",
        "tests/helpers.php"
    ],
    "psr-4": {
        "Filament\\": [
            "packages/panels/src",
            "packages/spatie-laravel-media-library-plugin/src",
            ...
        ],
        "Filament\\Actions\\": "packages/actions/src",
        "Filament\\Forms\\": "packages/forms/src",
        ...
    }
}
```

**Key Points**:
- Root composer.json knows ALL package namespaces
- Test helpers loaded globally
- No need for individual package autoload in dev

#### Commerce (Root composer.json)
```json
"require": {
    "aiarmada/cart": "@dev",
    "aiarmada/chip": "@dev",
    ...
},
"repositories": [
    {"type": "path", "url": "./packages/cart"},
    {"type": "path", "url": "./packages/chip"},
    ...
]
```

**Key Points**:
- Uses Composer path repositories
- Each package loaded as dependency
- Requires individual composer.json with autoload
- More overhead but cleaner for splitting

**Verdict**: Both approaches valid. Filament = simpler dev. Commerce = easier to split/publish.

---

### D. Testing Infrastructure

#### Filament
```php
// tests/Pest.php
expect()->extend('toBeSameModel', function (Model $model) {
    return $this->is($model)->toBeTrue();
});

// tests/helpers.php
function livewire(string | Component $component, array $props = []): Testable
{
    return Livewire::test($component, $props);
}

// Root composer.json autoloads
"files": ["tests/helpers.php"]
```

**Features**:
- Shared Pest extensions
- Shared helper functions globally available
- Single `testbench.yaml` for all packages
- Matrix testing: PHP 8.2-8.4 × Laravel 11-12

#### Commerce
```php
// tests/Pest.php
uses(TestCase::class)->in('Feature', 'Unit');

// tests/src/TestCase.php
abstract class TestCase extends BaseTestCase
{
    // Shared setup
}
```

**Features**:
- Basic Pest setup
- TestCase base class
- No shared helpers file
- No CI/CD testing

**Verdict**: Filament has **richer testing infrastructure**. Commerce missing shared helpers and CI.

---

### E. CI/CD Automation

#### Filament GitHub Actions

**1. Code Quality (Automatic)**
```yaml
fix-code-style.yml
├── On: Every push
├── Runs: Rector → Pint → Prettier
└── Auto-commits fixes
```

**2. Testing Matrix**
```yaml
tests.yml / phpstan.yml
├── Matrix:
│   ├── PHP: 8.4, 8.3, 8.2
│   ├── Laravel: 12.*, 11.*
│   └── Testbench: 10.*, 9.*
├── Runs: pest / phpstan
└── Parallel execution
```

**3. Release Automation**
```yaml
monorepo-split.yml
├── Trigger: On tag push
├── Strategy: Matrix of 15 packages
├── Action: danharrin/monorepo-split-github-action
└── Splits to individual repos
```

#### Commerce GitHub Actions
```
❌ NONE - No .github/workflows/ directory exists
```

**Impact**:
- No automated code style enforcement
- No automated testing
- No automated releases
- Manual quality checks required
- High risk of inconsistency

---

### F. Documentation Structure

#### Filament
```
docs/
├── 01-introduction/
│   ├── 01-overview.md
│   ├── 02-installation.md
│   ├── 03-optimizing-local-development.md
│   ├── 04-help.md
│   ├── 05-version-support-policy.md
│   └── 06-contributing.md
├── 03-resources/
│   ├── 01-overview.md
│   ├── 02-listing-records.md
│   ├── 03-creating-records.md
│   └── 13-code-quality-tips.md
├── 06-navigation/
├── 07-users/
├── 08-styling/
├── 09-advanced/
├── 10-testing/
├── 11-plugins/
└── 12-components/

docs-assets/app/  # Live documentation app
```

**Features**:
- Numbered structure for ordering
- Comprehensive guides
- Version support policy
- Contributing guidelines
- Code quality tips
- Live demo app

#### Commerce
```
packages/*/docs/
├── API_REFERENCE.md
├── IMPLEMENTATION.md
└── README.md
```

**Features**:
- Package-level docs only
- No root-level getting started
- No contribution guide
- No version policy
- Fragmented structure

**Verdict**: Filament docs are **professional, comprehensive, user-focused**. Commerce docs are **fragmented, technical-only**.

---

### G. Developer Tooling

#### Filament
```bash
bin/
├── build.js                   # Build automation
├── setup-playground.sh        # Quick start script
└── translation-tool.php       # Translation management
```

**Composer Scripts**:
```json
"scripts": {
    "cs": ["rector", "pint", "npm run prettier"],
    "pint": "pint --config pint-strict-imports.json",
    "rector": "rector",
    "test:pest": "pest --parallel",
    "test:phpstan": "phpstan analyse",
    "test": ["@test:pest", "@test:phpstan"]
}
```

#### Commerce
```json
"scripts": {
    "test": "pest",
    "test:coverage": "pest --coverage",
    "phpstan": "phpstan analyse --memory-limit=2G",
    "rector": "rector process",
    "refactor": "rector process",
    "format": "pint"
}
```

**Verdict**: Filament has **comprehensive dev tooling**. Commerce has **basic scripts only**.

---

## 3. What Commerce Can Learn

### Critical Findings

1. **❌ Anti-Pattern: Individual Package Tooling**
   - `chip/`, `jnt/`, `filament-cart/`, `filament-chip/` have duplicate configs
   - Creates maintenance nightmare (update phpstan rules in 5 places)
   - Inconsistent between packages

2. **❌ Anti-Pattern: Package vendor/ Directories**
   - Should NEVER exist in monorepo packages
   - Causes confusion about dependencies
   - Bloats repository
   - Breaks monorepo-split

3. **❌ Missing: CI/CD Infrastructure**
   - No automated testing
   - No automated code style
   - No release automation
   - Manual quality gates

4. **❌ Missing: Comprehensive Documentation**
   - No root-level getting started
   - No contribution guide
   - No version support policy
   - Fragmented package docs

5. **❌ Incomplete: Rector Coverage**
   - Only covers `cart/src` and `tests`
   - Other packages not refactored automatically

6. **❌ Incomplete: PHPStan Coverage**
   - Root only covers 4 of 8 packages
   - `chip`, `jnt`, `filament-*` not analyzed centrally

---

## 4. Refactoring Recommendations

### Priority 1: Remove Anti-Patterns (Immediate)

#### A. Remove Individual Package Tooling
**Affected**: `chip/`, `jnt/`, `filament-cart/`, `filament-chip/`

**Action**:
```bash
# Delete from each package
rm packages/chip/phpstan.neon
rm packages/chip/pint.json  
rm packages/chip/phpunit.xml
rm packages/chip/rector.php
rm packages/chip/composer.lock

# Repeat for jnt, filament-cart, filament-chip
```

**Update root configs**:
```php
// rector.php - ADD all packages
->withPaths([
    __DIR__.'/packages/cart/src',
    __DIR__.'/packages/chip/src',
    __DIR__.'/packages/jnt/src',
    __DIR__.'/packages/stock/src',
    __DIR__.'/packages/vouchers/src',
    __DIR__.'/packages/filament-cart/src',
    __DIR__.'/packages/filament-chip/src',
    __DIR__.'/tests',
])
```

```yaml
# phpstan.neon - ADD all packages
parameters:
    paths:
        - packages/cart/src
        - packages/chip/src
        - packages/jnt/src
        - packages/stock/src
        - packages/vouchers/src
        - packages/filament-cart/src
        - packages/filament-chip/src
```

#### B. Remove vendor/ Directories
```bash
rm -rf packages/chip/vendor/
rm -rf packages/jnt/vendor/
rm -rf packages/filament-cart/vendor/
rm -rf packages/filament-chip/vendor/
```

**Why**: Monorepo uses root vendor/ only. Package vendor/ causes confusion.

---

### Priority 2: Improve Testing Infrastructure

#### A. Add Shared Test Helpers
**Create**: `tests/helpers.php`
```php
<?php

namespace AIArmada\Tests;

use Livewire\Component;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;

if (! function_exists('AIArmada\Tests\livewire')) {
    function livewire(string | Component $component, array $props = []): Testable
    {
        return Livewire::test($component, $props);
    }
}

if (! function_exists('AIArmada\Tests\expectMoney')) {
    function expectMoney(mixed $value, string $currency = 'MYR'): MoneyExpectation
    {
        return new MoneyExpectation($value, $currency);
    }
}
```

**Update**: `composer.json`
```json
"autoload": {
    "files": [
        "tests/helpers.php"
    ],
    ...
}
```

#### B. Add Testbench Config
**Create**: `testbench.yaml`
```yaml
migrations:
  - tests/database/migrations

workbench:
  install: true
```

---

### Priority 3: Add CI/CD Automation

#### A. Code Style Automation
**Create**: `.github/workflows/fix-code-style.yml`
```yaml
name: fix-code-style

on:
  push:
    branches: [main, develop]

jobs:
  fix-code-style:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
        with:
          ref: ${{ github.head_ref }}
          
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: 8.3
          
      - name: Install dependencies
        run: composer install --no-interaction
        
      - name: Run Rector
        run: composer rector
        
      - name: Run Pint
        run: composer format
        
      - name: Commit changes
        uses: stefanzweifel/git-auto-commit-action@v5
        with:
          commit_message: "chore: fix code style"
```

#### B. Testing Matrix
**Create**: `.github/workflows/tests.yml`
```yaml
name: tests

on:
  push:
  pull_request:

jobs:
  test:
    runs-on: ubuntu-latest
    strategy:
      fail-fast: false
      matrix:
        php: [8.4, 8.3, 8.2]
        laravel: [12.*, 11.*]
        include:
          - laravel: 12.*
            testbench: 10.*
          - laravel: 11.*
            testbench: 9.*
            
    name: PHP ${{ matrix.php }} - Laravel ${{ matrix.laravel }}
    
    steps:
      - uses: actions/checkout@v4
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: ${{ matrix.php }}
          extensions: mbstring, pdo, pdo_sqlite
          
      - name: Install dependencies
        run: |
          composer require "laravel/framework:${{ matrix.laravel }}" "orchestra/testbench:${{ matrix.testbench }}" --no-interaction --no-update
          composer update --prefer-stable --prefer-dist --no-interaction
          
      - name: Execute tests
        run: composer test
```

#### C. PHPStan Check
**Create**: `.github/workflows/phpstan.yml`
```yaml
name: phpstan

on:
  push:
  pull_request:

jobs:
  phpstan:
    runs-on: ubuntu-latest
    strategy:
      fail-fast: false
      matrix:
        php: [8.4, 8.3, 8.2]
        
    name: PHP ${{ matrix.php }}
    
    steps:
      - uses: actions/checkout@v4
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: ${{ matrix.php }}
          
      - name: Install dependencies
        run: composer install --no-interaction
        
      - name: Run PHPStan
        run: composer phpstan
```

#### D. Monorepo Split (Future)
**Create**: `.github/workflows/monorepo-split.yml`
```yaml
name: monorepo-split

on:
  push:
    tags: '*'

jobs:
  split:
    runs-on: ubuntu-latest
    strategy:
      fail-fast: false
      matrix:
        package:
          - cart
          - chip
          - jnt
          - stock
          - vouchers
          - filament-cart
          - filament-chip
          
    steps:
      - uses: actions/checkout@v4
      
      - name: Get tag
        id: tag
        uses: WyriHaximus/github-action-get-previous-tag@master
        
      - name: Split ${{ matrix.package }}
        uses: danharrin/monorepo-split-github-action@v2.4.0
        env:
          GITHUB_TOKEN: ${{ secrets.GITHUB_TOKEN }}
        with:
          package_directory: 'packages/${{ matrix.package }}'
          repository_organization: 'aiarmada'
          repository_name: '${{ matrix.package }}'
          branch: main
          tag: ${{ steps.tag.outputs.tag }}
          user_name: 'AIArmada Bot'
          user_email: 'bot@aiarmada.com'
```

---

### Priority 4: Documentation Overhaul

#### A. Create Root Documentation Structure
```
docs/
├── 01-introduction/
│   ├── 01-overview.md
│   ├── 02-installation.md
│   ├── 03-quick-start.md
│   ├── 04-architecture.md
│   └── 05-contributing.md
├── 02-packages/
│   ├── 01-cart.md
│   ├── 02-chip.md
│   ├── 03-jnt.md
│   ├── 04-stock.md
│   ├── 05-vouchers.md
│   ├── 06-filament-cart.md
│   └── 07-filament-chip.md
├── 03-integration/
│   ├── 01-laravel-app.md
│   ├── 02-filament-integration.md
│   └── 03-payment-gateways.md
├── 04-testing/
│   ├── 01-unit-tests.md
│   ├── 02-feature-tests.md
│   └── 03-browser-tests.md
├── 05-deployment/
│   ├── 01-production.md
│   └── 02-optimization.md
└── 06-support/
    ├── 01-troubleshooting.md
    ├── 02-version-support.md
    └── 03-changelog.md
```

#### B. Add Contributing Guide
**Create**: `CONTRIBUTING.md` (based on Filament's approach)

#### C. Add Version Support Policy
**Create**: `docs/06-support/02-version-support.md`

---

### Priority 5: Developer Tooling

#### A. Add Convenience Scripts
```json
// composer.json
"scripts": {
    "cs": [
        "@rector",
        "@format"
    ],
    "test:all": [
        "@test",
        "@phpstan"
    ],
    "test": "pest --parallel",
    "test:coverage": "pest --coverage",
    "phpstan": "phpstan analyse --memory-limit=2G",
    "rector": "rector process",
    "format": "pint",
    "check": [
        "@phpstan",
        "@test"
    ]
}
```

#### B. Add Setup Script
**Create**: `bin/setup.sh`
```bash
#!/bin/bash

echo "Setting up Commerce monorepo..."

# Install dependencies
composer install

# Setup test database
touch database/database.sqlite

# Run migrations
php artisan migrate --database=testing

# Run tests
composer test

echo "✅ Setup complete!"
```

---

## 5. Implementation Roadmap

### Phase 1: Cleanup (Week 1)
- [ ] Remove individual package configs (phpstan, pint, rector, phpunit)
- [ ] Remove all package vendor/ directories
- [ ] Update root phpstan.neon to cover ALL packages
- [ ] Update root rector.php to cover ALL packages
- [ ] Test that everything still works

### Phase 2: Testing Infrastructure (Week 2)
- [ ] Create shared test helpers file
- [ ] Add testbench.yaml config
- [ ] Enhance Pest.php with custom expectations
- [ ] Create TestCase utilities

### Phase 3: CI/CD (Week 2-3)
- [ ] Add fix-code-style workflow
- [ ] Add tests workflow (matrix)
- [ ] Add phpstan workflow (matrix)
- [ ] Test workflows on feature branch

### Phase 4: Documentation (Week 3-4)
- [ ] Create root docs/ structure
- [ ] Write introduction guides
- [ ] Write package integration guides
- [ ] Add CONTRIBUTING.md
- [ ] Add version support policy

### Phase 5: Developer Experience (Week 4)
- [ ] Add composer scripts
- [ ] Create bin/setup.sh
- [ ] Add code quality badges to README
- [ ] Document development workflow

### Phase 6: Release Automation (Future)
- [ ] Configure monorepo-builder.php properly
- [ ] Add monorepo-split workflow
- [ ] Test release process
- [ ] Document release workflow

---

## 6. Success Metrics

### Before Refactoring
- ❌ 5 places to update PHPStan rules
- ❌ 5 places to update Pint rules
- ❌ Package vendor/ directories
- ❌ No CI/CD automation
- ❌ No shared test helpers
- ❌ Fragmented documentation
- ❌ Manual release process

### After Refactoring
- ✅ 1 place to update PHPStan rules
- ✅ 1 place to update Pint rules
- ✅ No package vendor/ directories
- ✅ Automated code style fixes
- ✅ Automated testing matrix
- ✅ Shared test utilities
- ✅ Comprehensive docs
- ✅ Automated releases (Phase 6)

---

## 7. Conclusion

Filament's monorepo is **mature, consistent, and well-automated**. Key lessons:

1. **Centralize tooling** - One source of truth for PHPStan, Pint, Rector
2. **No package vendor/** - Ever
3. **Automate everything** - Code style, tests, releases
4. **Invest in docs** - User-focused, comprehensive
5. **Shared test infrastructure** - Helpers, extensions, utilities
6. **Matrix testing** - Multiple PHP/Laravel versions
7. **Clean package structure** - Only composer.json + source

Commerce can achieve the same level of maturity by following this roadmap systematically.

---

**Document Version**: 1.0  
**Date**: 2025-10-12  
**Based On**: Actual source code analysis of Filament v4.x and Commerce monorepos
