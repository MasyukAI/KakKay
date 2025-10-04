# Conditions & Discounts

Conditions adjust prices at the item, subtotal, or total level. They handle discounts, fees, taxes, and shipping with deterministic ordering.

## Condition Basics

Create a condition with the `CartCondition` value object:

```php
use MasyukAI\Cart\Conditions\CartCondition;

$condition = new CartCondition(
    name: 'black-friday',
    type: 'discount',
    target: 'subtotal',
    value: '-20%',
    attributes: ['source' => 'BF2025'],
    order: 10,
);

Cart::addCondition($condition);
```

- **name** – unique key per instance.
- **type** – descriptive tag (`discount`, `tax`, `fee`, `shipping`, …) for filtering.
- **target** – where the condition applies:
  - `item` (applies per line item via `addItemCondition()`)
  - `subtotal` (applies after item-level adjustments, before total-level adjustments)
  - `total` (applies last)
- **value** – supports `+`, `-`, `*`, `/`, or `%` expressions. Percentages are relative to the incoming amount.
- **order** – lower numbers execute first when multiple conditions share the same target.
- **attributes** – arbitrary metadata stored alongside the condition.

Invalid values trigger `InvalidCartConditionException`.

## Convenience Helpers

```php
Cart::addDiscount('new-customer', '15%');          // Target subtotal by default
Cart::addFee('environmental-fee', '+2.50');        // Applies at total level
Cart::addTax('vat', '8%');                         // Applies at subtotal level
Cart::addShipping('express', '18.00', 'express');
```

`Cart::removeShipping()` resets all shipping conditions, while `Cart::getShipping()` returns the active shipping condition if present.

## Item-Level Conditions

```php
$itemCondition = new CartCondition('bulk-discount', 'discount', 'item', '-5%');
Cart::addItemCondition('sku-1', $itemCondition);
Cart::removeItemCondition('sku-1', 'bulk-discount');
Cart::clearItemConditions('sku-1');
```

`CartItem` exposes helper methods for querying conditions:

```php
$item = Cart::get('sku-1');
$item->hasCondition('bulk-discount');
$item->getConditions()->byType('discount');
```

## Dynamic Conditions

Dynamic conditions respond to cart state changes (e.g., spend thresholds). Define them with rules (closures) and register them once per cart instance.

```php
$dynamic = new CartCondition(
    name: 'spend-200-get-20',
    type: 'discount',
    target: 'subtotal',
    value: '-20',
    attributes: ['threshold' => 200],
    rules: [
        fn ($cart) => $cart->getRawSubtotalWithoutConditions() >= 20000,
    ],
);

Cart::getCurrentCart()->registerDynamicCondition($dynamic);
```

- `rules` receives the cart and, for item targets, the current `CartItem`.
- When rules evaluate to `true`, the system applies a static copy of the condition. When they become `false`, the system removes it.
- `Cart::evaluateDynamicConditions()` runs automatically after add/update/remove operations. Manually call it if you change state outside the usual flow.

## Condition Collections

`Cart::getConditions()` returns a `CartConditionCollection` with useful filters:

```php
$discounts = Cart::getConditions()->discounts();
$shipping = Cart::getConditions()->byType('shipping')->first();
```

Use `toDetailedArray($baseValue)` to gather reporting-friendly snapshots including calculated amounts.

## Ordering & Precedence

1. Item-level conditions run first when totals are calculated.
2. Subtotal-targeted conditions apply next (respecting ascending `order`).
3. Total-targeted conditions apply last.

This mirrored flow ensures cross-driver parity and repeatable amounts.

## Best Practices

- Group related conditions via prefixes (`promo:`, `shipping:`) to simplify filtering.
- Use attributes for storing display labels, coupon codes, or rate IDs.
- Remove conflicting conditions explicitly (e.g., only one active shipping condition at a time).
- Combine with metadata to persist user selections.

For a full API surface, check the [reference](api-reference.md#conditions).
