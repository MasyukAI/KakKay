<?php

declare(strict_types=1);

use MasyukAI\Cart\Collections\CartCollection;
use MasyukAI\Cart\Models\CartItem;

describe('CartCollection Basic Operations', function () {
    it('creates empty collection', function () {
        $collection = new CartCollection;

        expect($collection)->toBeInstanceOf(CartCollection::class);
        expect($collection->isEmpty())->toBeTrue();
        expect($collection->count())->toBe(0);
    });

    it('creates collection from items', function () {
        $items = [
            new CartItem('item-1', 'Item 1', 10.00, 1),
            new CartItem('item-2', 'Item 2', 20.00, 2),
        ];

        $collection = new CartCollection($items);

        expect($collection->count())->toBe(2);
        expect($collection->isEmpty())->toBeFalse();
    });

    it('adds items to collection', function () {
        $collection = new CartCollection;
        $item = new CartItem('item', 'Item', 15.00, 1);

        $collection->push($item);

        expect($collection->count())->toBe(1);
        expect($collection->first())->toBe($item);
    });

    it('adds items using addItem method', function () {
        $collection = new CartCollection;
        $item = new CartItem('item-1', 'Item 1', 10.00, 1);

        $result = $collection->addItem($item);

        expect($result)->toBeInstanceOf(CartCollection::class);
        expect($collection->count())->toBe(1);
        expect($collection->has('item-1'))->toBeTrue();
    });

    it('removes items from collection', function () {
        $item1 = new CartItem('item-1', 'Item 1', 10.00, 1);
        $item2 = new CartItem('item-2', 'Item 2', 20.00, 1);

        $collection = new CartCollection([$item1, $item2]);
        $filtered = $collection->filter(fn ($item) => $item->id !== 'item-1');

        expect($filtered->count())->toBe(1);
        expect($filtered->first()->id)->toBe('item-2');
    });

    it('removes items using removeItem method', function () {
        $item1 = new CartItem('item-1', 'Item 1', 10.00, 1);
        $item2 = new CartItem('item-2', 'Item 2', 20.00, 1);

        $collection = new CartCollection;
        $collection->addItem($item1);
        $collection->addItem($item2);

        $result = $collection->removeItem('item-1');

        expect($result)->toBeInstanceOf(CartCollection::class);
        expect($collection->count())->toBe(1);
        expect($collection->has('item-1'))->toBeFalse();
        expect($collection->has('item-2'))->toBeTrue();
    });

    it('gets item by ID using getItem method', function () {
        $item1 = new CartItem('item-1', 'Item 1', 10.00, 1);
        $item2 = new CartItem('item-2', 'Item 2', 20.00, 1);

        $collection = new CartCollection;
        $collection->addItem($item1);
        $collection->addItem($item2);

        $retrieved = $collection->getItem('item-1');

        expect($retrieved)->not->toBeNull();
        expect($retrieved->id)->toBe('item-1');
        expect($retrieved->name)->toBe('Item 1');
    });

    it('returns null when getting non-existent item', function () {
        $collection = new CartCollection;

        $retrieved = $collection->getItem('non-existent');

        expect($retrieved)->toBeNull();
    });

    it('checks if item exists using hasItem method', function () {
        $item = new CartItem('item-1', 'Item 1', 10.00, 1);
        $collection = new CartCollection;
        $collection->addItem($item);

        expect($collection->hasItem('item-1'))->toBeTrue();
        expect($collection->hasItem('non-existent'))->toBeFalse();
    });

    it('gets total quantity using getTotalQuantity method', function () {
        $item1 = new CartItem('item-1', 'Item 1', 10.00, 2);
        $item2 = new CartItem('item-2', 'Item 2', 20.00, 3);
        $item3 = new CartItem('item-3', 'Item 3', 30.00, 5);

        $collection = new CartCollection;
        $collection->addItem($item1);
        $collection->addItem($item2);
        $collection->addItem($item3);

        $totalQty = $collection->getTotalQuantity();

        expect($totalQty)->toBe(10);
    });
});

describe('CartCollection Calculations', function () {
    it('calculates subtotal', function () {
        $items = [
            new CartItem('item-1', 'Item 1', 10.00, 2), // 20.00
            new CartItem('item-2', 'Item 2', 15.00, 3), // 45.00
        ];

        $collection = new CartCollection($items);
        $subtotal = $collection->sum(fn ($item) => $item->price * $item->quantity);

        expect($subtotal)->toBe(65.00);
    });

    it('calculates total quantity', function () {
        $items = [
            new CartItem('item-1', 'Item 1', 10.00, 2),
            new CartItem('item-2', 'Item 2', 20.00, 3),
            new CartItem('item-3', 'Item 3', 30.00, 1),
        ];

        $collection = new CartCollection($items);
        $totalQty = $collection->sum('quantity');

        expect($totalQty)->toBe(6);
    });

    it('filters items by criteria', function () {
        $items = [
            new CartItem('cheap', 'Cheap', 5.00, 1),
            new CartItem('expensive', 'Expensive', 100.00, 1),
            new CartItem('moderate', 'Moderate', 25.00, 1),
        ];

        $collection = new CartCollection($items);
        $expensive = $collection->filter(fn ($item) => $item->price > 20.00);

        expect($expensive->count())->toBe(2);
    });

    it('sorts items by price', function () {
        $items = [
            new CartItem('mid', 'Mid', 50.00, 1),
            new CartItem('low', 'Low', 10.00, 1),
            new CartItem('high', 'High', 100.00, 1),
        ];

        $collection = new CartCollection($items);
        $sorted = $collection->sortBy(fn ($item) => $item->price);

        expect($sorted->first()->id)->toBe('low');
        expect($sorted->last()->id)->toBe('high');
    });
});

describe('CartCollection Search and Find', function () {
    it('finds item by ID', function () {
        $items = [
            new CartItem('item-1', 'Item 1', 10.00, 1),
            new CartItem('item-2', 'Item 2', 20.00, 1),
            new CartItem('item-3', 'Item 3', 30.00, 1),
        ];

        $collection = new CartCollection($items);
        $found = $collection->firstWhere('id', 'item-2');

        expect($found)->not->toBeNull();
        expect($found->name)->toBe('Item 2');
    });

    it('finds items by attribute', function () {
        $item1 = new CartItem('item-1', 'Item 1', 10.00, 1, ['category' => 'electronics']);
        $item2 = new CartItem('item-2', 'Item 2', 20.00, 1, ['category' => 'books']);
        $item3 = new CartItem('item-3', 'Item 3', 30.00, 1, ['category' => 'electronics']);

        $collection = new CartCollection([$item1, $item2, $item3]);
        $electronics = $collection->filter(fn ($item) => $item->getAttribute('category') === 'electronics');

        expect($electronics->count())->toBe(2);
    });

    it('checks if collection contains item', function () {
        $item = new CartItem('item', 'Item', 10.00, 1);
        $collection = new CartCollection([$item]);

        expect($collection->contains($item))->toBeTrue();
        expect($collection->contains('id', 'item'))->toBeTrue();
        expect($collection->contains('id', 'nonexistent'))->toBeFalse();
    });

    it('plucks specific values', function () {
        $items = [
            new CartItem('item-1', 'Item 1', 10.00, 1),
            new CartItem('item-2', 'Item 2', 20.00, 1),
            new CartItem('item-3', 'Item 3', 30.00, 1),
        ];

        $collection = new CartCollection($items);
        $names = $collection->pluck('name');

        expect($names->toArray())->toBe(['Item 1', 'Item 2', 'Item 3']);
    });
});

describe('CartCollection Transformations', function () {
    it('maps items to new values', function () {
        $items = [
            new CartItem('item-1', 'Item 1', 10.00, 2),
            new CartItem('item-2', 'Item 2', 20.00, 1),
        ];

        $collection = new CartCollection($items);
        $totals = $collection->map(fn ($item) => $item->price * $item->quantity);

        expect($totals->toArray())->toBe([20.00, 20.00]);
    });

    it('groups items by attribute', function () {
        $item1 = new CartItem('item-1', 'Item 1', 10.00, 1, ['type' => 'physical']);
        $item2 = new CartItem('item-2', 'Item 2', 20.00, 1, ['type' => 'digital']);
        $item3 = new CartItem('item-3', 'Item 3', 30.00, 1, ['type' => 'physical']);

        $collection = new CartCollection([$item1, $item2, $item3]);
        $grouped = $collection->groupBy(fn ($item) => $item->getAttribute('type'));

        expect($grouped->has('physical'))->toBeTrue();
        expect($grouped->has('digital'))->toBeTrue();
        expect($grouped->get('physical')->count())->toBe(2);
    });

    it('chunks collection into smaller collections', function () {
        $items = collect(range(1, 10))->map(fn ($i) => new CartItem("item-{$i}", "Item {$i}", 10.00, 1));

        $collection = new CartCollection($items->toArray());
        $chunks = $collection->chunk(3);

        expect($chunks->count())->toBe(4);
        expect($chunks->first()->count())->toBe(3);
    });

    it('takes subset of items', function () {
        $items = [
            new CartItem('item-1', 'Item 1', 10.00, 1),
            new CartItem('item-2', 'Item 2', 20.00, 1),
            new CartItem('item-3', 'Item 3', 30.00, 1),
            new CartItem('item-4', 'Item 4', 40.00, 1),
            new CartItem('item-5', 'Item 5', 50.00, 1),
        ];

        $collection = new CartCollection($items);
        $subset = $collection->take(3);

        expect($subset->count())->toBe(3);
        expect($subset->first()->id)->toBe('item-1');
    });
});

describe('CartCollection Aggregations', function () {
    it('calculates minimum price', function () {
        $items = [
            new CartItem('item-1', 'Item 1', 10.00, 1),
            new CartItem('item-2', 'Item 2', 50.00, 1),
            new CartItem('item-3', 'Item 3', 25.00, 1),
        ];

        $collection = new CartCollection($items);
        $min = $collection->min(fn ($item) => $item->price);

        expect($min)->toBe(10.00);
    });

    it('calculates maximum price', function () {
        $items = [
            new CartItem('item-1', 'Item 1', 10.00, 1),
            new CartItem('item-2', 'Item 2', 50.00, 1),
            new CartItem('item-3', 'Item 3', 25.00, 1),
        ];

        $collection = new CartCollection($items);
        $max = $collection->max(fn ($item) => $item->price);

        expect($max)->toBe(50.00);
    });

    it('calculates average price', function () {
        $items = [
            new CartItem('item-1', 'Item 1', 10.00, 1),
            new CartItem('item-2', 'Item 2', 20.00, 1),
            new CartItem('item-3', 'Item 3', 30.00, 1),
        ];

        $collection = new CartCollection($items);
        $avg = $collection->avg(fn ($item) => $item->price);

        expect($avg)->toBe(20.00);
    });

    it('reduces collection to single value', function () {
        $items = [
            new CartItem('item-1', 'Item 1', 10.00, 2),
            new CartItem('item-2', 'Item 2', 15.00, 3),
        ];

        $collection = new CartCollection($items);
        $total = $collection->reduce(
            fn ($carry, $item) => $carry + ($item->price * $item->quantity),
            0
        );

        expect($total)->toBe(65.00);
    });
});

describe('CartCollection Edge Cases', function () {
    it('handles empty collection operations', function () {
        $collection = new CartCollection;

        expect($collection->sum('quantity'))->toBe(0);
        expect($collection->first())->toBeNull();
        expect($collection->last())->toBeNull();
    });

    it('handles single item collection', function () {
        $item = new CartItem('single', 'Single', 10.00, 1);
        $collection = new CartCollection([$item]);

        expect($collection->first())->toBe($item);
        expect($collection->last())->toBe($item);
    });

    it('chains multiple operations', function () {
        $items = [
            new CartItem('item-1', 'Item 1', 10.00, 1),
            new CartItem('item-2', 'Item 2', 50.00, 2),
            new CartItem('item-3', 'Item 3', 25.00, 1),
            new CartItem('item-4', 'Item 4', 5.00, 3),
        ];

        $collection = new CartCollection($items);

        $result = $collection
            ->filter(fn ($item) => $item->price > 5.00)
            ->sortBy(fn ($item) => $item->price)
            ->take(2);

        expect($result->count())->toBe(2);
        expect($result->first()->id)->toBe('item-1');
    });

    it('converts to array', function () {
        $items = [
            new CartItem('item-1', 'Item 1', 10.00, 1),
            new CartItem('item-2', 'Item 2', 20.00, 1),
        ];

        $collection = new CartCollection($items);
        $array = $collection->toArray();

        expect($array)->toBeArray();
        expect(count($array))->toBe(2);
    });
});

describe('CartCollection Totals and Subtotals', function () {
    it('calculates subtotal', function () {
        $item1 = new CartItem('item-1', 'Item 1', 10.00, 2);
        $item2 = new CartItem('item-2', 'Item 2', 20.00, 3);

        $collection = new CartCollection;
        $collection->addItem($item1);
        $collection->addItem($item2);

        $subtotal = $collection->subtotal();

        expect($subtotal)->toBe(80.00); // (10*2) + (20*3)
    });

    it('calculates total', function () {
        $item1 = new CartItem('item-1', 'Item 1', 15.00, 1);
        $item2 = new CartItem('item-2', 'Item 2', 25.00, 2);

        $collection = new CartCollection;
        $collection->addItem($item1);
        $collection->addItem($item2);

        $total = $collection->total();

        expect($total)->toBe(65.00); // 15 + (25*2)
    });

    it('calculates subtotal without conditions', function () {
        $item1 = new CartItem('item-1', 'Item 1', 10.00, 1);
        $item2 = new CartItem('item-2', 'Item 2', 20.00, 1);

        $collection = new CartCollection;
        $collection->addItem($item1);
        $collection->addItem($item2);

        $subtotal = $collection->subtotalWithoutConditions();

        expect($subtotal)->toBe(30.00);
    });

    it('calculates total without conditions', function () {
        $item1 = new CartItem('item-1', 'Item 1', 10.00, 2);
        $item2 = new CartItem('item-2', 'Item 2', 15.00, 1);

        $collection = new CartCollection;
        $collection->addItem($item1);
        $collection->addItem($item2);

        $total = $collection->totalWithoutConditions();

        expect($total)->toBe(35.00); // (10*2) + 15
    });

    it('converts to formatted array', function () {
        $item1 = new CartItem('item-1', 'Item 1', 10.00, 2);
        $item2 = new CartItem('item-2', 'Item 2', 20.00, 1);

        $collection = new CartCollection;
        $collection->addItem($item1);
        $collection->addItem($item2);

        $formatted = $collection->toFormattedArray();

        expect($formatted)->toBeArray();
        expect($formatted)->toHaveKeys(['items', 'total_quantity', 'subtotal', 'total', 'total_without_conditions', 'count', 'is_empty']);
        expect($formatted['count'])->toBe(2);
        expect($formatted['total_quantity'])->toBe(3);
        expect($formatted['is_empty'])->toBeFalse();
    });
});

describe('CartCollection Advanced Filtering', function () {
    it('filters items by attribute', function () {
        $item1 = new CartItem('item-1', 'Item 1', 10.00, 1, ['color' => 'red']);
        $item2 = new CartItem('item-2', 'Item 2', 20.00, 1, ['color' => 'blue']);
        $item3 = new CartItem('item-3', 'Item 3', 30.00, 1, ['color' => 'red']);

        $collection = new CartCollection;
        $collection->addItem($item1);
        $collection->addItem($item2);
        $collection->addItem($item3);

        $redItems = $collection->filterByAttribute('color', 'red');

        expect($redItems->count())->toBe(2);
    });

    it('filters items by attribute existence', function () {
        $item1 = new CartItem('item-1', 'Item 1', 10.00, 1, ['featured' => true]);
        $item2 = new CartItem('item-2', 'Item 2', 20.00, 1);
        $item3 = new CartItem('item-3', 'Item 3', 30.00, 1, ['featured' => false]);

        $collection = new CartCollection;
        $collection->addItem($item1);
        $collection->addItem($item2);
        $collection->addItem($item3);

        $featuredItems = $collection->filterByAttribute('featured');

        expect($featuredItems->count())->toBe(2);
    });

    it('searches items by name', function () {
        $item1 = new CartItem('item-1', 'Red Widget', 10.00, 1);
        $item2 = new CartItem('item-2', 'Blue Gadget', 20.00, 1);
        $item3 = new CartItem('item-3', 'Red Gizmo', 30.00, 1);

        $collection = new CartCollection;
        $collection->addItem($item1);
        $collection->addItem($item2);
        $collection->addItem($item3);

        $results = $collection->searchByName('red');

        expect($results->count())->toBe(2);
        expect($results->pluck('name')->toArray())->toContain('Red Widget');
        expect($results->pluck('name')->toArray())->toContain('Red Gizmo');
    });

    it('filters items by quantity greater than', function () {
        $item1 = new CartItem('item-1', 'Item 1', 10.00, 1);
        $item2 = new CartItem('item-2', 'Item 2', 20.00, 5);
        $item3 = new CartItem('item-3', 'Item 3', 30.00, 10);

        $collection = new CartCollection;
        $collection->addItem($item1);
        $collection->addItem($item2);
        $collection->addItem($item3);

        $results = $collection->whereQuantityGreaterThan(4);

        expect($results->count())->toBe(2);
    });

    it('filters items by quantity less than', function () {
        $item1 = new CartItem('item-1', 'Item 1', 10.00, 1);
        $item2 = new CartItem('item-2', 'Item 2', 20.00, 5);
        $item3 = new CartItem('item-3', 'Item 3', 30.00, 10);

        $collection = new CartCollection;
        $collection->addItem($item1);
        $collection->addItem($item2);
        $collection->addItem($item3);

        $results = $collection->whereQuantityLessThan(6);

        expect($results->count())->toBe(2);
    });

    it('filters items by price range', function () {
        $item1 = new CartItem('item-1', 'Item 1', 10.00, 1);
        $item2 = new CartItem('item-2', 'Item 2', 50.00, 1);
        $item3 = new CartItem('item-3', 'Item 3', 100.00, 1);

        $collection = new CartCollection;
        $collection->addItem($item1);
        $collection->addItem($item2);
        $collection->addItem($item3);

        $results = $collection->wherePriceBetween(20.00, 80.00);

        expect($results->count())->toBe(1);
        expect($results->first()->id)->toBe('item-2');
    });

    it('filters items where quantity is above threshold', function () {
        $item1 = new CartItem('item-1', 'Item 1', 10.00, 1);
        $item2 = new CartItem('item-2', 'Item 2', 20.00, 5);
        $item3 = new CartItem('item-3', 'Item 3', 30.00, 10);

        $collection = new CartCollection;
        $collection->addItem($item1);
        $collection->addItem($item2);
        $collection->addItem($item3);

        $results = $collection->whereQuantityAbove(3);

        expect($results->count())->toBe(2);
    });
});

describe('CartCollection Sorting', function () {
    it('sorts items by price ascending', function () {
        $item1 = new CartItem('item-1', 'Item 1', 50.00, 1);
        $item2 = new CartItem('item-2', 'Item 2', 10.00, 1);
        $item3 = new CartItem('item-3', 'Item 3', 30.00, 1);

        $collection = new CartCollection;
        $collection->addItem($item1);
        $collection->addItem($item2);
        $collection->addItem($item3);

        $sorted = $collection->sortByPrice('asc');
        $values = $sorted->values();

        expect($values->first()->price)->toBe(10.00);
        expect($values->last()->price)->toBe(50.00);
    });

    it('sorts items by price descending', function () {
        $item1 = new CartItem('item-1', 'Item 1', 50.00, 1);
        $item2 = new CartItem('item-2', 'Item 2', 10.00, 1);
        $item3 = new CartItem('item-3', 'Item 3', 30.00, 1);

        $collection = new CartCollection;
        $collection->addItem($item1);
        $collection->addItem($item2);
        $collection->addItem($item3);

        $sorted = $collection->sortByPrice('desc');
        $values = $sorted->values();

        expect($values->first()->price)->toBe(50.00);
        expect($values->last()->price)->toBe(10.00);
    });

    it('sorts items by quantity ascending', function () {
        $item1 = new CartItem('item-1', 'Item 1', 10.00, 5);
        $item2 = new CartItem('item-2', 'Item 2', 20.00, 2);
        $item3 = new CartItem('item-3', 'Item 3', 30.00, 10);

        $collection = new CartCollection;
        $collection->addItem($item1);
        $collection->addItem($item2);
        $collection->addItem($item3);

        $sorted = $collection->sortByQuantity('asc');
        $values = $sorted->values();

        expect($values->first()->quantity)->toBe(2);
        expect($values->last()->quantity)->toBe(10);
    });

    it('sorts items by quantity descending', function () {
        $item1 = new CartItem('item-1', 'Item 1', 10.00, 5);
        $item2 = new CartItem('item-2', 'Item 2', 20.00, 2);
        $item3 = new CartItem('item-3', 'Item 3', 30.00, 10);

        $collection = new CartCollection;
        $collection->addItem($item1);
        $collection->addItem($item2);
        $collection->addItem($item3);

        $sorted = $collection->sortByQuantity('desc');
        $values = $sorted->values();

        expect($values->first()->quantity)->toBe(10);
        expect($values->last()->quantity)->toBe(2);
    });

    it('sorts items by name ascending', function () {
        $item1 = new CartItem('item-1', 'Zebra', 10.00, 1);
        $item2 = new CartItem('item-2', 'Apple', 20.00, 1);
        $item3 = new CartItem('item-3', 'Mango', 30.00, 1);

        $collection = new CartCollection;
        $collection->addItem($item1);
        $collection->addItem($item2);
        $collection->addItem($item3);

        $sorted = $collection->sortByName('asc');
        $values = $sorted->values();

        expect($values->first()->name)->toBe('Apple');
        expect($values->last()->name)->toBe('Zebra');
    });

    it('sorts items by name descending', function () {
        $item1 = new CartItem('item-1', 'Zebra', 10.00, 1);
        $item2 = new CartItem('item-2', 'Apple', 20.00, 1);
        $item3 = new CartItem('item-3', 'Mango', 30.00, 1);

        $collection = new CartCollection;
        $collection->addItem($item1);
        $collection->addItem($item2);
        $collection->addItem($item3);

        $sorted = $collection->sortByName('desc');
        $values = $sorted->values();

        expect($values->first()->name)->toBe('Zebra');
        expect($values->last()->name)->toBe('Apple');
    });
});

describe('CartCollection Grouping', function () {
    it('groups items by attribute', function () {
        $item1 = new CartItem('item-1', 'Item 1', 10.00, 1, ['category' => 'electronics']);
        $item2 = new CartItem('item-2', 'Item 2', 20.00, 1, ['category' => 'books']);
        $item3 = new CartItem('item-3', 'Item 3', 30.00, 1, ['category' => 'electronics']);

        $collection = new CartCollection;
        $collection->addItem($item1);
        $collection->addItem($item2);
        $collection->addItem($item3);

        $grouped = $collection->groupByAttribute('category');

        expect($grouped->has('electronics'))->toBeTrue();
        expect($grouped->has('books'))->toBeTrue();
        expect($grouped->get('electronics')->count())->toBe(2);
        expect($grouped->get('books')->count())->toBe(1);
    });

    it('groups items by property', function () {
        $item1 = new CartItem('item-1', 'Item 1', 10.00, 5);
        $item2 = new CartItem('item-2', 'Item 2', 20.00, 2);
        $item3 = new CartItem('item-3', 'Item 3', 30.00, 5);

        $collection = new CartCollection;
        $collection->addItem($item1);
        $collection->addItem($item2);
        $collection->addItem($item3);

        $grouped = $collection->groupByProperty('quantity');

        expect($grouped->has(5))->toBeTrue();
        expect($grouped->has(2))->toBeTrue();
        expect($grouped->get(5)->count())->toBe(2);
    });
});

describe('CartCollection Statistics', function () {
    it('gets statistics', function () {
        $item1 = new CartItem('item-1', 'Item 1', 10.00, 2);
        $item2 = new CartItem('item-2', 'Item 2', 30.00, 1);
        $item3 = new CartItem('item-3', 'Item 3', 20.00, 3);

        $collection = new CartCollection;
        $collection->addItem($item1);
        $collection->addItem($item2);
        $collection->addItem($item3);

        $stats = $collection->getStatistics();

        expect($stats)->toBeArray();
        expect($stats)->toHaveKeys([
            'total_items',
            'total_quantity',
            'average_price',
            'highest_price',
            'lowest_price',
            'total_value',
            'total_with_conditions',
            'items_with_conditions',
        ]);
        expect($stats['total_items'])->toBe(3);
        expect($stats['total_quantity'])->toBe(6);
        expect($stats['average_price'])->toBe(20.00);
        expect($stats['highest_price'])->toBe(30.00);
        expect($stats['lowest_price'])->toBe(10.00);
    });
});

describe('CartCollection Condition Filtering', function () {
    it('filters items by condition name', function () {
        $discountCondition1 = new MasyukAI\Cart\Conditions\CartCondition(
            name: 'discount',
            type: 'discount',
            target: 'item',
            value: '-10%'
        );

        $taxCondition = new MasyukAI\Cart\Conditions\CartCondition(
            name: 'tax',
            type: 'tax',
            target: 'item',
            value: '+5%'
        );

        $discountCondition2 = new MasyukAI\Cart\Conditions\CartCondition(
            name: 'discount',
            type: 'discount',
            target: 'item',
            value: '-5%'
        );

        $item1 = new CartItem('item-1', 'Item 1', 10.00, 1, [], [$discountCondition1]);
        $item2 = new CartItem('item-2', 'Item 2', 20.00, 1, [], [$taxCondition]);
        $item3 = new CartItem('item-3', 'Item 3', 30.00, 1, [], [$discountCondition2]);

        $collection = new CartCollection;
        $collection->addItem($item1);
        $collection->addItem($item2);
        $collection->addItem($item3);

        $discounted = $collection->filterByCondition('discount');

        expect($discounted->count())->toBe(2);
        expect($discounted->pluck('id')->toArray())->toContain('item-1');
        expect($discounted->pluck('id')->toArray())->toContain('item-3');
    });

    it('returns empty collection when no items match condition name', function () {
        $item = new CartItem('item-1', 'Item 1', 10.00, 1);

        $collection = new CartCollection;
        $collection->addItem($item);

        $filtered = $collection->filterByCondition('nonexistent');

        expect($filtered->isEmpty())->toBeTrue();
    });

    it('filters items by condition type', function () {
        $holidaySale = new MasyukAI\Cart\Conditions\CartCondition(
            name: 'holiday-sale',
            type: 'discount',
            target: 'item',
            value: '-20%'
        );

        $vat = new MasyukAI\Cart\Conditions\CartCondition(
            name: 'vat',
            type: 'tax',
            target: 'item',
            value: '+15%'
        );

        $memberDiscount = new MasyukAI\Cart\Conditions\CartCondition(
            name: 'member-discount',
            type: 'discount',
            target: 'item',
            value: '-10%'
        );

        $item1 = new CartItem('item-1', 'Item 1', 10.00, 1, [], [$holidaySale]);
        $item2 = new CartItem('item-2', 'Item 2', 20.00, 1, [], [$vat]);
        $item3 = new CartItem('item-3', 'Item 3', 30.00, 1, [], [$memberDiscount]);

        $collection = new CartCollection;
        $collection->addItem($item1);
        $collection->addItem($item2);
        $collection->addItem($item3);

        $taxed = $collection->filterByConditionType('tax');
        $discounted = $collection->filterByConditionType('discount');

        expect($taxed->count())->toBe(1);
        expect($taxed->first()->id)->toBe('item-2');
        expect($discounted->count())->toBe(2);
    });

    it('filters items by condition target', function () {
        $subtotalDiscount = new MasyukAI\Cart\Conditions\CartCondition(
            name: 'subtotal-discount',
            type: 'discount',
            target: 'subtotal',
            value: '-10%'
        );

        $itemDiscount = new MasyukAI\Cart\Conditions\CartCondition(
            name: 'item-discount',
            type: 'discount',
            target: 'item',
            value: '-5%'
        );

        $subtotalTax = new MasyukAI\Cart\Conditions\CartCondition(
            name: 'subtotal-tax',
            type: 'tax',
            target: 'subtotal',
            value: '+8%'
        );

        $item1 = new CartItem('item-1', 'Item 1', 10.00, 1, [], [$subtotalDiscount]);
        $item2 = new CartItem('item-2', 'Item 2', 20.00, 1, [], [$itemDiscount]);
        $item3 = new CartItem('item-3', 'Item 3', 30.00, 1, [], [$subtotalTax]);

        $collection = new CartCollection;
        $collection->addItem($item1);
        $collection->addItem($item2);
        $collection->addItem($item3);

        $subtotalTargets = $collection->filterByConditionTarget('subtotal');
        $itemTargets = $collection->filterByConditionTarget('item');

        expect($subtotalTargets->count())->toBe(2);
        expect($itemTargets->count())->toBe(1);
        expect($itemTargets->first()->id)->toBe('item-2');
    });

    it('filters items by condition value', function () {
        $discount1 = new MasyukAI\Cart\Conditions\CartCondition(
            name: 'discount-1',
            type: 'discount',
            target: 'item',
            value: '-10%'
        );

        $discount2 = new MasyukAI\Cart\Conditions\CartCondition(
            name: 'discount-2',
            type: 'discount',
            target: 'item',
            value: '-5%'
        );

        $discount3 = new MasyukAI\Cart\Conditions\CartCondition(
            name: 'discount-3',
            type: 'discount',
            target: 'item',
            value: '-10%'
        );

        $item1 = new CartItem('item-1', 'Item 1', 10.00, 1, [], [$discount1]);
        $item2 = new CartItem('item-2', 'Item 2', 20.00, 1, [], [$discount2]);
        $item3 = new CartItem('item-3', 'Item 3', 30.00, 1, [], [$discount3]);

        $collection = new CartCollection;
        $collection->addItem($item1);
        $collection->addItem($item2);
        $collection->addItem($item3);

        $tenPercent = $collection->filterByConditionValue('-10%');
        $fivePercent = $collection->filterByConditionValue('-5%');

        expect($tenPercent->count())->toBe(2);
        expect($fivePercent->count())->toBe(1);
        expect($fivePercent->first()->id)->toBe('item-2');
    });

    it('filters items by numeric condition value', function () {
        $fixedDiscount = new MasyukAI\Cart\Conditions\CartCondition(
            name: 'fixed-discount',
            type: 'discount',
            target: 'item',
            value: -5.00
        );

        $fixedTax = new MasyukAI\Cart\Conditions\CartCondition(
            name: 'fixed-tax',
            type: 'tax',
            target: 'item',
            value: 3.50
        );

        $item1 = new CartItem('item-1', 'Item 1', 10.00, 1, [], [$fixedDiscount]);
        $item2 = new CartItem('item-2', 'Item 2', 20.00, 1, [], [$fixedTax]);

        $collection = new CartCollection;
        $collection->addItem($item1);
        $collection->addItem($item2);

        $discount = $collection->filterByConditionValue(-5.00);
        $tax = $collection->filterByConditionValue(3.50);

        expect($discount->count())->toBe(1);
        expect($discount->first()->id)->toBe('item-1');
        expect($tax->count())->toBe(1);
        expect($tax->first()->id)->toBe('item-2');
    });

    it('checks if collection has items with conditions', function () {
        $discount = new MasyukAI\Cart\Conditions\CartCondition(
            name: 'discount',
            type: 'discount',
            target: 'item',
            value: '-10%'
        );

        $item1 = new CartItem('item-1', 'Item 1', 10.00, 1, [], [$discount]);
        $item2 = new CartItem('item-2', 'Item 2', 20.00, 1);

        $collection = new CartCollection;
        $collection->addItem($item1);
        $collection->addItem($item2);

        expect($collection->hasItemsWithConditions())->toBeTrue();
    });

    it('returns false when no items have conditions', function () {
        $item1 = new CartItem('item-1', 'Item 1', 10.00, 1);
        $item2 = new CartItem('item-2', 'Item 2', 20.00, 1);

        $collection = new CartCollection;
        $collection->addItem($item1);
        $collection->addItem($item2);

        expect($collection->hasItemsWithConditions())->toBeFalse();
    });

    it('gets total discount amount from all items', function () {
        $discount1 = new MasyukAI\Cart\Conditions\CartCondition(
            name: 'discount-1',
            type: 'discount',
            target: 'item',
            value: '-10%'
        );

        $discount2 = new MasyukAI\Cart\Conditions\CartCondition(
            name: 'discount-2',
            type: 'discount',
            target: 'item',
            value: '-20%'
        );

        $item1 = new CartItem('item-1', 'Item 1', 100.00, 1, [], [$discount1]);
        $item2 = new CartItem('item-2', 'Item 2', 50.00, 1, [], [$discount2]);

        $collection = new CartCollection;
        $collection->addItem($item1);
        $collection->addItem($item2);

        $totalDiscount = $collection->getTotalDiscount();

        expect($totalDiscount)->toBeGreaterThan(0);
    });

    it('returns zero discount when no items have discounts', function () {
        $item1 = new CartItem('item-1', 'Item 1', 10.00, 1);
        $item2 = new CartItem('item-2', 'Item 2', 20.00, 1);

        $collection = new CartCollection;
        $collection->addItem($item1);
        $collection->addItem($item2);

        $totalDiscount = $collection->getTotalDiscount();

        expect($totalDiscount)->toBe(0.0);
    });
});

describe('CartCollection Model Filtering', function () {
    it('filters items by model class', function () {
        $product = new class
        {
            public static function getMorphClass(): string
            {
                return 'Product';
            }
        };

        $service = new class
        {
            public static function getMorphClass(): string
            {
                return 'Service';
            }
        };

        $item1 = new CartItem('item-1', 'Item 1', 10.00, 1, [], [], $product);
        $item2 = new CartItem('item-2', 'Item 2', 20.00, 1, [], [], $service);
        $item3 = new CartItem('item-3', 'Item 3', 30.00, 1, [], [], $product);

        $collection = new CartCollection;
        $collection->addItem($item1);
        $collection->addItem($item2);
        $collection->addItem($item3);

        $products = $collection->filterByModel(get_class($product));
        $services = $collection->filterByModel(get_class($service));

        expect($products->count())->toBe(2);
        expect($services->count())->toBe(1);
    });

    it('filters items where model matches using whereModel', function () {
        $product = new class
        {
            public static function getMorphClass(): string
            {
                return 'App\\Models\\Product';
            }
        };

        $item1 = new CartItem('item-1', 'Item 1', 10.00, 1, [], [], $product);
        $item2 = new CartItem('item-2', 'Item 2', 20.00, 1);

        $collection = new CartCollection;
        $collection->addItem($item1);
        $collection->addItem($item2);

        $results = $collection->whereModel(get_class($product));

        expect($results->count())->toBe(1);
        expect($results->first()->id)->toBe('item-1');
    });

    it('returns empty collection when no items match model', function () {
        $item = new CartItem('item-1', 'Item 1', 10.00, 1);

        $collection = new CartCollection;
        $collection->addItem($item);

        $results = $collection->filterByModel('NonExistentModel');

        expect($results->isEmpty())->toBeTrue();
    });
});

describe('CartCollection Unique Operations', function () {
    it('gets unique items by property', function () {
        $item1 = new CartItem('item-1', 'Item 1', 10.00, 1, ['color' => 'red']);
        $item2 = new CartItem('item-2', 'Item 2', 20.00, 1, ['color' => 'blue']);
        $item3 = new CartItem('item-3', 'Item 3', 30.00, 1, ['color' => 'red']);

        $collection = new CartCollection;
        $collection->addItem($item1);
        $collection->addItem($item2);
        $collection->addItem($item3);

        $unique = $collection->uniqueBy('name');

        expect($unique->count())->toBe(3);
    });
});
