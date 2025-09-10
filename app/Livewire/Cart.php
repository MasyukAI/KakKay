<?php


namespace App\Livewire;

use Livewire\Component;
use MasyukAI\Cart\Facades\Cart as CartFacade;
use Illuminate\Support\Facades\Log;
use Filament\Notifications\Notification;

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

    public function loadCartItems(): void
    {
        try {
            $cartContents = CartFacade::getItems();
            $this->cartItems = $cartContents->map(function ($item) {
                return [
                    'id' => (string) $item->id,
                    'name' => (string) $item->name,
                    'price' => (int) $item->getPrice(),
                    'quantity' => (int) $item->quantity,
                    'slug' => $item->attributes->get('slug', 'cara-bercinta'),
                ];
            })->values()->toArray();
        } catch (\Exception $e) {
            $this->cartItems = [];
            Log::error('Cart loading error: ' . $e->getMessage());
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
    CartFacade::update($itemId, ['quantity' => $quantity]);
        $this->loadCartItems();
        $this->dispatch('product-added-to-cart');
    }

    public function incrementQuantity($itemId)
    {
        $item = Cart::get($itemId);
        if ($item) {
            CartFacade::update($itemId, ['quantity' => ['value' => $item->quantity + 1]]);
            $this->loadCartItems();
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
        $item = Cart::get($itemId);
        if ($item) {
            $newQuantity = $item->quantity - 1;
            CartFacade::update($itemId, ['quantity' => ['value' => $newQuantity]]);
            $this->loadCartItems();
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
        $item = Cart::get($itemId);
        $itemName = $item ? $item->name : 'Item';
    CartFacade::remove($itemId);
        $this->loadCartItems();
        $this->loadSuggestedProducts();
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
        if (!empty($this->voucherCode)) {
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

    public function addToCart(int $productId): void
    {
        $product = \App\Models\Product::find($productId);
        if (!$product) {
            Notification::make()
                ->title('Produk Tidak Dijumpai')
                ->body('Produk yang diminta tidak wujud.')
                ->danger()
                ->icon('heroicon-o-exclamation-triangle')
                ->iconColor('danger')
                ->duration(5000)
                ->send();
            return;
        }
    CartFacade::add(
            (string) $product->id,
            $product->name,
            $product->price,
            1,
            ['slug' => $product->slug]
        );
        $this->loadCartItems();
        $this->loadSuggestedProducts();
        $this->dispatch('product-added-to-cart');
        Notification::make()
            ->title('Buku dimasukkan!')
            ->body("'{$product->name}' telah dimasukkan!")
            ->success()
            ->icon('heroicon-o-shopping-cart')
            ->iconColor('success')
            ->duration(4000)
            ->send();
    }

    public function getSubtotal(): int
    {
    return (int) CartFacade::subtotal();
    }

    public function getShipping(): int
    {
        return 990;
    }

    public function getTotal(): int
    {
        return $this->getSubtotal() + $this->getShipping();
    }

    public function formatPrice(int $cents): string
    {
        return 'RM' . number_format($cents / 100, 2);
    }

    public function render()
    {
        return view('livewire.cart');
    }
}
