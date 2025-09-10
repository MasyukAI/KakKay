<?php

declare(strict_types=1);

use MasyukAI\Cart\Cart;
use MasyukAI\Cart\Conditions\CartCondition;
use MasyukAI\Cart\Storage\SessionStorage;
use MasyukAI\Cart\Support\PriceFormatManager;

describe('Comprehensive Serialization and Persistence Coverage', function () {
    beforeEach(function () {
        PriceFormatManager::resetFormatting();

        $sessionStore = new \Illuminate\Session\Store('testing', new \Illuminate\Session\ArraySessionHandler(120));
        $sessionStorage = new SessionStorage($sessionStore);

        $this->cart = new Cart(
            storage: $sessionStorage,
            events: new \Illuminate\Events\Dispatcher,
            instanceName: 'test_serialization',
            eventsEnabled: true
        );
        $this->cart->clear();

        // Create comprehensive test data
        // Add items with various attributes
        $this->cart->add('item-1', 'Premium Product', 100.00, 2, [
            'category' => 'electronics',
            'brand' => 'TechCorp',
            'warranty' => '2 years',
            'features' => ['waterproof', 'wireless'],
            'metadata' => ['sku' => 'TECH-001', 'weight' => 1.5],
        ]);

        $this->cart->add('item-2', 'Standard Product', 50.00, 1, [
            'category' => 'accessories',
            'color' => 'blue',
            'size' => 'medium',
        ]);

        // Add item-level conditions
        $itemDiscount = new CartCondition('item_discount', 'discount', 'subtotal', '-20%', [
            'description' => 'Item loyalty discount',
            'expires_at' => '2024-12-31',
            'applicable_items' => ['item-1'],
        ]);

        $itemTax = new CartCondition('item_tax', 'tax', 'subtotal', '+10%', [
            'description' => 'Premium item tax',
            'rate_type' => 'percentage',
        ]);

        $this->cart->addItemCondition('item-1', $itemDiscount);
        $this->cart->addItemCondition('item-2', $itemTax);

        // Add cart-level conditions
        $cartDiscount = new CartCondition('cart_discount', 'discount', 'subtotal', '-15%', [
            'description' => 'Seasonal sale discount',
            'promotion_code' => 'SUMMER2024',
            'min_amount' => 100.00,
        ]);

        $salesTax = new CartCondition('sales_tax', 'tax', 'subtotal', '+8.25%', [
            'description' => 'State sales tax',
            'jurisdiction' => 'CA',
            'tax_id' => 'CA-TAX-001',
        ]);

        $shipping = new CartCondition('shipping', 'shipping', 'subtotal', '+25.00', [
            'description' => 'Express shipping',
            'carrier' => 'FedEx',
            'estimated_delivery' => '2024-01-15',
        ]);

        $this->cart->addCondition($cartDiscount);
        $this->cart->addCondition($salesTax);
        $this->cart->addCondition($shipping);
    });

    afterEach(function () {
        PriceFormatManager::resetFormatting();
    });

    describe('CartItem Serialization', function () {
        it('serializes CartItem to array with raw and calculated values', function () {
            $item = $this->cart->get('item-1');
            $array = $item->toArray();

            // Verify essential fields are present
            expect($array)->toHaveKeys([
                'id', 'name', 'price', 'quantity', 'subtotal',
                'attributes', 'conditions', 'associated_model',
            ]);

            // Verify raw values are stored
            expect($array['id'])->toBe('item-1');
            expect($array['name'])->toBe('Premium Product');
            expect($array['price'])->toBe(100.00); // Raw price, not calculated
            expect($array['quantity'])->toBe(2);

            // Verify calculated values are included
            expect($array['subtotal'])->toBeNumeric(); // Should be calculated subtotal

            // Verify nested data is serialized
            expect($array['attributes'])->toBeArray();
            expect($array['attributes']['category'])->toBe('electronics');
            expect($array['attributes']['features'])->toBe(['waterproof', 'wireless']);

            expect($array['conditions'])->toBeArray();
            expect($array['conditions'])->toHaveCount(1);
            expect($array['conditions']['item_discount']['name'])->toBe('item_discount');
        });

        it('serializes CartItem to JSON correctly', function () {
            $item = $this->cart->get('item-1');
            $json = $item->toJson();

            expect($json)->toBeString();

            $decoded = json_decode($json, true);
            expect($decoded)->toBeArray();
            expect($decoded['id'])->toBe('item-1');
            expect((float) $decoded['price'])->toBe(100.00);
        });

        it('implements JsonSerializable correctly', function () {
            $item = $this->cart->get('item-1');
            $serialized = json_encode($item);

            expect($serialized)->toBeString();

            $decoded = json_decode($serialized, true);
            expect($decoded['id'])->toBe('item-1');
        });

        it('handles complex attributes in serialization', function () {
            $item = $this->cart->get('item-1');
            $array = $item->toArray();

            // Verify complex nested attributes
            expect($array['attributes']['metadata'])->toBe(['sku' => 'TECH-001', 'weight' => 1.5]);
            expect($array['attributes']['features'])->toBe(['waterproof', 'wireless']);
        });

        it('stores raw prices for persistence', function () {
            $item = $this->cart->get('item-1');
            $array = $item->toArray();

            // Price field should store raw price, not calculated price
            expect($array['price'])->toBe(100.00); // Raw price

            // But subtotal should be calculated (for display purposes)
            expect($array['subtotal'])->not->toBe(200.00); // Should include conditions
        });
    });

    describe('CartCondition Serialization', function () {
        it('serializes CartCondition to array with all properties', function () {
            $condition = new CartCondition(
                'test_condition',
                'discount',
                'subtotal',
                '-25%',
                ['description' => 'Test discount', 'code' => 'TEST25'],
                5
            );

            $array = $condition->toArray();

            expect($array)->toHaveKeys([
                'name', 'type', 'target', 'value', 'attributes', 'order',
            ]);

            expect($array['name'])->toBe('test_condition');
            expect($array['type'])->toBe('discount');
            expect($array['target'])->toBe('subtotal');
            expect($array['value'])->toBe('-25%');
            expect($array['attributes'])->toBe(['description' => 'Test discount', 'code' => 'TEST25']);
            expect($array['order'])->toBe(5);
        });

        it('creates CartCondition from array correctly', function () {
            $data = [
                'name' => 'recreated_condition',
                'type' => 'tax',
                'target' => 'total',
                'value' => '+15%',
                'attributes' => ['jurisdiction' => 'NY'],
                'order' => 3,
            ];

            $condition = CartCondition::fromArray($data);

            expect($condition->getName())->toBe('recreated_condition');
            expect($condition->getType())->toBe('tax');
            expect($condition->getTarget())->toBe('total');
            expect($condition->getValue())->toBe('+15%');
            expect($condition->getAttributes())->toBe(['jurisdiction' => 'NY']);
            expect($condition->getOrder())->toBe(3);
        });

        it('handles CartCondition JSON serialization', function () {
            $condition = new CartCondition('json_test', 'fee', 'subtotal', '+10');

            $json = $condition->toJson();
            expect($json)->toBeString();

            $decoded = json_decode($json, true);
            expect($decoded['name'])->toBe('json_test');
            expect($decoded['type'])->toBe('fee');
        });
    });

    describe('Cart Content Serialization', function () {
        it('serializes complete cart content correctly', function () {
            $content = $this->cart->content();

            // Verify structure
            expect($content)->toHaveKeys([
                'items', 'conditions', 'count', 'total', 'subtotal',
            ]);

            // Verify items are properly serialized
            expect($content['items'])->toBeArray();
            expect($content['items'])->toHaveCount(2);

            // Verify each item has correct structure
            foreach ($content['items'] as $item) {
                expect($item)->toHaveKeys([
                    'id', 'name', 'price', 'quantity', 'subtotal',
                    'attributes', 'conditions', 'associated_model',
                ]);
            }

            // Verify conditions are serialized
            expect($content['conditions'])->toBeArray();
            expect($content['conditions'])->toHaveCount(3); // cart_discount, sales_tax, shipping

            // Verify totals are included
            expect($content['count'])->toBeInt();
            expect($content['total'])->toBeNumeric();
            expect($content['subtotal'])->toBeNumeric();
        });

        it('handles empty cart serialization', function () {
            $this->cart->clear();
            $content = $this->cart->content();

            expect($content['items'])->toBeArray();
            expect($content['items'])->toBeEmpty();
            expect($content['conditions'])->toBeArray();
            expect($content['count'])->toBe(0);
            expect($content['total'])->toBe(0.0);
            expect($content['subtotal'])->toBe(0.0);
        });
    });

    describe('Persistence and Restoration', function () {
        it('prevents double-application of conditions after serialization round-trip', function () {
            // Get original calculations
            $originalSubtotal = $this->cart->getRawSubtotal();
            $originalTotal = $this->cart->getRawTotal();
            $originalItemTotal = $this->cart->get('item-1')->getRawPriceSum();

            // Serialize cart content
            $serializedContent = $this->cart->content();

            // Clear cart and restore from serialized data
            $this->cart->clear();

            // Restore items
            foreach ($serializedContent['items'] as $itemData) {
                $this->cart->add(
                    $itemData['id'],
                    $itemData['name'],
                    $itemData['price'], // Use raw price, not calculated
                    $itemData['quantity'],
                    $itemData['attributes']
                );

                // Restore item conditions
                foreach ($itemData['conditions'] as $conditionData) {
                    $condition = CartCondition::fromArray($conditionData);
                    $this->cart->addItemCondition($itemData['id'], $condition);
                }
            }

            // Restore cart conditions
            foreach ($serializedContent['conditions'] as $conditionData) {
                $condition = CartCondition::fromArray($conditionData);
                $this->cart->addCondition($condition);
            }

            // Verify calculations are identical (no double-application)
            expect($this->cart->getRawSubtotal())->toBe($originalSubtotal);
            expect($this->cart->getRawTotal())->toBe($originalTotal);
            expect($this->cart->get('item-1')->getRawPriceSum())->toBe($originalItemTotal);
        });

        it('maintains data integrity through multiple serialization cycles', function () {
            // Perform multiple serialization/restoration cycles
            for ($i = 0; $i < 3; $i++) {
                $content = $this->cart->content();
                $originalTotal = $this->cart->getRawTotal();

                // Simulate storage and retrieval
                $json = json_encode($content);
                $restored = json_decode($json, true);

                // Verify data integrity
                expect(abs($restored['total'] - $content['total']))->toBeLessThan(0.01);
                expect(abs($restored['subtotal'] - $content['subtotal']))->toBeLessThan(0.01);
                expect($restored['count'])->toBe($content['count']);
                expect(count($restored['items']))->toBe(count($content['items']));
                expect(count($restored['conditions']))->toBe(count($content['conditions']));
            }
        });

        it('handles complex attribute serialization/deserialization', function () {
            $item = $this->cart->get('item-1');
            $originalAttributes = $item->attributes->toArray();

            // Serialize and deserialize
            $serialized = $item->toArray();
            $restoredAttributes = $serialized['attributes'];

            // Verify complex nested structures are preserved
            expect($restoredAttributes)->toBe($originalAttributes);
            expect($restoredAttributes['features'])->toBe(['waterproof', 'wireless']);
            expect($restoredAttributes['metadata']['sku'])->toBe('TECH-001');
            expect($restoredAttributes['metadata']['weight'])->toBe(1.5);
        });
    });

    describe('Formatting Behavior in Serialization', function () {
        it('respects formatting settings in serialized output', function () {
            // Add an item first
            $this->cart->add('format-item', 'Format Test Item', 19.99, 1);

            // Test without formatting
            PriceFormatManager::disableFormatting();
            $contentUnformatted = $this->cart->content();

            // Test with formatting
            PriceFormatManager::enableFormatting();
            $contentFormatted = $this->cart->content();

            // The content() method returns raw values for persistence
            // but individual item/total methods respect formatting
            expect(gettype($contentUnformatted['total']))->toBe('double');
            expect(gettype($contentFormatted['total']))->toBe('double');

            // Ensure we have items in both arrays before comparing
            expect($contentUnformatted['items'])->not->toBeEmpty();
            expect($contentFormatted['items'])->not->toBeEmpty();

            // Check that we have items in both cases (2 from setup + 1 from this test = 3 total)
            expect($contentUnformatted['items'])->toHaveCount(3);
            expect($contentFormatted['items'])->toHaveCount(3);
        });

        it('stores raw values in item arrays regardless of formatting', function () {
            PriceFormatManager::enableFormatting();

            $item = $this->cart->get('item-1');
            $array = $item->toArray();

            // Price should always be raw value for persistence
            expect($array['price'])->toBe(100.00);
            expect($array['price'])->toBeFloat();

            // But calculated values like subtotal may be formatted
            expect($array['subtotal'])->toBeString(); // When formatting is enabled
        });
    });

    describe('Collection Serialization', function () {
        it('serializes CartCollection toFormattedArray correctly', function () {
            $items = $this->cart->getItems();
            $array = $items->toFormattedArray();

            expect($array)->toHaveKeys(['items', 'count', 'subtotal']);
            expect($array['items'])->toBeArray();
            expect($array['count'])->toBeInt();
            expect($array['subtotal'])->toBeNumeric();

            // Verify individual items
            expect($array['items'])->toHaveCount(2);
            foreach ($array['items'] as $item) {
                expect($item)->toHaveKeys(['id', 'name', 'price', 'quantity']);
            }
        });

        it('handles filtered collection serialization', function () {
            $items = $this->cart->getItems();
            $electronicsItems = $items->filterByAttribute('category', 'electronics');
            $array = $electronicsItems->toFormattedArray();

            expect($array['items'])->toHaveCount(1);
            expect($array['items']['item-1']['id'])->toBe('item-1');
        });
    });

    describe('Error Handling in Serialization', function () {
        it('handles invalid JSON gracefully', function () {
            $invalidJson = '{"invalid": json}';
            $decoded = json_decode($invalidJson, true);

            expect($decoded)->toBeNull();
        });

        it('handles missing required fields in fromArray', function () {
            expect(fn () => CartCondition::fromArray([
                'type' => 'discount',
                // Missing required 'name' field
            ]))->toThrow(\MasyukAI\Cart\Exceptions\InvalidCartConditionException::class);
        });

        it('handles circular reference prevention', function () {
            // Create an item with self-referencing attribute
            $this->cart->add('circular-item', 'Test Item', 100.00, 1, [
                'self_ref' => null, // This would be a circular reference in real scenarios
            ]);

            $item = $this->cart->get('circular-item');

            // Should not throw error
            expect(fn () => $item->toArray())->not->toThrow(\Exception::class);
            expect(fn () => $item->toJson())->not->toThrow(\Exception::class);
        });
    });
});
