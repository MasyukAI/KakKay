<?php

declare(strict_types=1);

/**
 * Smart Dynamic Conditions - Usage Examples
 *
 * This file demonstrates all the ways to use the Filament-style smart evaluation
 * for dynamic cart conditions.
 */

use MasyukAI\Cart\Cart;
use MasyukAI\Cart\Conditions\CartCondition;
use MasyukAI\Cart\Contracts\RulesFactoryInterface;

require_once __DIR__.'/../../vendor/autoload.php';

// ============================================================================
// Example Rules Factory (for persistence)
// ============================================================================

class SimpleRulesFactory implements RulesFactoryInterface
{
    public function createRules(string $key, array $metadata): array
    {
        return match ($key) {
            'bulk_discount' => [
                fn (Cart $cart) => $cart->countItems() >= ($metadata['min_qty'] ?? 10),
            ],
            'min_order' => [
                fn (Cart $cart) => $cart->subtotal()->greaterThanOrEqual(
                    money($metadata['min_amount'] ?? 10000)
                ),
            ],
            'time_based' => [
                fn (Cart $cart) => now()->hour >= 14 && now()->hour < 16,
            ],
            default => throw new InvalidArgumentException("Unknown rule key: {$key}"),
        };
    }

    public function canCreateRules(string $key): bool
    {
        return in_array($key, ['bulk_discount', 'min_order', 'time_based']);
    }

    public function getAvailableKeys(): array
    {
        return ['bulk_discount', 'min_order', 'time_based'];
    }
}

// ============================================================================
// Setup
// ============================================================================

$cart = new Cart(
    storage: new MasyukAI\Cart\Storage\InMemoryStorage(),
    identifier: 'demo-cart'
);

$cart->setRulesFactory(new SimpleRulesFactory());

echo "Cart Smart Dynamic Conditions Demo\n";
echo str_repeat('=', 60)."\n\n";

// ============================================================================
// Example 1: Direct Closures (Quick & Simple)
// ============================================================================

echo "1) DIRECT CLOSURES - Quick one-off conditions\n";
echo str_repeat('-', 60)."\n";

$cart->registerDynamicCondition(
    condition: [
        'name' => 'flash-sale',
        'type' => 'percentage',
        'target' => 'subtotal',
        'value' => -20,
        'attributes' => ['label' => 'Flash Sale 20% OFF'],
    ],
    rules: [
        // Multiple rules - ALL must pass
        fn (Cart $cart) => now()->isWeekend(),
        fn (Cart $cart) => $cart->subtotal()->greaterThan(money(5000)),
    ]
);

echo "[OK] Registered 'flash-sale' with direct closure rules\n";
echo "   - Applies on weekends only\n";
echo "   - Requires subtotal > RM 50.00\n";
echo "   [Note] Not persisted (lost after request)\n\n";

// ============================================================================
// Example 2: Factory Key (Persisted & Reusable)
// ============================================================================

echo "2) FACTORY KEY - Production-ready persistence\n";
echo str_repeat('-', 60)."\n";

$cart->registerDynamicCondition(
    condition: [
        'name' => 'bulk-discount',
        'type' => 'percentage',
        'target' => 'subtotal',
        'value' => -10,
        'attributes' => [
            'label' => 'Bulk Order Discount',
            'min_qty' => 5,
        ],
    ],
    rules: 'bulk_discount' // Factory key (auto-persists!)
);

echo "[OK] Registered 'bulk-discount' via factory key\n";
echo "   - Uses 'bulk_discount' factory\n";
echo "   - Automatically persisted to storage\n";
echo "   - Restored across requests\n";
echo "   [OK] Production-ready\n\n";

// ============================================================================
// Example 3: Closure Generator (Dynamic Rules)
// ============================================================================

echo "3) CLOSURE GENERATOR - Dynamic rule creation\n";
echo str_repeat('-', 60)."\n";

$promoCode = 'SUMMER2025';

$cart->registerDynamicCondition(
    condition: [
        'name' => 'promo-code-discount',
        'type' => 'fixed',
        'target' => 'subtotal',
        'value' => -1000, // RM 10.00 off
    ],
    rules: fn () => [
        // Generate rules dynamically based on context
        fn (Cart $cart) => $cart->getMetadata('promo_code') === $promoCode,
        fn (Cart $cart) => $cart->countItems() > 0,
    ]
);

echo "[OK] Registered 'promo-code-discount' with closure generator\n";
echo "   - Rules generated dynamically for promo: {$promoCode}\n";
echo "   - Flexible context-aware logic\n";
echo "   [Note] Not persisted\n\n";

// ============================================================================
// Example 4: Traditional CartCondition (Full Control)
// ============================================================================

echo "4) TRADITIONAL - Pre-built CartCondition object\n";
echo str_repeat('-', 60)."\n";

$vipCondition = new CartCondition(
    name: 'vip-discount',
    type: 'percentage',
    target: 'subtotal',
    value: -15,
    attributes: ['label' => 'VIP Member Discount'],
    rules: [
        fn (Cart $cart) => $cart->getMetadata('user_tier') === 'vip',
    ]
);

$cart->registerDynamicCondition($vipCondition);

echo "[OK] Registered 'vip-discount' using CartCondition object\n";
echo "   - Full control over all properties\n";
echo "   - Traditional API still works\n";
echo "   - Can add factory key for persistence\n\n";

// ============================================================================
// Example 5: Hybrid - Factory + Explicit Metadata
// ============================================================================

echo "5) HYBRID - Factory with explicit persistence control\n";
echo str_repeat('-', 60)."\n";

$cart->registerDynamicCondition(
    condition: [
        'name' => 'min-order-discount',
        'type' => 'fixed',
        'target' => 'subtotal',
        'value' => -500, // RM 5.00 off
        'attributes' => [
            'label' => 'Minimum Order Discount',
            'min_amount' => 20000, // RM 200.00
        ],
    ],
    rules: 'min_order',
    ruleFactoryKey: 'min_order' // Explicit persistence
);

echo "[OK] Registered 'min-order-discount' with explicit factory key\n";
echo "   - Uses 'min_order' factory\n";
echo "   - Explicit persistence control\n";
echo "   - Full metadata support\n\n";

// ============================================================================
// Testing the Smart System
// ============================================================================

echo "Testing smart detection\n";
echo str_repeat('=', 60)."\n\n";

// Add some items to trigger conditions
$cart->addItem('item1', 'Product A', money(3000), 2);
$cart->addItem('item2', 'Product B', money(5000), 4);

echo "Cart Contents:\n";
echo "- Item 1: RM 30.00 x 2 = RM 60.00\n";
echo "- Item 2: RM 50.00 x 4 = RM 200.00\n";
echo '- Total Items: '.$cart->countItems()."\n";
echo '- Subtotal: '.$cart->subtotal()->format()."\n\n";

// Check which conditions are active
$activeConditions = $cart->getConditions();

echo "Active Dynamic Conditions:\n";
foreach ($activeConditions as $condition) {
    echo "- {$condition->getName()}: {$condition->getCalculatedValue($cart)->format()}\n";
}

echo "\n";
echo "Demo complete!\n";
echo "\n";
echo "Key Takeaways:\n";
echo "  - Smart detection works like Filament's evaluate()\n";
echo "  - Multiple input formats supported automatically\n";
echo "  - Use factory keys for production (persisted)\n";
echo "  - Use direct closures for quick prototyping\n";
echo "  - System handles everything intelligently\n";
