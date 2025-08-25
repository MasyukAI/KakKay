<?php

namespace App\Livewire;

use App\Traits\ManagesCart;
use Livewire\Component;
use Livewire\Attributes\On;

class CartCounter extends Component
{
    use ManagesCart;
    
    public int $count = 0;

    public function mount(): void
    {
        $this->updateCartCount();
    }

    #[On('product-added-to-cart')]
    public function updateCartCount(): void
    {
        $this->count = $this->getCartCount();
    }

    public function render()
    {
        return view('livewire.cart-counter');
    }
}
