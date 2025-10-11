<?php

declare(strict_types=1);

use AIArmada\Cart\Cart;
use AIArmada\Cart\Conditions\CartCondition;
use AIArmada\Cart\Storage\CacheStorage;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Cache::flush();
    $storage = new CacheStorage(Cache::store(), 'test_cart', 3600);
    $this->cart = new Cart($storage, 'test-user', null, 'default');
});

describe('ManagesConditions Trait', function () {
    it('removes conditions by type successfully', function () {
        // Add multiple conditions of different types
        $this->cart->addCondition(new CartCondition(
            name: 'tax1',
            type: 'tax',
            target: 'subtotal',
            value: '+10%'
        ));

        $this->cart->addCondition(new CartCondition(
            name: 'tax2',
            type: 'tax',
            target: 'subtotal',
            value: '+5%'
        ));

        $this->cart->addCondition(new CartCondition(
            name: 'discount',
            type: 'discount',
            target: 'subtotal',
            value: '-20%'
        ));

        expect($this->cart->getConditions())->toHaveCount(3);

        // Remove all tax conditions
        $result = $this->cart->removeConditionsByType('tax');

        expect($result)->toBeTrue();
        expect($this->cart->getConditions())->toHaveCount(1);
        expect($this->cart->getCondition('discount'))->not->toBeNull();
        expect($this->cart->getCondition('tax1'))->toBeNull();
        expect($this->cart->getCondition('tax2'))->toBeNull();
    });

    it('returns false when removing conditions by non-existent type', function () {
        $this->cart->addCondition(new CartCondition(
            name: 'tax',
            type: 'tax',
            target: 'subtotal',
            value: '+10%'
        ));

        $result = $this->cart->removeConditionsByType('shipping');

        expect($result)->toBeFalse();
        expect($this->cart->getConditions())->toHaveCount(1);
    });

    it('returns false when removing item condition that does not exist', function () {
        $this->cart->add(
            id: 'prod-1',
            name: 'Product',
            price: 100,
            quantity: 1
        );

        $result = $this->cart->removeItemCondition('prod-1', 'nonexistent');

        expect($result)->toBeFalse();
    });

    it('returns false when removing condition from non-existent item', function () {
        $result = $this->cart->removeItemCondition('nonexistent-item', 'some-condition');

        expect($result)->toBeFalse();
    });

    it('gets shipping condition', function () {
        $this->cart->addCondition(new CartCondition(
            name: 'standard_shipping',
            type: 'shipping',
            target: 'subtotal',
            value: '+15',
            attributes: ['method' => 'standard']
        ));

        $shipping = $this->cart->getShipping();

        expect($shipping)->not->toBeNull();
        expect($shipping->getName())->toBe('standard_shipping');
        expect($shipping->getType())->toBe('shipping');
    });

    it('returns null when no shipping condition exists', function () {
        $this->cart->addCondition(new CartCondition(
            name: 'tax',
            type: 'tax',
            target: 'subtotal',
            value: '+10%'
        ));

        $shipping = $this->cart->getShipping();

        expect($shipping)->toBeNull();
    });

    it('gets shipping method from condition attributes', function () {
        $this->cart->addCondition(new CartCondition(
            name: 'express_shipping',
            type: 'shipping',
            target: 'subtotal',
            value: '+25',
            attributes: ['method' => 'express']
        ));

        $method = $this->cart->getShippingMethod();

        expect($method)->toBe('express');
    });

    it('returns null when shipping condition has no method attribute', function () {
        $this->cart->addCondition(new CartCondition(
            name: 'shipping',
            type: 'shipping',
            target: 'subtotal',
            value: '+15'
        ));

        $method = $this->cart->getShippingMethod();

        expect($method)->toBeNull();
    });

    it('returns null for shipping method when no shipping condition exists', function () {
        $method = $this->cart->getShippingMethod();

        expect($method)->toBeNull();
    });

    it('gets shipping value as float from positive operator', function () {
        $this->cart->addCondition(new CartCondition(
            name: 'shipping',
            type: 'shipping',
            target: 'subtotal',
            value: '+15.50'
        ));

        $value = $this->cart->getShippingValue();

        expect($value)->toBe(15.50);
    });

    it('gets shipping value as negative float from minus operator', function () {
        $this->cart->addCondition(new CartCondition(
            name: 'shipping',
            type: 'shipping',
            target: 'subtotal',
            value: '-10.00'
        ));

        $value = $this->cart->getShippingValue();

        expect($value)->toBe(-10.00);
    });

    it('gets shipping value from numeric string without operator', function () {
        $this->cart->addCondition(new CartCondition(
            name: 'shipping',
            type: 'shipping',
            target: 'subtotal',
            value: '25.99'
        ));

        $value = $this->cart->getShippingValue();

        expect($value)->toBe(25.99);
    });

    it('gets shipping value from integer', function () {
        $this->cart->addCondition(new CartCondition(
            name: 'shipping',
            type: 'shipping',
            target: 'subtotal',
            value: 20
        ));

        $value = $this->cart->getShippingValue();

        expect($value)->toBe(20.0);
    });

    it('returns null for shipping value when no shipping condition exists', function () {
        $value = $this->cart->getShippingValue();

        expect($value)->toBeNull();
    });

    it('handles multiplication operator in shipping value', function () {
        $this->cart->addCondition(new CartCondition(
            name: 'shipping',
            type: 'shipping',
            target: 'subtotal',
            value: '*1.5'
        ));

        $value = $this->cart->getShippingValue();

        // Multiplication operator returns positive value
        expect($value)->toBe(1.5);
    });

    it('handles percentage operator in shipping value', function () {
        $this->cart->addCondition(new CartCondition(
            name: 'shipping',
            type: 'shipping',
            target: 'subtotal',
            value: '%10'
        ));

        $value = $this->cart->getShippingValue();

        expect($value)->toBe(10.0);
    });
});
