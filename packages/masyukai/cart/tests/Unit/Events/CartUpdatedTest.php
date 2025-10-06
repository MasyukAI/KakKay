<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use MasyukAI\Cart\Cart;
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

        public function swapIdentifier(string $oldIdentifier, string $newIdentifier, string $instance): bool
        {
            $oldKey = "{$oldIdentifier}.{$instance}";
            $newKey = "{$newIdentifier}.{$instance}";

            if (! isset($this->data[$oldKey])) {
                return false;
            }

            // Move data from old to new identifier
            $this->data[$newKey] = $this->data[$oldKey];
            unset($this->data[$oldKey]);

            // Also move conditions and metadata if they exist
            $oldConditionsKey = "{$oldIdentifier}.{$instance}.conditions";
            $newConditionsKey = "{$newIdentifier}.{$instance}.conditions";
            if (isset($this->data[$oldConditionsKey])) {
                $this->data[$newConditionsKey] = $this->data[$oldConditionsKey];
                unset($this->data[$oldConditionsKey]);
            }

            // Move metadata
            foreach ($this->data as $key => $value) {
                if (str_starts_with($key, "{$oldIdentifier}.{$instance}.metadata.")) {
                    $metadataKeySuffix = mb_substr($key, mb_strlen("{$oldIdentifier}.{$instance}.metadata."));
                    $newMetadataKey = "{$newIdentifier}.{$instance}.metadata.{$metadataKeySuffix}";
                    $this->data[$newMetadataKey] = $value;
                    unset($this->data[$key]);
                }
            }

            return true;
        }

        public function getVersion(string $identifier, string $instance): ?int
        {
            return null;
        }

        public function getId(string $identifier, string $instance): ?string
        {
            return null;
        }
    };

    app()->bind(StorageInterface::class, fn () => $storage);
});

it('can be instantiated with required parameters', function (): void {
    $cart = new Cart(
        identifier: 'event_test',
        storage: app(StorageInterface::class),
        events: app('events'),
        instanceName: 'default',
        eventsEnabled: true
    );
    $cart->add('product-1', 'Product 1', 100, 2);
    $reason = 'item_added';

    $event = new CartUpdated($cart, $reason);

    expect($event->cart)->toBe($cart)
        ->and($event->reason)->toBe($reason);
});

it('can be dispatched manually', function (): void {
    Event::fake();

    $cart = new Cart(
        identifier: 'event_test',
        storage: app(StorageInterface::class),
        events: app('events'),
        instanceName: 'default',
        eventsEnabled: true
    );
    $cart->add('product-1', 'Product 1', 100, 2);
    $reason = 'manual_test';

    $event = new CartUpdated($cart, $reason);
    event($event);

    Event::assertDispatched(CartUpdated::class, function (CartUpdated $event) use ($cart, $reason) {
        return $event->cart === $cart && $event->reason === $reason;
    });
});

it('contains proper cart data when event is created', function (): void {
    $cart = new Cart(
        identifier: 'event_test',
        storage: app(StorageInterface::class),
        events: app('events'),
        instanceName: 'default',
        eventsEnabled: true
    );
    $cart->add('product-1', 'Product 1', 100, 2);

    $event = new CartUpdated($cart, 'test_update');

    expect($event->cart->getRawTotal())->toBe(200.0)
        ->and($event->cart->has('product-1'))->toBeTrue()
        ->and($event->cart->instance())->toBe('default')
        ->and($event->reason)->toBe('test_update');
});

it('cart update triggers ItemUpdated event which could fire CartUpdated', function (): void {
    Event::fake();

    $cart = new Cart(
        identifier: 'event_test',
        storage: app(StorageInterface::class),
        events: app('events'),
        instanceName: 'default',
        eventsEnabled: true
    );
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

    $cart = new Cart(
        identifier: 'event_test',
        storage: app(StorageInterface::class),
        events: app('events'),
        instanceName: 'default',
        eventsEnabled: true
    );
    $cart->add('product-1', 'Product 1', 100, 2);

    $condition = new MasyukAI\Cart\Conditions\CartCondition(
        'item-discount',
        'discount',
        'subtotal',
        '-10%'
    );

    $cart->addCondition($condition);

    // Manually dispatch CartUpdated event after cart state change
    $event = new CartUpdated($cart, 'condition_added');
    event($event);

    Event::assertDispatched(CartUpdated::class);
});

it('preserves event data immutability', function (): void {
    $cart = new Cart(
        identifier: 'event_test',
        storage: app(StorageInterface::class),
        events: app('events'),
        instanceName: 'default',
        eventsEnabled: true
    );
    $cart->add('product-1', 'Product 1', 100, 2);
    $reason = 'immutability_test';

    $event = new CartUpdated($cart, $reason);

    // Check that all properties are accessible (readonly properties)
    expect($event->cart)->toBe($cart)
        ->and($event->reason)->toBe($reason);
});

it('converts event to array with all cart data', function (): void {
    $cart = new Cart(
        identifier: 'cart-123',
        storage: app(StorageInterface::class),
        events: app('events'),
        instanceName: 'shopping',
        eventsEnabled: true
    );
    $cart->add('product-1', 'Product 1', 50.00, 2);
    $cart->add('product-2', 'Product 2', 30.00, 1);

    $event = new CartUpdated($cart, 'items_added');
    $array = $event->toArray();

    expect($array)->toBeArray()
        ->and($array)->toHaveKey('identifier', 'cart-123')
        ->and($array)->toHaveKey('instance_name', 'shopping')
        ->and($array)->toHaveKey('reason', 'items_added')
        ->and($array)->toHaveKey('items_count', 2)
        ->and($array)->toHaveKey('total_quantity', 3)
        ->and($array)->toHaveKey('subtotal', 130.00)
        ->and($array)->toHaveKey('total', 130.00)
        ->and($array)->toHaveKey('conditions_count', 0)
        ->and($array)->toHaveKey('timestamp');
});

it('converts event to array without reason', function (): void {
    $cart = new Cart(
        identifier: 'cart-456',
        storage: app(StorageInterface::class),
        events: app('events'),
        instanceName: 'default',
        eventsEnabled: true
    );

    $event = new CartUpdated($cart);
    $array = $event->toArray();

    expect($array)->toBeArray()
        ->and($array)->toHaveKey('identifier', 'cart-456')
        ->and($array)->toHaveKey('reason')
        ->and($array['reason'])->toBeNull()
        ->and($array)->toHaveKey('items_count', 0)
        ->and($array)->toHaveKey('timestamp');
});

it('includes conditions count in array', function (): void {
    $cart = new Cart(
        identifier: 'cart-789',
        storage: app(StorageInterface::class),
        events: app('events'),
        instanceName: 'default',
        eventsEnabled: true
    );
    $cart->add('product-1', 'Product 1', 100.00, 1);
    $cart->addCondition(new MasyukAI\Cart\Conditions\CartCondition('tax', 'percentage', 'total', '5.0'));
    $cart->addCondition(new MasyukAI\Cart\Conditions\CartCondition('discount', 'percentage', 'subtotal', '-10.0'));

    $event = new CartUpdated($cart, 'conditions_applied');
    $array = $event->toArray();

    expect($array)->toHaveKey('conditions_count', 2)
        ->and($array)->toHaveKey('reason', 'conditions_applied');
});
