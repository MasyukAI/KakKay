# Quick Reference: Voucher Integration

## 🎯 Two Key Questions Answered

### 1. Static vs Dynamic Vouchers

#### When to Use Static (No Rules)

```php
// Simple "$5 off" - no conditions
$condition = new VoucherCondition(
    voucher: $voucherData,
    order: 50,
    dynamic: false  // ← Static mode
);

Cart::addCondition($condition);
```

**Use when:**
- ✅ Simple discount with no conditions
- ✅ Pre-validated voucher
- ✅ Performance is critical
- ✅ No need for re-validation

#### When to Use Dynamic (With Rules)

```php
// "10% off orders over $100"
$condition = new VoucherCondition(
    voucher: $voucherData,
    order: 50,
    dynamic: true  // ← Dynamic mode (DEFAULT)
);

Cart::registerDynamicCondition($condition);
Cart::evaluateDynamicConditions();
```

**Use when:**
- ✅ Voucher has minimum cart value
- ✅ Voucher has usage limits
- ✅ Voucher has date restrictions
- ✅ Want automatic validation
- ✅ Want auto-removal if invalid

**Default: Use Dynamic** (safer, more flexible)

---

### 2. Multiple Cart Extensions

#### The Problem

```php
// Multiple packages try to extend Cart:
masyukai/cart-vouchers      → HasVouchers trait
masyukai/cart-loyalty       → HasLoyaltyPoints trait
masyukai/cart-gift-cards    → HasGiftCards trait

// ❌ Can't all bind to Cart::class individually!
```

#### The Solution: Manual Composition

```php
// Step 1: Create ExtendedCart in YOUR application
// app/Support/Cart/ExtendedCart.php

<?php

namespace App\Support\Cart;

use MasyukAI\Cart\Cart;
use MasyukAI\Cart\Vouchers\Traits\HasVouchers;
use MasyukAI\Cart\Loyalty\Traits\HasLoyaltyPoints;
use MasyukAI\Cart\GiftCards\Traits\HasGiftCards;

class ExtendedCart extends Cart
{
    use HasVouchers;
    use HasLoyaltyPoints;
    use HasGiftCards;
}
```

```php
// Step 2: Bind in AppServiceProvider
// app/Providers/AppServiceProvider.php

public function register(): void
{
    $this->app->bind(
        \MasyukAI\Cart\Cart::class, 
        \App\Support\Cart\ExtendedCart::class
    );
}
```

```php
// Step 3: Use all extensions!
use MasyukAI\Cart\Facades\Cart;

Cart::applyVoucher('SUMMER20');
Cart::addLoyaltyPoints(100);
Cart::applyGiftCard('GIFT-123');
```

**Why manual composition?**
- ✅ Application controls what gets included
- ✅ No package conflicts
- ✅ Explicit and clear
- ✅ Easy to debug
- ✅ IDE autocomplete works

---

## 📋 Quick Setup Checklist

### For Single Extension (Vouchers Only)

```bash
# 1. Install
composer require masyukai/cart-vouchers

# 2. Option A: Use directly (no trait)
$condition = new VoucherCondition($voucherData);
Cart::registerDynamicCondition($condition);

# 2. Option B: Bind CartWithVouchers
// In AppServiceProvider::register()
$this->app->bind(
    \MasyukAI\Cart\Cart::class,
    \MasyukAI\Cart\Vouchers\Support\CartWithVouchers::class
);

// Use
Cart::applyVoucher('CODE');
```

### For Multiple Extensions

```bash
# 1. Install packages
composer require masyukai/cart-vouchers
composer require masyukai/cart-loyalty
composer require masyukai/cart-gift-cards

# 2. Create ExtendedCart
php artisan make:class Support/Cart/ExtendedCart
```

```php
// 3. Add traits
class ExtendedCart extends Cart
{
    use HasVouchers;
    use HasLoyaltyPoints;
    use HasGiftCards;
}

// 4. Bind in AppServiceProvider
$this->app->bind(Cart::class, ExtendedCart::class);

// 5. Use
Cart::applyVoucher('SUMMER20');
Cart::addLoyaltyPoints(100);
```

---

## 🎨 Visual Guide

### Static vs Dynamic Flow

```
┌─────────────────────────────────────────────────────────┐
│              STATIC VOUCHER                             │
│                                                         │
│  Validate Once → Create Condition → Add to Cart       │
│                                                         │
│  ✓ Faster                                              │
│  ✓ Simple                                              │
│  ✗ No re-validation                                    │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│              DYNAMIC VOUCHER                            │
│                                                         │
│  Create Condition → Register as Dynamic                │
│       ↓                                                 │
│  Cart calculates → Auto re-validates                   │
│       ↓                                                 │
│  Invalid? → Auto removes                               │
│                                                         │
│  ✓ Automatic validation                                │
│  ✓ Auto-removal                                        │
│  ✗ Slightly slower                                     │
└─────────────────────────────────────────────────────────┘
```

### Multiple Extensions Architecture

```
┌─────────────────────────────────────────────────────────┐
│                 YOUR APPLICATION                        │
│                                                         │
│  ┌───────────────────────────────────────────────────┐ │
│  │         ExtendedCart extends Cart                 │ │
│  │  ┌─────────────────────────────────────────────┐ │ │
│  │  │ use HasVouchers;                            │ │ │
│  │  │ use HasLoyaltyPoints;                       │ │ │
│  │  │ use HasGiftCards;                           │ │ │
│  │  └─────────────────────────────────────────────┘ │ │
│  └───────────────────────────────────────────────────┘ │
│                         ↓                               │
│              Bound to Cart::class                       │
└─────────────────────────────────────────────────────────┘
                          ↑
        ┌─────────────────┼─────────────────┐
        ↑                 ↑                 ↑
┌───────────────┐  ┌───────────────┐  ┌───────────────┐
│cart-vouchers  │  │cart-loyalty   │  │cart-gift-cards│
│               │  │               │  │               │
│HasVouchers    │  │HasLoyaltyPts  │  │HasGiftCards   │
│trait          │  │trait          │  │trait          │
└───────────────┘  └───────────────┘  └───────────────┘
```

---

## 💡 Examples

### Example 1: Static Simple Voucher

```php
// No rules, just $5 off
$voucher = Voucher::find('SIMPLE5');
$condition = new VoucherCondition($voucher, order: 50, dynamic: false);
Cart::addCondition($condition);
```

### Example 2: Dynamic Voucher with Min Value

```php
// "10% off orders over $100" - needs re-validation
$voucher = Voucher::find('MIN100');
$condition = new VoucherCondition($voucher, order: 50, dynamic: true);
Cart::registerDynamicCondition($condition);
```

### Example 3: Multiple Extensions in Application

```php
// Your ExtendedCart.php
class ExtendedCart extends Cart
{
    use HasVouchers;
    use HasLoyaltyPoints;
}

// Usage
Cart::applyVoucher('SUMMER20');      // From HasVouchers
Cart::addLoyaltyPoints(100);         // From HasLoyaltyPoints
Cart::total();                        // From base Cart
```

---

## 📖 Full Documentation

See detailed docs:
- `STATIC_VS_DYNAMIC_AND_EXTENSIONS.md` - Complete guide
- `INTEGRATION_APPROACHES.md` - Three integration methods
- `CORRECT_INTEGRATION.md` - Architecture explanation

---

**Quick Answer:**

1. **Static vs Dynamic?** → Use Dynamic (default) unless you have simple vouchers with no rules
2. **Multiple Extensions?** → Create ExtendedCart in your app with all traits

**Both maintain package independence!** ✅
