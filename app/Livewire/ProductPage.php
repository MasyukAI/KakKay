<?php

declare(strict_types=1);

namespace App\Livewire;

use AIArmada\Cart\Facades\Cart;
use AIArmada\Products\Models\Product;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.pages')]
final class ProductPage extends Component
{
    public Product $product;

    public int $quantity = 1;

    /** @var Collection<int, Product> */
    public Collection $relatedProducts;

    public function mount(string $slug): void
    {
        $this->product = Product::query()
            ->select(['id', 'name', 'slug', 'description', 'price', 'compare_price'])
            ->with(['categories:id,name'])
            ->where('slug', $slug)
            ->firstOrFail();

        $this->relatedProducts = Product::query()
            ->select(['id', 'name', 'slug', 'description', 'price'])
            ->where('slug', '!=', $slug)
            ->where('status', 'active')
            ->orderByDesc('is_featured')
            ->limit(4)
            ->get();
    }

    public function increaseQuantity(): void
    {
        if ($this->quantity < 9) {
            $this->quantity++;
        }
    }

    public function decreaseQuantity(): void
    {
        if ($this->quantity > 1) {
            $this->quantity--;
        }
    }

    public function addToCart(): void
    {
        $this->pushProductToCart();

        $this->redirect('/cart', navigate: true);
    }

    public function buyNow(): void
    {
        $this->pushProductToCart();

        $this->redirect('/checkout', navigate: true);
    }

    public function render(): View
    {
        return view('livewire.product-page')
            ->title($this->product->name);
    }

    private function pushProductToCart(): void
    {
        Cart::add(
            (string) $this->product->id,
            $this->product->name,
            $this->product->price,
            $this->quantity,
            [
                'slug' => $this->product->slug,
                'category' => $this->product->categories->first()->name ?? 'books',
            ]
        );

        Log::info('Cart add from product page', [
            'product_id' => (string) $this->product->id,
            'cart_id' => Cart::getId(),
            'cart_identifier' => Cart::getIdentifier(),
            'cart_instance' => Cart::instance(),
            'session_id' => session()->getId(),
            'items_count' => Cart::getItems()->count(),
        ]);

        Notification::make()
            ->title('Berjaya Ditambah!')
            ->body('Produk telah ditambah ke keranjang!')
            ->success()
            ->icon('heroicon-o-check-circle')
            ->iconColor('success')
            ->duration(3000)
            ->send();

        $this->dispatch('cart-updated');
    }
}
