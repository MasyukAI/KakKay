# Money & Currency

MasyukAI Cart uses [`akaunting/money`](https://github.com/akaunting/money) to provide consistent arithmetic and formatting.

## Default Currency

`config('cart.money.default_currency')` sets the currency for every `Money` instance returned by the cart. The default is `MYR`; override it to match your storefront:

```php
// config/cart.php
'money' => [
    'default_currency' => 'USD',
],
```

After changing the currency run `php artisan config:cache` to propagate the update.

## What Returns Money Objects?

- `Cart::subtotal()` / `Cart::total()` / `Cart::savings()`
- `Cart::subtotalWithoutConditions()` / `Cart::totalWithoutConditions()`
- `Cart::get('sku')->getPrice()` / `getSubtotal()` / `getDiscountAmount()`

Money objects keep precision intact, even when applying percentage-based conditions.

## Converting for Display

```php
$total = Cart::total();

$total->format();      // "1,234.56"
$total->getAmount();   // 1234.56 (float)
$total->getValue();    // "1234.56" string
```

If you require localized formatting, configure Money’s locale or use Laravel’s localization helpers around `$total->getAmount()`.

## Storing & Serializing

- `Cart::content()` renders raw numeric amounts (`subtotal`, `total`) for APIs. These values are derived from Money objects via `getAmount()`.
- `CartItem::toArray()` emits base price and quantity; use the Money helpers when presenting totals to users.

## Multi-Currency Strategies

The package stores monetary amounts without currency conversion. For multi-currency storefronts:

1. Maintain a cart per currency (instances like `cart-USD`, `cart-EUR`).
2. Convert incoming prices before pushing them into the cart.
3. Override the default currency at runtime with `config(['cart.money.default_currency' => 'EUR']);` or a scoped config helper.

Always clear cached carts if you change currencies mid-session to avoid mixing price bases.
