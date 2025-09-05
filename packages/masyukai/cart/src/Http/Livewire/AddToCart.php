<?php

declare(strict_types=1);

namespace MasyukAI\Cart\Http\Livewire;

use Livewire\Attributes\Validate;
use Livewire\Component;
use MasyukAI\Cart\Facades\Cart;

class AddToCart extends Component
{
    #[Validate('required|string')]
    public string $productId = '';

    #[Validate('required|string|min:1')]
    public string $productName = '';

    #[Validate('required|numeric|min:0')]
    public float $productPrice = 0;

    #[Validate('required|integer|min:1')]
    public int $quantity = 1;

    public array $productAttributes = [];
    public ?string $associatedModel = null;
    public bool $showForm = false;

    public function mount(
        string $productId = '',
        string $productName = '',
        float $productPrice = 0,
        int $quantity = 1,
        array $attributes = [],
        ?string $associatedModel = null
    ): void {
        $this->productId = $productId;
        $this->productName = $productName;
        $this->productPrice = $productPrice;
        $this->quantity = $quantity;
        $this->productAttributes = $attributes;
        $this->associatedModel = $associatedModel;
    }

    public function addToCart(): void
    {
        $this->validate();

        try {
            $item = Cart::add(
                id: $this->productId,
                name: $this->productName,
                price: $this->productPrice,
                quantity: $this->quantity,
                attributes: $this->productAttributes,
                associatedModel: $this->associatedModel
            );

            $this->dispatch('cart-updated');
            $this->dispatch('item-added', itemId: $item->id, itemName: $item->name);

            session()->flash('cart.message', "Added \"{$item->name}\" to cart.");

            // Reset form if it's shown
            if ($this->showForm) {
                $this->reset(['quantity', 'productAttributes']);
            }

        } catch (\Exception $e) {
            session()->flash('cart.error', 'Failed to add item to cart: ' . $e->getMessage());
        }
    }

    public function quickAdd(): void
    {
        if (empty($this->productId) || empty($this->productName) || $this->productPrice < 0) {
            session()->flash('cart.error', 'Invalid product data for quick add.');
            return;
        }

        $this->addToCart();
    }

    public function toggleForm(): void
    {
        $this->showForm = !$this->showForm;
    }

    public function increaseQuantity(): void
    {
        $this->quantity++;
    }

    public function decreaseQuantity(): void
    {
        if ($this->quantity > 1) {
            $this->quantity--;
        }
    }

    public function addAttribute(string $key, mixed $value): void
    {
        $this->productAttributes[$key] = $value;
    }

    public function removeAttribute(string $key): void
    {
        unset($this->productAttributes[$key]);
    }

    public function render(): \Illuminate\View\View
    {
        return view('cart::livewire.add-to-cart');
    }
}
