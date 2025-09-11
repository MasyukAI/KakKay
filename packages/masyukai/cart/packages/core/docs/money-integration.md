# Money Integration Guide

## Overview

The cart package now includes a **Money value object** for precision arithmetic and a **MoneyCartAdapter** that provides Money-aware cart operations without breaking existing functionality.

## Why Use Money?

### Float Precision Problems
```php
// ❌ Float arithmetic can be imprecise
$price = 0.1;
$quantity = 3;
$total = $price * $quantity; // Might be 0.30000000000000004

// ✅ Money arithmetic is always precise
$price = Money::fromMajorUnits(0.1, 'USD');
$total = $price->multiply(3); // Always exactly $0.30
```

### Currency Safety
```php
// ❌ No currency safety with floats
$usdPrice = 100.00;
$eurPrice = 85.00;
$total = $usdPrice + $eurPrice; // Mixing currencies!

// ✅ Money prevents currency mixing
$usdPrice = Money::fromMajorUnits(100.00, 'USD');
$eurPrice = Money::fromMajorUnits(85.00, 'EUR');
$total = $usdPrice->add($eurPrice); // Throws InvalidArgumentException
```

## Basic Money Usage

### Creating Money Objects
```php
use MasyukAI\Cart\Support\Money;

// From major units (dollars, euros, etc.)
$price = Money::fromMajorUnits(19.99, 'USD');
$price = Money::fromMajorUnits(29.50, 'EUR');

// From minor units (cents, etc.)
$price = Money::fromMinorUnits(1999, 'USD'); // $19.99
$price = Money::fromMinorUnits(2950, 'EUR'); // €29.50

// From strings (cleans formatting)
$price = Money::fromMajorUnits('$1,234.56', 'USD'); // $1234.56
```

### Arithmetic Operations
```php
$price1 = Money::fromMajorUnits(10.00, 'USD');
$price2 = Money::fromMajorUnits(5.50, 'USD');

$sum = $price1->add($price2);           // $15.50
$diff = $price1->subtract($price2);     // $4.50
$double = $price1->multiply(2);         // $20.00
$half = $price1->divide(2);             // $5.00
$tax = $price1->percentage(8.25);       // $0.83 (8.25% of $10.00)
```

### Formatting and Display
```php
$price = Money::fromMajorUnits(1234.56, 'USD');

echo $price->format();           // $1,234.56
echo $price->getMajorUnits();    // 1234.56
echo $price->getMinorUnits();    // 123456
echo $price->getCurrency();      // USD
```

## Cart Integration with MoneyCartAdapter

### Basic Setup
```php
use MasyukAI\Cart\Support\{Money, MoneyCartAdapter};

// Create money-aware cart
$moneyCart = new MoneyCartAdapter('shopping', 'USD', 2);

// Add items with Money prices
$itemPrice = Money::fromMajorUnits(29.99, 'USD');
$moneyCart->addMoney('product-1', 'Premium Widget', $itemPrice, 2);
```

### Getting Precise Totals
```php
// Get subtotal as Money object
$subtotal = $moneyCart->getSubtotalMoney();
echo $subtotal->format(); // $59.98

// Get total as Money object
$total = $moneyCart->getTotalMoney();
echo $total->format(); // $59.98 (if no conditions)

// Calculate tax with precision
$tax = $moneyCart->calculateTaxMoney(8.25); // 8.25% tax
$totalWithTax = $moneyCart->getTotalWithTaxMoney(8.25);
echo $totalWithTax->format(); // $64.93
```

### Working with Conditions
```php
// Apply discount using Money precision
$discountAmount = Money::fromMajorUnits(10.00, 'USD');
$moneyCart->applyMoneyCondition('PROMO_CODE', $discountAmount, 'discount');

// Apply shipping cost
$shipping = Money::fromMajorUnits(9.95, 'USD');
$grandTotal = $moneyCart->addShippingMoney($shipping);
```

### Getting Detailed Breakdown
```php
// Get complete price breakdown with Money objects
$breakdown = $moneyCart->getPriceBreakdownMoney();
// Returns:
// [
//     'subtotal' => Money object,
//     'total' => Money object,
//     'currency' => 'USD',
//     'precision' => 2,
//     'items_count' => 3,
//     'total_quantity' => 5
// ]

// Get individual item prices as Money objects
$itemPrices = $moneyCart->getItemPricesMoney();
// Returns array with Money objects for each item
```

## Advanced Use Cases

### Multi-Currency Support
```php
// USD cart
$usdCart = new MoneyCartAdapter('usd', 'USD', 2);
$usdCart->addMoney('item', 'Product', Money::fromMajorUnits(100.00, 'USD'), 1);

// EUR cart
$eurCart = new MoneyCartAdapter('eur', 'EUR', 2);
$eurCart->addMoney('item', 'Product', Money::fromMajorUnits(85.00, 'EUR'), 1);

// Each cart maintains its currency integrity
```

### Precision Verification
```php
// Verify cart total matches expected amount
$expectedTotal = Money::fromMajorUnits(159.98, 'USD');
$isCorrect = $moneyCart->verifyTotal($expectedTotal);

if (!$isCorrect) {
    // Handle price mismatch
    $actualTotal = $moneyCart->getTotalMoney();
    throw new Exception("Expected {$expectedTotal->format()}, got {$actualTotal->format()}");
}
```

### Integration with Existing Code
```php
// MoneyCartAdapter wraps existing cart functionality
$cart = $moneyCart->getCartInstance(); // Get underlying CartManager
$items = $cart->getItems();            // Use existing methods

// Convert existing float prices to Money
$existingPrice = $cart->getRawTotal(); // float
$moneyPrice = Money::fromMajorUnits($existingPrice, 'USD');
```

## Configuration

### Environment Variables
Add to your `.env`:
```bash
# Enable Money objects in cart calculations
CART_USE_MONEY_OBJECTS=true
CART_DEFAULT_CURRENCY=USD
CART_DEFAULT_PRECISION=2
CART_ROUNDING_MODE=round
```

### Programmatic Configuration
```php
// Set different currency/precision per cart
$moneyCart = new MoneyCartAdapter('special', 'JPY', 0); // No decimals for JPY
$moneyCart->setCurrency('EUR')->setPrecision(2);        // Chain methods
```

## Testing with Money

```php
it('calculates cart totals with money precision', function () {
    $moneyCart = new MoneyCartAdapter('test', 'USD', 2);
    
    // Add items
    $moneyCart->addMoney('item1', 'Test Item', Money::fromMajorUnits(19.99, 'USD'), 2);
    
    // Test precision
    $subtotal = $moneyCart->getSubtotalMoney();
    expect($subtotal->getMajorUnits())->toBe(39.98);
    expect($subtotal->getMinorUnits())->toBe(3998);
    expect($subtotal->format())->toBe('$39.98');
});
```

## Migration from Float to Money

### 1. Gradual Migration
```php
// Keep existing float-based cart
$cart = Cart::instance('main');

// Add Money wrapper for new calculations
$moneyCart = new MoneyCartAdapter('main', 'USD', 2);

// Use Money for new features, float for existing
$total = $cart->getTotal();                    // float (existing)
$preciseTotal = $moneyCart->getTotalMoney();   // Money (new)
```

### 2. Validation Layer
```php
// Verify Money calculations match float calculations (within tolerance)
$floatTotal = $cart->getRawTotal();
$moneyTotal = $moneyCart->getTotalMoney();

$difference = abs($floatTotal - $moneyTotal->getMajorUnits());
if ($difference > 0.01) { // 1 cent tolerance
    // Log potential precision issue
    Log::warning('Cart total mismatch', [
        'float_total' => $floatTotal,
        'money_total' => $moneyTotal->format(),
        'difference' => $difference
    ]);
}
```

## Best Practices

1. **Use Money for all new price calculations**
2. **Keep currency consistent within each cart instance**
3. **Validate Money totals against expected amounts**
4. **Use MoneyCartAdapter for gradual migration**
5. **Test precision-critical calculations thoroughly**
6. **Log any float vs Money discrepancies during migration**
