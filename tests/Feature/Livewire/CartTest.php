<?php

use Livewire\Volt\Volt;

it('can render', function () {
    $component = Volt::test('cart');

    $component->assertSee('');
});
