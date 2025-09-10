<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use MasyukAI\Cart\CartManager;
use MasyukAI\Cart\Storage\DatabaseStorage;
use MasyukAI\Cart\Storage\SessionStorage;

describe('CartManager Coverage Tests', function () {
    beforeEach(function () {
        $this->storage = new SessionStorage(app('session.store'), 'cart');
        $this->events = app('events');
        $this->config = config('cart');
        $this->manager = new CartManager($this->storage, $this->events, true, $this->config);
    });

    it('can get cart instance without changing global state', function () {
        $instance = $this->manager->getCartInstance('test-instance');

        expect($instance)->toBeInstanceOf(\MasyukAI\Cart\Cart::class);
        expect($this->manager->instance())->toBe('default'); // Should not change global state
    });

    it('can get session storage when using session storage', function () {
        $sessionStorage = new SessionStorage(app('session.store'), 'cart');
        $manager = new CartManager($sessionStorage, $this->events, true, $this->config);

        $session = $manager->session();

        expect($session)->toBeInstanceOf(SessionStorage::class);
    });

    it('creates temporary session storage when not using session storage', function () {
        $databaseStorage = new DatabaseStorage(
            DB::connection(),
            config('cart.database.table', 'cart')
        );
        $manager = new CartManager($databaseStorage, $this->events, true, $this->config);

        $session = $manager->session();

        expect($session)->toBeInstanceOf(SessionStorage::class);
    });

    it('can use custom session key for session storage', function () {
        $customKey = 'custom-cart-key';
        $session = $this->manager->session($customKey);

        expect($session)->toBeInstanceOf(SessionStorage::class);
    });

    it('proxies method calls to current cart instance', function () {
        $this->manager->add('test-item', 'Test Item', 10.00, 1);

        expect($this->manager->count())->toBe(1);
        expect($this->manager->getItems())->toHaveCount(1);
    });

    it('handles setInstance correctly when instance already set', function () {
        $this->manager->setInstance('test');
        $currentCart = $this->manager->getCurrentCart();

        // Setting same instance should not create new cart
        $this->manager->setInstance('test');
        $sameCart = $this->manager->getCurrentCart();

        expect($currentCart)->toBe($sameCart);
    });

    it('creates new cart when setInstance changes instance', function () {
        $originalCart = $this->manager->getCurrentCart();

        $this->manager->setInstance('different');
        $newCart = $this->manager->getCurrentCart();

        expect($originalCart)->not->toBe($newCart);
        expect($this->manager->instance())->toBe('different');
    });
});
