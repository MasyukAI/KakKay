# MasyukAI Cart Coding Guidelines

These rules support AI-assisted development inside the `masyukai/cart` monorepo. Follow them before editing source, tests, or docs.

## Technology Baseline
- PHP 8.4, Laravel 12, Livewire v3, Filament v4, Octane v2, Pest v4, PHPUnit 12, Tailwind 4.
- Storage drivers: session, cache, database (with optimistic locking). Akaunting\Money handles currency.
- Traits organise cart behaviour (items, conditions, totals, metadata, storage, instances).

## Style & Syntax
- Strict types everywhere (`declare(strict_types=1);`).
- Promote constructor properties; no empty constructors.
- Use typed method signatures and return types.
- Prefer PHPDoc only for complex array shapes; avoid inline comments unless absolutely necessary.
- Follow existing naming and file placement conventions (e.g., traits in `src/Traits`, services in `src/Services`).

## Error Handling
- Throw domain exceptions (`InvalidCartItemException`, `InvalidCartConditionException`, `CartConflictException`, etc.) rather than generic ones.
- When extending storage logic, honour `StorageInterface` and maintain identifier swap support.

## Money & Conditions
- Keep monetary amounts inside `Money` instances when returning data from public APIs.
- Sanitise string prices, but never auto-convert currency.
- Respect condition ordering: item → subtotal → total.

## Events & Metrics
- Guard code paths that dispatch events with `eventsEnabled` and `$events` checks.

## Concurrency
- Database driver uses optimistic locking with version numbers; ensure new writes increment versions and throw `CartConflictException` on conflicts.
- Handle conflicts explicitly at the application level with try/catch blocks.

## Testing
- All tests use Pest. Place feature specs under `tests/Feature`, unit specs under `tests/Unit`.
- Reuse Testbench config (`tests/TestCase.php`). Clear carts between tests and flush cache when relevant.
- Add or update tests for every behavioural change and run the targeted suite.

## Tooling
- Run `vendor/bin/pint --dirty` after edits.
- Use `vendor/bin/pest` (optionally filtered) for test runs.
- Avoid adding new dependencies without approval.

## Documentation
- Docs live under `docs/`. Keep them aligned with the rewritten structure (see `docs/index.md`). Update docs when behaviours change.
- Troubleshooting and configuration sections must remain accurate for storage drivers and migration flows.

## Pull Request Ready Checklist
1. No TODOs or commented-out code.
2. Tests updated & passing locally.
3. Pint formatting applied.
4. Documentation updated when public behaviour changes.
5. Events properly guarded with config checks.
