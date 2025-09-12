<?php

declare(strict_types=1);

use MasyukAI\Cart\Cart;
use MasyukAI\Cart\Conditions\CartCondition;
use MasyukAI\Cart\Storage\SessionStorage;

// Since CartCondition is readonly, we'll create custom condition factories
// and use functional approaches to demonstrate extensibility

class CustomConditionFactory
{
    public static function createBuyOneGetOne(string $name, array $attributes = []): CartCondition
    {
        $attributes['type'] = 'buy_one_get_one';
        $attributes['calculator'] = function (float $value, CartCondition $condition): float {
            $item = $condition->getAttributes()['context_item'] ?? null;
            if ($item && $item->quantity >= 2) {
                $discountQuantity = intval($item->quantity / 2);
                $discountAmount = $item->price * $discountQuantity;

                return max(0, $value - $discountAmount);
            }

            return $value;
        };

        return new CartCondition($name, 'discount', 'subtotal', 'custom', $attributes);
    }

    public static function createTieredDiscount(string $name, array $tiers, array $attributes = []): CartCondition
    {
        $attributes['tiers'] = $tiers;
        $attributes['type'] = 'tiered_discount';
        $attributes['calculator'] = function (float $value, CartCondition $condition): float {
            $tiers = $condition->getAttributes()['tiers'] ?? [];

            // Sort tiers by min_amount in descending order to get highest applicable tier
            usort($tiers, function ($a, $b) {
                return $b['min_amount'] <=> $a['min_amount'];
            });

            foreach ($tiers as $tier) {
                if ($value >= $tier['min_amount']) {
                    $discount = $tier['discount_percent'] / 100;

                    return $value * (1 - $discount);
                }
            }

            return $value;
        };

        return new CartCondition($name, 'discount', 'subtotal', 'tiered', $attributes);
    }

    public static function createVolumeDiscount(string $name, int $minQuantity, float $discountPercent, array $attributes = []): CartCondition
    {
        $attributes['min_quantity'] = $minQuantity;
        $attributes['discount_percent'] = $discountPercent;
        $attributes['type'] = 'volume_discount';
        $attributes['calculator'] = function (float $value, CartCondition $condition): float {
            $item = $condition->getAttributes()['context_item'] ?? null;
            $minQuantity = $condition->getAttributes()['min_quantity'];
            $discountPercent = $condition->getAttributes()['discount_percent'];

            if ($item && $item->quantity >= $minQuantity) {
                return $value * (1 - $discountPercent / 100);
            }

            return $value;
        };

        return new CartCondition($name, 'discount', 'subtotal', 'volume', $attributes);
    }

    public static function createLoyaltyPoints(string $name, int $pointsToRedeem, float $pointValue, array $attributes = []): CartCondition
    {
        $attributes['points_to_redeem'] = $pointsToRedeem;
        $attributes['point_value'] = $pointValue;
        $attributes['type'] = 'loyalty_points';
        $attributes['calculator'] = function (float $value, CartCondition $condition): float {
            $pointsToRedeem = $condition->getAttributes()['points_to_redeem'];
            $pointValue = $condition->getAttributes()['point_value'];

            $discountAmount = $pointsToRedeem * $pointValue;

            return max(0, $value - $discountAmount);
        };

        return new CartCondition($name, 'discount', 'subtotal', 'loyalty_points', $attributes);
    }
}

describe('Custom Condition Extensibility and Examples', function () {
    beforeEach(function () {
        $sessionStore = new \Illuminate\Session\Store('testing', new \Illuminate\Session\ArraySessionHandler(120));
        $sessionStorage = new SessionStorage($sessionStore);

        $this->cart = new Cart(
            storage: $sessionStorage,
            events: new \Illuminate\Events\Dispatcher,
            identifier: 'test_custom_conditions',
            instanceName: 'test_custom_conditions',
            eventsEnabled: true
        );
        $this->cart->clear();
    });

    describe('Buy One Get One Custom Condition', function () {
        it('applies buy one get one discount correctly', function () {
            $this->cart->add('item-1', 'BOGO Item', 20.00, 4); // $80 total

            $item = $this->cart->get('item-1');
            $bogoCondition = CustomConditionFactory::createBuyOneGetOne('bogo_deal', [
                'description' => 'Buy One Get One Free',
                'context_item' => $item,
            ]);

            // Manually test the logic
            // 4 items: pay for 2, get 2 free
            // Expected: 20 * 2 = 40 (instead of 80)
            $calculator = $bogoCondition->getAttributes()['calculator'];
            $result = $calculator(80.00, $bogoCondition);
            expect($result)->toBe(40.00);
        });

        it('handles odd quantities in BOGO', function () {
            $this->cart->add('item-1', 'BOGO Item', 20.00, 3); // $60 total

            $item = $this->cart->get('item-1');
            $bogoCondition = CustomConditionFactory::createBuyOneGetOne('bogo_deal', [
                'context_item' => $item,
            ]);

            // 3 items: pay for 2, get 1 free
            // Expected: 60 - 20 = 40
            $calculator = $bogoCondition->getAttributes()['calculator'];
            $result = $calculator(60.00, $bogoCondition);
            expect($result)->toBe(40.00);
        });

        it('does not apply BOGO when quantity is 1', function () {
            $this->cart->add('item-1', 'BOGO Item', 20.00, 1);

            $item = $this->cart->get('item-1');
            $bogoCondition = CustomConditionFactory::createBuyOneGetOne('bogo_deal', [
                'context_item' => $item,
            ]);

            $calculator = $bogoCondition->getAttributes()['calculator'];
            $result = $calculator(20.00, $bogoCondition);
            expect($result)->toBe(20.00); // No discount
        });
    });

    describe('Tiered Discount Custom Condition', function () {
        it('applies correct tier based on amount', function () {
            $tiers = [
                ['min_amount' => 500, 'discount_percent' => 20], // 20% off $500+
                ['min_amount' => 200, 'discount_percent' => 15], // 15% off $200+
                ['min_amount' => 100, 'discount_percent' => 10], // 10% off $100+
            ];

            $tieredCondition = CustomConditionFactory::createTieredDiscount('tiered_discount', $tiers);
            $calculator = $tieredCondition->getAttributes()['calculator'];

            // Test different amounts
            expect($calculator(50.00, $tieredCondition))->toBe(50.00);    // No discount
            expect($calculator(150.00, $tieredCondition))->toBe(135.00);  // 10% off
            expect($calculator(300.00, $tieredCondition))->toBe(255.00);  // 15% off
            expect($calculator(600.00, $tieredCondition))->toBe(480.00);  // 20% off
        });

        it('applies highest applicable tier', function () {
            $tiers = [
                ['min_amount' => 100, 'discount_percent' => 10],
                ['min_amount' => 200, 'discount_percent' => 15],
            ];

            $tieredCondition = CustomConditionFactory::createTieredDiscount('tiered_discount', $tiers);
            $calculator = $tieredCondition->getAttributes()['calculator'];

            // $250 qualifies for both tiers, should get 15% (higher tier)
            expect($calculator(250.00, $tieredCondition))->toBe(212.50);
        });
    });

    describe('Volume Discount Custom Condition', function () {
        it('applies volume discount when minimum quantity is met', function () {
            $this->cart->add('bulk-item', 'Bulk Item', 10.00, 100);

            $item = $this->cart->get('bulk-item');
            $volumeCondition = CustomConditionFactory::createVolumeDiscount('volume_discount', 50, 25.0, [
                'context_item' => $item,
                'description' => '25% off when buying 50 or more',
            ]);

            // 100 items * $10 = $1000, with 25% discount = $750
            $calculator = $volumeCondition->getAttributes()['calculator'];
            $result = $calculator(1000.00, $volumeCondition);
            expect($result)->toBe(750.00);
        });

        it('does not apply volume discount when minimum quantity is not met', function () {
            $this->cart->add('bulk-item', 'Bulk Item', 10.00, 25);

            $item = $this->cart->get('bulk-item');
            $volumeCondition = CustomConditionFactory::createVolumeDiscount('volume_discount', 50, 25.0, [
                'context_item' => $item,
            ]);

            $calculator = $volumeCondition->getAttributes()['calculator'];
            $result = $calculator(250.00, $volumeCondition);
            expect($result)->toBe(250.00); // No discount
        });
    });

    describe('Loyalty Points Custom Condition', function () {
        it('applies loyalty points discount correctly', function () {
            $loyaltyCondition = CustomConditionFactory::createLoyaltyPoints(
                'loyalty_discount',
                1000, // points to redeem
                0.01, // point value ($0.01 per point)
                ['description' => 'Redeem 1000 points for $10 off']
            );

            // $10 discount on $50 cart = $40
            $calculator = $loyaltyCondition->getAttributes()['calculator'];
            $result = $calculator(50.00, $loyaltyCondition);
            expect($result)->toBe(40.00);
        });

        it('prevents negative totals with loyalty points', function () {
            $loyaltyCondition = CustomConditionFactory::createLoyaltyPoints(
                'loyalty_discount',
                2000, // points worth $20
                0.01
            );

            // $20 discount on $15 cart should result in $0, not negative
            $calculator = $loyaltyCondition->getAttributes()['calculator'];
            $result = $calculator(15.00, $loyaltyCondition);
            expect($result)->toBe(0.00);
        });
    });

    describe('Custom Condition Integration', function () {
        it('allows custom conditions to be added to cart', function () {
            $this->cart->add('item-1', 'Test Item', 100.00, 2);

            // Create a simple custom condition using factory pattern
            $customCondition = new CartCondition(
                'custom_test',
                'discount',
                'subtotal',
                'custom',
                [
                    'calculator' => function (float $value, CartCondition $condition): float {
                        return $value - 50;
                    },
                ]
            );

            $this->cart->addCondition($customCondition);

            // Verify condition is added
            expect($this->cart->getConditions()->has('custom_test'))->toBeTrue();
            expect($this->cart->getConditions()->count())->toBe(1);
        });

        it('allows custom conditions to serialize and deserialize', function () {
            $tieredCondition = CustomConditionFactory::createTieredDiscount('tiered_test', [
                ['min_amount' => 100, 'discount_percent' => 10],
            ]);

            // Test serialization
            $array = $tieredCondition->toArray();
            expect($array['name'])->toBe('tiered_test');
            expect($array['type'])->toBe('discount');
            expect($array['value'])->toBe('tiered');
            expect($array['attributes']['tiers'])->toHaveCount(1);

            // Test JSON serialization
            $json = $tieredCondition->toJson();
            expect($json)->toBeString();

            $decoded = json_decode($json, true);
            expect($decoded['name'])->toBe('tiered_test');
        });

        it('handles complex custom condition attributes', function () {
            $complexCondition = new CartCondition(
                'complex_condition',
                'discount',
                'subtotal',
                'custom',
                [
                    'rules' => [
                        'apply_to' => ['electronics', 'clothing'],
                        'exclude_brands' => ['Brand A', 'Brand B'],
                        'time_restrictions' => [
                            'start' => '2024-01-01',
                            'end' => '2024-12-31',
                        ],
                    ],
                    'metadata' => [
                        'created_by' => 'promotion_system',
                        'version' => '1.2.0',
                    ],
                ]
            );

            expect($complexCondition->getAttributes()['rules']['apply_to'])->toBe(['electronics', 'clothing']);
            expect($complexCondition->getAttributes()['metadata']['version'])->toBe('1.2.0');
        });
    });

    describe('Custom Condition Documentation and Examples', function () {
        it('demonstrates how to create a simple percentage discount condition', function () {
            // Use standard CartCondition with percentage value
            $simpleDiscount = new CartCondition('simple_discount', 'discount', 'subtotal', '-15%');

            $result = $simpleDiscount->apply(100.00);
            expect($result)->toBe(85.00); // 15% off
        });

        it('demonstrates how to create a fixed amount condition', function () {
            // Use standard CartCondition with fixed amount
            $fixedDiscount = new CartCondition('fixed_discount', 'discount', 'subtotal', '-25');

            $result = $fixedDiscount->apply(100.00);
            expect($result)->toBe(75.00); // $25 off
        });

        it('demonstrates conditional logic using factory pattern', function () {
            // Use factory pattern for complex custom conditions
            $categoryDiscount = CustomConditionFactory::createTieredDiscount('category_discount', [
                ['min_amount' => 0, 'discount_percent' => 20],
            ], [
                'target_category' => 'electronics',
                'item_category' => 'electronics',
            ]);

            // Test the condition
            $calculator = $categoryDiscount->getAttributes()['calculator'];
            $result = $calculator(100.00, $categoryDiscount);
            expect($result)->toBe(80.00); // 20% off
        });
    });
});
