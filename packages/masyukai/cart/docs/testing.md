# Testing Guide

Reliable carts demand automated coverage. These tips keep your test suite fast and deterministic.

## Base Test Case

The package ships with a Pest-enabled Testbench setup (`tests/TestCase.php`). It:

- Registers the service provider and core Laravel dependencies.
- Uses the in-memory SQLite connection by default.
- Configures the cart for database storage so concurrency logic can be exercised.

Import it in your package or application tests:

```php
use MasyukAI\Cart\Tests\TestCase;

uses(TestCase::class);
```

## Common Patterns

### Seeding a Cart

```php
beforeEach(function () {
    Cart::clear();
    Cart::add('sku-1', 'Notebook', 12.00, 2);
});
```

### Asserting Totals

```php
it('calculates subtotal with discounts', function () {
    Cart::addDiscount('promo', '10%');

    expect(Cart::subtotal()->getAmount())->toBe(21.6);
});
```

### Working with Instances in Tests

```php
it('separates wishlist from main cart', function () {
    Cart::setInstance('wishlist')->add('dream', 'Gadget', 99.00);

    expect(Cart::instance())->toBe('wishlist');
    expect(Cart::countItems())->toBe(1);
});
```

### Event Assertions

```php
Event::fake();

Cart::add('sku-1', 'Notebook', 12);

Event::assertDispatched(ItemAdded::class);
Event::assertDispatched(CartCreated::class);
```

## Database Driver Tips

- Run migrations via `php artisan migrate` or `Schema::create` in your test case.
- Use `RefreshDatabase` or `DatabaseTransactions` to isolate tests.
- To simulate conflicts, manipulate the `version` column manually and assert `CartConflictException` is thrown.

## Disabling Metrics in Tests

Metrics can clutter in-memory cache stores during tests. Disable them when asserting pure logic:

```php
config()->set('cart.metrics.enabled', false);
```

## Browser & Integration Tests

For end-to-end tests (Pest Browser or Dusk):

1. Seed items through HTTP routes that call the facade.
2. Assert the DOM presents totals via `->assertSee('Subtotal: $42.00')`.
3. Consider clearing (`Cart::clear()`) between scenarios to avoid data carry-over.

## Snapshotting Cart State

Use `Cart::content()` to capture a serializable snapshot for assertions or debugging:

```php
expect(Cart::content())->toMatchSnapshot();
```

This includes items, conditions, totals, and metadata in a single array.
