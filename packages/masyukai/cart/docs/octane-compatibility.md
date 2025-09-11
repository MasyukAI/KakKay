# Laravel Octane Compatibility Guide

## Overview

This document outlines the Laravel Octane compatibility status and improvements for the MasyukAI Cart package.

## Identified Issues and Solutions

### 1. Static State in PriceFormatManager ❌ FIXED

**Issue**: The `PriceFormatManager` class uses static properties that persist between requests in Octane:
- `$formatter`
- `$globalFormatOverride`
- `$globalCurrencyOverride`
- `$lastTransformerClass`

**Solution**: Added Octane listeners to reset state between requests.

### 2. Session Storage Compatibility ✅ COMPATIBLE

**Status**: Session storage is compatible with Octane as it relies on Laravel's session handling which is properly reset between requests.

### 3. Cache Storage Compatibility ✅ COMPATIBLE  

**Status**: Cache storage is fully compatible with Octane as it uses Laravel's cache system.

### 4. Database Storage Compatibility ✅ COMPATIBLE

**Status**: Database storage is fully compatible with Octane.

### 5. Event Listeners ✅ COMPATIBLE

**Status**: Event listeners are compatible but may need to be queued to avoid blocking requests.

### 6. Middleware ✅ COMPATIBLE

**Status**: The `AutoSwitchCartInstance` middleware is stateless and compatible.

## Octane Configuration

### Required Listeners

Add these listeners to your `config/octane.php`:

```php
'listeners' => [
    // ... existing listeners
    
    OperationTerminated::class => [
        // ... existing listeners
        \MasyukAI\Cart\Listeners\ResetCartState::class,
    ],
],
```

### Warm Services

Consider warming cart services:

```php
'warm' => [
    ...Octane::defaultServicesToWarm(),
    'cart',
    \MasyukAI\Cart\Services\CartMigrationService::class,
    \MasyukAI\Cart\Services\PriceFormatterService::class,
],
```

## Testing with Octane

To test Octane compatibility:

1. Run tests with Octane: `php artisan octane:start --workers=4`
2. Run stress tests: `RUN_STRESS_TESTS=true php vendor/bin/pest`
3. Verify state isolation between requests

## Performance Recommendations

### 1. Use Cache Storage for High Traffic

```php
// config/cart.php
'default_storage' => 'cache',
```

### 2. Queue Heavy Operations

Event listeners like `HandleUserLogin` implement `ShouldQueue` for async processing.

### 3. Optimize Storage Keys

Use consistent, predictable storage keys to leverage cache warming.

## Verification Checklist

- [x] No static state persists between requests
- [x] Event listeners are queued appropriately  
- [x] Storage drivers work correctly
- [x] Session handling works properly
- [x] Cache usage is optimized
- [x] Memory usage is stable
- [x] Performance is maintained

## Known Limitations

1. **Session Storage**: Less efficient with many workers; consider cache storage for high traffic.
2. **Event Processing**: Synchronous events may impact response times; use queuing.

## Monitoring

Monitor these metrics with Octane:

- Memory usage per worker
- Request processing time
- Cache hit rates
- Queue processing times
- Cart migration success rates
