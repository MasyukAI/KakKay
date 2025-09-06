# Advanced Features Specification

This document outlines the design and implementation plan for advanced cart features that would enhance the MasyukAI Cart package's capabilities for enterprise and complex e-commerce scenarios.

## Overview

The current cart package provides a solid foundation with immutable design, condition stacking, events, and comprehensive testing. The following advanced features would extend its capabilities for sophisticated e-commerce requirements:

1. **Cart Expiration & Abandonment**
2. **Multi-Currency Support**
3. **Cart Merging & Migration**
4. **Advanced Coupon System**
5. **Inventory Integration**
6. **Cart Persistence & Recovery**
7. **Advanced Pricing Rules**
8. **Cart Sharing & Collaboration**

## 1. Cart Expiration & Abandonment

### Current State
- Basic cart persistence through storage backends
- No built-in expiration or abandonment tracking

### Proposed Implementation

#### 1.1 Cart Expiration
```php
// Configuration
'cart' => [
    'expiration' => [
        'enabled' => true,
        'ttl' => 604800, // 7 days in seconds
        'cleanup_interval' => 86400, // Daily cleanup
        'extend_on_activity' => true,
    ],
];

// Usage
Cart::setExpiration(now()->addDays(7));
Cart::extendExpiration(now()->addDays(3));
$expiresAt = Cart::getExpiration();

// Events
Event::listen(CartExpired::class, function (CartExpired $event) {
    // Handle expired cart
});
```

#### 1.2 Abandonment Tracking
```php
// Service Class
class CartAbandonmentService
{
    public function trackAbandonment(string $cartId): void;
    public function resetTimer(string $cartId): void;
    public function getAbandonedCarts(int $hours = 2): Collection;
    public function sendRecoveryEmail(string $cartId): void;
}

// Configuration
'abandonment' => [
    'enabled' => true,
    'trigger_after' => 7200, // 2 hours
    'recovery_attempts' => 3,
    'recovery_intervals' => [2, 24, 72], // hours
];

// Events
Event::listen(CartAbandoned::class, function (CartAbandoned $event) {
    SendAbandonmentEmail::dispatch($event->cart)->delay(now()->addHours(2));
});
```

#### 1.3 Implementation Components
- `CartExpirationService` - Handles expiration logic
- `CartAbandonmentTracker` - Tracks abandonment patterns
- `AbandonmentEmailService` - Recovery email campaigns
- Database migrations for tracking tables
- Scheduled commands for cleanup and triggers

## 2. Multi-Currency Support

### Current State
- Price formatting supports currency symbols but no currency conversion
- Single currency per cart instance

### Proposed Implementation

#### 2.1 Currency Management
```php
// Currency Value Object
readonly class Currency
{
    public function __construct(
        public string $code,        // USD, EUR, GBP
        public string $symbol,      // $, €, £
        public int $decimals,       // 2, 0, 3
        public string $separator,   // ., ,
        public string $thousands,   // ,, ., space
    ) {}
}

// Multi-currency cart
Cart::setCurrency('EUR');
Cart::addWithCurrency('product', 'Name', 99.99, 1, 'USD');
Cart::convertTo('GBP'); // Convert entire cart
Cart::getTotal('USD'); // Get total in specific currency
```

#### 2.2 Exchange Rate Integration
```php
interface ExchangeRateProvider
{
    public function getRate(string $from, string $to): float;
    public function getRates(string $base): array;
    public function getTimestamp(): Carbon;
}

// Implementations
class FixerExchangeRateProvider implements ExchangeRateProvider;
class CentralBankExchangeRateProvider implements ExchangeRateProvider;
class CachedExchangeRateProvider implements ExchangeRateProvider;

// Configuration
'currencies' => [
    'default' => 'USD',
    'supported' => ['USD', 'EUR', 'GBP', 'JPY'],
    'exchange_provider' => 'fixer',
    'cache_duration' => 3600,
    'round_precision' => 2,
];
```

#### 2.3 Implementation Components
- `CurrencyService` - Currency conversion and formatting
- `ExchangeRateManager` - Rate fetching and caching
- `MultiCurrencyCartItem` - Extended cart item with currency support
- Database tables for currency rates and cache
- Integration with external rate providers

## 3. Advanced Cart Merging & Migration

### Current State
- Basic cart merging in `CartMigrationService`
- Limited conflict resolution

### Proposed Implementation

#### 3.1 Smart Merging Strategies
```php
enum MergeStrategy: string
{
    case REPLACE = 'replace';           // Replace target with source
    case ADD_QUANTITIES = 'add';        // Add quantities together
    case KEEP_HIGHEST = 'highest';      // Keep highest quantity
    case PROMPT_USER = 'prompt';        // Ask user to resolve
    case PRESERVE_BOTH = 'preserve';    // Keep as separate items
}

// Advanced merging
Cart::instance('guest')->mergeInto('user', [
    'strategy' => MergeStrategy::ADD_QUANTITIES,
    'condition_handling' => 'preserve_user',
    'conflict_resolution' => 'prompt',
    'preserve_metadata' => true,
]);
```

#### 3.2 Migration Scenarios
```php
class CartMigrationService
{
    // Guest to authenticated user
    public function migrateGuestToUser(string $guestId, User $user): void;
    
    // Cross-device synchronization
    public function syncAcrossDevices(User $user): void;
    
    // Account merging (when accounts are merged)
    public function mergeUserAccounts(User $primary, User $secondary): void;
    
    // Platform migration (moving between systems)
    public function exportCart(string $cartId): array;
    public function importCart(array $data, string $targetInstance): void;
}
```

#### 3.3 Implementation Components
- Enhanced `CartMigrationService` with conflict resolution
- `MergeConflictResolver` for handling item conflicts
- Migration events and logging
- User interface components for conflict resolution

## 4. Advanced Coupon System

### Current State
- Basic discount conditions
- Simple percentage and fixed amount discounts

### Proposed Implementation

#### 4.1 Coupon Types & Rules
```php
class CouponRule
{
    public function __construct(
        public string $type,                    // percentage, fixed, bogo, tiered
        public mixed $value,                    // 10%, $50, buy:2,get:1
        public ?array $constraints = null,      // minimum amount, specific items
        public ?Carbon $validFrom = null,
        public ?Carbon $validUntil = null,
        public ?int $usageLimit = null,
        public ?int $usagePerCustomer = null,
    ) {}
}

// Complex coupon examples
$bogo = new CouponRule('bogo', ['buy' => 2, 'get' => 1, 'discount' => '50%']);
$tiered = new CouponRule('tiered', [
    ['min' => 100, 'discount' => '10%'],
    ['min' => 200, 'discount' => '15%'],
    ['min' => 500, 'discount' => '20%'],
]);
```

#### 4.2 Coupon Validation Engine
```php
class CouponValidator
{
    public function validate(string $code, Cart $cart, ?User $user = null): CouponValidationResult;
    public function canApply(Coupon $coupon, Cart $cart): bool;
    public function getConstraintViolations(Coupon $coupon, Cart $cart): array;
}

// Validation constraints
class CouponConstraints
{
    public static function minimumAmount(float $amount): Closure;
    public static function specificItems(array $itemIds): Closure;
    public static function customerSegment(string $segment): Closure;
    public static function firstTimeCustomer(): Closure;
    public static function dayOfWeek(array $days): Closure;
}
```

#### 4.3 Implementation Components
- `Coupon` model with complex rule definitions
- `CouponEngine` for validation and application
- `CouponUsageTracker` for usage limits and analytics
- Integration with existing condition system

## 5. Inventory Integration

### Current State
- No inventory awareness
- No stock validation

### Proposed Implementation

#### 5.1 Inventory Service Integration
```php
interface InventoryServiceInterface
{
    public function checkAvailability(string $itemId, int $quantity): bool;
    public function reserve(string $itemId, int $quantity, string $cartId): void;
    public function release(string $itemId, int $quantity, string $cartId): void;
    public function getStock(string $itemId): int;
    public function isInStock(string $itemId): bool;
}

// Cart integration
Cart::add('product', 'Name', 99.99, 5); // Automatically reserves 5 units
Cart::remove('product'); // Automatically releases reservation

// Configuration
'inventory' => [
    'enabled' => true,
    'auto_reserve' => true,
    'reservation_ttl' => 1800, // 30 minutes
    'allow_backorder' => false,
    'low_stock_threshold' => 10,
];
```

#### 5.2 Stock Validation
```php
class StockValidator
{
    public function validateCart(Cart $cart): StockValidationResult;
    public function getOutOfStockItems(Cart $cart): Collection;
    public function suggestAlternatives(string $itemId): Collection;
}

// Events
Event::listen(ItemOutOfStock::class, function (ItemOutOfStock $event) {
    NotifyCustomer::dispatch($event->cart, $event->item);
    SuggestAlternatives::dispatch($event->cart, $event->item);
});
```

#### 5.3 Implementation Components
- `InventoryService` with multiple backend adapters
- `StockReservationManager` for temporary holds
- `InventoryEventHandler` for cart-inventory synchronization
- Background jobs for reservation cleanup

## 6. Cart Persistence & Recovery

### Current State
- Session-based persistence
- Basic storage interfaces

### Proposed Implementation

#### 6.1 Advanced Persistence Options
```php
// Persistent cart across devices and sessions
Cart::makePersistent(); // Saves to database with user association
Cart::loadPersistent(User $user); // Load user's persistent cart

// Cart snapshots for recovery
Cart::createSnapshot('before_checkout');
Cart::restoreSnapshot('before_checkout');
Cart::listSnapshots(); // Get all snapshots for user

// Cloud synchronization
Cart::syncToCloud(); // Sync to external storage (Redis, S3, etc.)
Cart::loadFromCloud(); // Load from cloud storage
```

#### 6.2 Recovery Mechanisms
```php
class CartRecoveryService
{
    // Session recovery after timeout
    public function recoverFromSession(string $sessionId): ?Cart;
    
    // Browser crash recovery
    public function saveRecoveryPoint(Cart $cart): string;
    public function getRecoveryPoints(User $user): Collection;
    
    // Cross-device recovery
    public function getDeviceCarts(User $user): Collection;
    public function mergeDeviceCarts(User $user): Cart;
}
```

#### 6.3 Implementation Components
- Enhanced storage backends with versioning
- `CartSnapshotManager` for state preservation
- `RecoveryService` for cart restoration
- Background cleanup of old snapshots

## 7. Advanced Pricing Rules

### Current State
- Basic condition system for discounts, taxes, fees
- Simple percentage and fixed amount calculations

### Proposed Implementation

#### 7.1 Dynamic Pricing Engine
```php
class PricingRule
{
    public function __construct(
        public string $name,
        public Closure $condition,      // When to apply
        public Closure $calculator,     // How to calculate
        public int $priority = 0,       // Execution order
        public array $constraints = [], // Additional constraints
    ) {}
}

// Example rules
$bulkDiscount = new PricingRule(
    name: 'bulk_discount',
    condition: fn(CartItem $item) => $item->quantity >= 10,
    calculator: fn(CartItem $item) => $item->getRawPrice() * 0.9,
    priority: 100
);

$loyaltyPricing = new PricingRule(
    name: 'loyalty_pricing',
    condition: fn(CartItem $item, User $user) => $user->isVip(),
    calculator: fn(CartItem $item) => $item->getRawPrice() * 0.95,
    priority: 200
);
```

#### 7.2 Time-Based Pricing
```php
// Flash sales, happy hours, seasonal pricing
class TimePricingRule extends PricingRule
{
    public function __construct(
        string $name,
        Carbon $startTime,
        Carbon $endTime,
        Closure $calculator,
        array $constraints = []
    ) {}
}

// Demand-based pricing
class DemandPricingRule extends PricingRule
{
    public function calculatePrice(CartItem $item): float
    {
        $demand = $this->getDemandLevel($item->id);
        return $item->getRawPrice() * (1 + ($demand * 0.1));
    }
}
```

#### 7.3 Implementation Components
- `PricingEngine` for rule evaluation and application
- `RuleRepository` for storing and managing pricing rules
- `PriceCalculationService` with rule chaining
- Admin interface for rule management

## 8. Cart Sharing & Collaboration

### Current State
- Single-user cart ownership
- No sharing capabilities

### Proposed Implementation

#### 8.1 Cart Sharing
```php
// Share cart with others
$shareLink = Cart::createShareLink(['expires' => now()->addDays(7)]);
Cart::shareWith(['user@example.com'], 'Please review these items');

// Collaborative carts (family, team purchases)
$collaborativeCart = Cart::makeCollaborative([
    'permissions' => ['add', 'remove', 'edit'],
    'approval_required' => true,
    'budget_limit' => 1000.00,
]);

// Wishlist sharing
$wishlist = Cart::instance('wishlist')->share([
    'public' => true,
    'allow_purchases' => true,
    'gift_mode' => true,
]);
```

#### 8.2 Approval Workflows
```php
class CartApprovalWorkflow
{
    public function requireApproval(Cart $cart, array $approvers): void;
    public function approve(string $cartId, User $approver): void;
    public function reject(string $cartId, User $approver, string $reason): void;
    public function getApprovalStatus(string $cartId): ApprovalStatus;
}

// Events
Event::listen(CartApprovalRequested::class, function ($event) {
    foreach ($event->approvers as $approver) {
        Mail::to($approver)->send(new CartApprovalRequest($event->cart));
    }
});
```

#### 8.3 Implementation Components
- `CartSharingService` for share link generation and management
- `CollaborativeCartManager` for multi-user cart operations
- `ApprovalWorkflowEngine` for approval processes
- Permission system for cart operations

## Implementation Priority & Roadmap

### Phase 1: Foundation (v2.1)
1. **Cart Expiration & Abandonment** - Essential for production use
2. **Enhanced Cart Merging** - Improves user experience
3. **Inventory Integration** - Critical for stock management

### Phase 2: Commerce Enhancement (v2.2)
4. **Advanced Coupon System** - Competitive feature requirement
5. **Multi-Currency Support** - International market expansion
6. **Advanced Persistence** - Reliability and recovery

### Phase 3: Enterprise Features (v2.3)
7. **Advanced Pricing Rules** - Complex business requirements
8. **Cart Sharing & Collaboration** - Team and family use cases

## Technical Considerations

### Performance Impact
- **Caching Strategy**: Extensive caching for exchange rates, inventory, pricing rules
- **Database Optimization**: Proper indexing for cart queries and lookups
- **Queue Processing**: Background processing for heavy operations
- **Memory Management**: Efficient handling of large cart datasets

### Scalability
- **Horizontal Scaling**: Support for distributed cart storage
- **Microservice Architecture**: Separate services for inventory, pricing, currencies
- **API Rate Limiting**: Protection against abuse in public-facing features
- **Event Sourcing**: Consider event sourcing for complex audit requirements

### Security
- **Share Link Security**: Secure tokens with expiration and access controls
- **Permission System**: Fine-grained permissions for collaborative features
- **Data Privacy**: GDPR compliance for user data and cart information
- **Audit Logging**: Comprehensive logging for security and compliance

### Backward Compatibility
- **Migration Strategy**: Smooth upgrades from current version
- **Feature Flags**: Gradual rollout of new features
- **API Versioning**: Maintain compatibility with existing integrations
- **Configuration Options**: Disable features for simpler use cases

## Configuration Example

```php
// config/cart.php
return [
    'advanced_features' => [
        'expiration' => [
            'enabled' => true,
            'default_ttl' => 604800, // 7 days
        ],
        'abandonment' => [
            'enabled' => true,
            'trigger_after' => 7200, // 2 hours
        ],
        'multi_currency' => [
            'enabled' => false,
            'default' => 'USD',
            'provider' => 'fixer',
        ],
        'inventory' => [
            'enabled' => false,
            'auto_reserve' => true,
            'provider' => 'internal',
        ],
        'coupons' => [
            'enabled' => true,
            'complex_rules' => true,
        ],
        'sharing' => [
            'enabled' => false,
            'public_sharing' => false,
        ],
        'pricing_rules' => [
            'enabled' => false,
            'dynamic_pricing' => false,
        ],
    ],
];
```

This specification provides a comprehensive roadmap for extending the MasyukAI Cart package with enterprise-grade features while maintaining the current architecture's integrity and performance characteristics.