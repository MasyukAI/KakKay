<?php

declare(strict_types=1);

namespace App\Livewire;

use AIArmada\Cart\Facades\Cart;
use AIArmada\Products\Models\Product;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.pages')]
final class ProductPage extends Component
{
    public Product $product;

    public function mount(string $slug): void
    {
        $product = Product::where('slug', $slug)->first();

        if (! $product) {
            abort(404);
        }

        $this->product = $product;
    }

    public function addToCart(): void
    {
        Cart::add(
            (string) $this->product->id,
            $this->product->name,
            $this->product->price,
            1,
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

        $this->redirect('/cart', navigate: true);
    }

    public function render(): View
    {
        return view('livewire.product-page')
            ->title($this->product->name);
    }
}
