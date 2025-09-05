<?php

declare(strict_types=1);

namespace MasyukAI\Cart\Http\Livewire;

use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use MasyukAI\Cart\Facades\Cart;
use MasyukAI\Cart\Models\CartItem;

class CartTable extends Component
{
    public bool $showConditions = false;

    #[Computed]
    public function items(): \MasyukAI\Cart\Collections\CartCollection
    {
        return Cart::getItems();
    }

    #[Computed]
    public function isEmpty(): bool
    {
        return Cart::isEmpty();
    }

    public function updateQuantity(string $itemId, int $quantity): void
    {
        if ($quantity <= 0) {
            $this->removeItem($itemId);

            return;
        }

        Cart::update($itemId, [
            'quantity' => [
                'relative' => false,
                'value' => $quantity,
            ],
        ]);

        $this->dispatch('cart-updated');
        $this->dispatch('item-updated', itemId: $itemId);
    }

    public function increaseQuantity(string $itemId): void
    {
        Cart::update($itemId, ['quantity' => 1]);
        $this->dispatch('cart-updated');
        $this->dispatch('item-updated', itemId: $itemId);
    }

    public function decreaseQuantity(string $itemId): void
    {
        $item = Cart::get($itemId);

        if ($item && $item->quantity <= 1) {
            $this->removeItem($itemId);

            return;
        }

        Cart::update($itemId, ['quantity' => -1]);
        $this->dispatch('cart-updated');
        $this->dispatch('item-updated', itemId: $itemId);
    }

    public function removeItem(string $itemId): void
    {
        $item = Cart::remove($itemId);

        if ($item) {
            $this->dispatch('cart-updated');
            $this->dispatch('item-removed', itemId: $itemId, itemName: $item->name);

            session()->flash('cart.message', "Removed \"{$item->name}\" from cart.");
        }
    }

    public function clearCart(): void
    {
        Cart::clear();
        $this->dispatch('cart-updated');
        $this->dispatch('cart-cleared');

        session()->flash('cart.message', 'Cart cleared successfully.');
    }

    public function toggleConditions(): void
    {
        $this->showConditions = ! $this->showConditions;
    }

    #[On('cart-updated')]
    public function refreshCart(): void
    {
        // Items will be automatically refreshed due to computed properties
    }

    public function getItemPrice(CartItem $item): float
    {
        return $this->showConditions ? $item->getPriceWithConditions() : $item->price;
    }

    public function getItemTotal(CartItem $item): float
    {
        return $this->showConditions ? $item->getPriceSumWithConditions() : $item->getPriceSum();
    }

    public function render(): \Illuminate\View\View
    {
        return view('cart::livewire.cart-table');
    }
}
