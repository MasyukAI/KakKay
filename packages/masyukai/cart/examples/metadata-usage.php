<?php

declare(strict_types=1);

/**
 * Cart Metadata Usage Examples
 *
 * This file demonstrates how to use the Cart metadata functionality
 * that was added to provide a flexible way to store additional
 * cart-related information.
 *
 * NOTE: This is an example/demo file showing usage patterns.
 * Some methods referenced may not exist or may require proper setup
 * in a real Laravel application context.
 */

use MasyukAI\Cart\Cart;

// Assume you have a cart instance
/** @var Cart $cart */

// ============================================================================
// BASIC METADATA OPERATIONS
// ============================================================================

// Set individual metadata values
$cart->setMetadata('user_id', auth()->id());
$cart->setMetadata('currency', 'USD');
$cart->setMetadata('notes', 'Gift wrap requested');

// Retrieve metadata values
$userId = $cart->getMetadata('user_id');
$currency = $cart->getMetadata('currency', 'USD'); // with default value
$notes = $cart->getMetadata('notes');

// Check if metadata exists
if ($cart->hasMetadata('coupon_code')) {
    $coupon = $cart->getMetadata('coupon_code');
    // Apply coupon logic...
}

// Remove metadata
$cart->removeMetadata('temporary_flag');

// ============================================================================
// BATCH OPERATIONS
// ============================================================================

// Set multiple metadata values at once
$cart->setMetadataBatch([
    'session_id' => session()->getId(),
    'ip_address' => request()->ip(),
    'user_agent' => request()->userAgent(),
    'created_at' => now()->toISOString(),
]);

// ============================================================================
// METHOD CHAINING
// ============================================================================

// All metadata methods return the cart instance for chaining
$cart->setMetadata('step', 'checkout')
    ->setMetadata('payment_method', 'credit_card')
    ->setMetadata('shipping_method', 'express');

// ============================================================================
// REAL-WORLD USE CASES
// ============================================================================

// 1. CART ABANDONMENT TRACKING
$cart->setMetadata('last_activity', now()->timestamp);
$cart->setMetadata('reminder_count', 0);
$cart->setMetadata('abandoned', false);

// Later, in a scheduled task:
$lastActivity = $cart->getMetadata('last_activity');
if ($lastActivity && (time() - $lastActivity) > 3600) { // 1 hour
    $cart->setMetadata('abandoned', true);
    // Send abandonment email...
}

// 2. PROMOTIONAL CAMPAIGNS
$cart->setMetadata('referral_source', 'email_campaign_2024');
$cart->setMetadata('coupon_applied', 'SAVE20');
$cart->setMetadata('discount_amount', 15.50);

// 3. USER PREFERENCES
$cart->setMetadata('delivery_instructions', 'Leave at front door');
$cart->setMetadata('preferred_delivery_time', 'evening');
$cart->setMetadata('gift_wrap', true);
$cart->setMetadata('gift_message', 'Happy Birthday!');

// 4. ANALYTICS AND TRACKING
$cart->setMetadata('utm_source', request()->get('utm_source'));
$cart->setMetadata('utm_campaign', request()->get('utm_campaign'));
$cart->setMetadata('landing_page', request()->headers->get('referer'));

// 5. BUSINESS LOGIC FLAGS
$cart->setMetadata('requires_approval', $cart->getSubtotal() > 1000);
$cart->setMetadata('bulk_discount_eligible', $cart->getItemsCount() >= 10);
$cart->setMetadata('free_shipping_eligible', $cart->getSubtotal() >= 50);

// 6. CHECKOUT WORKFLOW
$cart->setMetadata('checkout_step', 'shipping_address');
$cart->setMetadata('shipping_address', [
    'name' => 'John Doe',
    'street' => '123 Main St',
    'city' => 'Anytown',
    'postal_code' => '12345',
]);

// Progress to next step
$cart->setMetadata('checkout_step', 'payment');

// 7. TEMPORARY DATA STORAGE
// Store form data temporarily during multi-step checkout
$cart->setMetadata('temp_billing_address', request()->only([
    'billing_name', 'billing_street', 'billing_city',
]));

// Clear temporary data when no longer needed
$cart->removeMetadata('temp_billing_address');

// ============================================================================
// INTEGRATION WITH EXISTING FEATURES
// ============================================================================

// The metadata system works alongside existing cart functionality
$cart->add('product-1', 'T-Shirt', 25.00, 2);

// You can associate models and still use metadata
$cart->associate(Product::class);
$cart->setMetadata('added_via', 'quick_add_button');

// Metadata persists through cart operations
$cart->update('product-1', ['quantity' => 3]);
$cart->setMetadata('last_modified', now()->toISOString());

// But is cleared when cart is cleared
$cart->clear(); // This removes items AND metadata

// ============================================================================
// CONDITIONAL LOGIC BASED ON METADATA
// ============================================================================

// Apply different logic based on stored metadata
$customerType = $cart->getMetadata('customer_type', 'regular');

switch ($customerType) {
    case 'vip':
        // Apply VIP discount
        $cart->condition('vip_discount', [
            'type' => 'percentage',
            'value' => -10,
        ]);
        break;

    case 'wholesale':
        // Apply wholesale pricing
        $cart->setMetadata('requires_tax_exemption', true);
        break;

    default:
        // Regular customer logic
        break;
}

// ============================================================================
// ERROR HANDLING
// ============================================================================

// Metadata methods are safe - they won't throw exceptions
$safeValue = $cart->getMetadata('might_not_exist', 'default');

// You can check existence before using
if ($cart->hasMetadata('important_flag')) {
    $importantValue = $cart->getMetadata('important_flag');
    // Process important value...
}

// ============================================================================
// DATA TYPES
// ============================================================================

// Metadata supports various data types
$cart->setMetadata('string_value', 'text');
$cart->setMetadata('numeric_value', 42);
$cart->setMetadata('float_value', 3.14);
$cart->setMetadata('boolean_value', true);
$cart->setMetadata('array_value', ['item1', 'item2', 'item3']);
$cart->setMetadata('object_value', (object) ['property' => 'value']);

// Complex data structures
$cart->setMetadata('user_preferences', [
    'theme' => 'dark',
    'language' => 'en',
    'notifications' => [
        'email' => true,
        'sms' => false,
    ],
]);
