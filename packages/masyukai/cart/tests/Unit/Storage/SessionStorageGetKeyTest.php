<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Session;
use MasyukAI\Cart\Storage\SessionStorage;

describe('SessionStorage::getKey', function () {
    it('returns the correct legacy storage key', function () {
        $storage = new SessionStorage(Session::driver(), 'cart');
        $identifier = 'user-42';
        $instance = 'default';
        $expected = 'cart.user-42.default';
        $result = (fn () => $this->getKey($identifier, $instance))->call($storage);
        expect($result)->toBe($expected);
    });
});
