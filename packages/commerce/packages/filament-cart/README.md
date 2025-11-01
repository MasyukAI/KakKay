# Filament Cart Plugin

Filament Cart brings the aiarmada/cart ecosystem into Filament with blazing-fast, normalized cart data, a delightful admin experience, and dynamic pricing insights built for high-volume commerce teams.

<p align="center">
    <strong>Laravel&nbsp;12 · Filament&nbsp;4 · aiarmada/cart · Tailwind&nbsp;4</strong>
</p>

---

## At a glance

| Why teams love it | What you get |
| --- | --- |
| 🧭 Clear visibility | Normalized `Cart`, `CartItem`, and `CartCondition` resources with instant search |
| ⚡ Operates at scale | Event-driven sync keeps data fresh without expensive JSON queries |
| 🧰 Built for builders | Dynamic condition tooling, analytics-ready tables, and extensible actions |

> � Looking for the Filament admin tour? Jump to the [resources guide](docs/filament-cart.md).

---

## Quick start

```bash
composer require aiarmada/filament-cart
```

Register the plugin with your Filament panel (Laravel 12+, Filament 4+, and the aiarmada/cart package are required):

```php
<?php

namespace App\Providers\Filament;

use Filament\Panel;
use Filament\PanelProvider;
use AIArmada\FilamentCart\FilamentCart;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            // ...your existing configuration
            ->plugins([
                FilamentCart::make(),
            ]);
    }
}
```

That’s it—no additional migration or configuration steps. The plugin auto-discovers the normalized cart tables provided by aiarmada/cart.

---

## Feature highlights

### 1. Normalized cart intelligence
- Dedicated `Cart`, `CartItem`, and `CartCondition` records for analytics-grade querying.
- Up to 100× faster lookups compared to searching JSON payloads.
- Rich filtering by instance (default, wishlist, quote, etc.), price range, quantities, and condition type.

### 2. Filament-native admin surfaces
- Purpose-built resources for carts, items, and conditions with polished tables, filters, and widgets.
- Bulk actions for clear, export, and housekeeping flows.
- Real-time synchronization powered by aiarmada/cart events—no manual syncing required.

### 3. Dynamic pricing that understands your rules
- Global conditions with rule-based application and auto-removal via `registerDynamicCondition()`.
- Per-item rule support: `min_items` counts distinct items, while `item_quantity` and `item_price` evaluate individual items.
- Snapshot history for auditing which incentives were active at any moment.

### 4. Operations & insights
- Dashboard widget summarizing total carts, active carts, item counts, and total value.
- Instant drill-down from any cart to its underlying items and conditions.
- Export-ready tables for BI teams with zero additional modeling.

---

## Configuration & customization

Filament Cart follows Filament’s extension patterns:

- Extend resources or widgets in your own namespace to add actions, metrics, or custom filters.
- Tailor navigation groups, icons, or localization strings via standard Filament hooks.
- Use the normalized models in your own tooling (reports, notifications, scheduled jobs) without touching cart storage internals.

Need extra guidance? Explore the [resources guide](docs/filament-cart.md) for route summaries, column layouts, and permissions.

---

## Working with dynamic conditions

The plugin surfaces the full power of aiarmada/cart’s dynamic pricing engine:

```php
use AIArmada\Cart\Facades\Cart;
use AIArmada\Cart\Conditions\CartCondition;

$condition = CartCondition::fromArray([
    'name' => 'free-shipping',
    'type' => 'shipping',
    'target' => 'total',
    'value' => '-1000',
    'rules' => [
        'min_total' => '10000',
        'min_items' => '3',
    ],
]);

Cart::registerDynamicCondition($condition); // auto-applies and auto-removes as the cart changes
```

- `registerDynamicCondition()` keeps rule logic in sync on every cart update.
- Item-level rules evaluate per item; `min_items` counts distinct items rather than quantity.
- Snapshot records allow customer support to answer “which discount applied?” with confidence.

---

## Quality gates

We ship with a full Pest test suite and Pint formatting profile. Before opening a pull request:

```bash
vendor/bin/pint --dirty
vendor/bin/pest --parallel
```

CI mirrors these checks to keep the plugin production-ready.

---

## Contributing & support

Pull requests are welcome! Please:

1. Open an issue describing the enhancement or bug.
2. Keep documentation changes alongside behavior changes.
3. Include targeted tests for any observable behavior shift.

Questions or ideas? Start a discussion or ping us via GitHub Issues.

---

## License

This package is open-sourced software licensed under the [MIT license](LICENSE.md).