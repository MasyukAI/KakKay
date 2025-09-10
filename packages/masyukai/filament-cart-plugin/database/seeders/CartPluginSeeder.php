<?php

declare(strict_types=1);

namespace MasyukAI\FilamentCartPlugin\Database\Seeders;

use Illuminate\Database\Seeder;
use MasyukAI\FilamentCartPlugin\Models\Cart;
use MasyukAI\FilamentCartPlugin\Models\CartCondition;

class CartPluginSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create some global conditions
        CartCondition::factory()->create([
            'name' => 'bulk-discount',
            'type' => 'static',
            'target' => 'subtotal',
            'value' => '-10%',
            'is_global' => true,
            'is_active' => true,
            'description' => 'Apply 10% discount on orders over $100',
        ]);

        CartCondition::factory()->create([
            'name' => 'shipping-fee',
            'type' => 'static',
            'target' => 'total',
            'value' => '+15',
            'is_global' => true,
            'is_active' => true,
            'description' => 'Standard shipping fee',
        ]);

        CartCondition::factory()->create([
            'name' => 'tax',
            'type' => 'dynamic',
            'target' => 'total',
            'value' => '+6%',
            'is_global' => true,
            'is_active' => true,
            'description' => 'Sales tax',
        ]);

        CartCondition::factory()->create([
            'name' => 'member-discount',
            'type' => 'static',
            'target' => 'subtotal',
            'value' => '-5%',
            'is_global' => false,
            'is_active' => true,
            'description' => 'Member exclusive discount',
        ]);

        // Create some sample carts with different scenarios
        Cart::factory()->create([
            'identifier' => 'cart-001',
            'instance' => 'default',
            'items' => [
                [
                    'id' => 'product-1',
                    'name' => 'Gaming Laptop',
                    'price' => 1200,
                    'quantity' => 1,
                    'attributes' => [
                        'weight' => 2.5,
                        'category' => 'electronics',
                        'brand' => 'TechCorp'
                    ]
                ],
                [
                    'id' => 'product-2',
                    'name' => 'Wireless Mouse',
                    'price' => 50,
                    'quantity' => 2,
                    'attributes' => [
                        'weight' => 0.2,
                        'category' => 'accessories',
                        'brand' => 'TechCorp'
                    ]
                ]
            ],
            'conditions' => [
                [
                    'name' => 'bulk-discount',
                    'type' => 'static',
                    'target' => 'cart',
                    'value' => '-10%',
                    'applied_at' => now()->toISOString(),
                ]
            ],
            'metadata' => [
                'customer_type' => 'premium',
                'source' => 'website'
            ]
        ]);

        Cart::factory()->create([
            'identifier' => 'cart-002',
            'instance' => 'wishlist',
            'items' => [
                [
                    'id' => 'product-3',
                    'name' => 'Smart Watch',
                    'price' => 300,
                    'quantity' => 1,
                    'attributes' => [
                        'weight' => 0.1,
                        'category' => 'wearables',
                        'brand' => 'WearTech'
                    ]
                ]
            ],
            'conditions' => [],
            'metadata' => [
                'customer_type' => 'regular',
                'source' => 'mobile_app'
            ]
        ]);

        Cart::factory()->create([
            'identifier' => 'cart-003',
            'instance' => 'default',
            'items' => [
                [
                    'id' => 'product-4',
                    'name' => 'Office Chair',
                    'price' => 400,
                    'quantity' => 1,
                    'attributes' => [
                        'weight' => 15.0,
                        'category' => 'furniture',
                        'brand' => 'ComfortSeating'
                    ]
                ],
                [
                    'id' => 'product-5',
                    'name' => 'Desk Lamp',
                    'price' => 80,
                    'quantity' => 1,
                    'attributes' => [
                        'weight' => 2.0,
                        'category' => 'lighting',
                        'brand' => 'BrightLight'
                    ]
                ],
                [
                    'id' => 'product-6',
                    'name' => 'Notebook Set',
                    'price' => 25,
                    'quantity' => 3,
                    'attributes' => [
                        'weight' => 0.5,
                        'category' => 'stationery',
                        'brand' => 'PaperPlus'
                    ]
                ]
            ],
            'conditions' => [
                [
                    'name' => 'member-discount',
                    'type' => 'static',
                    'target' => 'cart',
                    'value' => '-5%',
                    'applied_at' => now()->toISOString(),
                ],
                [
                    'name' => 'shipping-fee',
                    'type' => 'static',
                    'target' => 'cart',
                    'value' => '+15',
                    'applied_at' => now()->toISOString(),
                ]
            ],
            'metadata' => [
                'customer_type' => 'member',
                'source' => 'website'
            ]
        ]);

        // Create some additional conditions
        CartCondition::factory()->count(5)->create();
        
        // Create some empty carts
        Cart::factory()->count(3)->create([
            'items' => [],
            'conditions' => [],
        ]);
    }
}