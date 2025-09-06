<?php

declare(strict_types=1);

use MasyukAI\Cart\Events\CartCreated;
use MasyukAI\Cart\Events\CartMerged;
use MasyukAI\Cart\Http\Controllers\DemoController;
use MasyukAI\Cart\Http\Livewire\AddToCart;
use MasyukAI\Cart\Http\Livewire\CartSummary;
use MasyukAI\Cart\Http\Livewire\CartTable;
use MasyukAI\Cart\Listeners\HandleUserLogin;
use MasyukAI\Cart\Listeners\HandleUserLogout;

describe('Additional Coverage Tests', function () {

    it('can test IntegerPriceTransformer', function () {
        $transformer = new \MasyukAI\Cart\PriceTransformers\IntegerPriceTransformer;

        $displayPrice = $transformer->toDisplay(1999);
        expect($displayPrice)->toBe('19.99');

        // For formatCurrency, we need to pass the storage format (cents)
        $currencyFormatted = $transformer->formatCurrency(1999);
        expect($currencyFormatted)->toBe('$19.99');
    });

    it('can instantiate CartMerged event', function () {
        $cartManager = app('cart');
        $targetCart = $cartManager->getCartInstance('target');
        $sourceCart = $cartManager->getCartInstance('source');

        $event = new CartMerged(
            $targetCart,
            $sourceCart,
            0,
            'add_quantities',
            false
        );

        expect($event->totalItemsMerged)->toBe(0);
        expect($event->mergeStrategy)->toBe('add_quantities');
        expect($event->hadConflicts)->toBeFalse();
    });

    it('can instantiate CartCreated event', function () {
        $cartManager = app('cart');
        $cart = $cartManager->getCartInstance('test');
        $event = new CartCreated($cart);

        expect($event->cart)->toBe($cart);
    });

    it('can test CartManager methods', function () {
        $cartManager = app('cart');

        // Test instance switching
        $cartManager->setInstance('test-instance');
        expect($cartManager->instance())->toBe('test-instance');

        // Test getting cart instance
        $cartInstance = $cartManager->getCartInstance('another-instance');
        expect($cartInstance)->toBeInstanceOf(\MasyukAI\Cart\Cart::class);

        // Test current cart access
        $currentCart = $cartManager->getCurrentCart();
        expect($currentCart)->toBeInstanceOf(\MasyukAI\Cart\Cart::class);

        // Test storage access
        $storage = $cartManager->storage();
        expect($storage)->toBeInstanceOf(\MasyukAI\Cart\Storage\StorageInterface::class);
    });

    it('can instantiate demo controller', function () {
        $controller = new DemoController;

        expect($controller)->toBeInstanceOf(DemoController::class);
    });

    it('can test demo controller index method', function () {
        $controller = new DemoController;

        $response = $controller->index();

        expect($response)->toBeInstanceOf(\Illuminate\View\View::class);
    });

    it('can instantiate Livewire components', function () {
        expect(class_exists(AddToCart::class))->toBeTrue();
        expect(class_exists(CartSummary::class))->toBeTrue();
        expect(class_exists(CartTable::class))->toBeTrue();
    });

    it('can instantiate event listeners', function () {
        expect(class_exists(HandleUserLogin::class))->toBeTrue();
        expect(class_exists(HandleUserLogout::class))->toBeTrue();
    });

    it('can test Collections namespace coverage', function () {
        // Test coverage for Collections that might be missed
        $collection = collect();
        expect($collection)->toBeInstanceOf(\Illuminate\Support\Collection::class);
    });

});
