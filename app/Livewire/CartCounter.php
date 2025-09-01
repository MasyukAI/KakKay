<?php

namespace App\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;
use MasyukAI\Cart\Facades\Cart;

class CartCounter extends Component
{
    public int $count = 0;

    public function mount(): void
    {
        $this->updateCartCount();
    }

    #[On('product-added-to-cart')]
    public function updateCartCount(): void
    {
        $this->count = Cart::getTotalQuantity();
    }

    public function render()
    {
        return view('livewire.cart-counter');
    }
}
