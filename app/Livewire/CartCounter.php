<?php

declare(strict_types=1);

namespace App\Livewire;

use AIArmada\Cart\Facades\Cart as CartFacade;
use Livewire\Attributes\On;
use Livewire\Component;

final class CartCounter extends Component
{
    public int $count = 0;

    public function mount(): void
    {
        $this->updateCartCount();
    }

    #[On('cart-updated')]
    public function updateCartCount(): void
    {
        $this->count = CartFacade::getTotalQuantity();
    }

    public function render()
    {
        return view('livewire.cart-counter');
    }
}
