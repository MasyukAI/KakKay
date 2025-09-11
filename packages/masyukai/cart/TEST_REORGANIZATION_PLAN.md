# Cart Package Test Reorganization Plan

## Current Issues Identified

### 1. Monolithic Test Files
- `CartTest.php` (1,165 lines) - Covers 16 different concerns
- `CartCollectionTest.php` (815 lines)
- `DatabaseStorageCoverageTest.php` (705 lines)
- `CartConditionTest.php` (604 lines)
- `CartItemTest.php` (539 lines)

### 2. Missing Infrastructure
- Database table not created for tests
- Missing middleware classes being tested
- Inconsistent test setup

### 3. Poor Organization
- Mixed unit and integration tests
- Tests not aligned with package structure
- Duplicate test scenarios
- Tests not reflecting actual codebase architecture

## Proposed New Structure

### Core Package Structure Mapping
```
packages/core/src/
├── Cart.php                           → tests/Unit/Core/CartTest.php
├── CartManager.php                    → tests/Unit/Core/CartManagerTest.php
├── CartServiceProvider.php           → tests/Unit/Core/CartServiceProviderTest.php
├── Collections/                      → tests/Unit/Collections/
├── Conditions/                       → tests/Unit/Conditions/
├── Contracts/                        → tests/Unit/Contracts/
├── Events/                           → tests/Unit/Events/
├── Exceptions/                       → tests/Unit/Exceptions/
├── Facades/                          → tests/Unit/Facades/
├── Listeners/                        → tests/Unit/Listeners/
├── Models/                           → tests/Unit/Models/
├── PriceTransformers/                → tests/Unit/PriceTransformers/
├── Services/                         → tests/Unit/Services/
├── Storage/                          → tests/Unit/Storage/
├── Support/                          → tests/Unit/Support/
└── Traits/                           → tests/Unit/Traits/
```

### Feature Test Organization
```
tests/Feature/
├── CartOperations/
│   ├── AddItemsTest.php
│   ├── UpdateItemsTest.php
│   ├── RemoveItemsTest.php
│   └── ClearCartTest.php
├── CartCalculations/
│   ├── SubtotalCalculationTest.php
│   ├── TotalCalculationTest.php
│   └── ConditionsTest.php
├── CartPersistence/
│   ├── SessionStorageTest.php
│   ├── DatabaseStorageTest.php
│   └── CacheStorageTest.php
├── CartEvents/
│   ├── ItemEventsTest.php
│   ├── CartEventsTest.php
│   └── ConditionEventsTest.php
└── Integration/
    ├── CartFacadeTest.php
    ├── ServiceProviderIntegrationTest.php
    └── FullWorkflowTest.php
```

## Refactoring Strategy

### Phase 1: Core Infrastructure
1. Set up proper test database
2. Remove tests for non-existent classes
3. Create base test classes

### Phase 2: Unit Test Separation
1. Break down monolithic `CartTest.php` into focused unit tests
2. Separate trait tests from main Cart tests
3. Organize tests by concern/responsibility

### Phase 3: Feature Test Reorganization
1. Extract integration scenarios to Feature tests
2. Group related functionality together
3. Remove duplicate tests

### Phase 4: Test Accuracy
1. Ensure tests reflect actual codebase behavior
2. Fix incorrect assumptions
3. Add missing coverage

## Priority Order
1. Fix immediate failing tests (database, missing classes)
2. Reorganize largest monolithic files first
3. Create proper separation of concerns
4. Validate all tests pass and reflect truth