<?php

declare(strict_types=1);

use MasyukAI\Cart\Cart;
use MasyukAI\Cart\CartManager;
use MasyukAI\Cart\Conditions\CartCondition;
use MasyukAI\Cart\Storage\SessionStorage;

beforeEach(function () {
    $this->session = app('session.store');
    $this->storage = new SessionStorage($this->session);
    $this->cartManager = new CartManager($this->storage);
    $this->cart = $this->cartManager->getCurrentCart();
});

describe('Missing Features Compatibility Tests', function () {

    it('can get subtotal without conditions', function () {
        $this->cart->add('1', 'Product 1', 10.00, 2);
        $this->cart->add('2', 'Product 2', 15.00, 1);

        $subtotal = $this->cart->getSubTotalWithoutConditions();

        expect($subtotal)->toBe(35.0); // (10 * 2) + (15 * 1)
    });

    it('can count items using count() method', function () {
        $this->cart->add('1', 'Product 1', 10.00, 2);
        $this->cart->add('2', 'Product 2', 15.00, 3);

        $count = $this->cart->count();

        expect($count)->toBe(5); // 2 + 3 items
    });

    it('can use associate() method after add()', function () {
        $item = $this->cart->add('1', 'Product 1', 10.00, 1);

        $cartAfterAssociate = $this->cart->associate('stdClass'); // Use built-in class

        expect($cartAfterAssociate)->toBeInstanceOf(Cart::class);

        $items = $this->cart->getItems();
        $associatedItem = $items->get('1');

        expect($associatedItem->associatedModel)->toBe('stdClass');
    });

    it('throws exception when using associate() without adding item first', function () {
        expect(fn () => $this->cart->associate('stdClass'))
            ->toThrow(InvalidArgumentException::class, 'No item has been added to associate with. Call add() first.');
    });

    describe('Condition Management', function () {

        it('can remove cart condition by name', function () {
            $condition = new CartCondition('tax', 'tax', 'subtotal', '+10%');
            $this->cart->addCondition($condition);

            $result = $this->cart->removeCondition('tax');

            expect($result)->toBeTrue();
            expect($this->cart->getCondition('tax'))->toBeNull();
        });

        it('can clear all cart conditions', function () {
            $tax = new CartCondition('tax', 'tax', 'subtotal', '+10%');
            $shipping = new CartCondition('shipping', 'shipping', 'subtotal', '+5.00');

            $this->cart->addCondition([$tax, $shipping]);

            $result = $this->cart->clearConditions();

            expect($result)->toBeTrue();
            expect($this->cart->getConditions())->toBeEmpty();
        });

        it('can get conditions by type', function () {
            $tax = new CartCondition('tax', 'tax', 'subtotal', '+10%');
            $shipping = new CartCondition('shipping', 'shipping', 'subtotal', '+5.00');
            $discount = new CartCondition('discount', 'discount', 'subtotal', '-5.00');

            $this->cart->addCondition([$tax, $shipping, $discount]);

            $taxConditions = $this->cart->getConditionsByType('tax');
            $shippingConditions = $this->cart->getConditionsByType('shipping');

            expect($taxConditions)->toHaveCount(1);
            expect($taxConditions->first()->getName())->toBe('tax');

            expect($shippingConditions)->toHaveCount(1);
            expect($shippingConditions->first()->getName())->toBe('shipping');
        });

        it('can remove conditions by type', function () {
            $tax1 = new CartCondition('tax1', 'tax', 'subtotal', '+10%');
            $tax2 = new CartCondition('tax2', 'tax', 'subtotal', '+5%');
            $shipping = new CartCondition('shipping', 'shipping', 'subtotal', '+5.00');

            $this->cart->addCondition([$tax1, $tax2, $shipping]);

            $result = $this->cart->removeConditionsByType('tax');

            expect($result)->toBeTrue();
            expect($this->cart->getConditionsByType('tax'))->toBeEmpty();
            expect($this->cart->getCondition('shipping'))->not->toBeNull();
        });

        it('returns false when removing non-existent condition type', function () {
            $result = $this->cart->removeConditionsByType('non-existent');

            expect($result)->toBeFalse();
        });

        it('returns false when removing non-existent condition by name', function () {
            $result = $this->cart->removeCondition('non-existent');

            expect($result)->toBeFalse();
        });
    });

    describe('Metadata Storage', function () {

        it('can store and retrieve metadata', function () {
            $this->cart->add('1', 'Product 1', 10.00, 1);
            $cart2 = $this->cart->associate('stdClass'); // Use built-in class

            // The last added item should be stored as metadata
            $lastAddedItemId = $this->storage->getMetadata(
                $this->cart->getIdentifier(),
                $this->cart->getStorageInstanceName(),
                'last_added_item_id'
            );

            expect($lastAddedItemId)->toBe('1');
        });
    });

    describe('Enhanced Calculation Methods', function () {

        it('ensures getSubTotal and getSubTotalWithoutConditions return same value', function () {
            $this->cart->add('1', 'Product 1', 10.00, 2);
            $this->cart->add('2', 'Product 2', 15.00, 1);

            $subtotal = $this->cart->getSubTotal();
            $subtotalWithoutConditions = $this->cart->getSubTotalWithoutConditions();

            expect($subtotal)->toBe($subtotalWithoutConditions);
            expect($subtotal)->toBe(35.0);
        });

        it('correctly calculates subtotal with item conditions vs without conditions', function () {
            $this->cart->add('1', 'Product 1', 10.00, 2);

            // Add item-level condition
            $condition = new CartCondition('item_discount', 'discount', 'subtotal', '-20%');
            $this->cart->addItemCondition('1', $condition);

            $subtotalWithoutConditions = $this->cart->getSubTotalWithoutConditions();
            $subtotalWithConditions = $this->cart->getSubTotalWithConditions();

            expect($subtotalWithoutConditions)->toBe(20.0); // 10 * 2
            expect($subtotalWithConditions)->toBe(16.0); // 20 - 20% = 16
        });
    });

    describe('Comprehensive Feature Test', function () {

        it('can chain multiple operations like original package', function () {
            $this->cart->add('1', 'Product 1', 10.00, 2);
            $cart = $this->cart->associate('stdClass'); // Use built-in class

            expect($cart)->toBeInstanceOf(Cart::class);

            $items = $cart->getItems();
            expect($items)->toHaveCount(1);
            expect($items->get('1')->associatedModel)->toBe('stdClass');
        });

        it('maintains correct state across different instances', function () {
            $defaultCart = $this->cartManager->getCartInstance('default');
            $wishlistCart = $this->cartManager->getCartInstance('wishlist');

            $defaultCart->add('1', 'Product 1', 10.00, 1);
            $wishlistCart->add('2', 'Product 2', 15.00, 1);

            expect($defaultCart->getItems())->toHaveCount(1);
            expect($wishlistCart->getItems())->toHaveCount(1);
            expect($defaultCart->getTotal())->toBe(10.0);
            expect($wishlistCart->getTotal())->toBe(15.0);
        });
    });
});
