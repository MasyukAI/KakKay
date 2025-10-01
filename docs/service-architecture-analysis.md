# Service Architecture Analysis

## Summary

Your application has **4 similar-sounding services** with overlapping responsibilities. Here's the breakdown:

---

## Services Overview

### 1. **PaymentService** (`app/Services/PaymentService.php`)
**Status**: ✅ **ACTIVELY USED**

**Purpose**: Core payment operations (general-purpose)

**Responsibilities**:
- Payment record creation with retry logic
- Payment gateway communication
- Payment status updates
- Payment queries and calculations
- Payment status checks (successful/pending/failed)

**Used By**:
- `CheckoutService` (legacy checkout flow)
- `Checkout.php` Livewire component (for payment methods)
- Multiple tests

**Verdict**: **KEEP** - This is your core payment service used across the application.

---

### 2. **CheckoutService** (`app/Services/CheckoutService.php`)
**Status**: ⚠️ **LEGACY / UNUSED IN PRODUCTION**

**Purpose**: Original checkout flow (order creation + payment processing)

**Responsibilities**:
- Complete checkout process (create user → address → order → payment)
- Cart calculations
- Shipping methods

**Used By**:
- **ONLY in tests** (CheckoutServiceImprovedTest, CheckoutServiceIntegrationTest, CheckoutServiceRefactoredTest)
- **NOT used in any controllers or Livewire components**

**Verdict**: **DEPRECATED** - This is the OLD checkout flow. It's been replaced by `EnhancedCheckoutService` but tests still reference it.

---

### 3. **EnhancedCheckoutService** (`app/Services/EnhancedCheckoutService.php`)
**Status**: ✅ **ACTIVELY USED** (Current Production System)

**Purpose**: Enhanced checkout with cart metadata-based payment intents

**Responsibilities**:
- Process checkout with payment intent validation
- Reuse valid payment intents
- Handle cart changes during checkout
- Webhook-based order creation
- Cart snapshot management

**Used By**:
- `Checkout.php` Livewire component (main checkout page)
- `ChipWebhookController` (webhook processing)
- CartPaymentIntentTest

**Verdict**: **KEEP** - This is your CURRENT checkout system using the modern cart-based payment intent approach.

---

### 4. **CartPaymentService** (`app/Services/CartPaymentService.php`)
**Status**: ✅ **ACTIVELY USED**

**Purpose**: Cart-specific payment intent management (dependency of EnhancedCheckoutService)

**Responsibilities**:
- Create payment intents in cart metadata
- Validate cart payment intents (cart version, amount, expiry)
- Clear/update payment intent status
- Webhook validation

**Used By**:
- `EnhancedCheckoutService` (injected dependency)
- CartPaymentIntentTest

**Verdict**: **KEEP** - This is a specialized service focused on cart payment intent lifecycle.

---

## Architecture Flow

```
┌─────────────────────────────────────────────────────┐
│                  PRODUCTION FLOW                     │
└─────────────────────────────────────────────────────┘

User → Checkout.php (Livewire)
           ↓
    EnhancedCheckoutService
           ↓
    CartPaymentService → Cart Metadata
           ↓
    PaymentService → Gateway
           ↓
    ChipWebhookController → EnhancedCheckoutService
           ↓
    Order Created


┌─────────────────────────────────────────────────────┐
│                   LEGACY FLOW (UNUSED)               │
└─────────────────────────────────────────────────────┘

(No longer used in production)
CheckoutService → PaymentService → Gateway
     ↓
Order Created Immediately (No Cart Intent)
```

---

## Recommendations

### 1. **Deprecate CheckoutService**
- **Action**: Mark as deprecated or remove
- **Reason**: Not used in production, only in legacy tests
- **Impact**: Low - only affects old tests

### 2. **Rename for Clarity**
Consider renaming to clarify responsibilities:

```
CartPaymentService       → CartPaymentIntentService
EnhancedCheckoutService  → CheckoutService (since it's the only one being used)
PaymentService           → PaymentService (keep as-is)
```

### 3. **Service Responsibility Matrix**

| Service | Payment Creation | Cart Intent | Order Creation | Webhook Handling |
|---------|-----------------|-------------|----------------|------------------|
| PaymentService | ✅ | ❌ | ❌ | ❌ |
| CartPaymentService | ❌ | ✅ | ❌ | ❌ |
| EnhancedCheckoutService | ❌ | ✅ | ✅ | ✅ |
| ~~CheckoutService~~ | ✅ | ❌ | ✅ | ❌ |

---

## Usage Statistics

### Production Usage:
- **PaymentService**: Used in 3 locations (Checkout.php, CheckoutService, tests)
- **EnhancedCheckoutService**: Used in 2 locations (Checkout.php, ChipWebhookController)
- **CartPaymentService**: Used in 1 location (EnhancedCheckoutService)
- **CheckoutService**: Used in 0 production locations (only tests)

---

## Migration Path

If you want to clean up:

### Phase 1: Mark as Deprecated
```php
/**
 * @deprecated Use EnhancedCheckoutService instead
 */
class CheckoutService
```

### Phase 2: Update Tests
- Migrate tests from CheckoutService to EnhancedCheckoutService
- Remove CheckoutServiceImprovedTest, CheckoutServiceIntegrationTest, CheckoutServiceRefactoredTest
- Keep only CartPaymentIntentTest

### Phase 3: Remove
- Delete CheckoutService.php
- Update any documentation references

---

## Conclusion

You have **ONE legacy service (CheckoutService)** that can be removed after migrating tests. The other three services are all actively used and serve distinct purposes:

1. **PaymentService** - Core payment operations
2. **CartPaymentService** - Cart payment intent lifecycle
3. **EnhancedCheckoutService** - Orchestrates checkout with cart intents

The naming confusion comes from having both "CheckoutService" (legacy) and "EnhancedCheckoutService" (current). Consider renaming EnhancedCheckoutService to CheckoutService after removing the legacy one.
