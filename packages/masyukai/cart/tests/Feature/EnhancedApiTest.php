<?php

declare(strict_types=1);

use MasyukAI\Cart\Cart;
use MasyukAI\Cart\Models\CartItem;
use MasyukAI\Cart\Storage\SessionStorage;

describe('Enhanced Cart API', function () {
    beforeEach(function () {
        $session = new \Illuminate\Session\Store('test', new \Illuminate\Session\ArraySessionHandler(60));
        $this->storage = new SessionStorage($session);
        $this->cart = new Cart($this->storage);
    });

    test('can use intuitive method aliases', function () {
        $this->cart->add('item1', 'Product 1', 10.00, 2);
        $this->cart->add('item2', 'Product 2', 15.00, 1);

        // Test items() for item collection (CORRECTED: content() now returns full cart content)
        $items = $this->cart->getItems();
        expect($items)->toHaveCount(2);

        // Test content() returns complete cart content
        $content = $this->cart->content();
        expect($content)->toBeArray()
            ->and($content['items'])->toHaveCount(2)
            ->and($content['count'])->toBe(2)
            ->and($content['quantity'])->toBe(3);

        // Test count() for total quantity
        expect($this->cart->count())->toBe(3);

        // Test countItems() for unique items
        expect($this->cart->countItems())->toBe(2);

        // Test subtotal() alias
        expect($this->cart->subtotal())->toBe(35.00);

        // Test total() alias
        expect($this->cart->total())->toBe(35.00);
    });

    test('can search cart content with callback', function () {
        $this->cart->add('item1', 'Red Shirt', 20.00, 1, ['color' => 'red']);
        $this->cart->add('item2', 'Blue Shirt', 25.00, 1, ['color' => 'blue']);
        $this->cart->add('item3', 'Red Hat', 15.00, 1, ['color' => 'red']);

        // Search by name
        $redItems = $this->cart->search(function (CartItem $item) {
            return str_contains(strtolower($item->name), 'red');
        });

        expect($redItems)->toHaveCount(2)
            ->and($redItems->first()->name)->toBe('Red Shirt');

        // Search by attribute
        $redColorItems = $this->cart->search(function (CartItem $item) {
            return $item->getAttribute('color') === 'red';
        });

        expect($redColorItems)->toHaveCount(2);
    });

    test('can use simplified condition helpers', function () {
        $this->cart->add('item1', 'Product 1', 100.00, 1);

        // Add discount using helper
        $this->cart->addDiscount('summer-sale', '10%');
        expect($this->cart->total())->toBe(90.00);

        // Add tax using helper
        $this->cart->addTax('sales-tax', '8%');
        expect($this->cart->total())->toBe(97.20);

        // Add fee using helper
        $this->cart->addFee('shipping', '5.00');
        expect($this->cart->total())->toBe(102.20);
    });

    test('can use shopping-cart style item methods', function () {
        $item = $this->cart->add('item1', 'Product 1', 10.00, 2);

        // Test withQuantity alias
        $newItem = $item->withQuantity(5);
        expect($newItem->quantity)->toBe(5)
            ->and($item->quantity)->toBe(2); // Original unchanged

        // Test getRawPriceSum method
        expect($item->getRawPriceSum())->toBe(20.00);

        // Test discountAmount alias
        expect($item->discountAmount())->toBe(0.00);
    });

    test('enhanced collection methods work', function () {
        $this->cart->add('item1', 'Cheap Item', 5.00, 3);
        $this->cart->add('item2', 'Expensive Item', 50.00, 1);
        $this->cart->add('item3', 'Medium Item', 25.00, 2);

        $items = $this->cart->getItems(); // CORRECTED: Use getItems() for collection methods

        // Test whereQuantityAbove
        $bulkItems = $items->whereQuantityAbove(1);
        expect($bulkItems)->toHaveCount(2);

        // Test wherePriceBetween
        $midRangeItems = $items->wherePriceBetween(10.00, 30.00);
        expect($midRangeItems)->toHaveCount(1)
            ->and($midRangeItems->first()->name)->toBe('Medium Item');

        // Test statistics
        $stats = $items->getStatistics();
        expect($stats['total_items'])->toBe(3)
            ->and($stats['total_quantity'])->toBe(6)
            ->and($stats['total_value'])->toBe(115.00); // 3*5 + 1*50 + 2*25 = 15 + 50 + 50 = 115
    });

    test('can group items by attributes', function () {
        $this->cart->add('item1', 'Red Shirt', 20.00, 1, ['category' => 'clothing', 'color' => 'red']);
        $this->cart->add('item2', 'Blue Shirt', 25.00, 1, ['category' => 'clothing', 'color' => 'blue']);
        $this->cart->add('item3', 'Red Hat', 15.00, 1, ['category' => 'accessories', 'color' => 'red']);

        $items = $this->cart->getItems(); // CORRECTED: Use getItems() for collection methods

        // Group by category
        $grouped = $items->groupByAttribute('category');
        expect($grouped)->toHaveCount(2)
            ->and($grouped->get('clothing'))->toHaveCount(2)
            ->and($grouped->get('accessories'))->toHaveCount(1);

        // Group by color
        $colorGrouped = $items->groupByAttribute('color');
        expect($colorGrouped->get('red'))->toHaveCount(2)
            ->and($colorGrouped->get('blue'))->toHaveCount(1);
    });
});
