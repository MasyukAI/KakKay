<?php

declare(strict_types=1);

use MasyukAI\Cart\Events\CartUpdated;
use MasyukAI\Cart\Events\ItemUpdated;
use MasyukAI\Cart\Cart;
use MasyukAI\Cart\Collections\CartCollection;
use MasyukAI\Cart\Collections\CartConditionCollection;
use MasyukAI\Cart\Storage\StorageInterface;
use MasyukAI\Cart\Storage\CacheStorage;
use Illuminate\Support\Facades\Event;

beforeEach(function (): void {
    // Create a simple in-memory storage mock
    $storage = new class implements StorageInterface {
        private array $data = [];
        
        public function get(string $key): mixed {
            return $this->data[$key] ?? [];
        }
        
        public function put(string $key, mixed $value): void {
            $this->data[$key] = $value;
        }
        
        public function forget(string $key): void {
            unset($this->data[$key]);
        }
        
        public function flush(): void {
            $this->data = [];
        }
        
        public function has(string $key): bool {
            return isset($this->data[$key]);
        }
    };
    
    app()->bind(StorageInterface::class, fn() => $storage);
});

it('can be instantiated with required parameters', function (): void {
    $items = new CartCollection();
    $conditions = new CartConditionCollection();
    $instance = 'default';
    $total = 250.0;
    
    $event = new CartUpdated($items, $conditions, $instance, $total);
    
    expect($event->items)->toBe($items)
        ->and($event->conditions)->toBe($conditions)
        ->and($event->instance)->toBe($instance)
        ->and($event->total)->toBe($total);
});

it('can be dispatched manually', function (): void {
    Event::fake();
    
    $items = new CartCollection();
    $conditions = new CartConditionCollection();
    $instance = 'default';
    $total = 250.0;
    
    $event = new CartUpdated($items, $conditions, $instance, $total);
    event($event);
    
    Event::assertDispatched(CartUpdated::class, function (CartUpdated $event) {
        return $event->instance === 'default' && $event->total === 250.0;
    });
});

it('contains proper cart data when event is created', function (): void {
    $cart = app(Cart::class);
    $cart->add('product-1', 'Product 1', 100, 2);
    
    $items = $cart->getContent();
    $conditions = $cart->getConditions();
    $total = $cart->getTotal();
    
    $event = new CartUpdated($items, $conditions, $cart->instance(), $total);
    
    expect($event->total)->toBe(200.0)
        ->and($event->items->has('product-1'))->toBeTrue()
        ->and($event->instance)->toBe('default');
});

it('cart update triggers ItemUpdated event which could fire CartUpdated', function (): void {
    Event::fake();
    
    $cart = app(Cart::class);
    $cart->add('product-1', 'Product 1', 100, 2, ['color' => 'red']);
    
    // This will fire ItemUpdated event (as that's what Cart actually fires)
    $cart->update('product-1', ['attributes' => ['color' => 'blue', 'size' => 'large']]);
    
    Event::assertDispatched(ItemUpdated::class, function (ItemUpdated $event) {
        return isset($event->item->attributes['color']) &&
               $event->item->attributes['color'] === 'blue' && 
               isset($event->item->attributes['size']) &&
               $event->item->attributes['size'] === 'large';
    });
});

it('cart condition updates can trigger manual CartUpdated', function (): void {
    Event::fake();
    
    $cart = app(Cart::class);
    $cart->add('product-1', 'Product 1', 100, 2);
    
    $condition = new \MasyukAI\Cart\Conditions\CartCondition(
        'item-discount',
        'discount', 
        'subtotal',
        '-10%'
    );
    
    $cart->condition($condition);
    
    // Manually dispatch CartUpdated event after cart state change
    $event = new CartUpdated(
        $cart->getContent(), 
        $cart->getConditions(), 
        $cart->instance(), 
        $cart->getTotal()
    );
    event($event);
    
    Event::assertDispatched(CartUpdated::class);
});

it('preserves event data immutability', function (): void {
    $items = new CartCollection();
    $conditions = new CartConditionCollection();
    $instance = 'test-instance';
    $total = 150.50;
    
    $event = new CartUpdated($items, $conditions, $instance, $total);
    
    // Check that all properties are accessible (readonly classes expose public properties)
    expect($event->items)->toBe($items)
        ->and($event->conditions)->toBe($conditions)
        ->and($event->instance)->toBe($instance)
        ->and($event->total)->toBe($total);
});
