# Cart-Vouchers Integration Architecture

## System Overview

```
┌─────────────────────────────────────────────────────────────────────────┐
│                         Laravel Application                              │
│                                                                          │
│  ┌────────────────┐      ┌────────────────┐      ┌─────────────────┐  │
│  │   Controller   │      │   Livewire     │      │   Filament      │  │
│  │   /API Route   │      │   Component    │      │   Resource      │  │
│  └────────┬───────┘      └────────┬───────┘      └────────┬────────┘  │
│           │                       │                        │           │
│           └───────────────────────┼────────────────────────┘           │
│                                   │                                    │
└───────────────────────────────────┼────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                           Cart Facade                                    │
│                   (AIArmada\Cart\Facades\Cart)                          │
└─────────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                          Cart Manager                                    │
│                    (Proxies to Cart Instance)                           │
└─────────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                           Cart Instance                                  │
│  ┌───────────────────────────────────────────────────────────────────┐  │
│  │                       Cart Class                                   │  │
│  │  ┌──────────────────────────────────────────────────────────┐    │  │
│  │  │  Traits:                                                  │    │  │
│  │  │  • ManagesItems                                          │    │  │
│  │  │  • ManagesConditions                                     │    │  │
│  │  │  • CalculatesTotals                                      │    │  │
│  │  │  • HasVouchers ← NEW! Voucher Integration               │    │  │
│  │  └──────────────────────────────────────────────────────────┘    │  │
│  └───────────────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────────────┘
                                    │
                    ┌───────────────┴───────────────┐
                    │                               │
                    ▼                               ▼
        ┌───────────────────────┐     ┌──────────────────────────┐
        │   Cart Conditions     │     │   Voucher Methods        │
        │   (Pricing Engine)    │     │   (from HasVouchers)     │
        └───────────────────────┘     └──────────────────────────┘
                    │                               │
                    │                               │
                    ▼                               ▼
        ┌───────────────────────┐     ┌──────────────────────────┐
        │  CartCondition        │     │  applyVoucher($code)     │
        │  (Base Class)         │     │  removeVoucher($code)    │
        └───────────────────────┘     │  hasVoucher($code)       │
                    ▲                 │  getVoucherDiscount()    │
                    │                 └──────────────────────────┘
                    │                               │
                    │                               │
                    │                               ▼
        ┌───────────────────────┐     ┌──────────────────────────┐
        │  VoucherCondition     │◄────│  VoucherValidator        │
        │  (Bridges to Cart)    │     │  (Validates Voucher)     │
        └───────────────────────┘     └──────────────────────────┘
                    │                               │
                    │                               │
                    └───────────────┬───────────────┘
                                    │
                                    ▼
                    ┌───────────────────────────────┐
                    │      Voucher Package          │
                    │  ┌─────────────────────────┐  │
                    │  │  VoucherService         │  │
                    │  │  VoucherValidator       │  │
                    │  │  Voucher Model          │  │
                    │  │  VoucherUsage Model     │  │
                    │  │  VoucherData (DTO)      │  │
                    │  └─────────────────────────┘  │
                    └───────────────────────────────┘
```

## Data Flow: Applying a Voucher

```
┌─────────────────────────────────────────────────────────────────────────┐
│ 1. User Action                                                           │
│    Cart::applyVoucher('SUMMER20')                                       │
└────────────────────────────────┬────────────────────────────────────────┘
                                 │
                                 ▼
┌─────────────────────────────────────────────────────────────────────────┐
│ 2. HasVouchers::applyVoucher()                                          │
│    • Validate max vouchers limit                                        │
│    • Check if voucher already applied                                   │
│    • Call VoucherValidator::validate()                                  │
└────────────────────────────────┬────────────────────────────────────────┘
                                 │
                                 ▼
┌─────────────────────────────────────────────────────────────────────────┐
│ 3. VoucherValidator::validate()                                         │
│    • Check voucher exists                                               │
│    • Check status (active)                                              │
│    • Check dates (started, not expired)                                 │
│    • Check usage limits (global, per user)                              │
│    • Check minimum cart value                                           │
│    • Return VoucherValidationResult                                     │
└────────────────────────────────┬────────────────────────────────────────┘
                                 │
                                 ▼ (if valid)
┌─────────────────────────────────────────────────────────────────────────┐
│ 4. Create VoucherCondition                                              │
│    • Convert voucher to condition format                                │
│    • Set validation rules closure                                       │
│    • Configure target (subtotal/total)                                  │
│    • Set order (default: 50)                                            │
└────────────────────────────────┬────────────────────────────────────────┘
                                 │
                                 ▼
┌─────────────────────────────────────────────────────────────────────────┐
│ 5. Cart::addCondition()                                                 │
│    • Add VoucherCondition to cart conditions                            │
│    • Condition stored in cart data                                      │
└────────────────────────────────┬────────────────────────────────────────┘
                                 │
                                 ▼
┌─────────────────────────────────────────────────────────────────────────┐
│ 6. Dispatch Event                                                        │
│    VoucherApplied($cart, $voucher)                                      │
│    • Analytics tracking                                                 │
│    • Usage recording                                                    │
│    • Notifications                                                      │
└─────────────────────────────────────────────────────────────────────────┘
```

## Data Flow: Cart Calculation with Voucher

```
┌─────────────────────────────────────────────────────────────────────────┐
│ 1. User Action                                                           │
│    $total = Cart::total()                                               │
└────────────────────────────────┬────────────────────────────────────────┘
                                 │
                                 ▼
┌─────────────────────────────────────────────────────────────────────────┐
│ 2. CalculatesTotals::total()                                            │
│    • Calculate items subtotal                                           │
│    • Get all conditions                                                 │
│    • Sort conditions by order                                           │
└────────────────────────────────┬────────────────────────────────────────┘
                                 │
                                 ▼
┌─────────────────────────────────────────────────────────────────────────┐
│ 3. Process Each Condition                                               │
│    for each condition in conditions:                                    │
│        if condition.shouldApply():                                      │
│            value = condition.apply(value)                               │
└────────────────────────────────┬────────────────────────────────────────┘
                                 │
                                 ▼ (when VoucherCondition is reached)
┌─────────────────────────────────────────────────────────────────────────┐
│ 4. VoucherCondition::shouldApply()                                      │
│    • Check if condition is dynamic                                      │
│    • Run validation rules closure                                       │
│    • Call validateVoucher($cart, $item)                                 │
└────────────────────────────────┬────────────────────────────────────────┘
                                 │
                                 ▼
┌─────────────────────────────────────────────────────────────────────────┐
│ 5. VoucherCondition::validateVoucher()                                  │
│    • Re-validate voucher against current cart state                     │
│    • Call VoucherValidator::validate()                                  │
│    • Return true/false                                                  │
└────────────────────────────────┬────────────────────────────────────────┘
                                 │
                                 ▼ (if valid)
┌─────────────────────────────────────────────────────────────────────────┐
│ 6. VoucherCondition::apply()                                            │
│    • Parse voucher value (percentage/fixed)                             │
│    • Apply discount calculation                                         │
│    • Apply max discount cap                                             │
│    • Return modified value                                              │
└────────────────────────────────┬────────────────────────────────────────┘
                                 │
                                 ▼
┌─────────────────────────────────────────────────────────────────────────┐
│ 7. Return Total                                                          │
│    $total = $subtotal - $discount                                       │
└─────────────────────────────────────────────────────────────────────────┘
```

## Class Relationships

```
┌─────────────────────────────────────────────────────────────────────────┐
│                           Cart Package                                   │
│  ┌───────────────────────────────────────────────────────────────────┐  │
│  │  Cart                                                              │  │
│  │  ├─ uses → HasVouchers                                            │  │
│  │  ├─ uses → ManagesConditions                                      │  │
│  │  └─ uses → CalculatesTotals                                       │  │
│  └───────────────────────────────────────────────────────────────────┘  │
│                                                                          │
│  ┌───────────────────────────────────────────────────────────────────┐  │
│  │  CartCondition (Base)                                             │  │
│  │  ├─ getName(): string                                             │  │
│  │  ├─ getValue(): string|float                                      │  │
│  │  ├─ apply(float): float                                           │  │
│  │  └─ shouldApply(Cart, ?Item): bool                                │  │
│  └───────────────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────────────┘
                                    ▲
                                    │ extends
                                    │
┌─────────────────────────────────────────────────────────────────────────┐
│                        Voucher Package                                   │
│  ┌───────────────────────────────────────────────────────────────────┐  │
│  │  VoucherCondition                                                  │  │
│  │  ├─ extends → CartCondition                                       │  │
│  │  ├─ __construct(VoucherData)                                      │  │
│  │  ├─ validateVoucher(Cart, ?Item): bool                            │  │
│  │  ├─ getVoucher(): VoucherData                                     │  │
│  │  ├─ isFreeShipping(): bool                                        │  │
│  │  └─ apply(float): float (with max cap)                            │  │
│  └───────────────────────────────────────────────────────────────────┘  │
│                                                                          │
│  ┌───────────────────────────────────────────────────────────────────┐  │
│  │  HasVouchers (Trait)                                              │  │
│  │  ├─ applyVoucher(string, int): self                               │  │
│  │  ├─ removeVoucher(string): self                                   │  │
│  │  ├─ clearVouchers(): self                                         │  │
│  │  ├─ hasVoucher(?string): bool                                     │  │
│  │  ├─ getVoucherCondition(string): ?VoucherCondition                │  │
│  │  ├─ getAppliedVouchers(): array                                   │  │
│  │  ├─ getAppliedVoucherCodes(): array                               │  │
│  │  ├─ getVoucherDiscount(): float                                   │  │
│  │  ├─ canAddVoucher(): bool                                         │  │
│  │  └─ validateAppliedVouchers(): array                              │  │
│  └───────────────────────────────────────────────────────────────────┘  │
│                                                                          │
│  ┌───────────────────────────────────────────────────────────────────┐  │
│  │  VoucherValidator                                                  │  │
│  │  └─ validate(string, Cart): VoucherValidationResult               │  │
│  └───────────────────────────────────────────────────────────────────┘  │
│                                                                          │
│  ┌───────────────────────────────────────────────────────────────────┐  │
│  │  VoucherService                                                    │  │
│  │  ├─ find(string): ?VoucherData                                    │  │
│  │  ├─ create(array): VoucherData                                    │  │
│  │  ├─ recordUsage(string, Cart, ?int): void                         │  │
│  │  └─ getUsageHistory(string): Collection                           │  │
│  └───────────────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────────────┘
```

## Integration Points

```
┌─────────────────────────────────────────────────────────────────────────┐
│                      Integration Point 1                                 │
│                   VoucherCondition → CartCondition                      │
│                                                                          │
│  Purpose: Bridge voucher data into cart's condition system              │
│  Method: Extend CartCondition and implement voucher-specific logic      │
│  Benefit: Seamless integration with cart's pricing engine               │
└─────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────┐
│                      Integration Point 2                                 │
│                      HasVouchers → Cart (Trait)                         │
│                                                                          │
│  Purpose: Add voucher methods to Cart class                             │
│  Method: Trait usage for modular functionality                          │
│  Benefit: Convenient API for voucher management                         │
└─────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────┐
│                      Integration Point 3                                 │
│                     Events → Application Layer                          │
│                                                                          │
│  Purpose: Enable extensibility and custom business logic                │
│  Method: Laravel event system                                           │
│  Benefit: Decouple voucher actions from implementation details          │
└─────────────────────────────────────────────────────────────────────────┘
```

## Package Independence

```
┌─────────────────────────────────────────────────────────────────────────┐
│                          Development                                     │
│                                                                          │
│            Single Repository (Monorepo)                                 │
│  ┌──────────────────────────────────────────────────────────────────┐  │
│  │  packages/aiarmada/cart/                                          │  │
│  │  ├── packages/                                                    │  │
│  │  │   ├── core/          (AIArmada\Cart)                          │  │
│  │  │   └── vouchers/      (AIArmada\Cart\Vouchers)                 │  │
│  │  ├── composer.json                                                │  │
│  │  └── monorepo-builder.php                                         │  │
│  └──────────────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────────────┘
                                    │
                                    │ GitHub Actions (Package Splitting)
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                           Publishing                                     │
│                                                                          │
│                    Two Independent Packages                             │
│  ┌───────────────────────────┐     ┌───────────────────────────────┐  │
│  │  packagist.org/           │     │  packagist.org/               │  │
│  │  aiarmada/cart            │     │  aiarmada/cart-vouchers       │  │
│  │                           │     │                               │  │
│  │  Core cart functionality  │     │  Vouchers + Integration       │  │
│  │  Can be used standalone   │     │  Requires: aiarmada/cart ^2.0 │  │
│  └───────────────────────────┘     └───────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────────────┘
                                    │
                                    │ composer require
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                          Usage in Apps                                   │
│                                                                          │
│  Option 1: Cart Only                                                    │
│  ┌──────────────────────────────────────────────────────────────────┐  │
│  │  composer require aiarmada/cart                                   │  │
│  │  → Get core cart functionality                                    │  │
│  └──────────────────────────────────────────────────────────────────┘  │
│                                                                          │
│  Option 2: Cart + Vouchers                                              │
│  ┌──────────────────────────────────────────────────────────────────┐  │
│  │  composer require aiarmada/cart-vouchers                          │  │
│  │  → Automatically installs aiarmada/cart as dependency             │  │
│  │  → Get cart + voucher system + integration                        │  │
│  └──────────────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────────────┘
```

## Voucher Lifecycle

```
┌─────────────────────────────────────────────────────────────────────────┐
│ 1. Creation                                                              │
│    Voucher::create(['code' => 'SUMMER20', ...])                         │
│    → VoucherModel stored in database                                    │
└────────────────────────────────┬────────────────────────────────────────┘
                                 │
                                 ▼
┌─────────────────────────────────────────────────────────────────────────┐
│ 2. Application                                                           │
│    Cart::applyVoucher('SUMMER20')                                       │
│    → Validation → VoucherCondition created → Added to cart              │
└────────────────────────────────┬────────────────────────────────────────┘
                                 │
                                 ▼
┌─────────────────────────────────────────────────────────────────────────┐
│ 3. Cart Calculations                                                     │
│    Cart::total()                                                         │
│    → VoucherCondition::validateVoucher() → apply() → discount applied   │
└────────────────────────────────┬────────────────────────────────────────┘
                                 │
                                 ▼
┌─────────────────────────────────────────────────────────────────────────┐
│ 4. Cart Modifications                                                    │
│    Cart::remove($itemId) or Cart::add($product)                         │
│    → Cart::validateAppliedVouchers() → Remove if invalid                │
└────────────────────────────────┬────────────────────────────────────────┘
                                 │
                                 ▼
┌─────────────────────────────────────────────────────────────────────────┐
│ 5. Order Completion                                                      │
│    OrderPaid event → Listener                                           │
│    → Voucher::recordUsage($code, $cart, $userId)                        │
│    → VoucherUsage stored in database                                    │
└────────────────────────────────┬────────────────────────────────────────┘
                                 │
                                 ▼
┌─────────────────────────────────────────────────────────────────────────┐
│ 6. Cleanup                                                               │
│    Cart::clearVouchers() or Cart::clear()                               │
│    → Voucher conditions removed from cart                               │
└─────────────────────────────────────────────────────────────────────────┘
```

---

This architecture provides:
- ✅ Clean separation of concerns
- ✅ Type-safe integration
- ✅ Event-driven extensibility
- ✅ Independent package publishing
- ✅ Flexible configuration
- ✅ Scalable validation system
