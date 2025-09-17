<?php

namespace App\Livewire;

use App\Models\Product;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Component;
use MasyukAI\Cart\Facades\Cart as CartFacade;

class Cart extends Component
{
    public array $cartItems = [];

    public string $voucherCode = '';

    public $suggestedProducts;

    public function mount(): void
    {
        $this->loadCartItems();
        $this->loadSuggestedProducts();
    }

    #[On('product-added-to-cart')]
    public function refreshCart(): void
    {
        $this->loadCartItems();
        $this->loadSuggestedProducts();
    }

    public function loadCartItems(): void
    {
        try {
            $cartContents = CartFacade::getItems();

            $this->cartItems = $cartContents->map(function ($item) {
                return [
                    'id' => (string) $item->id,
                    'name' => (string) $item->name,
                    'price' => $item->getRawPrice(),
                    'price_formatted' => $item->getPrice()->format(),
                    'subtotal' => $item->getRawSubtotal(),
                    'subtotal_formatted' => $item->getSubtotal()->format(),
                    'quantity' => (int) $item->quantity,
                    'slug' => $item->attributes->get('slug', 'cara-bercinta'),
                ];
            })->values()->toArray();

        } catch (\Exception $e) {
            $this->cartItems = [];
            Log::error('Cart loading error: '.$e->getMessage());
        }
    }

    public function loadSuggestedProducts(): void
    {
        $cartProductIds = collect($this->cartItems)->pluck('id')->toArray();
        $this->suggestedProducts = \App\Models\Product::where('is_active', true)
            ->whereNotIn('id', $cartProductIds)
            ->inRandomOrder()
            ->limit(3)
            ->get();
    }

    public function updateQuantity(string $itemId, int $quantity): void
    {
        if ($quantity <= 0) {
            $this->removeItem($itemId);

            return;
        }

        // Use absolute quantity update by passing as array
        CartFacade::update($itemId, ['quantity' => ['value' => $quantity]]);
        $this->loadCartItems();
        $this->dispatch('product-added-to-cart');
    }

    public function incrementQuantity($itemId)
    {
        $item = CartFacade::get($itemId);
        if ($item) {
            $newQuantity = $item->quantity + 1;
            CartFacade::update($itemId, ['quantity' => ['value' => $newQuantity]]);
            $this->loadCartItems();
            $this->dispatch('product-added-to-cart');
            Notification::make()
                ->title('Buku Ditambah')
                ->body("Kuantiti '{$item->name}' telah ditambah.")
                ->info()
                ->icon('heroicon-o-plus-circle')
                ->iconColor('info')
                ->send();
        }
    }

    public function decrementQuantity($itemId)
    {
        $item = CartFacade::get($itemId);
        if ($item) {
            $newQuantity = $item->quantity - 1;

            // If quantity would become 0 or less, remove the item instead
            if ($newQuantity <= 0) {
                $this->removeItem($itemId);

                return;
            }

            CartFacade::update($itemId, ['quantity' => ['value' => $newQuantity]]);
            $this->loadCartItems();
            $this->dispatch('product-added-to-cart');
            Notification::make()
                ->title('Buku Dikurangkan')
                ->body("Kuantiti '{$item->name}' telah dikurangkan.")
                ->info()
                ->icon('heroicon-o-minus-circle')
                ->iconColor('info')
                ->send();
        }
    }

    public function removeItem(string $itemId): void
    {
        $item = CartFacade::get($itemId);
        $itemName = $item ? $item->name : 'Item';
        CartFacade::remove($itemId);
        $this->loadCartItems();
        $this->loadSuggestedProducts();
        $this->dispatch('product-added-to-cart'); // Refresh cart counter
        Notification::make()
            ->title('Buku Dikeluarkan!')
            ->body("'{$itemName}' telah dikeluarkan.")
            ->success()
            ->icon('heroicon-o-trash')
            ->iconColor('success')
            ->send();
    }

    public function applyVoucher(): void
    {
        if (! empty($this->voucherCode)) {
            Notification::make()
                ->title('Voucher Berjaya!')
                ->body("Kod voucher '{$this->voucherCode}' telah digunakan.")
                ->success()
                ->icon('heroicon-o-ticket')
                ->iconColor('success')
                ->duration(4000)
                ->send();
            $this->voucherCode = '';
        }
    }

    public function addToCart($productId, int $quantity = 1): void
    {
        // Handle both Product object and product ID
        if ($productId instanceof Product) {
            $product = $productId;
        } else {
            $product = Product::findOrFail($productId);
        }

        // Add item to cart - price is already in cents (integer)
        CartFacade::add(
            id: $product->id,
            name: $product->name,
            price: $product->price, // Keep as cents (integer)
            quantity: $quantity,
            attributes: [
                'slug' => $product->slug,
                'image' => $product->getFirstMediaUrl() ?: null,
                'weight' => $product->getShippingWeight(),
            ]
        );

        $this->loadCartItems();
        $this->loadSuggestedProducts();

        // Dispatch consistent event for UI feedback
        $this->dispatch('product-added-to-cart', [
            'product' => $product->name,
            'quantity' => $quantity,
        ]);

        // Show notification
        Notification::make()
            ->title('Buku Ditambah!')
            ->body("'{$product->name}' telah ditambah ke troli.")
            ->success()
            ->icon('heroicon-o-shopping-cart')
            ->iconColor('success')
            ->send();
    }

    public function getSubtotal(): \Akaunting\Money\Money
    {
        return CartFacade::subtotal(); // Return Money object directly for formatting
    }

    public function getShipping(): \Akaunting\Money\Money
    {
        $currency = config('cart.money.default_currency', 'MYR');

        return \Akaunting\Money\Money::{$currency}(990); // RM9.90 as Money object
    }

    public function getTotal(): \Akaunting\Money\Money
    {
        $subtotal = $this->getSubtotal();
        $shipping = $this->getShipping();

        return $subtotal->add($shipping);
    }

    public function render()
    {
        return view('livewire.cart');
    }
}
