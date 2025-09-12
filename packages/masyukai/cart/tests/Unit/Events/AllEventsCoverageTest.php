<?php

declare(strict_types=1);

use MasyukAI\Cart\Cart;
use MasyukAI\Cart\Events\CartCleared;
use MasyukAI\Cart\Events\CartCreated;
use MasyukAI\Cart\Events\CartMerged;
use MasyukAI\Cart\Events\CartUpdated;
use MasyukAI\Cart\Events\ItemAdded;
use MasyukAI\Cart\Events\ItemRemoved;
use MasyukAI\Cart\Events\ItemUpdated;
use MasyukAI\Cart\Models\CartItem;
use MasyukAI\Cart\Storage\SessionStorage;

describe('Event Classes Basic Coverage', function () {
    beforeEach(function () {
        $sessionStore = new \Illuminate\Session\Store('testing', new \Illuminate\Session\ArraySessionHandler(120));
        $this->storage = new SessionStorage($sessionStore);

        if (! app()->bound('events')) {
            app()->singleton('events', function ($app) {
                return new \Illuminate\Events\Dispatcher($app);
            });
        }

        $this->cart = new Cart(
            $this->storage,
            'test-user',
            app('events'),
            'default',
            true
        );

        $this->item = new CartItem(
            id: 'test_item',
            name: 'Test Item',
            price: 10.00,
            quantity: 1
        );
    });

    describe('CartMerged Event', function () {
        it('can be instantiated', function () {
            $targetCart = $this->cart;
            $sourceCart = $this->cart->setInstance('source', app('events'));

            $event = new CartMerged($targetCart, $sourceCart, 5, 'add_quantities', true);

            expect($event->targetCart)->toBe($targetCart);
            expect($event->sourceCart)->toBe($sourceCart);
            expect($event->totalItemsMerged)->toBe(5);
            expect($event->mergeStrategy)->toBe('add_quantities');
            expect($event->hadConflicts)->toBeTrue();
        });

        it('can convert to array', function () {
            $event = new CartMerged($this->cart, $this->cart, 3, 'keep_highest', false);
            $array = $event->toArray();

            expect($array)->toHaveKey('target_cart');
            expect($array)->toHaveKey('source_cart');
            expect($array)->toHaveKey('merge_details');
            expect($array)->toHaveKey('timestamp');
            expect($array['merge_details']['items_merged'])->toBe(3);
        });
    });

    describe('CartCleared Event', function () {
        it('can be instantiated', function () {
            $event = new CartCleared($this->cart);

            expect($event->cart)->toBe($this->cart);
            expect($event->cart)->toBeInstanceOf(Cart::class);
        });

        it('can convert to array', function () {
            $event = new CartCleared($this->cart);
            $array = $event->toArray();

            expect($array)->toHaveKey('identifier');
            expect($array)->toHaveKey('instance_name');
            expect($array)->toHaveKey('timestamp');
        });
    });

    describe('CartCreated Event', function () {
        it('can be instantiated', function () {
            $event = new CartCreated($this->cart);

            expect($event->cart)->toBe($this->cart);
            expect($event->cart)->toBeInstanceOf(Cart::class);
        });

        it('can convert to array', function () {
            $event = new CartCreated($this->cart);
            $array = $event->toArray();

            expect($array)->toHaveKey('identifier');
            expect($array)->toHaveKey('instance_name');
            expect($array)->toHaveKey('timestamp');
        });
    });

    describe('CartUpdated Event', function () {
        it('can be instantiated', function () {
            $event = new CartUpdated($this->cart);

            expect($event->cart)->toBe($this->cart);
            expect($event->cart)->toBeInstanceOf(Cart::class);
        });

        it('can convert to array', function () {
            $event = new CartUpdated($this->cart);
            $array = $event->toArray();

            expect($array)->toHaveKey('identifier');
            expect($array)->toHaveKey('instance_name');
            expect($array)->toHaveKey('timestamp');
        });
    });

    describe('ItemAdded Event', function () {
        it('can be instantiated', function () {
            $event = new ItemAdded($this->item, $this->cart);

            expect($event->item)->toBe($this->item);
            expect($event->cart)->toBe($this->cart);
        });

        it('can convert to array', function () {
            $event = new ItemAdded($this->item, $this->cart);
            $array = $event->toArray();

            expect($array)->toHaveKey('item_id');
            expect($array)->toHaveKey('item_name');
            expect($array)->toHaveKey('quantity');
            expect($array)->toHaveKey('price');
            expect($array)->toHaveKey('identifier');
            expect($array)->toHaveKey('instance_name');
            expect($array)->toHaveKey('timestamp');
        });

        it('handles different items', function () {
            $expensiveItem = new CartItem('exp', 'Expensive', 999.99, 1);
            $bulkItem = new CartItem('bulk', 'Bulk', 1.99, 100);

            $event1 = new ItemAdded($expensiveItem, $this->cart);
            $event2 = new ItemAdded($bulkItem, $this->cart);

            expect($event1->item->price)->toBe(999.99);
            expect($event2->item->quantity)->toBe(100);
        });
    });

    describe('ItemRemoved Event', function () {
        it('can be instantiated', function () {
            $event = new ItemRemoved($this->item, $this->cart);

            expect($event->item)->toBe($this->item);
            expect($event->cart)->toBe($this->cart);
        });

        it('can convert to array', function () {
            $event = new ItemRemoved($this->item, $this->cart);
            $array = $event->toArray();

            expect($array)->toHaveKey('item_id');
            expect($array)->toHaveKey('item_name');
            expect($array)->toHaveKey('quantity');
            expect($array)->toHaveKey('price');
            expect($array)->toHaveKey('identifier');
            expect($array)->toHaveKey('instance_name');
            expect($array)->toHaveKey('timestamp');
        });

        it('handles different quantities', function () {
            $singleItem = new CartItem('single', 'Single', 5.00, 1);
            $multiItem = new CartItem('multi', 'Multi', 2.50, 10);

            $event1 = new ItemRemoved($singleItem, $this->cart);
            $event2 = new ItemRemoved($multiItem, $this->cart);

            expect($event1->item->quantity)->toBe(1);
            expect($event2->item->quantity)->toBe(10);
        });
    });

    describe('ItemUpdated Event', function () {
        it('can be instantiated', function () {
            $event = new ItemUpdated($this->item, $this->cart);

            expect($event->item)->toBe($this->item);
            expect($event->cart)->toBe($this->cart);
        });

        it('can convert to array', function () {
            $event = new ItemUpdated($this->item, $this->cart);
            $array = $event->toArray();

            expect($array)->toHaveKey('item_id');
            expect($array)->toHaveKey('item_name');
            expect($array)->toHaveKey('quantity');
            expect($array)->toHaveKey('price');
            expect($array)->toHaveKey('identifier');
            expect($array)->toHaveKey('instance_name');
            expect($array)->toHaveKey('timestamp');
        });

        it('handles price and quantity updates', function () {
            $originalItem = new CartItem('orig', 'Original', 10.00, 2);
            $updatedItem = new CartItem('orig', 'Original Updated', 15.50, 3);

            $event1 = new ItemUpdated($originalItem, $this->cart);
            $event2 = new ItemUpdated($updatedItem, $this->cart);

            expect($event1->item->price)->toBe(10.00);
            expect($event1->item->quantity)->toBe(2);
            expect($event2->item->price)->toBe(15.50);
            expect($event2->item->quantity)->toBe(3);
        });
    });

    describe('Event Properties and Methods', function () {
        it('events maintain readonly properties', function () {
            $event = new ItemAdded($this->item, $this->cart);

            // Readonly properties should be accessible but not modifiable
            expect($event->item->id)->toBe('test_item');
            expect($event->cart->instance())->toBe('default');
        });

        it('timestamp generation works', function () {
            $event = new CartCreated($this->cart);
            $array = $event->toArray();

            expect($array['timestamp'])->toBeString();
            // Check if it's a valid ISO 8601 timestamp format
            expect($array['timestamp'])->toMatch('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{6}Z$/');
        });

        it('works with different cart instances', function () {
            $defaultCart = $this->cart;
            $wishlistCart = $this->cart->setInstance('wishlist', app('events'));

            $event1 = new CartCreated($defaultCart);
            $event2 = new CartCreated($wishlistCart);

            expect($event1->cart->instance())->toBe('default');
            expect($event2->cart->instance())->toBe('wishlist');
        });

        it('handles complex scenarios', function () {
            // Test with attributes
            $itemWithAttrs = new CartItem(
                id: 'complex',
                name: 'Complex Item',
                price: 25.99,
                quantity: 3,
                attributes: ['color' => 'red', 'size' => 'large']
            );

            $event = new ItemAdded($itemWithAttrs, $this->cart);
            $array = $event->toArray();

            expect($array['item_id'])->toBe('complex');
            expect($array['price'])->toBe(25.99);
            expect($event->item->attributes['color'])->toBe('red');
        });
    });
});
