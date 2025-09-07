<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Session;
use MasyukAI\Cart\Storage\SessionStorage;

describe('SessionStorage::has', function () {
    beforeEach(function () {
        Session::flush();
        $this->storage = new SessionStorage(Session::driver(), 'cart');
        $this->identifier = 'user-1';
        $this->instance = 'default';
    });

    it('returns false if neither items nor conditions exist', function () {
        expect($this->storage->has($this->identifier, $this->instance))->toBeFalse();
    });

    it('returns true if items exist', function () {
        Session::put('cart', [
            $this->identifier => [
                $this->instance => [
                    'items' => ['foo'],
                ],
            ],
        ]);
        expect($this->storage->has($this->identifier, $this->instance))->toBeTrue();
    });

    it('returns true if conditions exist', function () {
        Session::put('cart', [
            $this->identifier => [
                $this->instance => [
                    'conditions' => ['bar'],
                ],
            ],
        ]);
        expect($this->storage->has($this->identifier, $this->instance))->toBeTrue();
    });

    it('returns true if both items and conditions exist', function () {
        Session::put('cart', [
            $this->identifier => [
                $this->instance => [
                    'items' => ['foo'],
                    'conditions' => ['bar'],
                ],
            ],
        ]);
        expect($this->storage->has($this->identifier, $this->instance))->toBeTrue();
    });

    it('returns false if neither key exists but instance exists', function () {
        Session::put('cart', [
            $this->identifier => [
                $this->instance => [
                    // empty
                ],
            ],
        ]);
        expect($this->storage->has($this->identifier, $this->instance))->toBeFalse();
    });
});
