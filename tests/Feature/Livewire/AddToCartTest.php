<?php

declare(strict_types=1);

use Livewire\Livewire;

it('can render', function () {
    $component = Livewire::test('add-to-cart');

    $component->assertSee('');
});
