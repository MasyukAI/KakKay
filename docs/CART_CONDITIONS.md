# Cart Conditions Guide

## Overview

The cart supports two complementary condition systems:

- **Static conditions** - discounts, fees, and taxes that are always applied once attached to the cart or an item.
- **Dynamic conditions** - rule-driven adjustments that attach or detach themselves automatically as the cart changes.

Both systems rely on the same `CartCondition` value object, ensuring consistent calculations and serialization.

## Static Conditions

Static conditions are created without rules and can be attached directly to a cart or item.

```php
use MasyukAI\Cart\Conditions\CartCondition;

$condition = new CartCondition(
    name: 'holiday_discount',
    type: 'discount',
    target: 'subtotal',
    value: '-15%'
);

$cart->addCondition($condition);
```

Helpers such as `addDiscount`, `addFee`, `addTax`, and `addShipping` handle common scenarios and persist the condition through the configured storage driver.

## Dynamic Conditions

Dynamic conditions carry rule closures that determine when they should apply. They are registered through `registerDynamicCondition()` and evaluated automatically whenever the cart mutates.

```php
$cart->registerDynamicCondition(
    condition: [
        'name' => 'vip_discount',
        'type' => 'discount',
        'target' => 'subtotal',
        'value' => '-10%'
    ],
    rules: 'min-items',
    metadata: ['min_items' => 2]
);
```

Key behaviors:

- Rules can be provided as closures, an array of closures, a factory key, or a mix of factory keys and closures.
- Dynamic conditions are stored in memory during the request and serialized via metadata when a factory key is supplied.
- Item-level conditions (`target: item`) run their rules for each cart item and attach static clones per item.
- `CartCondition::withoutRules()` now caches the static clone for dynamic conditions to avoid repeated instantiation.

## Using a Rules Factory

Rules factories make dynamic conditions persistent by replacing closures with resolvable keys.

```php
use MasyukAI\Cart\Contracts\RulesFactoryInterface;

final class DemoRulesFactory implements RulesFactoryInterface
{
    public function createRules(string $key, array $metadata = []): array
    {
        return match ($key) {
            'min-items' => [
                static fn (Cart $cart): bool => $cart->count() >= ($metadata['context']['min_items'] ?? 0),
            ],
            default => throw new InvalidArgumentException("Unknown key: {$key}"),
        };
    }

    public function canCreateRules(string $key): bool
    {
        return in_array($key, ['min-items'], true);
    }

    public function getAvailableKeys(): array
    {
        return ['min-items'];
    }
}
```

Attach the factory to the cart before registering or restoring dynamic conditions:

```php
$cart->withRulesFactory(new DemoRulesFactory());
```

When registering a condition with a factory key, optional `metadata` will be persisted under the `context` key and supplied to the factory during restoration.

## Failure Handling

Dynamic rule execution may throw exceptions. Register an optional failure handler to observe and log issues without interrupting the cart flow:

```php
$cart->onDynamicConditionFailure(function (
    string $operation,
    ?CartCondition $condition,
    ?Throwable $exception,
    array $context
) {
    logger()->warning('Dynamic condition failure', [
        'operation' => $operation,
        'condition' => $condition?->getName(),
        'context' => $context,
        'error' => $exception?->getMessage(),
    ]);
});
```

`$operation` will be `evaluate` when rules are executed or `restore` when metadata reconstruction fails. `$context` includes the target type and item identifiers (when applicable).

## Lifecycle Summary

1. **Configure** the cart with `withRulesFactory()` to enable persistence.
2. **Register** dynamic conditions via `registerDynamicCondition()` and supply optional metadata for the factory.
3. **Mutate** the cart (add/update/remove items); dynamic rules re-evaluate automatically after each mutation.
4. **Restore** previously registered conditions by rehydrating the cart and calling `withRulesFactory()` again.
5. **Monitor** failures through `onDynamicConditionFailure()` for observability.

Following these steps keeps static and dynamic adjustments predictable, resilient, and easy to reason about across requests.
