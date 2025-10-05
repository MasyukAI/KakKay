# MasyukAI Cart Documentation

MasyukAI Cart is a multi-channel shopping cart engine for Laravel 12 built around robust storage drivers, accurate money calculations, and powerful conditions. These docs guide you from installation to advanced tuning.

## Feature Highlights

- **First-class storage drivers** for session, cache, and database (with concurrency-safe versioning).
- **Composable cart instances** per user, channel, or purpose with automatic identifier resolution.
- **Powerful pricing engine** supporting dynamic cart- and item-level conditions, taxes, fees, and shipping rules.
## Why This Cart?

- **Flexible storage** with session, cache, or database (with optimistic locking for concurrent checkouts).
- **Money precision** via [akaunting/money](https://github.com/akaunting/money)—no floating‑point pitfalls.
- **Production-ready extras** including seamless guest → user migration and built-in validation.
- **Extensible architecture** supporting conditions, events, and multiple cart instances.

## Documentation Roadmap

| Topic | When to read |
| --- | --- |
| [Getting Started](getting-started.md) | Install the package and add your first cart items. |
| [Cart Operations](cart-operations.md) | Learn every day-to-day API for items, totals, and metadata. |
| [Conditions & Discounts](conditions.md) | Model discounts, taxes, shipping, and dynamic rules. |
| [Configuration Reference](configuration.md) | Understand every configuration flag and how to tweak it. |
| [Storage Drivers](storage.md) | Choose between session, cache, or database and see how they differ. |
| [Identifiers & Migration](identifiers-and-migration.md) | Control how carts follow users across sessions and logins. |
| [Events](events.md) | Hook into lifecycle events across items, metadata, and merges. |
| [Concurrency](concurrency-and-retry.md) | Handle conflicts and optimistic locking. |
| [Money & Currency](money-and-currency.md) | Discover how totals are calculated with Akaunting Money. |
| [Testing Guide](testing.md) | Reproduce cart scenarios with Pest and Testbench. |
| [Security Checklist](security.md) | Enforce limits and guard sensitive data. |
| [Troubleshooting](troubleshooting.md) | Resolve the most common integration surprises. |
| [API Reference](api-reference.md) | Quick lookup for facades, services, and console commands. |
| [Recipes & Examples](examples.md) | Copy‑ready snippets for popular scenarios. |

Need a crash course? Start with **Getting Started → Cart Operations**, then skim the topics that match your deployment needs.
