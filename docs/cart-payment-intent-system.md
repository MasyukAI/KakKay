# Cart-Based Payment Intent System

## Overview

This implementation provides a robust cart-metadata-based payment intent system that addresses cart modifications during the checkout process. The system leverages the MasyukAI Cart's database persistence and metadata storage capabilities.

## Key Components

### 1. PaymentService (`app/Services/PaymentService.php`)

**Purpose**: Manages all payment operations including cart payment intent lifecycle

**Key Methods**:

#### Payment Record Management
- `createPaymentWithRetry()` - Creates payment with retry logic
- `processPayment()` - Processes payment with gateway
- `updatePaymentStatus()` - Updates payment status
- `getPaymentByReference()` - Gets payment by reference
- `getPaymentsByOrderId()` - Gets payments by order

#### Cart Payment Intent Methods
- `createPaymentIntent()` - Creates payment intent and stores in cart metadata
- `validateCartPaymentIntent()` - Validates if cart matches stored payment intent
- `clearPaymentIntent()` - Removes payment intent from cart
- `updatePaymentIntentStatus()` - Updates payment intent status
- `validatePaymentWebhook()` - Validates webhook data against cart intent

**Features**:
- Cart version tracking for change detection
- Payment intent expiration handling
- Amount and cart content validation
- Webhook validation

### 2. CheckoutService (`app/Services/CheckoutService.php`)

**Purpose**: Orchestrates the checkout flow using cart-based payment intents

**Key Features**:
- Reuses valid existing payment intents
- Creates orders only after payment verification
- Handles cart changes by clearing invalid intents
- Webhook-based order creation from cart snapshots

### 3. Updated Checkout Component (`app/Livewire/Checkout.php`)

**Enhanced Features**:
- Cart change detection and warnings
- Payment intent reuse messaging
- Integration with checkout service

### 4. Enhanced Webhook Controller (`app/Http/Controllers/ChipWebhookController.php`)

**New Features**:
- Order creation from cart payment intents
- Fallback to existing payment record handling
- Cart metadata validation

## Architecture Benefits

### 1. **Database Persistence**
- Cart data survives browser restarts and session timeouts
- Works across multiple browser tabs and devices
- No data loss from session management issues

### 2. **Cart Metadata Storage**
- Payment intent data travels with the cart
- No need for separate payment intent storage
- Clean API through cart metadata methods

### 3. **Cart Change Detection**
- Hash-based cart content validation
- Automatic invalidation of outdated payment intents
- Prevention of payment/cart mismatches

### 4. **Cross-Tab Support**
- Multiple browser tabs can access same cart
- Payment intent status shared across sessions
- Consistent user experience

## Payment Intent Lifecycle

```
1. User initiates checkout
2. System checks for existing valid payment intent
   ├─ Valid intent found → Reuse existing payment URL
   └─ No valid intent → Create new payment intent
3. Payment intent stored in cart metadata with:
   ├─ Cart snapshot
   ├─ Cart hash for validation
   ├─ Payment details
   └─ Expiration time
4. User completes payment on gateway
5. Webhook received with payment confirmation
6. System validates payment against cart intent
7. Order created from cart snapshot
8. Cart cleared after successful order
```

## Cart Change Handling

### Scenarios Handled:
- **Cart modifications after payment intent creation**
- **Multiple browser tabs modifying same cart**
- **Payment intent expiration**
- **Amount changes due to cart updates**

### Resolution Strategy:
- **Strict validation**: Create new intent if cart changes
- **User notification**: Show warnings about cart changes
- **Automatic cleanup**: Clear invalid/expired intents

## Testing

Comprehensive test suite covers:
- Cart metadata storage and retrieval
- Payment intent validation logic
- Cart change detection
- Payment intent expiration
- Cross-tab functionality

## Usage Examples

### Basic Checkout Flow
```php
$checkoutService = app(CheckoutService::class);
$result = $checkoutService->processCheckout($customerData);

if ($result['success']) {
    if ($result['reused_intent'] ?? false) {
        // Existing payment intent reused
    }
    redirect($result['checkout_url']);
}
```

### Cart Change Detection
```php
$cartStatus = $enhancedCheckout->getCartChangeStatus();

if ($cartStatus['cart_changed'] && $cartStatus['has_active_intent']) {
    // Show warning to user about cart changes
}
```

### Webhook Handling
```php
// In webhook controller
$order = $this->checkoutService->handlePaymentSuccess($purchaseId, $webhookData);

if ($order) {
    // Order created successfully from cart intent
} else {
    // Fallback to existing payment record handling
}
```

## Configuration

### Payment Intent Expiration
Default: 30 minutes (configurable in `PaymentService::getPaymentIntentExpiryMinutes()`)

### Cart Version Tracking
Cart version increments on each cart modification, ensuring accurate change detection

## Future Enhancements

1. **Advanced Cart Locking**: Prevent cart modifications during active checkout
2. **Intent Update Strategy**: Allow payment amount updates for minor cart changes
3. **Multi-Currency Support**: Handle different currencies in payment intents
4. **Admin Dashboard**: View and manage active payment intents
5. **Analytics**: Track cart abandonment and payment intent success rates