# MasyukAI Cart Package - Comprehensive Demo & Testing Suite

## Overview

This package includes a full-featured demo system and comprehensive browser testing suite built with PestPHP 4. The demo serves two purposes:

1. **Live Demonstration**: Showcases all package capabilities with an interactive web interface
2. **Real-world Testing**: Provides browser-based tests to ensure the package is bug-free in real usage scenarios

## Demo Features

### 1. Shopping Cart Demo (`/cart-demo`)
- **Product Catalog**: Interactive product grid with attributes selection
- **Cart Management**: Add, update, remove items with real-time updates
- **Conditions System**: Apply discounts, taxes, and shipping fees
- **Modern UI**: Alpine.js frontend with Tailwind CSS styling
- **Responsive Design**: Works on desktop, tablet, and mobile devices

### 2. Cart Instances Demo (`/cart-demo/instances`)
- **Multiple Carts**: Default, wishlist, and comparison carts
- **Instance Switching**: Seamless switching between cart instances
- **Cross-Instance Operations**: Copy and merge items between instances
- **Separate State**: Each instance maintains independent cart contents

### 3. Migration Demo (`/cart-demo/migration`)
- **Guest-to-User Migration**: Simulates user login scenarios
- **Strategy Testing**: Test all 4 migration strategies
- **Conflict Resolution**: See how duplicate items are handled
- **Visual Results**: Real-time migration results with detailed feedback

## Browser Testing Suite

### Test Coverage

The package includes comprehensive browser tests using PestPHP 4 with Playwright integration:

#### 1. Shopping Experience Tests (`CartDemoShoppingTest.php`)
- Product catalog loading and display
- Product attribute selection
- Cart operations (add, update, remove, clear)
- Condition application and removal
- Notification system testing
- Responsive design verification
- Loading states and error handling

#### 2. Cart Instances Tests (`CartInstancesTest.php`)
- Instance switching functionality
- Separate cart state management
- Cross-instance operations (copy, merge)
- Instance-specific operations
- Data persistence across switches

#### 3. Migration Tests (`CartMigrationTest.php`)
- Cart setup for migration scenarios
- All migration strategy testing
- Conflict resolution verification
- Migration result tracking
- Edge case handling (empty carts)

#### 4. Integration Tests (`FullIntegrationTest.php`)
- Complete e-commerce workflow simulation
- Cross-demo navigation and state persistence
- Performance testing with multiple items
- Concurrent operations handling
- Mobile responsiveness across all demos
- Accessibility features testing

### Running Tests

```bash
# Run all tests
./vendor/bin/pest

# Run specific test suites
./vendor/bin/pest tests/Feature/
./vendor/bin/pest tests/Browser/

# Run specific test files
./vendor/bin/pest tests/Browser/CartDemoShoppingTest.php
./vendor/bin/pest tests/Browser/CartInstancesTest.php
./vendor/bin/pest tests/Browser/CartMigrationTest.php
./vendor/bin/pest tests/Browser/FullIntegrationTest.php

# Run with coverage
./vendor/bin/pest --coverage
```

## Demo Setup

### 1. Enable Demo Routes

The demo routes are automatically enabled in `local` and `testing` environments. To manually control:

```php
// config/cart.php
'demo' => [
    'enabled' => true // Force enable demos
],
```

### 2. Access Demo Pages

Once enabled, access the demos at:

- Main Demo: `http://your-app.com/cart-demo`
- Instances Demo: `http://your-app.com/cart-demo/instances`
- Migration Demo: `http://your-app.com/cart-demo/migration`

### 3. Demo Data

The demos use in-memory sample data and don't require database setup. All cart operations use the package's native API.

## Technical Architecture

### Frontend Technology Stack
- **Alpine.js**: Reactive frontend framework
- **Tailwind CSS**: Utility-first CSS framework
- **JavaScript**: Modern ES6+ with async/await patterns
- **AJAX**: RESTful API communication with proper error handling

### Backend Integration
- **Native Cart API**: Uses the package's Cart facade exclusively
- **RESTful Endpoints**: Clean API design following Laravel conventions
- **Error Handling**: Comprehensive validation and error responses
- **Session Management**: Proper cart instance and session handling

### Testing Technology
- **PestPHP 4**: Modern PHP testing framework
- **Browser Plugin**: Playwright-based browser automation
- **Laravel Integration**: Full Laravel application testing
- **Custom Helpers**: Reusable test utilities and macros

## Demo Components

### 1. Product Catalog
```php
// Sample products with attributes
$products = [
    [
        'id' => 'wireless-headphones',
        'name' => 'Wireless Headphones Pro',
        'price' => 199.99,
        'attributes' => [
            'color' => ['Black', 'White', 'Blue', 'Red'],
            'storage' => ['128GB', '256GB', '512GB']
        ]
    ],
    // ... more products
];
```

### 2. Cart Operations
```javascript
// Add to cart with attributes
CartDemo.makeRequest('/cart-demo/add', {
    method: 'POST',
    body: JSON.stringify({
        id: productId,
        name: productName,
        price: productPrice,
        quantity: quantity,
        attributes: selectedAttributes
    })
});
```

### 3. Migration Scenarios
```php
// Migration strategies
'add_quantities'         // Combine quantities: Guest(2) + User(3) = Final(5)
'keep_highest_quantity'  // Keep higher: Guest(2) + User(3) = Final(3)
'keep_user_cart'        // Preserve user: Guest(2) + User(3) = Final(3)
'replace_with_guest'    // Use guest: Guest(2) + User(3) = Final(2)
```

## Browser Test Examples

### Basic Cart Operations
```php
test('can add product to cart with attributes', function () {
    $this->browse(function ($browser) {
        $browser->visit('/cart-demo')
            ->click('.product-card:first-child .color-option[data-value="blue"]')
            ->click('.product-card:first-child .add-to-cart-btn')
            ->waitFor('.notification-success', 5)
            ->assertSee('added to cart successfully');
    });
});
```

### Instance Management
```php
test('can switch between cart instances', function () {
    $this->browse(function ($browser) {
        $browser->visit('/cart-demo/instances')
            ->click('.instance-option[data-instance="wishlist"]')
            ->waitFor('.notification-success', 5)
            ->assertSee('Switched to wishlist cart');
    });
});
```

### Migration Testing
```php
test('can perform migration with add quantities strategy', function () {
    $this->browse(function ($browser) {
        $browser->visit('/cart-demo/migration')
            ->click('.setup-guest-cart-btn')
            ->click('.setup-user-cart-btn')
            ->radio('add_quantities')
            ->click('.perform-migration-btn')
            ->assertSee('Migration completed successfully');
    });
});
```

## Performance Considerations

### Optimizations
- **Efficient DOM Updates**: Minimal DOM manipulation with targeted updates
- **Request Debouncing**: Prevents rapid-fire requests during user interactions
- **Loading States**: Visual feedback during async operations
- **Error Boundaries**: Graceful error handling and recovery

### Testing Performance
- **Concurrent Operations**: Tests handle rapid user interactions
- **Large Datasets**: Performance testing with multiple cart items
- **Memory Management**: Proper cleanup between test scenarios
- **Browser Resources**: Optimized browser test execution

## Customization

### Extending Demos
```php
// Add custom demo routes
Route::prefix('cart-demo')->group(function () {
    Route::get('/custom', [CustomDemoController::class, 'index']);
});
```

### Custom Test Scenarios
```php
// Add custom browser tests
test('custom cart workflow', function () {
    $this->browse(function ($browser) {
        // Your custom test logic
    });
});
```

### UI Customization
```blade
{{-- Override demo views --}}
@extends('cart::demo.layout')
@section('content')
    {{-- Your custom demo content --}}
@endsection
```

## Best Practices

### Demo Development
1. **Real API Usage**: Demos use actual package APIs, not mocked data
2. **Error Handling**: Comprehensive error scenarios and user feedback
3. **Accessibility**: ARIA labels, keyboard navigation, screen reader support
4. **Mobile First**: Responsive design tested across device sizes

### Testing Strategy
1. **User Journey Testing**: Complete e-commerce workflows
2. **Edge Case Coverage**: Empty carts, invalid data, network errors
3. **Cross-Browser Testing**: Ensure compatibility across browsers
4. **Performance Testing**: Load testing with realistic data volumes

## Troubleshooting

### Common Issues

1. **Demo Routes Not Loading**
   ```php
   // Ensure demo is enabled
   config(['cart.demo.enabled' => true]);
   ```

2. **Browser Tests Failing**
   ```bash
   # Check browser dependencies
   ./vendor/bin/pest --help browser
   ```

3. **JavaScript Errors**
   ```bash
   # Check browser console in demo pages
   # Ensure Alpine.js and Tailwind CSS are loaded
   ```

### Debug Mode
```php
// Enable detailed logging
config(['app.debug' => true]);
config(['cart.debug' => true]);
```

## Contributing

When contributing to the demo or test suite:

1. **Maintain Test Coverage**: All new features should include browser tests
2. **Follow UI Patterns**: Use existing Alpine.js and Tailwind CSS patterns
3. **Real-world Scenarios**: Tests should simulate actual user behavior
4. **Documentation**: Update this README for new demo features

## License

This demo and testing suite is part of the MasyukAI Cart package and follows the same MIT license.
