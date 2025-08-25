<?php

namespace App\Livewire\Checkout;

use App\Traits\ManagesCart;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Joelwmale\Cart\Facades\CartFacade as Cart;
use Illuminate\Support\Facades\Log;

class CartStep extends Component
{
    use ManagesCart;
    
    public array $checkoutData;
    public array $cartItems = [];
    public string $voucherCode = '';

    public function mount(array $checkoutData = []): void
    {
        $this->checkoutData = $checkoutData;
        $this->loadCartItems();
    }

    public function loadCartItems(): void
    {
        try {
            // Set session key for cart
            $this->setCartSession();
            
            // Load cart items from Cart package
            $cartContents = Cart::getContent();
            
            if ($cartContents->isEmpty()) {
                $this->cartItems = [];
                return;
            }
            
            $this->cartItems = $cartContents->map(function ($item) {
                return [
                    'id' => (int) $item->id,
                    'name' => (string) $item->name,
                    'price' => (int) $item->price, // Already in cents from our addToCart method
                    'quantity' => (int) $item->quantity,
                    'attributes' => $item->attributes ? $item->attributes->toArray() : [],
                    'image' => 'https://flowbite.s3.amazonaws.com/blocks/e-commerce/book-placeholder.svg',
                ];
            })->values()->toArray();
        } catch (\Exception $e) {
            // Fallback to empty cart if there's an error
            $this->cartItems = [];
            Log::error('Cart loading error: ' . $e->getMessage());
        }
    }

    public function updateQuantity(int $itemId, int $quantity): void
    {
        // Set session key for cart
        $this->setCartSession();

        if ($quantity <= 0) {
            $this->removeItem($itemId);
            return;
        }

        // Update quantity in cart
        Cart::update($itemId, [
            'quantity' => [
                'relative' => false,
                'value' => $quantity
            ]
        ]);

        // Reload cart items to reflect changes
        $this->loadCartItems();
    }

    public function removeItem(int $itemId): void
    {
        // Set session key for cart
        $this->setCartSession();
        
        // Remove item from cart
        Cart::remove($itemId);
        
        // Reload cart items to reflect changes
        $this->loadCartItems();
    }

    public function applyVoucher(): void
    {
        if (!empty($this->voucherCode)) {
            // Validate voucher code logic here
            session()->flash('success', "Voucher '{$this->voucherCode}' applied successfully!");
            $this->voucherCode = '';
        }
    }

    public function proceedToCheckout(): void
    {
        if (empty($this->cartItems)) {
            session()->flash('error', 'Your cart is empty.');
            return;
        }

        $cartData = [
            'items' => $this->cartItems,
            'subtotal' => $this->getSubtotal(),
            'total' => $this->getTotal(),
        ];

        $this->dispatch('next-step', $cartData);
    }

    #[Computed]
    public function getSubtotal(): int
    {
        return $this->getCartSubtotal();
    }

    #[Computed]
    public function getSavings(): int
    {
        return 29900; // Sample savings amount
    }

    #[Computed]
    public function getShipping(): int
    {
        return 9900; // Sample shipping cost
    }

    #[Computed]
    public function getTax(): int
    {
        return (int) ($this->getSubtotal() * 0.1); // 10% tax
    }

    #[Computed]
    public function getTotal(): int
    {
        return $this->getSubtotal() - $this->getSavings() + $this->getShipping() + $this->getTax();
    }

    public function formatPrice(int $cents): string
    {
        return 'RM ' . number_format($cents / 100, 2);
    }

    public function render()
    {
        return view('livewire.checkout.cart-step');
    }
}
