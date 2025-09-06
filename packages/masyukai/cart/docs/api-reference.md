# 📋 API Reference

Complete reference for all MasyukAI Cart classes, methods, and interfaces.

## 🚀 Core Classes

### Cart Manager

The main entry point for all cart operations.

```php
class CartManager
{
    // Instance Management
    public function instance(string $name = 'default'): CartInstance
    public function getInstances(): array
    public function destroyInstance(string $name): bool
    
    // Direct Cart Operations (uses default instance)
    public function add(string $id, string $name, float $price, int $quantity = 1, array $attributes = []): CartItem
    public function update(string $itemId, array $data): bool
    public function remove(string $itemId): bool
    public function clear(): bool
    
    // Content & Information
    public function content(): CartContent
    public function getItems(): Collection
    public function get(string $itemId): ?CartItem
    public function count(): int
    public function quantity(): int
    public function isEmpty(): bool
    
    // Pricing & Totals
    public function subtotal(): float
    public function total(): float
    public function getConditions(): Collection
    
    // Search & Filter
    public function search(callable $callback): Collection
    public function filter(callable $callback): Collection
    
    // Storage Operations
    public function store(string $identifier): bool
    public function restore(string $identifier): bool
    public function forget(string $identifier): bool
    
    // Utility Methods
    public function toArray(): array
    public function toJson(): string
}
```

### Cart Instance

Individual cart instance with full functionality.

```php
class CartInstance
{
    // Item Management
    public function add(string $id, string $name, float $price, int $quantity = 1, array $attributes = []): CartItem
    public function update(string $itemId, array $data): bool
    public function remove(string $itemId): bool
    public function clear(): bool
    
    // Content Access
    public function content(): CartContent
    public function getItems(): Collection
    public function get(string $itemId): ?CartItem
    public function has(string $itemId): bool
    
    // Quantities & Counts
    public function count(): int
    public function quantity(): int
    public function countItems(): int
    public function totalQuantity(): int
    public function isEmpty(): bool
    
    // Pricing
    public function subtotal(bool $formatted = false): float|string
    public function total(bool $formatted = false): float|string
    public function tax(bool $formatted = false): float|string
    public function discount(bool $formatted = false): float|string
    
    // Conditions Management
    public function addCondition(CartConditionInterface $condition): self
    public function addDiscount(string $name, string $value, array $attributes = []): self
    public function addTax(string $name, string $value, array $attributes = []): self
    public function addFee(string $name, string $value, array $attributes = []): self
    public function removeCondition(string|array $names): self
    public function clearConditions(): self
    public function getConditions(): Collection
    public function getCondition(string $name): ?CartConditionInterface
    public function hasCondition(string $name): bool
    
    // Search & Filter
    public function search(callable $callback): Collection
    public function filter(callable $callback): Collection
    public function where(string $key, mixed $value): Collection
    public function whereIn(string $key, array $values): Collection
    public function whereNotIn(string $key, array $values): Collection
    
    // Instance Operations
    public function merge(string $fromInstance, string $strategy = 'add_quantities'): bool
    public function copy(string $toInstance): bool
    public function duplicate(string $newInstance): bool
    
    // Storage
    public function store(string $identifier): bool
    public function restore(string $identifier): bool
    public function forget(string $identifier): bool
    
    // Events
    public function onItemAdded(callable $callback): self
    public function onItemUpdated(callable $callback): self
    public function onItemRemoved(callable $callback): self
    public function onCartCleared(callable $callback): self
    
    // Serialization
    public function toArray(): array
    public function toJson(): string
    public function __toString(): string
}
```

### Cart Item

Individual item within a cart.

```php
class CartItem
{
    // Properties (read-only)
    public readonly string $id;
    public readonly string $name;
    public readonly float $price;
    public readonly int $quantity;
    public readonly Collection $attributes;
    
    // Constructors
    public function __construct(string $id, string $name, float $price, int $quantity, array $attributes = [])
    public static function make(string $id, string $name, float $price, int $quantity, array $attributes = []): self
    
    // Price Calculations
    public function getPriceSum(): float
    public function getFormattedPrice(): string
    public function getFormattedPriceSum(): string
    
    // Attribute Management
    public function getAttribute(string $key, mixed $default = null): mixed
    public function hasAttribute(string $key): bool
    public function getAttributes(): Collection
    public function setAttribute(string $key, mixed $value): self
    public function setAttributes(array $attributes): self
    public function removeAttribute(string $key): self
    
    // Quantity Management
    public function setQuantity(int $quantity): self
    public function addQuantity(int $quantity): self
    public function subtractQuantity(int $quantity): self
    
    // Conditions (Item-level)
    public function addCondition(CartConditionInterface $condition): self
    public function removeCondition(string $name): self
    public function getConditions(): Collection
    public function hasCondition(string $name): bool
    public function clearConditions(): self
    
    // Validation
    public function isValid(): bool
    public function validate(): array
    
    // Comparison
    public function equals(CartItem $item): bool
    public function isSimilar(CartItem $item): bool
    
    // Serialization
    public function toArray(): array
    public function toJson(): string
    public function __toString(): string
}
```

### Cart Content

Container for cart data with metadata.

```php
class CartContent
{
    // Properties
    public readonly string $instance;
    public readonly Collection $items;
    public readonly Collection $conditions;
    public readonly array $metadata;
    
    // Totals
    public function subtotal(): float
    public function total(): float
    public function tax(): float
    public function discount(): float
    public function count(): int
    public function quantity(): int
    public function isEmpty(): bool
    
    // Item Access
    public function getItems(): Collection
    public function getItem(string $id): ?CartItem
    public function hasItem(string $id): bool
    
    // Conditions
    public function getConditions(): Collection
    public function getCondition(string $name): ?CartConditionInterface
    public function hasCondition(string $name): bool
    
    // Metadata
    public function getMetadata(): array
    public function getMeta(string $key, mixed $default = null): mixed
    public function setMeta(string $key, mixed $value): self
    
    // Search
    public function search(callable $callback): Collection
    public function filter(callable $callback): Collection
    
    // Serialization
    public function toArray(): array
    public function toJson(): string
}
```

---

## 🔧 Condition System

### Cart Condition Interface

```php
interface CartConditionInterface
{
    // Basic Properties
    public function getName(): string
    public function getType(): string
    public function getTarget(): string
    public function getValue(): string
    public function getOrder(): int
    
    // Attributes
    public function getAttributes(): array
    public function getAttribute(string $key, mixed $default = null): mixed
    public function setAttribute(string $key, mixed $value): self
    
    // Calculation
    public function getCalculatedValue(float $targetValue): float
    public function apply(float $targetValue): float
    
    // Validation
    public function isValid(): bool
    public function canApply(float $targetValue): bool
    
    // Serialization
    public function toArray(): array
}
```

### Cart Condition Implementation

```php
class CartCondition implements CartConditionInterface
{
    // Constructor
    public function __construct(
        string $name,
        string $type,
        string $target,
        string $value,
        array $attributes = [],
        int $order = 0
    )
    
    // Static Constructors
    public static function discount(string $name, string $value, array $attributes = []): self
    public static function tax(string $name, string $value, array $attributes = []): self
    public static function fee(string $name, string $value, array $attributes = []): self
    
    // Value Parsing
    public function isPercentage(): bool
    public function isFixed(): bool
    public function getNumericValue(): float
    
    // Calculation Methods
    public function calculatePercentage(float $targetValue): float
    public function calculateFixed(float $targetValue): float
    public function getCalculatedValue(float $targetValue): float
    public function apply(float $targetValue): float
    
    // Validation
    public function isValid(): bool
    public function canApply(float $targetValue): bool
    public function validate(): array
    
    // Comparison
    public function equals(CartConditionInterface $condition): bool
    
    // Utility
    public function getDisplayValue(): string
    public function getDescription(): string
    public function isActive(): bool
    
    // Serialization
    public function toArray(): array
    public function toJson(): string
    public function __toString(): string
}
```

### Condition Builder

```php
class ConditionBuilder
{
    // Fluent Interface
    public function name(string $name): self
    public function type(string $type): self
    public function target(string $target): self
    public function value(string $value): self
    public function order(int $order): self
    public function attributes(array $attributes): self
    public function attribute(string $key, mixed $value): self
    
    // Quick Builders
    public function discount(string $name, string $value): self
    public function tax(string $name, string $value): self
    public function fee(string $name, string $value): self
    
    // Conditional Logic
    public function when(callable $condition, callable $callback): self
    public function unless(callable $condition, callable $callback): self
    
    // Validation Rules
    public function minValue(float $min): self
    public function maxValue(float $max): self
    public function validUntil(string $date): self
    public function validFrom(string $date): self
    
    // Build
    public function build(): CartCondition
    public function apply(CartInstance $cart): CartInstance
}
```

---

## 📦 Storage System

### Storage Interface

```php
interface CartStorageInterface
{
    // Basic Operations
    public function store(string $identifier, array $data): bool
    public function restore(string $identifier): ?array
    public function forget(string $identifier): bool
    public function exists(string $identifier): bool
    
    // Batch Operations
    public function storeMultiple(array $data): bool
    public function forgetMultiple(array $identifiers): bool
    public function getAll(): array
    
    // Cleanup
    public function flush(): bool
    public function cleanup(): int
    
    // Information
    public function count(): int
    public function size(): int
    public function getKeys(): array
}
```

### Session Storage

```php
class SessionCartStorage implements CartStorageInterface
{
    // Configuration
    public function __construct(string $keyPrefix = 'cart_')
    
    // Storage Operations
    public function store(string $identifier, array $data): bool
    public function restore(string $identifier): ?array
    public function forget(string $identifier): bool
    public function exists(string $identifier): bool
    
    // Session Management
    public function getSessionKey(string $identifier): string
    public function regenerateSession(): bool
}
```

### Database Storage

```php
class DatabaseCartStorage implements CartStorageInterface
{
    // Configuration
    public function __construct(
        string $table = 'cart_storage',
        string $connection = null
    )
    
    // Storage Operations
    public function store(string $identifier, array $data): bool
    public function restore(string $identifier): ?array
    public function forget(string $identifier): bool
    public function exists(string $identifier): bool
    
    // Database Queries
    public function getQuery(): Builder
    public function cleanup(): int
    public function deleteExpired(): int
    
    // User Association
    public function storeForUser(int $userId, string $instance, array $data): bool
    public function restoreForUser(int $userId, string $instance): ?array
    public function forgetForUser(int $userId, string $instance = null): bool
}
```

### File Storage

```php
class FileCartStorage implements CartStorageInterface
{
    // Configuration
    public function __construct(
        string $path = null,
        string $extension = '.json'
    )
    
    // Storage Operations
    public function store(string $identifier, array $data): bool
    public function restore(string $identifier): ?array
    public function forget(string $identifier): bool
    public function exists(string $identifier): bool
    
    // File Management
    public function getFilePath(string $identifier): string
    public function ensureDirectory(): bool
    public function cleanup(): int
}
```

---

## 🎣 Event System

### Event Dispatcher Interface

```php
interface CartEventDispatcherInterface
{
    // Event Registration
    public function listen(string $event, callable $listener): void
    public function subscribe(object $subscriber): void
    
    // Event Dispatch
    public function dispatch(string $event, array $payload = []): void
    public function fire(string $event, array $payload = []): void
    
    // Event Management
    public function hasListeners(string $event): bool
    public function getListeners(string $event): array
    public function forget(string $event): void
    public function flush(): void
}
```

### Available Events

```php
// Item Events
'cart.item.adding'     // Before item is added
'cart.item.added'      // After item is added
'cart.item.updating'   // Before item is updated
'cart.item.updated'    // After item is updated
'cart.item.removing'   // Before item is removed
'cart.item.removed'    // After item is removed

// Cart Events
'cart.clearing'        // Before cart is cleared
'cart.cleared'         // After cart is cleared
'cart.storing'         // Before cart is stored
'cart.stored'          // After cart is stored
'cart.restoring'       // Before cart is restored
'cart.restored'        // After cart is restored

// Condition Events
'cart.condition.adding'    // Before condition is added
'cart.condition.added'     // After condition is added
'cart.condition.removing'  // Before condition is removed
'cart.condition.removed'   // After condition is removed

// Instance Events
'cart.instance.created'    // When new instance is created
'cart.instance.destroyed'  // When instance is destroyed
'cart.instance.switched'   // When switching between instances
```

### Event Payload Examples

```php
// Item Added Event
[
    'instance' => 'default',
    'item' => CartItem $item,
    'cart' => CartInstance $cart,
    'metadata' => ['user_id' => 123, 'timestamp' => '2024-01-01 12:00:00']
]

// Condition Added Event
[
    'instance' => 'default',
    'condition' => CartCondition $condition,
    'cart' => CartInstance $cart,
    'metadata' => ['applied_by' => 'system']
]

// Cart Cleared Event
[
    'instance' => 'default',
    'items_count' => 5,
    'previous_total' => 199.99,
    'metadata' => ['reason' => 'checkout_completed']
]
```

---

## 🔄 Migration System

### Cart Migrator

```php
class CartMigrator
{
    // Migration Operations
    public function migrate(string $fromStorage, string $toStorage): bool
    public function migrateInstance(string $instance, string $fromStorage, string $toStorage): bool
    public function migrateUser(int $userId, string $fromStorage, string $toStorage): bool
    
    // Batch Migration
    public function migrateBatch(array $identifiers, string $fromStorage, string $toStorage): array
    public function migrateAll(string $fromStorage, string $toStorage): array
    
    // Validation
    public function validateMigration(string $fromStorage, string $toStorage): bool
    public function testMigration(string $identifier, string $fromStorage, string $toStorage): bool
    
    // Progress Tracking
    public function getMigrationProgress(): array
    public function setProgressCallback(callable $callback): self
    
    // Rollback
    public function rollbackMigration(string $migrationId): bool
    public function createBackup(string $storage): string
    public function restoreBackup(string $backupId): bool
}
```

### Migration Events

```php
// Migration Events
'cart.migration.starting'   // Before migration starts
'cart.migration.progress'   // During migration (with progress data)
'cart.migration.completed'  // After migration completes
'cart.migration.failed'     // If migration fails
'cart.migration.rollback'   // During rollback operation
```

---

## 🧪 Testing Utilities

### Cart Test Helper

```php
class CartTestHelper
{
    // Setup Helpers
    public static function createEmptyCart(string $instance = 'default'): CartInstance
    public static function createCartWithItems(array $items, string $instance = 'default'): CartInstance
    public static function createSampleItem(array $overrides = []): CartItem
    
    // Assertion Helpers
    public static function assertCartHasItem(CartInstance $cart, string $itemId): void
    public static function assertCartItemCount(CartInstance $cart, int $count): void
    public static function assertCartTotal(CartInstance $cart, float $total, float $delta = 0.01): void
    public static function assertCartSubtotal(CartInstance $cart, float $subtotal, float $delta = 0.01): void
    public static function assertCartIsEmpty(CartInstance $cart): void
    public static function assertCartIsNotEmpty(CartInstance $cart): void
    
    // Condition Assertions
    public static function assertCartHasCondition(CartInstance $cart, string $conditionName): void
    public static function assertConditionValue(CartInstance $cart, string $conditionName, float $value): void
    
    // Mock Helpers
    public static function mockStorage(): MockInterface
    public static function mockEventDispatcher(): MockInterface
    public static function mockCondition(string $name, string $type, string $value): MockInterface
    
    // Data Generators
    public static function generateRandomItems(int $count = 5): array
    public static function generateConditions(int $count = 3): array
}
```

### Test Traits

```php
trait InteractsWithCart
{
    // Setup
    protected function setUpCart(): void
    protected function tearDownCart(): void
    
    // Helper Methods
    protected function addItemToCart(array $itemData = [], string $instance = 'default'): CartItem
    protected function createCartCondition(array $conditionData = []): CartCondition
    protected function clearAllCarts(): void
    
    // Assertions
    protected function assertCartEquals(CartInstance $expected, CartInstance $actual): void
    protected function assertItemEquals(CartItem $expected, CartItem $actual): void
}

trait MocksCartDependencies
{
    // Mock Setup
    protected function mockCartStorage(): MockInterface
    protected function mockEventDispatcher(): MockInterface
    protected function mockSessionDriver(): MockInterface
    
    // Mock Expectations
    protected function expectStorageStore(string $identifier, array $data): void
    protected function expectStorageRestore(string $identifier, array $data = null): void
    protected function expectEventDispatch(string $event, array $payload = []): void
}
```

---

## 🏗️ Service Providers & Configuration

### Cart Service Provider

```php
class CartServiceProvider extends ServiceProvider
{
    // Registration
    public function register(): void
    {
        $this->registerCartManager();
        $this->registerStorageDrivers();
        $this->registerEventDispatcher();
        $this->registerMigrator();
    }
    
    // Booting
    public function boot(): void
    {
        $this->publishConfiguration();
        $this->publishMigrations();
        $this->registerCommands();
        $this->registerValidationRules();
    }
    
    // Configuration
    public function provides(): array
    {
        return [
            'cart',
            'cart.manager',
            'cart.storage',
            'cart.events',
            'cart.migrator'
        ];
    }
}
```

### Configuration Options

```php
// config/cart.php
return [
    // Default storage driver
    'default_storage' => env('CART_STORAGE', 'session'),
    
    // Storage drivers configuration
    'storage' => [
        'session' => [
            'driver' => 'session',
            'key_prefix' => 'cart_',
        ],
        'database' => [
            'driver' => 'database',
            'connection' => env('CART_DB_CONNECTION', 'default'),
            'table' => 'cart_storage',
        ],
        'file' => [
            'driver' => 'file',
            'path' => storage_path('framework/cart'),
            'extension' => '.json',
        ],
        'redis' => [
            'driver' => 'redis',
            'connection' => env('CART_REDIS_CONNECTION', 'default'),
            'key_prefix' => 'cart:',
        ],
    ],
    
    // Default instance name
    'default_instance' => 'default',
    
    // Event system
    'events' => [
        'enabled' => true,
        'dispatcher' => 'default', // or 'sync', 'queue'
    ],
    
    // Formatting
    'format' => [
        'currency' => 'USD',
        'decimal_places' => 2,
        'decimal_separator' => '.',
        'thousands_separator' => ',',
        'currency_symbol' => '$',
        'currency_position' => 'before', // 'before' or 'after'
    ],
    
    // Validation
    'validation' => [
        'max_quantity' => 999,
        'max_items' => 100,
        'max_price' => 999999.99,
        'min_price' => 0.01,
    ],
    
    // Cleanup
    'cleanup' => [
        'enabled' => true,
        'max_age_days' => 30,
        'run_probability' => 2, // 2% chance to run cleanup
    ],
];
```

This comprehensive API reference covers all public methods, interfaces, and configuration options available in the MasyukAI Cart package. Use this as your complete guide for implementation and integration.
- [Facades](#facades)
- [Events](#events)
- [Exceptions](#exceptions)

## Cart Class

The main cart class providing all cart functionality.

### Constructor

```php
public function __construct(
    private StorageInterface $storage,
    private ?Dispatcher $events = null,
    string $instanceName = 'default',
    private bool $eventsEnabled = true,
    private array $config = []
): void
```

**Example:**
```php
use MasyukAI\Cart\Cart;
use MasyukAI\Cart\Storage\SessionStorage;

$cart = new Cart(
    storage: new SessionStorage(session()->driver()),
    events: app('events'),
    instanceName: 'my-cart',
    eventsEnabled: true,
    config: ['decimals' => 2]
);
```

### Instance Management

#### `instance(): string`
Get the current instance name.

```php
$name = Cart::instance(); // Returns 'default'
```

#### `setInstance(string $name): static`
Create a new cart instance.

```php
$wishlist = Cart::setInstance('wishlist');
$comparison = Cart::setInstance('comparison');
```

#### `getCurrentInstance(): string`  
Alias for `instance()` method.

```php
$current = Cart::getCurrentInstance();
```

### Adding Items

#### `add(string|array $id, ?string $name = null, float|string|null $price = null, int $quantity = 1, array $attributes = [], array|CartCondition|null $conditions = null, string|object|null $associatedModel = null): CartItem|CartCollection`

Add item(s) to the cart.

**Parameters:**
- `$id` - Item ID or array of items for bulk add
- `$name` - Item name (required for single items)
- `$price` - Item price (supports string with commas)
- `$quantity` - Quantity (default: 1, must be positive integer)
- `$attributes` - Additional attributes array
- `$conditions` - Item-specific conditions
- `$associatedModel` - Associated Eloquent model or class name

**Single Item Examples:**

```php
// Basic item
$item = Cart::add('iphone-15', 'iPhone 15 Pro', 999.99, 1);

// Item with attributes
$item = Cart::add('shirt-123', 'Cotton T-Shirt', 29.99, 2, [
    'size' => 'L',
    'color' => 'Navy Blue',
    'material' => 'Cotton'
]);

// Item with conditions
use MasyukAI\Cart\Conditions\CartCondition;

$discount = new CartCondition('member-discount', 'discount', 'price', '-10%');
$item = Cart::add('book-456', 'Laravel Guide', 49.99, 1, [], [$discount]);

// Item with model association
$product = Product::find(123);
$item = Cart::add('product-123', $product->name, $product->price, 1, [], null, $product);

// String prices with commas
$item = Cart::add('luxury-item', 'Diamond Ring', '12,999.99', 1);
```

**Multiple Items Example:**

```php
$items = Cart::add([
    [
        'id' => 'product-1',
        'name' => 'iPhone 15',
        'price' => 999.99,
        'quantity' => 1,
        'attributes' => ['color' => 'Blue']
    ],
    [
        'id' => 'product-2', 
        'name' => 'iPad Pro',
        'price' => 799.99,
        'quantity' => 2,
        'attributes' => ['storage' => '256GB']
    ]
]);

// Returns CartCollection of added items
```

**Error Handling:**

```php
try {
    Cart::add('', 'Invalid Product', 100); // Throws InvalidCartItemException
} catch (InvalidCartItemException $e) {
    // Handle invalid item data
}

try {
    Cart::add('product-1', 'Test', -50); // Throws InvalidCartItemException  
} catch (InvalidCartItemException $e) {
    // Handle negative price
}
```

### Removing Items

#### `remove(string $id): ?CartItem`
Remove item from cart.

```php
$removedItem = Cart::remove('product-1');
```

#### `clear(): bool`
Remove all items from cart.

```php
Cart::clear();
```

### Cart Information

#### `isEmpty(): bool`
Check if cart is empty.

```php
if (Cart::isEmpty()) {
    echo 'Cart is empty';
}
```

#### `count(): int`
Get total number of unique items.

```php
$itemCount = Cart::count();
```

#### `getTotalQuantity(): int`
Get total quantity of all items.

```php
$totalQuantity = Cart::getTotalQuantity();
```

#### `getSubTotal(): float`
Get cart subtotal (before conditions).

```php
$subtotal = Cart::subtotal();
```

#### `getSubTotalWithConditions(): float`
Get subtotal with item-level conditions applied.

```php
$subtotalWithConditions = Cart::subtotalWithConditions();
```

#### `getTotal(): float`
Get final total with all conditions applied.

```php
$total = Cart::total();
```

### Conditions Management

#### `addCondition(CartCondition|array $condition): static`
Add condition(s) to cart.

```php
$tax = new CartCondition('tax', 'tax', 'subtotal', '+10%');
Cart::addCondition($tax);

// Multiple conditions
Cart::addCondition([$tax, $shipping, $discount]);
```

#### `getConditions(): CartConditionCollection`
Get all cart conditions.

```php
$conditions = Cart::getConditions();
```

#### `getCondition(string $name): ?CartCondition`
Get specific condition by name.

```php
$tax = Cart::getCondition('tax');
```

#### `removeCondition(string $name): bool`
Remove condition by name.

```php
Cart::removeCondition('old-discount');
```

#### `clearConditions(): bool`
Remove all cart conditions.

```php
Cart::clearConditions();
```

### Item Conditions

#### `addItemCondition(string $itemId, CartCondition $condition): bool`
Add condition to specific item.

```php
$discount = new CartCondition('vip', 'discount', 'price', '-20%');
Cart::addItemCondition('product-1', $discount);
```

#### `removeItemCondition(string $itemId, string $conditionName): bool`
Remove condition from item.

```php
Cart::removeItemCondition('product-1', 'vip');
```

#### `clearItemConditions(string $itemId): bool`
Remove all conditions from item.

```php
Cart::clearItemConditions('product-1');
```

### Data Export

#### `content(): array`
Get complete cart content including items, conditions, totals, and metadata.

```php
$cartContent = Cart::content();
// Returns:
// [
//     'instance' => 'default',
//     'items' => [...], // Array of cart items
//     'conditions' => [...], // Array of cart conditions  
//     'subtotal' => 150.00,
//     'subtotal_with_conditions' => 145.00,
//     'total' => 158.25,
//     'quantity' => 5,
//     'count' => 3,
//     'is_empty' => false
// ]
```

#### `getContent(): array`
Alias for `content()` method.

```php
$cartContent = Cart::getContent();
```

#### `getItems(): CartCollection`
Get cart items only.

```php
$items = Cart::getItems();

foreach ($items as $item) {
    echo $item->name;
}
```

#### `getConditions(): CartConditionCollection`
Get cart conditions only.

```php
$conditions = Cart::getConditions();

foreach ($conditions as $condition) {
    echo $condition->getName();
}
```

#### `toArray(): array`
Convert cart to array (alias for `content()`).

```php
$cartData = Cart::toArray();
```

#### `getCurrentInstance(): string`
Get current instance name.

```php
$instance = Cart::getCurrentInstance();
```

## CartItem Class

### Properties

All properties are readonly:

```php
public readonly string $id;
public readonly string $name; 
public readonly float $price;
public readonly int $quantity;
public readonly CartConditionCollection $conditions;
public readonly Collection $attributes;
public readonly string|object|null $associatedModel;
```

### Methods

#### `getPriceSum(): float`
Get total price (price × quantity).

```php
$total = $item->getPriceSum();
```

#### `getPriceWithConditions(): float`
Get single unit price with conditions applied.

```php
$priceWithConditions = $item->getPriceWithConditions();
```

#### `getPriceSumWithConditions(): float`
Get total price with conditions applied.

```php
$totalWithConditions = $item->getPriceSumWithConditions();
```

#### `getDiscountAmount(): float`
Get total discount amount applied.

```php
$discount = $item->getDiscountAmount();
```

#### `addCondition(CartCondition $condition): static`
Add condition to item.

```php
$discount = new CartCondition('sale', 'discount', 'price', '-10%');
$newItem = $item->addCondition($discount);
```

#### `removeCondition(string $name): static`
Remove condition from item.

```php
$newItem = $item->removeCondition('old-discount');
```

#### `clearConditions(): static`
Remove all conditions from item.

```php
$newItem = $item->clearConditions();
```

#### `setQuantity(int $quantity): static`
Set item quantity.

```php
$newItem = $item->setQuantity(5);
```

#### `setAttributes(array $attributes): static`
Set item attributes.

```php
$newItem = $item->setAttributes(['color' => 'Red', 'size' => 'XL']);
```

#### `addAttribute(string $key, mixed $value): static`
Add single attribute.

```php
$newItem = $item->addAttribute('warranty', '2 years');
```

#### `removeAttribute(string $key): static`
Remove attribute.

```php
$newItem = $item->removeAttribute('old_attr');
```

#### `getAttribute(string $key, mixed $default = null): mixed`
Get attribute value.

```php
$color = $item->getAttribute('color', 'Default');
```

#### `hasAttribute(string $key): bool`
Check if attribute exists.

```php
if ($item->hasAttribute('warranty')) {
    // Has warranty
}
```

#### `isAssociatedWith(string $modelClass): bool`
Check if associated with specific model class.

```php
if ($item->isAssociatedWith(Product::class)) {
    // Associated with Product model
}
```

#### `toArray(): array`
Convert item to array.

```php
$itemData = $item->toArray();
```

## CartCondition Class

### Constructor

```php
public function __construct(
    private string $name,
    private string $type,
    private string $target,
    private string $value,
    private array $attributes = [],
    private int $order = 0
)
```

### Methods

#### `getName(): string`
Get condition name.

```php
$name = $condition->getName();
```

#### `getType(): string`
Get condition type.

```php
$type = $condition->getType();
```

#### `getTarget(): string`
Get condition target.

```php
$target = $condition->getTarget();
```

#### `getValue(): string`
Get condition value.

```php
$value = $condition->getValue();
```

#### `getOrder(): int`
Get condition order.

```php
$order = $condition->getOrder();
```

#### `getAttributes(): array`
Get all attributes.

```php
$attributes = $condition->getAttributes();
```

#### `getAttribute(string $key, mixed $default = null): mixed`
Get specific attribute.

```php
$description = $condition->getAttribute('description', 'No description');
```

#### `apply(float $value): float`
Apply condition to a value.

```php
$result = $condition->apply(100.0);
```

#### `isDiscount(): bool`
Check if condition is a discount.

```php
if ($condition->isDiscount()) {
    echo 'This is a discount';
}
```

#### `isCharge(): bool`
Check if condition is a charge.

```php
if ($condition->isCharge()) {
    echo 'This is a charge';
}
```

#### `toArray(): array`
Convert condition to array.

```php
$conditionData = $condition->toArray();
```

#### `fromArray(array $data): static`
Create condition from array.

```php
$condition = CartCondition::fromArray([
    'name' => 'tax',
    'type' => 'tax',
    'target' => 'subtotal',
    'value' => '+10%'
]);
```

## CartCollection Class

Extends Laravel's Collection with cart-specific methods.

### Methods

#### `sum(callable|string|null $callback = null): float`
Calculate sum with optional callback.

```php
$total = $items->sum(fn($item) => $item->getPriceSum());
```

#### `totalQuantity(): int`
Get total quantity of all items.

```php
$totalQty = $items->totalQuantity();
```

#### `findById(string $id): ?CartItem`
Find item by ID.

```php
$item = $items->findById('product-1');
```

#### `removeById(string $id): static`
Remove item by ID.

```php
$newCollection = $items->removeById('product-1');
```

## CartConditionCollection Class

Extends Laravel's Collection with condition-specific methods.

### Methods

#### `getByType(string $type): static`
Get conditions by type.

```php
$discounts = $conditions->getByType('discount');
```

#### `getByTarget(string $target): static`
Get conditions by target.

```php
$subtotalConditions = $conditions->getByTarget('subtotal');
```

#### `getDiscounts(): static`
Get only discount conditions.

```php
$discounts = $conditions->getDiscounts();
```

#### `getCharges(): static`
Get only charge conditions.

```php
$charges = $conditions->getCharges();
```

#### `apply(float $value): float`
Apply all conditions to a value.

```php
$result = $conditions->apply(100.0);
```

#### `getSummary(): array`
Get conditions summary.

```php
$summary = $conditions->getSummary();
/*
Returns:
[
    'total_discount' => 25.50,
    'total_charge' => 8.99,
    'net_effect' => -16.51,
    'count' => 3
]
*/
```

#### `groupByType(): Collection`
Group conditions by type.

```php
$grouped = $conditions->groupByType();
```

## Storage Interfaces

### StorageInterface

```php
interface StorageInterface
{
    public function get(string $key): mixed;
    public function put(string $key, mixed $value): void;
    public function has(string $key): bool;
    public function forget(string $key): void;
    public function flush(): void;
}
```

### SessionStorage

```php
public function __construct(
    private Session $session,
    private string $keyPrefix = 'cart'
)
```

### CacheStorage

```php
public function __construct(
    private Repository $cache,
    private string $keyPrefix = 'cart',
    private int $ttl = 86400
)
```

### DatabaseStorage

```php
public function __construct(
    private ConnectionInterface $database,
    private string $table = 'cart_storage'
)
```

## Exceptions

### InvalidCartItemException
Thrown when invalid item data is provided.

### InvalidCartConditionException
Thrown when invalid condition data is provided.

### UnknownModelException
Thrown when associated model cannot be resolved.

## Events

All events are in the `MasyukAI\Cart\Events` namespace:

- `CartCreated($cart, $instanceName)`
- `CartUpdated($cart, $instanceName, $action)`
- `CartCleared($cart, $instanceName, $previousCount)`
- `ItemAdded($cart, $item, $instanceName)`
- `ItemUpdated($cart, $item, $previousItem, $instanceName, $changes)`
- `ItemRemoved($cart, $item, $instanceName)`

## Facade Methods

The `Cart` facade provides access to all Cart class methods:

```php
use MasyukAI\Cart\Facades\Cart;

// All methods available on Cart class
Cart::add('id', 'name', 99.99);
Cart::total();
Cart::addCondition($taxCondition);
// etc.
```

---

## Real-World Usage Examples

### Complete E-commerce Checkout Flow

```php
class CheckoutService
{
    public function processOrder(User $user, array $shippingAddress): Order
    {
        // Get user's cart
        $cart = Cart::instance("user_{$user->id}");
        
        if ($cart->isEmpty()) {
            throw new EmptyCartException('Cart is empty');
        }
        
        // Apply user-specific discounts
        if ($user->isVip()) {
            Cart::addDiscount('vip-discount', '15%');
        }
        
        // Calculate shipping based on address
        $shippingCost = $this->calculateShipping($cart, $shippingAddress);
        Cart::addFee('shipping', $shippingCost);
        
        // Apply taxes based on location
        $taxRate = $this->getTaxRate($shippingAddress['state']);
        Cart::addTax('sales-tax', "{$taxRate}%");
        
        // Create order
        $order = Order::create([
            'user_id' => $user->id,
            'subtotal' => $cart->subtotal(),
            'tax_amount' => $cart->getConditions()->getByType('tax')->sum(fn($c) => $c->apply($cart->subtotal())),
            'shipping_amount' => $shippingCost,
            'total' => $cart->total(),
            'items_count' => $cart->count(),
        ]);
        
        // Create order items
        foreach ($cart->getItems() as $item) {
            $order->items()->create([
                'product_id' => $item->id,
                'name' => $item->name,
                'price' => $item->price,
                'quantity' => $item->quantity,
                'attributes' => $item->attributes->toArray(),
                'total' => $item->getPriceSumWithConditions(),
            ]);
        }
        
        // Clear cart after successful order
        $cart->clear();
        
        return $order;
    }
}
```

### Advanced Inventory Management

```php
class CartInventoryManager
{
    public function validateAndUpdateCart(string $instanceId): array
    {
        $cart = Cart::instance($instanceId);
        $issues = [];
        
        foreach ($cart->getItems() as $item) {
            $product = Product::find($item->id);
            
            if (!$product) {
                // Product no longer exists
                $cart->remove($item->id);
                $issues[] = "Product '{$item->name}' is no longer available";
                continue;
            }
            
            if ($product->stock < $item->quantity) {
                // Insufficient stock
                if ($product->stock > 0) {
                    $cart->update($item->id, [
                        'quantity' => ['operator' => '=', 'value' => $product->stock]
                    ]);
                    $issues[] = "Only {$product->stock} units of '{$item->name}' available. Quantity updated.";
                } else {
                    $cart->remove($item->id);
                    $issues[] = "'{$item->name}' is out of stock and removed from cart";
                }
            }
            
            if ($product->price !== $item->price) {
                // Price changed
                $cart->update($item->id, ['price' => $product->price]);
                $issues[] = "Price of '{$item->name}' updated to \${$product->price}";
            }
        }
        
        return $issues;
    }
    
    public function reserveInventory(string $instanceId, int $minutes = 15): void
    {
        $cart = Cart::instance($instanceId);
        $reservationId = Str::uuid();
        
        foreach ($cart->getItems() as $item) {
            InventoryReservation::create([
                'product_id' => $item->id,
                'quantity' => $item->quantity,
                'reservation_id' => $reservationId,
                'expires_at' => now()->addMinutes($minutes),
                'cart_instance' => $instanceId,
            ]);
        }
        
        // Store reservation ID in cart for later reference
        $cart->setAttribute('reservation_id', $reservationId);
    }
}
```

### Multi-Currency Support

```php
class CurrencyCartManager
{
    protected string $defaultCurrency = 'USD';
    
    public function convertCartCurrency(string $fromCurrency, string $toCurrency): void
    {
        $rate = $this->getExchangeRate($fromCurrency, $toCurrency);
        
        foreach (Cart::getItems() as $item) {
            $newPrice = $item->price * $rate;
            Cart::update($item->id, ['price' => $newPrice]);
        }
        
        // Update condition values if they use fixed amounts
        foreach (Cart::getConditions() as $condition) {
            if ($this->isFixedAmount($condition->getValue())) {
                $currentValue = (float) str_replace(['$', '+', '-'], '', $condition->getValue());
                $newValue = $currentValue * $rate;
                $prefix = str_starts_with($condition->getValue(), '-') ? '-' : '+';
                
                Cart::removeCondition($condition->getName());
                Cart::addCondition(new CartCondition(
                    $condition->getName(),
                    $condition->getType(),
                    $condition->getTarget(),
                    "{$prefix}{$newValue}"
                ));
            }
        }
        
        Cart::setAttribute('currency', $toCurrency);
    }
    
    protected function isFixedAmount(string $value): bool
    {
        return !str_contains($value, '%');
    }
    
    protected function getExchangeRate(string $from, string $to): float
    {
        // Integration with currency API
        return app(CurrencyService::class)->getRate($from, $to);
    }
}
```

### Subscription Cart

```php
class SubscriptionCartManager
{
    public function addSubscriptionItem(
        string $planId,
        array $options = []
    ): void {
        $plan = SubscriptionPlan::findOrFail($planId);
        
        // Remove existing subscription items (only one subscription at a time)
        $this->clearSubscriptionItems();
        
        $attributes = [
            'type' => 'subscription',
            'billing_cycle' => $plan->billing_cycle,
            'trial_days' => $plan->trial_days,
            'cancel_at_period_end' => $options['cancel_at_period_end'] ?? false,
        ];
        
        if ($options['addons'] ?? []) {
            foreach ($options['addons'] as $addonId) {
                $addon = PlanAddon::findOrFail($addonId);
                $attributes['addons'][] = [
                    'id' => $addon->id,
                    'name' => $addon->name,
                    'price' => $addon->price,
                ];
            }
        }
        
        Cart::add(
            "subscription_{$plan->id}",
            $plan->name,
            $plan->price,
            1,
            $attributes
        );
        
        // Add setup fee if applicable
        if ($plan->setup_fee > 0) {
            Cart::add(
                "setup_fee_{$plan->id}",
                "Setup Fee - {$plan->name}",
                $plan->setup_fee,
                1,
                ['type' => 'setup_fee', 'one_time' => true]
            );
        }
    }
    
    public function calculateRecurringTotal(): float
    {
        return Cart::getItems()
            ->reject(fn($item) => $item->getAttribute('one_time'))
            ->sum(fn($item) => $item->getPriceSumWithConditions());
    }
    
    public function calculateOneTimeTotal(): float
    {
        return Cart::getItems()
            ->filter(fn($item) => $item->getAttribute('one_time'))
            ->sum(fn($item) => $item->getPriceSumWithConditions());
    }
    
    protected function clearSubscriptionItems(): void
    {
        $subscriptionItems = Cart::search(fn($item) => 
            $item->getAttribute('type') === 'subscription'
        );
        
        foreach ($subscriptionItems as $item) {
            Cart::remove($item->id);
        }
    }
}
```

### Bundle Products

```php
class BundleCartManager
{
    public function addBundle(ProductBundle $bundle, array $selectedVariants = []): void
    {
        $bundleId = "bundle_{$bundle->id}";
        
        // Calculate bundle discount
        $originalPrice = $bundle->products->sum('price');
        $bundlePrice = $bundle->price;
        $discountAmount = $originalPrice - $bundlePrice;
        
        // Add bundle as single item
        Cart::add(
            $bundleId,
            $bundle->name,
            $bundlePrice,
            1,
            [
                'type' => 'bundle',
                'original_price' => $originalPrice,
                'discount_amount' => $discountAmount,
                'products' => $bundle->products->map(function($product) use ($selectedVariants) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'price' => $product->price,
                        'variant' => $selectedVariants[$product->id] ?? null,
                    ];
                })->toArray(),
            ]
        );
        
        // Remove individual products if they exist
        foreach ($bundle->products as $product) {
            if (Cart::has($product->id)) {
                Cart::remove($product->id);
            }
        }
    }
    
    public function splitBundle(string $bundleId): void
    {
        $bundleItem = Cart::get($bundleId);
        
        if (!$bundleItem || $bundleItem->getAttribute('type') !== 'bundle') {
            throw new InvalidOperationException('Item is not a bundle');
        }
        
        // Add individual products
        foreach ($bundleItem->getAttribute('products') as $product) {
            Cart::add(
                $product['id'],
                $product['name'],
                $product['price'],
                1,
                $product['variant'] ? ['variant' => $product['variant']] : []
            );
        }
        
        // Remove bundle
        Cart::remove($bundleId);
    }
}
```

### Cart Analytics

```php
class CartAnalytics
{
    public function trackAbandonmentRisk(string $instanceId): float
    {
        $cart = Cart::instance($instanceId);
        $riskScore = 0;
        
        // High value cart (lower abandonment risk)
        if ($cart->total() > 500) {
            $riskScore -= 10;
        } elseif ($cart->total() < 50) {
            $riskScore += 15;
        }
        
        // Many items (higher commitment)
        if ($cart->countItems() > 5) {
            $riskScore -= 5;
        } elseif ($cart->countItems() === 1) {
            $riskScore += 10;
        }
        
        // Time since last update
        $lastUpdate = $cart->getLastUpdated();
        $hoursSinceUpdate = $lastUpdate ? $lastUpdate->diffInHours(now()) : 0;
        
        if ($hoursSinceUpdate > 24) {
            $riskScore += 20;
        } elseif ($hoursSinceUpdate > 6) {
            $riskScore += 10;
        }
        
        // Shipping cost impact
        $shippingConditions = $cart->getConditions()->getByType('shipping');
        if ($shippingConditions->isNotEmpty()) {
            $shippingCost = $shippingConditions->sum(fn($c) => $c->apply($cart->subtotal()));
            $shippingPercentage = $shippingCost / $cart->subtotal() * 100;
            
            if ($shippingPercentage > 15) {
                $riskScore += 15; // High shipping costs increase abandonment
            }
        }
        
        return min(100, max(0, $riskScore)); // Clamp between 0-100
    }
    
    public function getConversionMetrics(): array
    {
        return [
            'carts_created_today' => $this->getCartsCreatedToday(),
            'carts_converted_today' => $this->getCartsConvertedToday(),
            'average_cart_value' => $this->getAverageCartValue(),
            'abandonment_rate' => $this->getAbandonmentRate(),
            'popular_combinations' => $this->getPopularProductCombinations(),
        ];
    }
    
    protected function getPopularProductCombinations(): Collection
    {
        // Analyze cart contents to find frequently bought together items
        return DB::table('cart_analytics')
            ->select(DB::raw('product_combinations, COUNT(*) as frequency'))
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('product_combinations')
            ->orderBy('frequency', 'desc')
            ->limit(10)
            ->get();
    }
}
```

---

## Error Handling Best Practices

### Graceful Error Recovery

```php
class RobustCartManager
{
    public function safeAddToCart(string $productId, int $quantity): array
    {
        try {
            $product = Product::findOrFail($productId);
            
            // Validate stock
            if ($product->stock < $quantity) {
                return [
                    'success' => false,
                    'message' => 'Insufficient stock available',
                    'available_stock' => $product->stock,
                ];
            }
            
            // Add to cart
            Cart::add(
                $product->id,
                $product->name,
                $product->price,
                $quantity
            );
            
            return [
                'success' => true,
                'message' => 'Product added to cart successfully',
                'cart_count' => Cart::count(),
                'cart_total' => Cart::total(),
            ];
            
        } catch (ModelNotFoundException $e) {
            return [
                'success' => false,
                'message' => 'Product not found',
            ];
        } catch (InvalidCartItemException $e) {
            return [
                'success' => false,
                'message' => 'Invalid product data',
                'details' => $e->getMessage(),
            ];
        } catch (Exception $e) {
            Log::error('Cart operation failed', [
                'product_id' => $productId,
                'quantity' => $quantity,
                'error' => $e->getMessage(),
            ]);
            
            return [
                'success' => false,
                'message' => 'Unable to add product to cart. Please try again.',
            ];
        }
    }
}
```

## Testing Your Cart Implementation

### Unit Test Example

```php
class CartTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cart::clear(); // Start with clean cart
    }
    
    public function test_can_add_products_to_cart(): void
    {
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'price' => 99.99,
        ]);
        
        Cart::add($product->id, $product->name, $product->price, 2);
        
        $this->assertEquals(2, Cart::count());
        $this->assertEquals(199.98, Cart::total());
        $this->assertTrue(Cart::has($product->id));
    }
    
    public function test_applies_conditions_correctly(): void
    {
        Cart::add('product-1', 'Product 1', 100, 1);
        
        // Add 10% discount
        Cart::addDiscount('sale', '10%');
        
        $this->assertEquals(100, Cart::subtotal());
        $this->assertEquals(90, Cart::total());
    }
    
    public function test_handles_multiple_instances(): void
    {
        Cart::add('product-1', 'Product 1', 50, 1);
        
        $wishlist = Cart::instance('wishlist');
        $wishlist->add('product-2', 'Product 2', 75, 1);
        
        $this->assertEquals(50, Cart::total());
        $this->assertEquals(75, $wishlist->total());
    }
}
```

This comprehensive API reference provides everything developers need to effectively use the MasyukAI Cart package without diving into the source code! 🚀
