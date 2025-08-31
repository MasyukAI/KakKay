<?php

declare(strict_types=1);

namespace MasyukAI\Cart\Http\Livewire;

use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use MasyukAI\Cart\Facades\Cart;

class CartSummary extends Component
{
    public bool $showDetails = false;

    #[Computed]
    public function cart()
    {
        return Cart::toArray();
    }

    #[Computed]
    public function itemCount()
    {
        return Cart::countItems();
    }

    #[Computed]
    public function totalQuantity()
    {
        return Cart::getTotalQuantity();
    }

    #[Computed]
    public function subtotal()
    {
        return Cart::getSubTotal();
    }

    #[Computed]
    public function total()
    {
        return Cart::getTotal();
    }

    #[Computed]
    public function isEmpty()
    {
        return Cart::isEmpty();
    }

    public function toggleDetails(): void
    {
        $this->showDetails = !$this->showDetails;
    }

    #[On('cart-updated')]
    public function refreshCart(): void
    {
        // Cart data will be automatically refreshed due to computed properties
        $this->dispatch('cart-summary-updated');
    }

    public function render()
    {
        return view('cart::livewire.cart-summary');
    }
}
