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
    public function cart(): array
    {
        return Cart::toArray();
    }

    #[Computed]
    public function itemCount(): int
    {
        return Cart::countItems();
    }

    #[Computed]
    public function totalQuantity(): int
    {
        return Cart::getTotalQuantity();
    }

    #[Computed]
    public function subtotal(): float
    {
        return Cart::getSubTotal();
    }
    
    #[Computed]
    public function shippingValue(): float
    {
        return Cart::getShippingValue() ?? 0.0;
    }

    #[Computed]
    public function total(): float
    {
        return Cart::getTotal();
    }

    #[Computed]
    public function isEmpty(): bool
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

    public function render(): \Illuminate\View\View
    {
        return view('cart::livewire.cart-summary');
    }
}
