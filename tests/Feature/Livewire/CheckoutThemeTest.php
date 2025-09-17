<?php

declare(strict_types=1);

use App\Livewire\Checkout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use MasyukAI\Cart\Facades\Cart as CartFacade;

uses(RefreshDatabase::class);

beforeEach(function () {
    CartFacade::add('test-product', 'Test Product', 1000, 1);
});

it('displays the enhanced checkout theming elements', function () {
    Livewire::test(Checkout::class)
        ->assertSee('Maklumat Penghantaran')
        ->assertSee('Pembayaran Dilindungi')
        ->assertSee('Penghantaran Dipantau');
});
