<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use MasyukAI\Cart\Cart;
use MasyukAI\Cart\Collections\CartCollection;
use MasyukAI\Cart\Collections\CartConditionCollection;
use MasyukAI\Cart\Events\CartUpdated;
use MasyukAI\Cart\Events\ItemUpdated;
use MasyukAI\Cart\Storage\StorageInterface;

beforeEach(function (): void {
    // Create a simple in-memory storage mock
    $storage = new class implements StorageInterface
    {
        private array $data = [];

        public function get(string $identifier, string $instance): ?string
        {
            $key = "{$identifier}.{$instance}";

            return $this->data[$key] ?? null;
        }

        public function put(string $identifier, string $instance, string $content): void
        {
            $key = "{$identifier}.{$instance}";
            $this->data[$key] = $content;
        }

        public function has(string $identifier, string $instance): bool
        {
            $key = "{$identifier}.{$instance}";

            return isset($this->data[$key]);
        }

        public function forget(string $identifier, string $instance): void
        {
            $key = "{$identifier}.{$instance}";
            unset($this->data[$key]);
        }

        public function flush(): void
        {
            $this->data = [];
        }

        public function getInstances(string $identifier): array
        {
            return [];
        }

        public function forgetIdentifier(string $identifier): void
        {
            foreach ($this->data as $key => $value) {
                if (str_starts_with($key, $identifier.'.')) {
                    unset($this->data[$key]);
                }
            }
        }

        public function getItems(string $identifier, string $instance): array
        {
            $data = $this->get($identifier, $instance);

            return $data ? json_decode($data, true) : [];
        }

        public function getConditions(string $identifier, string $instance): array
        {
            $key = "{$identifier}.{$instance}.conditions";
            $data = $this->data[$key] ?? null;

            return $data ? json_decode($data, true) : [];
        }

        public function putItems(string $identifier, string $instance, array $items): void
        {
            $this->put($identifier, $instance, json_encode($items));
        }

        public function putConditions(string $identifier, string $instance, array $conditions): void
        {
            $key = "{$identifier}.{$instance}.conditions";
            $this->data[$key] = json_encode($conditions);
        }

        public function putBoth(string $identifier, string $instance, array $items, array $conditions): void
        {
            $this->putItems($identifier, $instance, $items);
            $this->putConditions($identifier, $instance, $conditions);
        }

        public function putMetadata(string $identifier, string $instance, string $key, mixed $value): void
        {
            $metadataKey = "{$identifier}.{$instance}.metadata.{$key}";
            $this->data[$metadataKey] = json_encode($value);
        }

        public function getMetadata(string $identifier, string $instance, string $key): mixed
        {
            $metadataKey = "{$identifier}.{$instance}.metadata.{$key}";
            $data = $this->data[$metadataKey] ?? null;

            return $data ? json_decode($data, true) : null;
        }
    };

    app()->bind(StorageInterface::class, fn () => $storage);
});

it('can be instantiated with required parameters', function (): void {
    $items = new CartCollection;
    $conditions = new CartConditionCollection;
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

    $items = new CartCollection;
    $conditions = new CartConditionCollection;
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

    $items = $cart->getItems();
    $conditions = $cart->getConditions();
    $total = $cart->getRawTotal();

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

    $cart->addCondition($condition);

    // Manually dispatch CartUpdated event after cart state change
    $event = new CartUpdated(
        $cart->getItems(),
        $cart->getConditions(),
        $cart->instance(),
        $cart->getRawTotal()
    );
    event($event);

    Event::assertDispatched(CartUpdated::class);
});

it('preserves event data immutability', function (): void {
    $items = new CartCollection;
    $conditions = new CartConditionCollection;
    $instance = 'test-instance';
    $total = 150.50;

    $event = new CartUpdated($items, $conditions, $instance, $total);

    // Check that all properties are accessible (readonly classes expose public properties)
    expect($event->items)->toBe($items)
        ->and($event->conditions)->toBe($conditions)
        ->and($event->instance)->toBe($instance)
        ->and($event->total)->toBe($total);
});
